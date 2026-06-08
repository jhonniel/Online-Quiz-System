<?php

namespace App\Support;

use App\Models\Dtr;
use App\Models\DtrHoliday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

final class DtrHolidayCalendar
{
    public const STANDARD_HOURS = 8.0;

    public static function dateKey(Carbon|string $date): string
    {
        return $date instanceof Carbon
            ? $date->format('Y-m-d')
            : Carbon::parse($date)->format('Y-m-d');
    }

    public static function findForDate(Carbon|string $date): ?DtrHoliday
    {
        return DtrHoliday::query()
            ->whereDate('date', self::dateKey($date))
            ->first();
    }

    public static function isHoliday(Carbon|string $date): bool
    {
        return self::findForDate($date) !== null;
    }

    /**
     * @return array<string, DtrHoliday>
     */
    public static function mapForRange(Carbon $from, Carbon $to): array
    {
        $map = [];

        foreach (
            DtrHoliday::query()
                ->whereDate('date', '>=', $from->toDateString())
                ->whereDate('date', '<=', $to->toDateString())
                ->get() as $holiday
        ) {
            $map[$holiday->date->format('Y-m-d')] = $holiday;
        }

        return $map;
    }

    public static function forMonth(Carbon $month): Collection
    {
        return DtrHoliday::query()
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->orderBy('date')
            ->get();
    }

    public static function recordHasImportedWorkTime(?Dtr $record): bool
    {
        if ($record === null) {
            return false;
        }

        if (! $record->exists && empty($record->id)) {
            return false;
        }

        return (float) ($record->total_hours ?? 0) > 0;
    }

    public static function shouldApplyRegularHolidayCredit(?Dtr $record, ?DtrHoliday $holiday): bool
    {
        if (! $holiday || $holiday->type !== DtrHoliday::TYPE_REGULAR) {
            return false;
        }

        if (self::recordHasImportedWorkTime($record)) {
            return false;
        }

        if ($record && in_array((string) ($record->status ?? ''), ['on_leave', 'travel'], true)) {
            return false;
        }

        return true;
    }

    public static function applyRegularHolidayToRecordIfEligible(Dtr $record, ?DtrHoliday $holiday): bool
    {
        if (! self::shouldApplyRegularHolidayCredit($record, $holiday)) {
            return false;
        }

        self::applyRegularHolidayToRecord($record, $holiday);

        return true;
    }

    public static function applyRegularHolidayToRecord(Dtr $record, DtrHoliday $holiday): void
    {
        $entry = self::syntheticHolidayEntry(
            $record->date instanceof Carbon ? $record->date : Carbon::parse($record->date),
            $holiday
        );

        if ($entry === null) {
            return;
        }

        $record->total_hours = $entry['total_hours'];
        $record->overtime_hours = $entry['overtime_hours'];
        $record->status = $entry['status'];
        $record->remarks = $entry['remarks'];
    }

    /**
     * @param  iterable<Dtr>  $dtrs
     * @param  array<string, DtrHoliday>  $holidayMap
     */
    public static function applyRegularHolidayCreditsToCollection(iterable $dtrs, array $holidayMap): void
    {
        foreach ($dtrs as $dtr) {
            if (! $dtr->exists && empty($dtr->id)) {
                continue;
            }

            $dateKey = self::dateKey($dtr->date);
            self::applyRegularHolidayToRecordIfEligible($dtr, $holidayMap[$dateKey] ?? null);
        }
    }

    /**
     * @return array{total_hours: float, overtime_hours: float, status: string, remarks: string}|null
     */
    public static function syntheticHolidayEntry(Carbon $day, ?DtrHoliday $holiday = null): ?array
    {
        $holiday ??= self::findForDate($day);

        if (! $holiday || $holiday->type !== DtrHoliday::TYPE_REGULAR) {
            return null;
        }

        $name = trim((string) $holiday->name);
        $remarks = $name !== '' ? $name : 'Regular Holiday';

        return [
            'total_hours' => self::STANDARD_HOURS,
            'overtime_hours' => 0,
            'status' => 'holiday',
            'remarks' => $remarks,
        ];
    }

    public static function absentRemark(): string
    {
        return 'Auto-labeled absent (no DTR entry for this employee on this date).';
    }

    /**
     * @return list<array{date: Carbon, holiday: ?DtrHoliday}>
     */
    public static function buildMonthWeeks(Carbon $currentMonth): array
    {
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();
        $startOfCalendar = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);
        $holidayMap = self::mapForRange($startOfCalendar, $endOfCalendar);

        $days = [];
        foreach (CarbonPeriod::create($startOfCalendar, $endOfCalendar) as $date) {
            $key = $date->toDateString();
            $days[$key] = [
                'date' => $date->copy(),
                'holiday' => $holidayMap[$key] ?? null,
            ];
        }

        $weeks = [];
        $week = [];
        foreach ($days as $day) {
            $week[] = $day;
            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }

        if ($week !== []) {
            $weeks[] = $week;
        }

        return $weeks;
    }
}
