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
            ->with('exclusions:id,dtr_holiday_id,user_id')
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
                ->with('exclusions:id,dtr_holiday_id,user_id')
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

        if ($record && $holiday->excludesUser($record->user_id ? (int) $record->user_id : null)) {
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
        // Excluded employee with no Hubstaff time (and not on leave/travel): ABSENT for that holiday.
        if ($holiday
            && $holiday->type === DtrHoliday::TYPE_REGULAR
            && $holiday->excludesUser($record->user_id ? (int) $record->user_id : null)
            && ! self::recordHasImportedWorkTime($record)
            && ! in_array((string) ($record->status ?? ''), ['on_leave', 'travel'], true)) {
            $record->status = 'absent';
            $remark = self::excludedFromHolidayRemark($holiday);
            $existing = (string) ($record->remarks ?? '');
            if (! str_contains($existing, $remark)) {
                $record->remarks = trim($remark.' '.$existing);
            }

            return false;
        }

        if (! self::shouldApplyRegularHolidayCredit($record, $holiday)) {
            return false;
        }

        self::applyRegularHolidayToRecord($record, $holiday);

        return true;
    }

    /**
     * DTR export: a regular holiday is a completed 8:00 day credited under Leave/Offset, remarked with the
     * holiday name. Hubstaff and added time stay in their own columns. In-memory only (never saved).
     * Skipped when the employee is excluded from the holiday or is on leave/travel that day.
     */
    public static function applyRegularHolidayAsLeaveCredit(Dtr $record, ?DtrHoliday $holiday): void
    {
        if (! $holiday || $holiday->type !== DtrHoliday::TYPE_REGULAR) {
            return;
        }
        if ($holiday->excludesUser($record->user_id ? (int) $record->user_id : null)) {
            return;
        }
        if (in_array((string) ($record->status ?? ''), ['on_leave', 'travel'], true)) {
            return;
        }

        // status "holiday" = the 8h credit was applied because there was no Hubstaff time.
        $hasOwnTime = ($record->status ?? '') !== 'holiday';
        $added = $hasOwnTime ? DtrAddedTimeFromRemarks::effectiveAddedHours($record) : 0.0;
        $worked = $hasOwnTime ? DtrAddedTimeFromRemarks::effectiveWorkedHours($record) : 0.0;
        $name = trim((string) $holiday->name);

        $record->added_time_from_note = $added;
        $record->offset_hours = self::STANDARD_HOURS;
        $record->total_hours = $worked + $added + self::STANDARD_HOURS;
        $record->status = 'present';
        $record->remarks = $name !== '' ? $name : 'Regular Holiday';
        $record->setRelation('regularHolidayCredit', $holiday);
    }

    public static function hasRegularHolidayCredit(Dtr $record): bool
    {
        return $record->relationLoaded('regularHolidayCredit') && $record->getRelation('regularHolidayCredit') !== null;
    }

    public static function applyRegularHolidayToRecord(Dtr $record, DtrHoliday $holiday): void
    {
        $entry = self::syntheticHolidayEntry(
            $record->date instanceof Carbon ? $record->date : Carbon::parse($record->date),
            $holiday,
            $record->user_id ? (int) $record->user_id : null
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
    public static function syntheticHolidayEntry(Carbon $day, ?DtrHoliday $holiday = null, ?int $userId = null): ?array
    {
        $holiday ??= self::findForDate($day);

        if (! $holiday || $holiday->type !== DtrHoliday::TYPE_REGULAR) {
            return null;
        }

        if ($holiday->excludesUser($userId)) {
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

    public static function excludedFromHolidayRemark(DtrHoliday $holiday): string
    {
        $name = trim((string) $holiday->name);

        return 'Excluded from holiday'.($name !== '' ? ': '.$name : '').' (no Hubstaff time on this date).';
    }

    /**
     * Attributes for a weekday with no DTR entry: HOLIDAY (8h), or ABSENT — including when the
     * employee was excluded from that day's holiday.
     *
     * @return array{total_hours: float|int, overtime_hours: int, status: string, remarks: string}
     */
    public static function missingDayAttributes(Carbon $day, ?DtrHoliday $holiday, ?int $userId): array
    {
        $holidayEntry = self::syntheticHolidayEntry($day, $holiday, $userId);
        if ($holidayEntry !== null) {
            return $holidayEntry;
        }

        $excluded = $holiday
            && $holiday->type === DtrHoliday::TYPE_REGULAR
            && $holiday->excludesUser($userId);

        return [
            'total_hours' => 0,
            'overtime_hours' => 0,
            'status' => 'absent',
            'remarks' => $excluded ? self::excludedFromHolidayRemark($holiday) : self::absentRemark(),
        ];
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
