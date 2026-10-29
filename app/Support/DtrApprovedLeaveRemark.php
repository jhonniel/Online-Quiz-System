<?php

namespace App\Support;

use App\Models\Dtr;

final class DtrApprovedLeaveRemark
{
    /**
     * @return array<string, array{label: string, bg: string, color: string}>
     */
    public static function pdfPalette(): array
    {
        return [
            'vacation_leave' => ['label' => 'Vacation Leave', 'bg' => '#DBEAFE', 'color' => '#1E40AF'],
            'sick_leave' => ['label' => 'Sick Leave', 'bg' => '#FEE2E2', 'color' => '#991B1B'],
            'work_from_home' => ['label' => 'Work From Home', 'bg' => '#E0E7FF', 'color' => '#3730A3'],
            'offset' => ['label' => 'Offset', 'bg' => '#FEF3C7', 'color' => '#92400E'],
            'absent' => ['label' => 'Absent', 'bg' => '#FECACA', 'color' => '#7F1D1D'],
            'travel' => ['label' => 'Travel', 'bg' => '#EDE9FE', 'color' => '#5B21B6'],
            'excused' => ['label' => 'Excused', 'bg' => '#F3E8FF', 'color' => '#6B21A8'],
            'additional_time' => ['label' => 'Additional Time', 'bg' => '#FFEDD5', 'color' => '#9A3412'],
            'holiday' => ['label' => 'Holiday', 'bg' => '#E0F2FE', 'color' => '#0C4A6E'],
            'default' => ['label' => 'Approved Leave', 'bg' => '#ECFDF5', 'color' => '#065F46'],
        ];
    }

    /**
     * @return list<array{label: string, bg: string, color: string}>
     */
    public static function pdfLegend(): array
    {
        $order = [
            'vacation_leave',
            'sick_leave',
            'work_from_home',
            'offset',
            'travel',
            'absent',
            'excused',
            'additional_time',
            'holiday',
            'default',
        ];
        $palette = self::pdfPalette();
        $legend = [];
        foreach ($order as $key) {
            if (isset($palette[$key])) {
                $legend[] = $palette[$key];
            }
        }

        return $legend;
    }

    public static function inferApprovedLeaveType(
        ?string $requestType,
        ?Dtr $dtr = null,
        ?string $remarks = null,
        bool $hasApprovedTravel = false
    ): ?string {
        if ($hasApprovedTravel || ($dtr !== null && ($dtr->status ?? '') === 'travel')) {
            return 'travel';
        }

        if ($requestType !== null && $requestType !== '' && $requestType !== 'overtime') {
            return $requestType;
        }

        if ($dtr !== null && ($dtr->status ?? '') === 'holiday') {
            return 'holiday';
        }

        if ($dtr !== null && ($dtr->status ?? '') === 'absent') {
            return 'absent';
        }

        $text = self::normalizeRemarksDisplay($remarks ?? (string) ($dtr->remarks ?? ''));
        if ($text === '') {
            if ($dtr !== null && ($dtr->status ?? '') === 'on_leave') {
                return 'default';
            }

            return null;
        }

        if (stripos($text, 'Approved: Work From Home') !== false || stripos($text, 'Work From Home') !== false) {
            return 'work_from_home';
        }
        if (stripos($text, 'Approved: Offset') !== false || stripos($text, 'Offset: Offset') !== false) {
            return 'offset';
        }
        if (preg_match('/Vacation Leave|Approved Leave:\s*Vacation/i', $text)) {
            return 'vacation_leave';
        }
        if (preg_match('/Sick Leave|Approved Leave:\s*Sick/i', $text)) {
            return 'sick_leave';
        }
        if (preg_match('/\bAbsent\b/i', $text)) {
            return 'absent';
        }
        if (preg_match('/\bTravel\b/i', $text)) {
            return 'travel';
        }
        if (preg_match('/Excused/i', $text)) {
            return 'excused';
        }
        if (preg_match('/Additional Time/i', $text)) {
            return 'additional_time';
        }
        if (preg_match('/Approved Leave:|Approved:/i', $text)) {
            return 'default';
        }

        return null;
    }

    public static function pdfRowStyle(?string $leaveType): string
    {
        if ($leaveType === null || $leaveType === '') {
            return '';
        }

        $palette = self::pdfPalette()[$leaveType] ?? self::pdfPalette()['default'];

        return 'background-color: '.$palette['bg'].'; color: '.$palette['color'].';';
    }

    public static function pdfRemarksCellStyle(?string $leaveType): string
    {
        if ($leaveType === null || $leaveType === '') {
            return '';
        }

        $palette = self::pdfPalette()[$leaveType] ?? self::pdfPalette()['default'];

        return 'background-color: '.$palette['bg'].'; color: '.$palette['color'].'; font-weight: 600; border-left: 3px solid '.$palette['color'].';';
    }
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
