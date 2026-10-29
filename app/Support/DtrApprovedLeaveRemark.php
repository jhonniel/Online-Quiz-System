<?php

namespace App\Support;

final class DtrApprovedLeaveRemark
{
    /**
     * Remark line stored on DTR rows when an approved request is linked (admin index / import).
     */
    public static function storedRemark(string $leaveType, ?string $typeLabel = null): string
    {
        if ($leaveType === 'work_from_home') {
            return 'Approved: Work From Home';
        }

        if ($leaveType === 'offset') {
            return 'Approved: Offset';
        }

        $label = trim($typeLabel ?? '') !== ''
            ? trim($typeLabel)
            : ucfirst(str_replace('_', ' ', $leaveType));

        return 'Approved Leave: '.$label;
    }

    /**
     * Display line for PDF / UI (WFH omits the word "Leave").
     */
    public static function displayRemark(string $leaveType, ?string $typeLabel = null): string
    {
        return self::storedRemark($leaveType, $typeLabel);
    }

    /**
     * Legacy remark prefixes still present in older DTR rows (for de-duplication).
     *
     * @return list<string>
     */
    public static function equivalentRemarkPhrases(string $leaveType, ?string $typeLabel = null): array
    {
        $current = self::displayRemark($leaveType, $typeLabel);
        $phrases = [$current];

        if ($leaveType === 'work_from_home') {
            $phrases[] = 'Approved Leave: Work From Home';
            $phrases[] = 'Leave: Work From Home';
        }

        if ($leaveType === 'offset') {
            $phrases[] = 'Approved Leave: Offset';
            $phrases[] = 'Leave: Offset';
        }

        return array_values(array_unique($phrases));
    }

    public static function offsetCategoryRemark(): string
    {
        return 'Offset: Offset';
    }

    public static function remarksIncludePhrase(string $remarks, string $leaveType, ?string $typeLabel = null): bool
    {
        foreach (self::equivalentRemarkPhrases($leaveType, $typeLabel) as $phrase) {
            if (stripos($remarks, $phrase) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Show WFH without the word "Leave" (including legacy remark text).
     */
    public static function normalizeRemarksDisplay(?string $remarks): string
    {
        $text = trim((string) $remarks);
        if ($text === '') {
            return '';
        }

        $text = str_ireplace('Approved Leave: Work From Home', 'Approved: Work From Home', $text);
        $text = str_ireplace('Leave: Work From Home', 'Approved: Work From Home', $text);
        $text = str_ireplace('Approved Leave: Offset', 'Approved: Offset', $text);
        $text = str_ireplace('Leave: Offset', 'Approved: Offset', $text);
        $text = str_ireplace('Offset: Offset | Offset: Offset', 'Offset: Offset | Approved: Offset', $text);

        return $text;
    }

    /**
     * Persist normalized remark text (e.g. after loading legacy DTR rows).
     */
    public static function normalizeRemarksForStorage(?string $remarks): string
    {
        return self::normalizeRemarksDisplay($remarks);
    }
}
