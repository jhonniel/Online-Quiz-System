<?php

namespace App\Support;

use App\Mail\StudentRulesNoticeMail;
use App\Models\Dtr;
use App\Models\DtrTimeRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StudentUndertimeRulesViolation
{
    public const UNDERTIME_THRESHOLD = 5;

    public const RECOVERY_THRESHOLD = 10;

    /**
     * Admin manually enabled the warning — automation must not change it.
     */
    public static function isAutomationLocked(User $student): bool
    {
        return (bool) ($student->student_rules_warning_manual ?? false);
    }

    /**
     * Hours filed on the time request (day total when available).
     */
    public static function effectiveFiledHours(DtrTimeRequest $request): float
    {
        $total = (float) ($request->requested_total_hours ?? 0);
        if ($total <= 0) {
            $total = (float) $request->hours;
        }

        return $total;
    }

    /**
     * Approved regular time request with less than 08:00 for the day.
     */
    public static function isUndertimeRequest(DtrTimeRequest $request): bool
    {
        if ($request->isOvertime()) {
            return false;
        }

        if (self::effectiveFiledHours($request) < DtrTimeRequestHours::STANDARD_DAY_HOURS) {
            return true;
        }

        if ($request->status !== 'approved') {
            return false;
        }

        $dtr = Dtr::query()
            ->where('user_id', $request->user_id)
            ->whereDate('date', $request->date)
            ->first();

        if (! $dtr) {
            return false;
        }

        if (in_array((string) $dtr->status, ['on_leave', 'absent', 'holiday', 'travel'], true)) {
            return false;
        }

        return (float) ($dtr->total_hours ?? 0) < DtrTimeRequestHours::STANDARD_DAY_HOURS;
    }

    /**
     * Regular time request with at least 08:00 filed for the day.
     */
    public static function isFullDayRequest(DtrTimeRequest $request): bool
    {
        if ($request->isOvertime()) {
            return false;
        }

        return self::effectiveFiledHours($request) >= DtrTimeRequestHours::STANDARD_DAY_HOURS;
    }

    /**
     * @return array<int, string>
     */
    private static function approvedRegularDatesForUser(int $userId, callable $filter): array
    {
        return DtrTimeRequest::query()
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->where(function ($q): void {
                $q->where('request_type', 'regular')
                    ->orWhereNull('request_type');
            })
            ->orderBy('date')
            ->get()
            ->filter($filter)
            ->map(fn (DtrTimeRequest $request) => $request->date->format('Y-m-d'))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Count consecutive calendar days of approved under-time ending on the given date (inclusive).
     */
    public static function countConsecutiveApprovedUndertimeEndingOn(int $userId, Carbon $endDate): int
    {
        $undertimeDates = array_flip(self::approvedRegularDatesForUser(
            $userId,
            fn (DtrTimeRequest $request) => self::isUndertimeRequest($request)
        ));

        if (! isset($undertimeDates[$endDate->format('Y-m-d')])) {
            return 0;
        }

        $streak = 0;
        $cursor = $endDate->copy()->startOfDay();

        while (isset($undertimeDates[$cursor->format('Y-m-d')])) {
            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }

    /**
     * Count consecutive calendar days of approved 08:00+ time ending on the given date (inclusive).
     */
    public static function countConsecutiveApprovedFullDaysEndingOn(int $userId, Carbon $endDate): int
    {
        $fullDayDates = array_flip(self::approvedRegularDatesForUser(
            $userId,
            fn (DtrTimeRequest $request) => self::isFullDayRequest($request)
        ));

        if (! isset($fullDayDates[$endDate->format('Y-m-d')])) {
            return 0;
        }

        $streak = 0;
        $cursor = $endDate->copy()->startOfDay();

        while (isset($fullDayDates[$cursor->format('Y-m-d')])) {
            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }

    /**
     * Run under-time enable / full-day recovery disable after a time request is approved.
     *
     * @return array{enabled: bool, disabled: bool, manual: bool}
     */
    public static function evaluateAfterApprovedTimeRequest(DtrTimeRequest $timeRequest): array
    {
        $timeRequest->loadMissing('user');
        $student = $timeRequest->user;

        if (! $student instanceof User || $student->role !== 'student') {
            return ['enabled' => false, 'disabled' => false, 'manual' => false];
        }

        if (self::isAutomationLocked($student)) {
            return ['enabled' => false, 'disabled' => false, 'manual' => true];
        }

        $disabled = self::clearAfterApprovedTimeRequest($timeRequest);
        $enabled = ! $disabled && self::applyAfterApprovedTimeRequest($timeRequest);

        return [
            'enabled' => $enabled,
            'disabled' => $disabled,
            'manual' => false,
        ];
    }

    /**
     * Auto-disable rules violation warning after 10 consecutive approved 08:00+ days.
     */
    public static function clearAfterApprovedTimeRequest(DtrTimeRequest $timeRequest): bool
    {
        $timeRequest->loadMissing('user');
        $student = $timeRequest->user;

        if (! $student instanceof User || $student->role !== 'student') {
            return false;
        }

        if (self::isAutomationLocked($student)) {
            return false;
        }

        if (! (bool) ($student->student_rules_warning ?? false)) {
            return false;
        }

        if ((bool) ($student->student_rules_marquee_enabled ?? false)) {
            return false;
        }

        if (! self::isFullDayRequest($timeRequest)) {
            return false;
        }

        $streak = self::countConsecutiveApprovedFullDaysEndingOn(
            (int) $student->id,
            $timeRequest->date->copy()->startOfDay()
        );

        if ($streak < self::RECOVERY_THRESHOLD) {
            return false;
        }

        $student->forceFill([
            'student_rules_warning' => false,
            'student_rules_notice_message' => null,
        ])->save();

        return true;
    }

    /**
     * Auto-enable rules violation warning after 5 consecutive approved under-time days.
     */
    public static function applyAfterApprovedTimeRequest(DtrTimeRequest $timeRequest): bool
    {
        $timeRequest->loadMissing('user');
        $student = $timeRequest->user;

        if (! $student instanceof User || $student->role !== 'student') {
            return false;
        }

        if (self::isAutomationLocked($student)) {
            return false;
        }

        if (! self::isUndertimeRequest($timeRequest)) {
            return false;
        }

        $streak = self::countConsecutiveApprovedUndertimeEndingOn(
            (int) $student->id,
            $timeRequest->date->copy()->startOfDay()
        );

        if ($streak < self::UNDERTIME_THRESHOLD) {
            return false;
        }

        if ((bool) ($student->student_rules_marquee_enabled ?? false)) {
            return false;
        }

        $hoursLabel = DtrTimeRequestHours::decimalToTimeString(self::effectiveFiledHours($timeRequest));
        $noticeMessage = sprintf(
            'Rules violation — under time: You have %d consecutive approved attendance day(s) below the required 08:00 daily training time (most recent: %s on %s with %s filed). Please complete your required hours and follow the rules and regulations.',
            $streak,
            $student->name,
            $timeRequest->date->format('M d, Y'),
            $hoursLabel
        );

        $wasWarningEnabled = (bool) ($student->student_rules_warning ?? false);

        $student->forceFill([
            'student_rules_warning' => true,
            'student_rules_notice_message' => $noticeMessage,
            'student_rules_warning_manual' => false,
        ])->save();

        if (! $wasWarningEnabled && filter_var($student->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($student->email)->send(new StudentRulesNoticeMail(
                    $student->fresh(),
                    'violation',
                    $noticeMessage
                ));
            } catch (\Throwable $e) {
                Log::warning('Student undertime rules violation email failed: '.$e->getMessage(), [
                    'user_id' => $student->id,
                    'time_request_id' => $timeRequest->id,
                ]);
            }
        }

        return true;
    }

    /**
     * Backfill automation for students (enable / disable) excluding admin-locked accounts.
     *
     * @param  array<int, int>|null  $allowedDepartmentIds
     * @return array{enabled: int, disabled: int}
     */
    public static function reconcileForStudents(?array $allowedDepartmentIds = null): array
    {
        $enabled = 0;
        $disabled = 0;

        $studentQuery = User::query()
            ->where('role', 'student')
            ->where('student_rules_warning_manual', false);

        if ($allowedDepartmentIds !== null) {
            $studentQuery->whereIn('department_id', $allowedDepartmentIds);
        }

        foreach ($studentQuery->pluck('id') as $studentId) {
            if (self::tryReconcileRecoveryForStudent((int) $studentId)) {
                $disabled++;
                continue;
            }

            $latestUndertime = DtrTimeRequest::query()
                ->where('user_id', $studentId)
                ->where('status', 'approved')
                ->where(function ($q): void {
                    $q->where('request_type', 'regular')
                        ->orWhereNull('request_type');
                })
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get()
                ->first(fn (DtrTimeRequest $request) => self::isUndertimeRequest($request));

            if (! $latestUndertime) {
                continue;
            }

            $streak = self::countConsecutiveApprovedUndertimeEndingOn(
                (int) $studentId,
                $latestUndertime->date->copy()->startOfDay()
            );

            if ($streak < self::UNDERTIME_THRESHOLD) {
                continue;
            }

            if (self::applyAfterApprovedTimeRequest($latestUndertime)) {
                $enabled++;
            }
        }

        return [
            'enabled' => $enabled,
            'disabled' => $disabled,
        ];
    }

    private static function tryReconcileRecoveryForStudent(int $studentId): bool
    {
        $student = User::query()->find($studentId);
        if (! $student || self::isAutomationLocked($student)) {
            return false;
        }

        if (! (bool) ($student->student_rules_warning ?? false)) {
            return false;
        }

        $latestFullDay = DtrTimeRequest::query()
            ->where('user_id', $studentId)
            ->where('status', 'approved')
            ->where(function ($q): void {
                $q->where('request_type', 'regular')
                    ->orWhereNull('request_type');
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get()
            ->first(fn (DtrTimeRequest $request) => self::isFullDayRequest($request));

        if (! $latestFullDay) {
            return false;
        }

        return self::clearAfterApprovedTimeRequest($latestFullDay);
    }
}
