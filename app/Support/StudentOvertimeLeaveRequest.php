<?php

namespace App\Support;

use App\Models\Dtr;
use App\Models\LeaveRequest;

class StudentOvertimeLeaveRequest
{
    /**
     * @return array{total_hours: float, dates: list<string>}|null
     */
    public static function parseFromReason(?string $reason): ?array
    {
        $raw = (string) $reason;
        if ($raw === '') {
            return null;
        }

        if (! preg_match('/Total Overtime Hours:\s*(\d{1,3}:\d{2})/', $raw, $hoursMatch)) {
            return null;
        }

        $totalHours = DtrTimeRequestHours::timeStringToDecimal($hoursMatch[1]);
        if ($totalHours <= 0) {
            return null;
        }

        $dates = [];
        if (preg_match('/Overtime Dates:\s*(.+)/', $raw, $datesMatch)) {
            $dates = collect(explode(',', trim($datesMatch[1])))
                ->map(fn ($d) => trim($d))
                ->filter(fn ($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d))
                ->values()
                ->all();
        }

        if ($dates === []) {
            return null;
        }

        return [
            'total_hours' => $totalHours,
            'dates' => $dates,
        ];
    }

    public static function applyApprovedToDtr(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->type !== 'overtime' || $leaveRequest->user?->role !== 'student') {
            return;
        }

        if (TimeRequestOvertimeLeaveImport::shouldSkipDtrCreditOnLeaveApproval($leaveRequest)) {
            return;
        }

        $parsed = self::parseFromReason($leaveRequest->reason);
        if ($parsed === null) {
            return;
        }

        $hoursPerDate = $parsed['total_hours'] / max(count($parsed['dates']), 1);
        $timeLabel = DtrTimeRequestHours::decimalToTimeString($hoursPerDate);
        $remark = 'Approved Overtime leave ('.$timeLabel.' per day)';

        foreach ($parsed['dates'] as $dateStr) {
            $existingDtr = Dtr::query()
                ->where('user_id', $leaveRequest->user_id)
                ->whereDate('date', $dateStr)
                ->first();

            if ($existingDtr) {
                $existingDtr->total_hours = ((float) ($existingDtr->total_hours ?? 0)) + $hoursPerDate;
                $existingDtr->overtime_hours = ((float) ($existingDtr->overtime_hours ?? 0)) + $hoursPerDate;
                $existingDtr->status = $existingDtr->status ?: 'present';
                $existingRemarks = trim((string) ($existingDtr->remarks ?? ''));
                $existingDtr->remarks = $existingRemarks !== ''
                    ? $existingRemarks.' | '.$remark
                    : $remark;
                $existingDtr->save();

                continue;
            }

            $baseHours = DtrTimeRequestHours::STANDARD_DAY_HOURS;
            Dtr::create([
                'user_id' => $leaveRequest->user_id,
                'date' => $dateStr,
                'total_hours' => $baseHours + $hoursPerDate,
                'overtime_hours' => $hoursPerDate,
                'status' => 'present',
                'remarks' => $remark,
                'added_time_from_note' => 0,
            ]);
        }
    }

    public static function revertFromDtr(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->type !== 'overtime' || $leaveRequest->user?->role !== 'student') {
            return;
        }

        if (TimeRequestOvertimeLeaveImport::shouldSkipDtrCreditOnLeaveApproval($leaveRequest)) {
            return;
        }

        $parsed = self::parseFromReason($leaveRequest->reason);
        if ($parsed === null) {
            return;
        }

        $hoursPerDate = $parsed['total_hours'] / max(count($parsed['dates']), 1);
        $remarkToken = 'Approved Overtime leave';

        foreach ($parsed['dates'] as $dateStr) {
            $dtr = Dtr::query()
                ->where('user_id', $leaveRequest->user_id)
                ->whereDate('date', $dateStr)
                ->first();

            if (! $dtr) {
                continue;
            }

            $dtr->total_hours = max(((float) ($dtr->total_hours ?? 0)) - $hoursPerDate, 0);
            $dtr->overtime_hours = max(((float) ($dtr->overtime_hours ?? 0)) - $hoursPerDate, 0);

            $existingRemarks = (string) ($dtr->remarks ?? '');
            if ($existingRemarks !== '' && str_contains($existingRemarks, $remarkToken)) {
                $dtr->remarks = trim(preg_replace('/\s*\|\s*'.preg_quote($remarkToken, '/').'[^|]*/', '', $existingRemarks) ?? $existingRemarks);
                $dtr->remarks = trim(str_replace($remarkToken, '', $dtr->remarks));
            }

            $dtr->save();
        }
    }
}
