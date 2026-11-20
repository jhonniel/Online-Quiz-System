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
     * @param  float  $pendingRollbackHours  Hours the ranking treats as not-yet-credited
     *                                       (pending leave/additional-time resubmissions).
     * @return array{
     *     remaining_hours_completed: float,
     *     created_count: int,
     *     updated_count: int,
     *     entries: list<array{date: string, hours: float, action: string}>
     * }
     */
    public function completeForStudent(User $student, float $pendingRollbackHours = 0.0): array
    {
        if ($student->role !== 'student') {
            throw new \InvalidArgumentException('Selected user is not a student.');
        }

        $required = (float) ($student->required_training_hours ?? 0);
        if ($required <= 0) {
            throw new \InvalidArgumentException('Student has no required training hours set.');
        }

        $logged = (float) Dtr::query()->where('user_id', $student->id)->sum('total_hours');
        $effectiveLogged = max($logged - max($pendingRollbackHours, 0.0), 0.0);
        $remaining = round($required - $effectiveLogged, 2);

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
     * Keep topping up existing DTR rows (8 hrs per pass) until remaining hours are filled.
     * A single pass is not enough when the first→last DTR range is short.
     *
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
            ->get();

        if ($existing->isEmpty()) {
            return $hoursLeft;
        }

        // Safety: enough passes to place any remaining amount in 8-hour chunks.
        $maxPasses = max(1, (int) ceil($hoursLeft / 8.0) + $existing->count());

        for ($pass = 0; $pass < $maxPasses && $hoursLeft > 0.001; $pass++) {
            foreach ($existing->shuffle()->values() as $dtr) {
                if ($hoursLeft <= 0.001) {
                    break;
                }

                $add = round(min(8.0, $hoursLeft), 2);
                if ($add <= 0) {
                    continue;
                }

                $newTotal = round((float) $dtr->total_hours + $add, 2);
                $overtime = round(max(0, $newTotal - 8.0), 2);
                $remarks = trim((string) ($dtr->remarks ?? ''));
                if ($remarks === '') {
                    $remarks = self::REMARK;
                } elseif (! str_contains($remarks, self::REMARK)) {
                    $remarks .= "\n".self::REMARK;
                }

                $dtr->update([
                    'total_hours' => $newTotal,
                    'overtime_hours' => $overtime,
                    'remarks' => $remarks,
                ]);

                // Keep in-memory total in sync for subsequent passes.
                $dtr->total_hours = $newTotal;
                $dtr->overtime_hours = $overtime;
                $dtr->remarks = $remarks;

                $entries[] = [
                    'date' => Carbon::parse($dtr->date)->format('Y-m-d'),
                    'hours' => $add,
                    'action' => 'updated',
                ];
                $updatedCount++;
                $hoursLeft = round($hoursLeft - $add, 2);
            }
        }

        return $hoursLeft;
    }
}
