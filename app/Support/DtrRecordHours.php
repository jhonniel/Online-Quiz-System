<?php

namespace App\Support;

use App\Models\Dtr;

class DtrRecordHours
{
    public const STANDARD_DAY_HOURS = 8.0;

    public static function workedDecimal(?Dtr $dtr): float
    {
        if (! $dtr) {
            return 0.0;
        }

        return max(
            (float) ($dtr->total_hours ?? 0)
            - (float) ($dtr->added_time_from_note ?? 0)
            - (float) ($dtr->offset_hours ?? 0),
            0.0
        );
    }

    public static function composeTotal(float $worked, float $added, float $offset): float
    {
        return $worked + $added + $offset;
    }

    public static function overtimeFromTotal(float $total): float
    {
        return $total > self::STANDARD_DAY_HOURS
            ? $total - self::STANDARD_DAY_HOURS
            : 0.0;
    }

    /**
     * Hours that count toward weekly base (max 8:00 per day); excess is tracked as overtime only.
     */
    public static function countableDailyTotalHours(float $total): float
    {
        return min(max($total, 0.0), self::STANDARD_DAY_HOURS);
    }

    /**
     * Split legacy offset-only rows (offset baked into total_hours) into offset_hours + zero worked.
     */
    public static function ensureLegacyOffsetPlaceholderSplit(Dtr $dtr): void
    {
        if ((float) ($dtr->offset_hours ?? 0) > 0) {
            return;
        }

        $remarks = (string) ($dtr->remarks ?? '');
        if (! preg_match('/\bOffset\b/i', $remarks)) {
            return;
        }

        $added = (float) ($dtr->added_time_from_note ?? 0);
        $total = (float) ($dtr->total_hours ?? 0);
        $offset = max($total - $added, 0.0);
        if ($offset <= 0) {
            return;
        }

        $dtr->offset_hours = $offset;
        $dtr->total_hours = self::composeTotal(0.0, $added, $offset);
        $dtr->overtime_hours = self::overtimeFromTotal((float) $dtr->total_hours);
    }

    public static function applyWorkedAndAdded(Dtr $dtr, float $workedDelta, float $addedDelta): void
    {
        self::ensureLegacyOffsetPlaceholderSplit($dtr);

        $offset = (float) ($dtr->offset_hours ?? 0);
        $worked = self::workedDecimal($dtr) + $workedDelta;
        $added = (float) ($dtr->added_time_from_note ?? 0) + $addedDelta;
        $total = self::composeTotal($worked, $added, $offset);

        $dtr->added_time_from_note = $added;
        $dtr->total_hours = $total;
        $dtr->overtime_hours = self::overtimeFromTotal($total);
    }

    public static function setWorkedAndAdded(Dtr $dtr, float $worked, float $added): void
    {
        $offset = (float) ($dtr->offset_hours ?? 0);
        $total = self::composeTotal($worked, $added, $offset);

        $dtr->added_time_from_note = $added;
        $dtr->total_hours = $total;
        $dtr->overtime_hours = self::overtimeFromTotal($total);
    }

    public static function addOffset(Dtr $dtr, float $offsetDelta): void
    {
        if ($offsetDelta <= 0) {
            return;
        }

        self::ensureLegacyOffsetPlaceholderSplit($dtr);

        $offset = (float) ($dtr->offset_hours ?? 0) + $offsetDelta;
        $worked = self::workedDecimal($dtr);
        $added = (float) ($dtr->added_time_from_note ?? 0);
        $total = self::composeTotal($worked, $added, $offset);

        $dtr->offset_hours = $offset;
        $dtr->total_hours = $total;
        $dtr->overtime_hours = self::overtimeFromTotal($total);
    }

    public static function subtractOffset(Dtr $dtr, float $offsetDelta): void
    {
        if ($offsetDelta <= 0) {
            return;
        }

        $offset = max((float) ($dtr->offset_hours ?? 0) - $offsetDelta, 0.0);
        $worked = self::workedDecimal($dtr);
        $added = (float) ($dtr->added_time_from_note ?? 0);
        $total = self::composeTotal($worked, $added, $offset);

        $dtr->offset_hours = $offset;
        $dtr->total_hours = $total;
        $dtr->overtime_hours = self::overtimeFromTotal($total);
    }
}
