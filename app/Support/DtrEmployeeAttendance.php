<?php

namespace App\Support;

use App\Models\Dtr;
use Carbon\Carbon;

final class DtrEmployeeAttendance
{
    public const WEEKLY_BASE_HOURS = 40.0;

    /**
     * Leave column: offset leave hours, or vacation / sick leave credit (typically 08:00).
     */
    public static function displayedLeaveHours(Dtr $dtr): float
    {
        $offset = (float) ($dtr->offset_hours ?? 0);
        if ($offset > 0) {
            return $offset;
        }

        if (self::hasApprovedVacationOrSickLeave($dtr)) {
            return DtrRecordHours::STANDARD_DAY_HOURS;
        }

        return 0.0;
    }

    /**
     * Hubstaff column: tracked work time excluding leave / offset credit shown in Leave column.
     */
    public static function displayedHubstaffHours(Dtr $dtr): float
    {
        $worked = DtrAddedTimeFromRemarks::effectiveWorkedHours($dtr);
        $leave = self::displayedLeaveHours($dtr);

        if ($leave > 0 && self::hasApprovedVacationOrSickLeave($dtr)) {
            return max($worked - $leave, 0.0);
        }

        return max($worked, 0.0);
    }

    /**
     * Total Hours column: Hubstaff + Added Time + Leave, not capped at 8:00.
     */
    public static function displayedDailyTotal(Dtr $dtr): float
    {
        $added = DtrAddedTimeFromRemarks::effectiveAddedHours($dtr);

        return max(
            self::displayedHubstaffHours($dtr) + $added + self::displayedLeaveHours($dtr),
            0.0
        );
    }

    public static function hasApprovedVacationOrSickLeave(Dtr $dtr): bool
    {
        $remarks = (string) ($dtr->remarks ?? '');
        $label = strtolower((string) ($dtr->leave_type_label ?? ''));

        if (preg_match('/Approved\s+(Vacation Leave|Sick Leave)\b/i', $remarks)) {
            return true;
        }

        if (preg_match('/Approved Leave:\s*(Vacation Leave|Sick Leave|Vacation|Sick Leave|Sick)\b/i', $remarks)) {
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
     * Sum of {@see effectiveDailyTotal()} for each record on or before $today.
     */
    public static function sumCountableHoursForRecords(iterable $records, ?Carbon $today = null): float
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $sum = 0.0;

        foreach ($records as $dtr) {
            if (! $dtr instanceof Dtr) {
                continue;
            }

            $day = Carbon::parse($dtr->date)->startOfDay();
            if ($day->gt($today)) {
                continue;
            }

            $sum += self::effectiveDailyTotal($dtr);
        }

        return $sum;
    }

    public static function formatHoursDecimal(float $hours): string
    {
        $minutes = (int) round($hours * 60);
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return sprintf('%02d:%02d', $h, $m);
    }

    /**
     * Sum capped daily totals for weekday records on or before $today.
     */
    public static function sumCountableWeekdayHours(iterable $records, ?Carbon $today = null): float
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $sum = 0.0;

        foreach ($records as $dtr) {
            if (! $dtr instanceof Dtr) {
                continue;
            }

            $day = Carbon::parse($dtr->date)->startOfDay();
            if ($day->isWeekend() || $day->gt($today)) {
                continue;
            }

            $sum += self::effectiveDailyTotal($dtr);
        }

        return $sum;
    }

    /**
     * Weekly attendance deficit vs 40:00 using capped weekday hours (same as weekly summary).
     */
    public static function weekAttendanceDeficitHours(iterable $records, ?Carbon $today = null): float
    {
        $countableSum = self::sumCountableWeekdayHours($records, $today);

        return max(0.0, self::WEEKLY_BASE_HOURS - $countableSum);
    }

    /**
     * Deficit for a date-range export: (each record × 8:00) − sum of capped daily totals.
     * Matches PDF "Total Deficit" (not the Mon–Fri 40:00 week rule).
     */
    public static function periodAttendanceDeficitHours(iterable $records, ?Carbon $today = null): float
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
            return 0.0;
        }

        $sum = self::sumCountableHoursForRecords($records, $today);
        $expected = $recordCount * DtrRecordHours::STANDARD_DAY_HOURS;

        return max(0.0, $expected - $sum);
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
