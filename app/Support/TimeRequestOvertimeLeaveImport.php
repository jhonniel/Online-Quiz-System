<?php

namespace App\Support;

use App\Models\DtrTimeRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestLog;

class TimeRequestOvertimeLeaveImport
{
    /** Legacy: overtime was approved on the time-requests page first. */
    public const IMPORT_REMARK = 'Imported from the time request';

    /** Overtime filed via Record Attendance — shown under Leave Requests. */
    public const FILED_FROM_ATTENDANCE_REMARK = 'Filed from Record Attendance';

    public const ATTENDANCE_STUB_TASKS = '(Pending — complete on Leave Requests)';

    /**
     * Pending overtime from Record Attendance (not a DTR time request).
     */
    public static function createPendingFromAttendance(
        int $userId,
        string $dateStr,
        float $overtimeHours,
        ?float $dayTotalHours,
        string $batchId,
        ?string $remarks
    ): LeaveRequest {
        $existing = LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', 'overtime')
            ->whereDate('start_date', $dateStr)
            ->whereIn('status', ['pending', 'approved', 'for_more_verification'])
            ->first();

        if ($existing) {
            return $existing;
        }

        $reason = self::buildAttendanceStubOvertimeReason($dateStr, $overtimeHours, $dayTotalHours);

        return LeaveRequest::create([
            'user_id' => $userId,
            'type' => 'overtime',
            'start_date' => $dateStr,
            'end_date' => $dateStr,
            'reason' => $reason,
            'status' => 'pending',
            'attendance_submission_batch' => $batchId,
            'attendance_overtime_completed_at' => null,
        ]);
    }

    /**
     * Full overtime reason after the student completes the Leave Request form.
     *
     * @param  array<int, string>  $specificDates
     */
    public static function buildCompletedAttendanceOvertimeReason(
        string $dateStr,
        string $overtimeHoursFormatted,
        ?float $dayTotalHours,
        array $specificDates,
        string $tasks,
        string $additionalExplanation
    ): string {
        $datesList = collect($specificDates)
            ->map(fn ($date) => trim((string) $date))
            ->filter(fn ($date) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1)
            ->unique()
            ->sort()
            ->values();

        if ($datesList->isEmpty()) {
            $datesList = collect([$dateStr]);
        }

        $rangeStart = $datesList->first();
        $rangeEnd = $datesList->last();

        $reason = self::FILED_FROM_ATTENDANCE_REMARK." (awaiting approval).\n\n";
        $reason .= "Overtime Request Details:\n";
        $reason .= "Total Overtime Hours: {$overtimeHoursFormatted}\n";
        $reason .= "Overtime Date Range: {$rangeStart} to {$rangeEnd}\n";
        $reason .= 'Overtime Dates: '.$datesList->implode(', ')."\n";
        $reason .= "Tasks / ClickUp Links:\n".trim($tasks)."\n";
        $reason .= "\nAdditional Explanation:\n".trim($additionalExplanation)."\n";

        if ($dayTotalHours !== null && $dayTotalHours > 0) {
            $dayTotal = DtrTimeRequestHours::decimalToTimeString($dayTotalHours);
            $reason .= "\nDay total filed in attendance: {$dayTotal}\n";
        }

        return $reason;
    }

    public static function parseDayTotalHoursFromReason(?string $reason): ?float
    {
        if (! preg_match('/Day total filed in attendance:\s*(\d{1,3}:\d{2})/', (string) $reason, $m)) {
            return null;
        }

        return DtrTimeRequestHours::timeStringToDecimal(trim($m[1]));
    }

    /**
     * When a regular time request reflects more than 08:00 total (requested_total_hours),
     * ensure a pending Additional Time leave exists — same as Record Attendance filing.
     */
    public static function ensurePendingAdditionalTimeFromRegularTimeRequest(DtrTimeRequest $timeRequest): ?LeaveRequest
    {
        if (! $timeRequest->isRegular()) {
            return null;
        }

        $dateStr = $timeRequest->date?->format('Y-m-d');
        if (! $dateStr) {
            return null;
        }

        $effectiveTotal = (float) ($timeRequest->requested_total_hours ?? $timeRequest->hours);
        $overtimeHours = max($effectiveTotal - DtrTimeRequestHours::STANDARD_DAY_HOURS, 0);
        if ($overtimeHours <= 0) {
            return null;
        }

        if (self::hasPendingOrApprovedOvertimeLeaveForDate((int) $timeRequest->user_id, $dateStr)) {
            return LeaveRequest::query()
                ->where('user_id', $timeRequest->user_id)
                ->where('type', 'overtime')
                ->whereDate('start_date', $dateStr)
                ->whereIn('status', ['pending', 'approved', 'for_more_verification'])
                ->first();
        }

        $batchId = $timeRequest->submission_batch ?: (string) \Illuminate\Support\Str::uuid();

        return self::createPendingFromAttendance(
            (int) $timeRequest->user_id,
            $dateStr,
            $overtimeHours,
            $effectiveTotal,
            $batchId,
            $timeRequest->remarks
        );
    }

    /**
     * Legacy path: approved overtime time request → approved leave record.
     */
    public static function syncFromApprovedTimeRequest(
        DtrTimeRequest $timeRequest,
        ?int $reviewedBy = null,
        ?string $adminNotes = null
    ): ?LeaveRequest {
        if (! $timeRequest->isOvertime() || $timeRequest->status !== 'approved') {
            return null;
        }

        $dateStr = $timeRequest->date?->format('Y-m-d');
        if (! $dateStr) {
            return null;
        }

        $reason = self::buildOvertimeReason(
            self::IMPORT_REMARK." (Time Request #{$timeRequest->id}).",
            $dateStr,
            (float) $timeRequest->hours,
            $timeRequest->requested_total_hours ? (float) $timeRequest->requested_total_hours : null,
            $timeRequest->remarks
        );

        $existing = LeaveRequest::query()
            ->where('dtr_time_request_id', $timeRequest->id)
            ->first();

        $payload = [
            'user_id' => $timeRequest->user_id,
            'type' => 'overtime',
            'start_date' => $dateStr,
            'end_date' => $dateStr,
            'reason' => $reason,
            'status' => 'approved',
            'admin_notes' => $adminNotes ?? $timeRequest->admin_notes,
            'reviewed_by' => $reviewedBy ?? $timeRequest->reviewed_by,
            'reviewed_at' => $timeRequest->reviewed_at ?? now(),
            'dtr_time_request_id' => $timeRequest->id,
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing;
        }

        $leaveRequest = LeaveRequest::create($payload);

        LeaveRequestLog::create([
            'leave_request_id' => $leaveRequest->id,
            'action' => 'approved',
            'status_before' => null,
            'status_after' => 'approved',
            'notes' => self::IMPORT_REMARK.' — auto-created from approved overtime time request.',
            'performed_by' => $reviewedBy ?? $timeRequest->reviewed_by,
        ]);

        return $leaveRequest;
    }

    public static function hasPendingOrApprovedOvertimeLeaveForDate(int $userId, string $dateStr): bool
    {
        return LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', 'overtime')
            ->whereDate('start_date', $dateStr)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
    }

    public static function discardPendingLeaveForBatch(int $userId, string $batchId): void
    {
        LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('attendance_submission_batch', $batchId)
            ->where('status', 'pending')
            ->delete();
    }

    /**
     * Remove pending overtime leave requests filed via Record Attendance for the given dates.
     *
     * @param  array<int, string>  $dateStrings  Y-m-d dates
     */
    public static function discardPendingAttendanceOvertimeForDates(int $userId, array $dateStrings): void
    {
        $dates = collect($dateStrings)
            ->map(fn ($date) => trim((string) $date))
            ->filter(fn ($date) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1)
            ->unique()
            ->values();

        if ($dates->isEmpty()) {
            return;
        }

        LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', 'overtime')
            ->where('status', 'pending')
            ->where(function ($q) use ($dates): void {
                foreach ($dates as $date) {
                    $q->orWhereDate('start_date', $date);
                }
            })
            ->where(function ($q): void {
                $q->whereNotNull('attendance_submission_batch')
                    ->orWhere('reason', 'like', '%'.self::FILED_FROM_ATTENDANCE_REMARK.'%');
            })
            ->delete();
    }

    /**
     * DTR already credited via time-request approval; leave record is informational only.
     */
    public static function shouldSkipDtrCreditOnLeaveApproval(LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->dtr_time_request_id && str_contains((string) $leaveRequest->reason, self::IMPORT_REMARK)) {
            return true;
        }

        return false;
    }

    public static function isFiledFromAttendance(LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->attendance_submission_batch) {
            return true;
        }

        return str_contains((string) $leaveRequest->reason, self::FILED_FROM_ATTENDANCE_REMARK);
    }

    private static function buildAttendanceStubOvertimeReason(
        string $dateStr,
        float $overtimeHours,
        ?float $dayTotalHours
    ): string {
        $hoursFormatted = DtrTimeRequestHours::decimalToTimeString($overtimeHours);

        $reason = self::FILED_FROM_ATTENDANCE_REMARK." (details required).\n\n";
        $reason .= "Overtime Request Details:\n";
        $reason .= "Total Overtime Hours: {$hoursFormatted}\n";
        $reason .= "Overtime Date Range: {$dateStr} to {$dateStr}\n";
        $reason .= "Overtime Dates: {$dateStr}\n";
        $reason .= 'Tasks / ClickUp Links: '.self::ATTENDANCE_STUB_TASKS."\n";

        if ($dayTotalHours !== null && $dayTotalHours > 0) {
            $dayTotal = DtrTimeRequestHours::decimalToTimeString($dayTotalHours);
            $reason .= "\nDay total filed in attendance: {$dayTotal}\n";
        }

        return $reason;
    }

    private static function buildOvertimeReason(
        string $headerLine,
        string $dateStr,
        float $overtimeHours,
        ?float $dayTotalHours,
        ?string $remarks
    ): string {
        $hoursFormatted = DtrTimeRequestHours::decimalToTimeString($overtimeHours);
        $studentRemarks = trim((string) ($remarks));
        $tasksLine = $studentRemarks !== '' ? $studentRemarks : 'Imported from approved time request.';

        $reason = $headerLine."\n\n";
        $reason .= "Overtime Request Details:\n";
        $reason .= "Total Overtime Hours: {$hoursFormatted}\n";
        $reason .= "Overtime Date Range: {$dateStr} to {$dateStr}\n";
        $reason .= "Overtime Dates: {$dateStr}\n";
        $reason .= "Tasks / ClickUp Links:\n{$tasksLine}\n";

        if ($dayTotalHours !== null && $dayTotalHours > 0) {
            $dayTotal = DtrTimeRequestHours::decimalToTimeString($dayTotalHours);
            $reason .= "\nDay total filed in attendance: {$dayTotal}\n";
        }

        return $reason;
    }
}
