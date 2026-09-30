<?php

namespace App\Support;

use App\Models\Dtr;
use App\Models\User;
use App\Services\StudentOjtPostCompletionService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class StudentRemainingTimeCompletion
{
    private const REMARK = 'Remaining time completed by admin (auto-backfill).';

    public function __construct(
        private StudentOjtPostCompletionService $studentOjtPostCompletionService
    ) {}

    /**
     * Insert past DTR rows on random days between the student's first and last DTR date
     * until required training hours are met.
     *
     * @return array{
     *     remaining_hours_completed: float,
     *     created_count: int,
     *     updated_count: int,
     *     entries: list<array{date: string, hours: float, action: string}>
     * }
     */
    public function completeForStudent(User $student): array
    {
        if ($student->role !== 'student') {
            throw new \InvalidArgumentException('Selected user is not a student.');
        }

        $required = (float) ($student->required_training_hours ?? 0);
        if ($required <= 0) {
            throw new \InvalidArgumentException('Student has no required training hours set.');
        }

        $logged = (float) Dtr::query()->where('user_id', $student->id)->sum('total_hours');
        $remaining = round($required - $logged, 2);

        if ($remaining <= 0) {
            throw new \InvalidArgumentException('Student has no remaining time needed.');
        }

        $bounds = Dtr::query()
            ->where('user_id', $student->id)
            ->selectRaw('MIN(date) as start_date, MAX(date) as end_date')
            ->first();

        if (! $bounds?->start_date || ! $bounds?->end_date) {
            throw new \InvalidArgumentException('Student has no DTR records. Add at least one DTR entry before completing remaining time.');
        }

        $start = Carbon::parse($bounds->start_date)->startOfDay();
        $end = Carbon::parse($bounds->end_date)->startOfDay();

        $existingDateKeys = Dtr::query()
            ->where('user_id', $student->id)
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->flip();

        $emptyDates = collect();
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->format('Y-m-d');
            if (! $existingDateKeys->has($key)) {
                $emptyDates->push($date->copy());
            }
        }

        /** @var list<array{date: string, hours: float, action: string}> $entries */
        $entries = [];
        $createdCount = 0;
        $updatedCount = 0;
        $hoursLeft = $remaining;

        DB::transaction(function () use ($student, $emptyDates, $start, $end, &$entries, &$createdCount, &$updatedCount, &$hoursLeft) {
            $hoursLeft = $this->insertOnRandomEmptyDates($student, $emptyDates, $hoursLeft, $entries, $createdCount);

            if ($hoursLeft > 0.001) {
                $hoursLeft = $this->addHoursToRandomExistingDates($student, $start, $end, $hoursLeft, $entries, $updatedCount);
            }
        });

        if ($hoursLeft > 0.001) {
            throw new \RuntimeException('Could not allocate all remaining hours within the student DTR date range.');
        }

        $this->studentOjtPostCompletionService->syncForStudentId((int) $student->id, false);

        return [
            'remaining_hours_completed' => $remaining,
            'created_count' => $createdCount,
            'updated_count' => $updatedCount,
            'entries' => $entries,
        ];
    }

    /**
     * @param  list<array{date: string, hours: float, action: string}>  $entries
     */
    private function insertOnRandomEmptyDates(
        User $student,
        Collection $emptyDates,
        float $hoursLeft,
        array &$entries,
        int &$createdCount
    ): float {
        foreach ($emptyDates->shuffle()->values() as $date) {
            if ($hoursLeft <= 0.001) {
                break;
            }

            $dayHours = round(min(8.0, $hoursLeft), 2);
            $overtime = round(max(0, $dayHours - 8.0), 2);

            Dtr::create([
                'user_id' => $student->id,
                'date' => $date->toDateString(),
                'total_hours' => $dayHours,
                'overtime_hours' => $overtime,
                'added_time_from_note' => 0,
                'status' => 'present',
                'remarks' => self::REMARK,
            ]);

            $entries[] = [
                'date' => $date->format('Y-m-d'),
                'hours' => $dayHours,
                'action' => 'created',
            ];
            $createdCount++;
            $hoursLeft = round($hoursLeft - $dayHours, 2);
        }

        return $hoursLeft;
    }

    /**
     * @param  list<array{date: string, hours: float, action: string}>  $entries
     */
    private function addHoursToRandomExistingDates(
        User $student,
        Carbon $start,
        Carbon $end,
        float $hoursLeft,
        array &$entries,
        int &$updatedCount
    ): float {
        $existing = Dtr::query()
            ->where('user_id', $student->id)
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->inRandomOrder()
            ->get();

        foreach ($existing as $dtr) {
            if ($hoursLeft <= 0.001) {
                break;
            }

            $add = round(min(8.0, $hoursLeft), 2);
            $newTotal = round((float) $dtr->total_hours + $add, 2);
            $overtime = round(max(0, $newTotal - 8.0), 2);
            $remarks = trim((string) ($dtr->remarks ?? ''));

            $dtr->update([
                'total_hours' => $newTotal,
                'overtime_hours' => $overtime,
                'remarks' => $remarks === '' ? self::REMARK : $remarks."\n".self::REMARK,
            ]);

            $entries[] = [
                'date' => Carbon::parse($dtr->date)->format('Y-m-d'),
                'hours' => $add,
                'action' => 'updated',
            ];
            $updatedCount++;
            $hoursLeft = round($hoursLeft - $add, 2);
        }

        return $hoursLeft;
    }
}
