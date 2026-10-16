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
