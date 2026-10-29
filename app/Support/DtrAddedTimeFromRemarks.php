<?php

namespace App\Support;

use App\Helpers\SmartTimeParser;
use App\Models\Dtr;

final class DtrAddedTimeFromRemarks
{
    private const REJECTED_REMARK_TOKEN = 'Added time from note rejected';

    /**
     * Decimal hours parsed from free-text remarks (same rules as CSV import).
     */
    public static function decimalFromRemarks(?string $remarks): float
    {
        $text = trim((string) $remarks);
        if ($text === '') {
            return 0.0;
        }

        return max(0.0, SmartTimeParser::parse($text));
    }

    /**
     * Byte ranges of the remark phrases counted by {@see decimalFromRemarks()}.
     *
     * @return list<array{0: int, 1: int}> [offset, length]
     */
    public static function spansInRemarks(?string $remarks): array
    {
        return SmartTimeParser::durationSpans($remarks);
    }

    /**
     * Optional HH:MM (or smart text) from the Added Time column, when not already only in remarks.
     */
    public static function decimalFromAddedColumn(?string $value): float
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '00:00' || $value === '0') {
            return 0.0;
        }

        if (str_contains($value, ':')) {
            [$h, $m] = explode(':', $value, 2);

            return max(0.0, (int) $h + ((int) $m / 60));
        }

        return max(0.0, SmartTimeParser::parse($value));
    }

    /**
     * Match import: time in the Added Time column plus any duration mentioned in remarks.
     */
    public static function resolveAddedHours(?string $remarks, ?string $addedColumnValue = null): float
    {
        return self::decimalFromRemarks($remarks) + self::decimalFromAddedColumn($addedColumnValue);
    }

    /**
     * Added time shown on lists — prefers live parse from remarks unless admin rejected remark credit.
     */
    public static function effectiveAddedHours(Dtr $dtr): float
    {
        $stored = (float) ($dtr->added_time_from_note ?? 0);
        $remarks = (string) ($dtr->remarks ?? '');

        if (
            $stored <= 0
            && stripos($remarks, self::REJECTED_REMARK_TOKEN) !== false
        ) {
            return 0.0;
        }

        $fromRemarks = self::decimalFromRemarks($remarks);
        if ($fromRemarks > 0) {
            return $fromRemarks;
        }

        return $stored;
    }

    public static function effectiveWorkedHours(Dtr $dtr): float
    {
        $offset = (float) ($dtr->offset_hours ?? 0);
        $total = (float) ($dtr->total_hours ?? 0);
        $added = self::effectiveAddedHours($dtr);

        return max($total - $added - $offset, 0.0);
    }

    /**
     * Persist added time from remarks and recalculate total (keeps current worked + offset).
     */
    public static function syncStoredAddedTimeFromRemarks(Dtr $dtr, ?string $addedColumnValue = null): void
    {
        $remarks = (string) ($dtr->remarks ?? '');
        if (
            stripos($remarks, self::REJECTED_REMARK_TOKEN) !== false
            && (float) ($dtr->added_time_from_note ?? 0) <= 0
            && ($addedColumnValue === null || trim((string) $addedColumnValue) === '')
        ) {
            return;
        }

        $added = self::resolveAddedHours($remarks, $addedColumnValue);
        $worked = DtrRecordHours::workedDecimal($dtr);
        DtrRecordHours::setWorkedAndAdded($dtr, $worked, $added);
    }
}
