<?php

namespace App\Support;

use App\Models\Dtr;
use App\Models\LeaveRequest;
use Illuminate\Support\Carbon;

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

    public static function isLeaveRequestNewer(LeaveRequest $candidate, LeaveRequest $existing): bool
    {
        $candidateAt = self::leaveRequestSortTimestamp($candidate);
        $existingAt = self::leaveRequestSortTimestamp($existing);

        if ($candidateAt->gt($existingAt)) {
            return true;
        }
        if ($candidateAt->lt($existingAt)) {
            return false;
        }

        return $candidate->id > $existing->id;
    }

    /**
     * @param  array<int, array<string, LeaveRequest>>  $map
     */
    public static function assignLatestLeaveRequestForDate(array &$map, int $userId, string $dateKey, LeaveRequest $leave): void
    {
        if (! isset($map[$userId][$dateKey]) || self::isLeaveRequestNewer($leave, $map[$userId][$dateKey])) {
            $map[$userId][$dateKey] = $leave;
        }
    }

    /**
     * Latest approved leave request per date for one employee (excludes travel and overtime).
     *
     * @return array<string, LeaveRequest>
     */
    public static function approvedLeaveRequestMapForUser(
        int $userId,
        Carbon $dateFrom,
        Carbon $dateTo,
        bool $excludeWeekends = false
    ): array {
        $map = [];
        $approvedLeaves = LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->where('type', '!=', 'overtime')
            ->where('type', '!=', 'travel')
            ->whereDate('start_date', '<=', $dateTo->toDateString())
            ->where(function ($q) use ($dateFrom) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $dateFrom->toDateString());
            })
            ->get();

        foreach ($approvedLeaves as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = $leave->end_date ? Carbon::parse($leave->end_date) : $start->copy();
            $period = new \Carbon\CarbonPeriod($start, $end);
            foreach ($period as $day) {
                if ($day->lt($dateFrom) || $day->gt($dateTo)) {
                    continue;
                }
                if ($excludeWeekends && $day->isWeekend()) {
                    continue;
                }
                self::assignLatestLeaveRequestForDate($map, $userId, $day->format('Y-m-d'), $leave);
            }
        }

        return $map[$userId] ?? [];
    }

    public static function stripApprovedLeaveLabels(?string $remarks): string
    {
        $text = self::normalizeRemarksDisplay($remarks);
        if ($text === '') {
            return '';
        }

        $segments = preg_split('/\s*\|\s*/', $text) ?: [];
        $kept = [];
        foreach ($segments as $segment) {
            $segment = trim((string) $segment);
            if ($segment === '') {
                continue;
            }
            if (self::extractRemarkLabel($segment) !== null) {
                continue;
            }
            $kept[] = $segment;
        }

        return implode(' | ', $kept);
    }

    public static function mergeRemarksWithLatestLeaveRequest(?string $remarks, ?LeaveRequest $leaveRequest): string
    {
        $base = self::stripApprovedLeaveLabels($remarks);
        if (! $leaveRequest instanceof LeaveRequest || ! $leaveRequest->isApproved() || $leaveRequest->type === 'overtime') {
            return trim($base);
        }

        if ($leaveRequest->type === 'travel') {
            return trim($base);
        }

        $leaveType = (string) $leaveRequest->type;
        $typeLabel = $leaveRequest->type_label ?? null;
        $labelParts = [];

        if ($leaveType === 'offset') {
            $labelParts[] = self::offsetCategoryRemark();
        }

        $labelParts[] = self::displayRemark($leaveType, $typeLabel);

        $labelSegment = implode(' | ', $labelParts);

        if ($base === '') {
            return $labelSegment;
        }

        return $base.' | '.$labelSegment;
    }

    private static function leaveRequestSortTimestamp(LeaveRequest $leaveRequest): Carbon
    {
        $value = $leaveRequest->reviewed_at ?? $leaveRequest->updated_at ?? $leaveRequest->created_at;

        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }

    public static function pdfLabelSpanStyle(?string $leaveType): string
    {
        if ($leaveType === null || $leaveType === '') {
            return '';
        }

        $palette = self::pdfPalette()[$leaveType] ?? self::pdfPalette()['default'];

        return 'color: '.$palette['color'].'; font-weight: 600;';
    }

    public static function pdfRemarkLabelHtml(string $leaveType, ?string $typeLabel = null): string
    {
        $label = self::displayRemark($leaveType, $typeLabel);
        $style = self::pdfLabelSpanStyle($leaveType);

        return '<span style="'.$style.'">'.e($label).'</span>';
    }

    public static function pdfFixedLabelHtml(string $labelText, string $leaveType): string
    {
        $style = self::pdfLabelSpanStyle($leaveType);

        return '<span style="'.$style.'">'.e($labelText).'</span>';
    }

    /**
     * Color only approved-leave label phrases in remarks; other text stays default.
     */
    public static function pdfRemarksHtml(?string $remarks): string
    {
        $text = self::normalizeRemarksDisplay($remarks);
        if ($text === '') {
            return '-';
        }

        $segments = preg_split('/\s*\|\s*/', $text) ?: [];
        $htmlSegments = [];
        foreach ($segments as $segment) {
            $segment = trim((string) $segment);
            if ($segment === '') {
                continue;
            }
            $htmlSegments[] = self::pdfHighlightRemarkSegment($segment);
        }

        if ($htmlSegments === []) {
            return '-';
        }

        return implode(' | ', $htmlSegments);
    }

    private static function pdfHighlightRemarkSegment(string $segment): string
    {
        $labelInfo = self::extractRemarkLabel($segment);
        if ($labelInfo === null) {
            return e($segment);
        }

        $style = self::pdfLabelSpanStyle($labelInfo['type']);
        $label = $labelInfo['label'];
        $labelLength = strlen($label);
        $rest = substr($segment, $labelLength);

        return '<span style="'.$style.'">'.e($label).'</span>'.e($rest);
    }

    /**
     * @return array{type: string, label: string}|null
     */
    private static function extractRemarkLabel(string $segment): ?array
    {
        $segment = trim($segment);
        if ($segment === '') {
            return null;
        }

        if (strcasecmp($segment, 'Travel') === 0) {
            return ['type' => 'travel', 'label' => $segment];
        }

        if (preg_match('/^(Travel Leave)(.*)$/i', $segment, $matches)) {
            return ['type' => 'travel', 'label' => $matches[1]];
        }

        if (preg_match('/^(Travel:\s*.+?)(?:\s*\(Approved:|\s+-|\s*$)/i', $segment, $matches)) {
            return ['type' => 'travel', 'label' => trim($matches[1])];
        }

        if (preg_match('/^(Approved Travel Leave)(.*)$/i', $segment, $matches)) {
            return ['type' => 'travel', 'label' => $matches[1]];
        }

        if (preg_match('/^(Approved\s+(?:Vacation Leave|Sick Leave))(?:\s*\(|$)/i', $segment, $matches)) {
            $label = trim($matches[1]);
            $type = stripos($label, 'Sick') !== false ? 'sick_leave' : 'vacation_leave';

            return ['type' => $type, 'label' => $label];
        }

        if (preg_match('/^(Travel Leave\s*\([^)]+\))/i', $segment, $matches)) {
            return ['type' => 'travel', 'label' => trim($matches[1])];
        }

        $fixedPhrases = [
            'Approved: Work From Home' => 'work_from_home',
            'Approved: Offset' => 'offset',
            'Offset: Offset' => 'offset',
        ];
        foreach ($fixedPhrases as $phrase => $type) {
            if (stripos($segment, $phrase) === 0) {
                return ['type' => $type, 'label' => substr($segment, 0, strlen($phrase))];
            }
        }

        if (preg_match('/^(Approved Leave:\s*.+?)(?:\s*\(Approved:|\s+-|\s*$)/i', $segment, $matches)) {
            $label = trim($matches[1]);
            $inner = preg_replace('/^Approved Leave:\s*/i', '', $label);

            return [
                'type' => self::inferTypeFromDisplayLabel($inner) ?? 'default',
                'label' => $label,
            ];
        }

        if (preg_match('/^Approved Leave:\s*.+$/i', $segment)) {
            $inner = preg_replace('/^Approved Leave:\s*/i', '', $segment);

            return [
                'type' => self::inferTypeFromDisplayLabel($inner) ?? 'default',
                'label' => $segment,
            ];
        }

        return null;
    }

    private static function inferTypeFromDisplayLabel(string $label): ?string
    {
        $label = trim($label);
        if ($label === '') {
            return null;
        }

        $slug = strtolower(str_replace(' ', '_', $label));
        if (isset(self::pdfPalette()[$slug])) {
            return $slug;
        }

        return self::inferApprovedLeaveType(null, null, 'Approved Leave: '.$label, false);
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
