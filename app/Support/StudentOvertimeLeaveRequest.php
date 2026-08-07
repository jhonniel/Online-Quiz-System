<?php

namespace App\Support;

use App\Models\Dtr;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Log;

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

        $hoursFormatted = null;
        if (preg_match('/Total Overtime Hours:\s*(\d{1,3}:\d{2})/', $raw, $hoursMatch)) {
            $hoursFormatted = $hoursMatch[1];
        } elseif (preg_match('/Additional Time Hours:\s*(\d{1,3}:\d{2})/', $raw, $hoursMatch)) {
            $hoursFormatted = $hoursMatch[1];
        }

        if ($hoursFormatted === null) {
            return null;
        }

        $totalHours = DtrTimeRequestHours::timeStringToDecimal($hoursFormatted);
        if ($totalHours <= 0) {
            return null;
        }

        $dates = [];
        if (preg_match('/Overtime Dates:\s*(.+)/', $raw, $datesMatch)) {
            $dates = collect(explode(',', trim($datesMatch[1])))
                ->map(fn ($d) => trim($d))
                ->filter(fn ($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) === 1)
                ->values()
                ->all();
        }

        return [
            'total_hours' => $totalHours,
            'dates' => $dates,
        ];
    }

    /**
     * @return array{total_hours: float, dates: list<string>}|null
     */
    public static function parseForLeaveRequest(LeaveRequest $leaveRequest): ?array
    {
        $parsed = self::parseFromReason($leaveRequest->reason);
        if ($parsed === null) {
            return null;
        }

        if ($parsed['dates'] === []) {
            $start = $leaveRequest->start_date?->format('Y-m-d');
            if ($start) {
                $parsed['dates'] = [$start];
            }
        }

        if ($parsed['dates'] === []) {
            return null;
        }

        return $parsed;
    }

    public static function applyApprovedToDtr(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->loadMissing('user');

        if ($leaveRequest->user?->role !== 'student') {
            return;
        }

        if (! in_array($leaveRequest->type, ['overtime', 'additional_time'], true)) {
            return;
        }

        if (TimeRequestOvertimeLeaveImport::shouldSkipDtrCreditOnLeaveApproval($leaveRequest)) {
            return;
        }

        $parsed = self::parseForLeaveRequest($leaveRequest);
        if ($parsed === null) {
            Log::warning('Student Additional Time leave approved but DTR was not credited (could not parse hours/dates).', [
                'leave_request_id' => $leaveRequest->id,
                'user_id' => $leaveRequest->user_id,
                'type' => $leaveRequest->type,
            ]);

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
                $existingRemarks = trim((string) ($existingDtr->remarks ?? ''));
                // Avoid double-crediting the same approved leave remark.
                if (str_contains($existingRemarks, $remark)) {
                    continue;
                }

                $existingDtr->total_hours = ((float) ($existingDtr->total_hours ?? 0)) + $hoursPerDate;
                $existingDtr->overtime_hours = ((float) ($existingDtr->overtime_hours ?? 0)) + $hoursPerDate;
                $existingDtr->status = $existingDtr->status ?: 'present';
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
        $leaveRequest->loadMissing('user');

        if ($leaveRequest->user?->role !== 'student') {
            return;
        }

        if (! in_array($leaveRequest->type, ['overtime', 'additional_time'], true)) {
            return;
        }

        if (TimeRequestOvertimeLeaveImport::shouldSkipDtrCreditOnLeaveApproval($leaveRequest)) {
            return;
        }

        $parsed = self::parseForLeaveRequest($leaveRequest);
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
