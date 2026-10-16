<?php

namespace App\Support;

use App\Models\Dtr;
use Carbon\Carbon;

final class DtrEmployeeAttendance
{
    public const WEEKLY_BASE_HOURS = 40.0;

    /**
     * Daily total for attendance counting: actual time up to 8:00; excess is ignored (shown under Overtime).
     */
    public static function effectiveDailyTotal(Dtr $dtr): float
    {
        $worked = DtrAddedTimeFromRemarks::effectiveWorkedHours($dtr);
        $added = DtrAddedTimeFromRemarks::effectiveAddedHours($dtr);
        $offset = (float) ($dtr->offset_hours ?? 0);
        $total = $worked + $added + $offset;

        return min(max($total, 0.0), DtrRecordHours::STANDARD_DAY_HOURS);
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
