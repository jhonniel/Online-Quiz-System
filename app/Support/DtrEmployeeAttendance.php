<?php

namespace App\Support;

use App\Models\Dtr;
use Carbon\Carbon;

final class DtrEmployeeAttendance
{
    public const WEEKLY_BASE_HOURS = 40.0;

    public const STANDARD_DAY_MINUTES = 480;

    public const WEEKLY_BASE_MINUTES = 2400;

    /**
     * Leave column: offset leave hours, or vacation / sick leave credit (typically 08:00).
     */
    public static function displayedLeaveHours(Dtr $dtr): float
    {
        $offset = (float) ($dtr->offset_hours ?? 0);
        if ($offset > 0) {
            return $offset;
        }

        if (self::isOffsetLeave($dtr)) {
            $total = (float) ($dtr->total_hours ?? 0);

            return min(max($total, 0.0), DtrRecordHours::STANDARD_DAY_HOURS);
        }

        if (self::hasApprovedVacationOrSickLeave($dtr)) {
            return DtrRecordHours::STANDARD_DAY_HOURS;
        }

        if (($dtr->status ?? '') === 'on_leave') {
            $total = (float) ($dtr->total_hours ?? 0);

            return $total > 0
                ? min($total, DtrRecordHours::STANDARD_DAY_HOURS)
                : DtrRecordHours::STANDARD_DAY_HOURS;
        }

        return 0.0;
    }

    /**
     * Short label under Leave column (e.g. Offset, Vacation Leave).
     */
    public static function displayedLeaveTypeLabel(Dtr $dtr): ?string
    {
        if (self::isOffsetLeave($dtr)) {
            return 'Offset';
        }

        if (self::hasApprovedVacationOrSickLeave($dtr)) {
            $label = strtolower((string) ($dtr->leave_type_label ?? ''));
            $remarks = (string) ($dtr->remarks ?? '');
            if (str_contains($label, 'sick') || preg_match('/Sick Leave|Sick\b/i', $remarks)) {
                return 'Sick Leave';
            }

            return 'Vacation Leave';
        }

        if (($dtr->status ?? '') === 'on_leave') {
            return 'Leave';
        }

        return null;
    }

    public static function isOffsetLeave(Dtr $dtr): bool
    {
        if ((float) ($dtr->offset_hours ?? 0) > 0) {
            return true;
        }

        $remarks = (string) ($dtr->remarks ?? '');
        $label = strtolower((string) ($dtr->leave_type_label ?? ''));

        if ($label === 'offset' || str_contains($label, 'offset')) {
            return true;
        }

        return (bool) preg_match('/Leave:\s*Offset\b/i', $remarks)
            || (bool) preg_match('/Approved Leave:\s*Offset\b/i', $remarks)
            || (bool) preg_match('/\bOffset:\s*(Offset|Leave)\b/i', $remarks);
    }

    /**
     * Hubstaff column: day total minus added time minus leave (leave never double-counted here).
     */
    public static function displayedHubstaffHours(Dtr $dtr): float
    {
        $added = DtrAddedTimeFromRemarks::effectiveAddedHours($dtr);
        $leave = self::displayedLeaveHours($dtr);
        $total = self::rawEffectiveTotalHours($dtr);

        return max($total - $added - $leave, 0.0);
    }

    /**
     * Total Hours column: Hubstaff + Added Time + Leave, not capped at 8:00.
     */
    public static function displayedDailyTotal(Dtr $dtr): float
    {
        return max(self::rawEffectiveTotalHours($dtr), 0.0);
    }

    /**
     * Effective day total from Hubstaff + added + offset (stored total aligned with remarks).
     */
    public static function rawEffectiveTotalHours(Dtr $dtr): float
    {
        return max(
            DtrAddedTimeFromRemarks::effectiveWorkedHours($dtr)
            + DtrAddedTimeFromRemarks::effectiveAddedHours($dtr)
            + (float) ($dtr->offset_hours ?? 0),
            0.0
        );
    }

    public static function hasApprovedVacationOrSickLeave(Dtr $dtr): bool
    {
        if (self::isOffsetLeave($dtr)) {
            return false;
        }

        $remarks = (string) ($dtr->remarks ?? '');
        $label = strtolower((string) ($dtr->leave_type_label ?? ''));

        if (preg_match('/Approved\s+(Vacation Leave|Sick Leave)\b/i', $remarks)) {
            return true;
        }

        if (preg_match('/Approved Leave:\s*(Vacation Leave|Sick Leave|Vacation|Sick Leave|Sick)\b/i', $remarks)) {
            return true;
        }

        if (preg_match('/\b(vacation_leave|sick_leave|Vacation Leave|Sick Leave)\b/i', $remarks)) {
            return true;
        }

        if (($dtr->status ?? '') === 'on_leave') {
            if (str_contains($label, 'vacation') || str_contains($label, 'sick')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hours that count toward weekly/export totals (max 8:00 per day; time over 8:00 is excluded).
     */
    public static function effectiveDailyTotal(Dtr $dtr): float
    {
        return DtrRecordHours::countableDailyTotalHours(self::displayedDailyTotal($dtr));
    }

    /**
     * Total Hours column: day total capped at 08:00 (overtime shown separately).
     */
    public static function displayedTotalHoursForColumn(Dtr $dtr): float
    {
        $capMinutes = (int) round(DtrRecordHours::STANDARD_DAY_HOURS * 60);

        $added = DtrAddedTimeFromRemarks::effectiveAddedHours($dtr);
        $hubstaff = self::displayedHubstaffHours($dtr);
        $leave = self::displayedLeaveHours($dtr);

        $visibleSumMinutes = (int) round(($hubstaff + $added + $leave) * 60);
        $computedMinutes = (int) round(self::displayedDailyTotal($dtr) * 60);
        $storedMinutes = (int) round((float) ($dtr->total_hours ?? 0) * 60);

        $uncappedMinutes = max($visibleSumMinutes, $computedMinutes, $storedMinutes);

        return min($uncappedMinutes, $capMinutes) / 60.0;
    }

    public static function formatTotalHoursColumn(Dtr $dtr): string
    {
        return self::formatHoursDecimal(self::displayedTotalHoursForColumn($dtr));
    }

    /**
     * Daily deficit vs 08:00 when Total Hours for the day is under 8:00.
     */
    public static function displayedDailyDeficitHoursForColumn(Dtr $dtr): float
    {
        $deficitMinutes = self::dailyColumnDeficitMinutes($dtr);

        return $deficitMinutes / 60.0;
    }

    /**
     * Deficit vs 08:00 for the Total Hours column, in whole minutes.
     */
    public static function dailyColumnDeficitMinutes(Dtr $dtr): int
    {
        $totalMinutes = (int) round(self::displayedTotalHoursForColumn($dtr) * 60);

        if ($totalMinutes <= 0 || $totalMinutes >= self::STANDARD_DAY_MINUTES) {
            return 0;
        }

        return self::STANDARD_DAY_MINUTES - $totalMinutes;
    }

    public static function formatDailyDeficitForColumn(Dtr $dtr): ?string
    {
        $deficitMinutes = self::dailyColumnDeficitMinutes($dtr);
        if ($deficitMinutes <= 0) {
            return null;
        }

        return self::formatMinutesAsHours($deficitMinutes);
    }

    /**
     * Sum of {@see effectiveDailyTotal()} for each record on or before $today.
     */
    public static function sumCountableHoursForRecords(iterable $records, ?Carbon $today = null): float
    {
        return self::sumCountableMinutesForRecords($records, $today) / 60.0;
    }

    public static function sumCountableMinutesForRecords(iterable $records, ?Carbon $today = null): int
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $sumMinutes = 0;

        foreach ($records as $dtr) {
            if (! $dtr instanceof Dtr) {
                continue;
            }

            $day = Carbon::parse($dtr->date)->startOfDay();
            if ($day->gt($today)) {
                continue;
            }

            $sumMinutes += (int) round(self::effectiveDailyTotal($dtr) * 60);
        }

        return $sumMinutes;
    }

    public static function formatHoursDecimal(float $hours): string
    {
        return self::formatMinutesAsHours((int) round($hours * 60));
    }

    public static function formatMinutesAsHours(int $minutes): string
    {
        $minutes = max(0, $minutes);
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return sprintf('%02d:%02d', $h, $m);
    }

    /**
     * Sum Total Hours column values (capped at 08:00/day) in minutes for weekday records on or before $today.
     */
    public static function sumDisplayedColumnTotalMinutesForWeekdays(iterable $records, ?Carbon $today = null): int
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $sumMinutes = 0;

        foreach ($records as $dtr) {
            if (! $dtr instanceof Dtr) {
                continue;
            }

            $day = Carbon::parse($dtr->date)->startOfDay();
            if ($day->isWeekend() || $day->gt($today)) {
                continue;
            }

            $sumMinutes += (int) round(self::displayedTotalHoursForColumn($dtr) * 60);
        }

        return $sumMinutes;
    }

    /**
     * Sum capped daily totals for weekday records on or before $today.
     */
    public static function sumCountableWeekdayHours(iterable $records, ?Carbon $today = null): float
    {
        return self::sumCountableWeekdayMinutes($records, $today) / 60.0;
    }

    public static function sumCountableWeekdayMinutes(iterable $records, ?Carbon $today = null): int
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $sumMinutes = 0;

        foreach ($records as $dtr) {
            if (! $dtr instanceof Dtr) {
                continue;
            }

            $day = Carbon::parse($dtr->date)->startOfDay();
            if ($day->isWeekend() || $day->gt($today)) {
                continue;
            }

            $sumMinutes += (int) round(self::effectiveDailyTotal($dtr) * 60);
        }

        return $sumMinutes;
    }

    /**
     * Weekly attendance deficit vs 40:00 (Mon–Fri), using minute totals from the Total Hours column.
     */
    public static function weekAttendanceDeficitMinutes(iterable $records, ?Carbon $today = null): int
    {
        $sumMinutes = self::sumDisplayedColumnTotalMinutesForWeekdays($records, $today);

        return max(0, self::WEEKLY_BASE_MINUTES - $sumMinutes);
    }

    /**
     * Weekly attendance deficit vs 40:00 using capped weekday hours (same as weekly summary).
     */
    public static function weekAttendanceDeficitHours(iterable $records, ?Carbon $today = null): float
    {
        return self::weekAttendanceDeficitMinutes($records, $today) / 60.0;
    }

    public static function formatWeekAttendanceDeficit(iterable $records, ?Carbon $today = null): string
    {
        return self::formatMinutesAsHours(self::weekAttendanceDeficitMinutes($records, $today));
    }

    /**
     * Deficit for a date-range export: (each record × 8:00) − sum of capped daily totals.
     * Matches PDF "Total Deficit" (not the Mon–Fri 40:00 week rule).
     */
    public static function periodAttendanceDeficitHours(iterable $records, ?Carbon $today = null): float
    {
        return self::periodAttendanceDeficitMinutes($records, $today) / 60.0;
    }

    public static function periodAttendanceDeficitMinutes(iterable $records, ?Carbon $today = null): int
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $recordCount = 0;

        foreach ($records as $dtr) {
            if (! $dtr instanceof Dtr) {
                continue;
            }

            $day = Carbon::parse($dtr->date)->startOfDay();
            if ($day->gt($today)) {
                continue;
            }

            $recordCount++;
        }

        if ($recordCount === 0) {
            return 0;
        }

        $sumMinutes = self::sumDisplayedColumnTotalMinutesForRecords($records, $today);
        $expectedMinutes = $recordCount * self::STANDARD_DAY_MINUTES;

        return max(0, $expectedMinutes - $sumMinutes);
    }

    public static function sumDisplayedColumnTotalMinutesForRecords(iterable $records, ?Carbon $today = null): int
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $sumMinutes = 0;

        foreach ($records as $dtr) {
            if (! $dtr instanceof Dtr) {
                continue;
            }

            $day = Carbon::parse($dtr->date)->startOfDay();
            if ($day->gt($today)) {
                continue;
            }

            $sumMinutes += (int) round(self::displayedTotalHoursForColumn($dtr) * 60);
        }

        return $sumMinutes;
    }

    public static function isPeriodAttendanceLogComplete(iterable $records, ?Carbon $today = null): bool
    {
        return self::periodAttendanceDeficitHours($records, $today) < 0.001;
    }

    public static function isDayLogComplete(Dtr $dtr): bool
    {
        $status = (string) ($dtr->status ?? '');

        if ($status === 'absent') {
            return false;
        }

        if (in_array($status, ['holiday', 'on_leave', 'travel'], true)) {
            return true;
        }

        return self::effectiveDailyTotal($dtr) >= DtrRecordHours::STANDARD_DAY_HOURS - 0.001;
    }

    /**
     * True when every elapsed weekday in the week is complete and countable hours reach 40:00 (Mon–Fri).
     */
    public static function isWeekAttendanceLogComplete(iterable $records, Carbon $weekEnd, ?Carbon $today = null): bool
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $weekEnd = $weekEnd->copy()->startOfDay();

        $elapsedWeekdays = 0;

        foreach ($records as $dtr) {
            if (! $dtr instanceof Dtr) {
                continue;
            }

            $day = Carbon::parse($dtr->date)->startOfDay();
            if ($day->isWeekend() || $day->gt($today)) {
                continue;
            }

            $elapsedWeekdays++;

            if (! self::isDayLogComplete($dtr)) {
                return false;
            }
        }

        if ($elapsedWeekdays === 0) {
            return false;
        }

        $countableSum = self::sumCountableWeekdayHours($records, $today);

        if ($countableSum + 0.001 < self::WEEKLY_BASE_HOURS) {
            return false;
        }

        // Require full Mon–Fri set once the week has ended (no future weekdays left in that ISO week).
        if ($today->gt($weekEnd)) {
            $weekdayRows = 0;
            foreach ($records as $dtr) {
                if (! $dtr instanceof Dtr) {
                    continue;
                }
                $day = Carbon::parse($dtr->date)->startOfDay();
                if (! $day->isWeekend()) {
                    $weekdayRows++;
                }
            }

            return $weekdayRows >= 5;
        }

        return $elapsedWeekdays >= 5;
    }
}
