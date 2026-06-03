<?php

namespace App\Support;

use App\Mail\StudentRulesNoticeMail;
use App\Models\DtrTimeRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class StudentMeritRulesNotice
{
    /** @deprecated Use StudentMeritNoticeSettings::finalNoticeThreshold() */
    public const FINAL_NOTICE_MERIT_THRESHOLD = 3;

    /**
     * @return array{skipped: bool, enabled: bool, disabled: bool, updated: bool, total: int, final_enabled: bool}
     */
    public static function syncForStudent(User|int $student): array
    {
        $student = $student instanceof User ? $student->fresh() : User::query()->find($student);
        $empty = [
            'skipped' => true,
            'enabled' => false,
            'disabled' => false,
            'updated' => false,
            'total' => 0,
            'final_enabled' => false,
        ];

        if (! $student || $student->role !== 'student') {
            return $empty;
        }

        if (! StudentMeritNoticeSettings::autoNoticesEnabled()) {
            return array_merge($empty, ['total' => StudentViolationCounter::countForUser((int) $student->id)]);
        }

        if (self::isMeritAutomationLocked($student)) {
            return array_merge($empty, ['total' => StudentViolationCounter::countForUser((int) $student->id)]);
        }

        $breakdown = StudentViolationCounter::breakdownForUser((int) $student->id);
        $total = (int) ($breakdown['total'] ?? 0);
        $thresholds = StudentMeritNoticeSettings::thresholds();

        if ($total >= $thresholds['final']) {
            return self::applyFinalNotice($student, $breakdown, $total, $thresholds['final']);
        }

        if ($total >= $thresholds['warning']) {
            return self::applyMeritWarning($student, $breakdown, $total);
        }

        return self::clearMeritNoticesIfEligible($student, $total);
    }

    public static function isMeritAutomationLocked(User $student): bool
    {
        return (bool) ($student->student_rules_merit_automation_disabled ?? false)
            || (bool) ($student->student_rules_warning_manual ?? false)
            || (bool) ($student->student_rules_marquee_manual ?? false);
    }

    /**
     * @param  array<int, int>|null  $allowedDepartmentIds
     * @return array{enabled: int, disabled: int, updated: int, final_enabled: int}
     */
    public static function reconcileForStudents(?array $allowedDepartmentIds = null): array
    {
        if (! StudentMeritNoticeSettings::autoNoticesEnabled()) {
            return ['enabled' => 0, 'disabled' => 0, 'updated' => 0, 'final_enabled' => 0];
        }

        $enabled = 0;
        $disabled = 0;
        $updated = 0;
        $finalEnabled = 0;

        $studentQuery = User::query()
            ->where('role', 'student')
            ->where('student_rules_merit_automation_disabled', false)
            ->where('student_rules_warning_manual', false)
            ->where('student_rules_marquee_manual', false);

        if ($allowedDepartmentIds !== null) {
            $studentQuery->whereIn('department_id', $allowedDepartmentIds);
        }

        foreach ($studentQuery->get() as $student) {
            $result = self::syncForStudent($student);
            if ($result['skipped']) {
                continue;
            }
            if ($result['enabled']) {
                $enabled++;
            }
            if ($result['disabled']) {
                $disabled++;
            }
            if ($result['updated']) {
                $updated++;
            }
            if ($result['final_enabled']) {
                $finalEnabled++;
            }
        }

        return [
            'enabled' => $enabled,
            'disabled' => $disabled,
            'updated' => $updated,
            'final_enabled' => $finalEnabled,
        ];
    }

    /**
     * @param  array{undertime: int, excess_absence: int, manual: int, total: int}  $breakdown
     * @return array{skipped: bool, enabled: bool, disabled: bool, updated: bool, total: int, final_enabled: bool}
     */
    private static function applyFinalNotice(User $student, array $breakdown, int $total, int $finalThreshold): array
    {
        $message = self::buildFinalNoticeMessage($student, $breakdown, $total, $finalThreshold);
        $wasFinalEnabled = (bool) ($student->student_rules_marquee_enabled ?? false);
        $previousMessage = trim((string) ($student->student_rules_notice_message ?? ''));

        $student->forceFill([
            'student_rules_marquee_enabled' => true,
            'student_rules_warning' => false,
            'student_rules_notice_message' => $message,
            'student_rules_warning_manual' => false,
            'student_rules_marquee_manual' => false,
        ])->save();

        $updated = $wasFinalEnabled && $previousMessage !== $message;

        if (! $wasFinalEnabled && filter_var($student->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($student->email)->send(new StudentRulesNoticeMail(
                    $student->fresh(),
                    'final',
                    $message
                ));
            } catch (\Throwable $e) {
                Log::warning('Student merit final notice email failed: '.$e->getMessage(), [
                    'user_id' => $student->id,
                ]);
            }
        }

        return [
            'skipped' => false,
            'enabled' => false,
            'disabled' => false,
            'updated' => $updated,
            'total' => $total,
            'final_enabled' => ! $wasFinalEnabled,
        ];
    }

    /**
     * @param  array{undertime: int, excess_absence: int, manual: int, total: int}  $breakdown
     * @return array{skipped: bool, enabled: bool, disabled: bool, updated: bool, total: int, final_enabled: bool}
     */
    private static function applyMeritWarning(User $student, array $breakdown, int $total): array
    {
        $message = self::buildViolationNoticeMessage($student, $breakdown, $total);
        $wasEnabled = (bool) ($student->student_rules_warning ?? false);
        $previousMessage = trim((string) ($student->student_rules_notice_message ?? ''));

        $student->forceFill([
            'student_rules_warning' => true,
            'student_rules_marquee_enabled' => false,
            'student_rules_notice_message' => $message,
            'student_rules_warning_manual' => false,
            'student_rules_marquee_manual' => false,
        ])->save();

        $updated = $wasEnabled && $previousMessage !== $message;

        if (! $wasEnabled && filter_var($student->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($student->email)->send(new StudentRulesNoticeMail(
                    $student->fresh(),
                    'violation',
                    $message
                ));
            } catch (\Throwable $e) {
                Log::warning('Student merit rules notice email failed: '.$e->getMessage(), [
                    'user_id' => $student->id,
                ]);
            }
        }

        return [
            'skipped' => false,
            'enabled' => ! $wasEnabled,
            'disabled' => false,
            'updated' => $updated,
            'total' => $total,
            'final_enabled' => false,
        ];
    }

    /**
     * @return array{skipped: bool, enabled: bool, disabled: bool, updated: bool, total: int, final_enabled: bool}
     */
    private static function clearMeritNoticesIfEligible(User $student, int $total): array
    {
        if (self::undertimeStreakRequiresWarning((int) $student->id)) {
            return [
                'skipped' => false,
                'enabled' => false,
                'disabled' => false,
                'updated' => false,
                'total' => $total,
                'final_enabled' => false,
            ];
        }

        $hadNotice = (bool) ($student->student_rules_warning ?? false)
            || (bool) ($student->student_rules_marquee_enabled ?? false);

        if (! $hadNotice) {
            return [
                'skipped' => false,
                'enabled' => false,
                'disabled' => false,
                'updated' => false,
                'total' => $total,
                'final_enabled' => false,
            ];
        }

        $student->forceFill([
            'student_rules_warning' => false,
            'student_rules_marquee_enabled' => false,
            'student_rules_notice_message' => null,
        ])->save();

        return [
            'skipped' => false,
            'enabled' => false,
            'disabled' => true,
            'updated' => false,
            'total' => $total,
            'final_enabled' => false,
        ];
    }

    private static function undertimeStreakRequiresWarning(int $studentId): bool
    {
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
            ->first(fn (DtrTimeRequest $request) => StudentUndertimeRulesViolation::isUndertimeRequest($request));

        if (! $latestUndertime) {
            return false;
        }

        $streak = StudentUndertimeRulesViolation::countConsecutiveApprovedUndertimeEndingOn(
            $studentId,
            $latestUndertime->date->copy()->startOfDay()
        );

        return $streak >= StudentUndertimeRulesViolation::UNDERTIME_THRESHOLD;
    }

    /**
     * @param  array{undertime: int, excess_absence: int, manual: int, total: int}  $breakdown
     */
    public static function buildViolationNoticeMessage(User $student, array $breakdown, int $total): string
    {
        $detail = self::formatMeritBreakdownDetail($breakdown);

        return sprintf(
            'Rules violation — merits: You have %d merit(s) on record (%s). %s Please complete your required hours and follow the rules and regulations.',
            $total,
            $student->name,
            $detail
        );
    }

    /**
     * @param  array{undertime: int, excess_absence: int, manual: int, total: int}  $breakdown
     */
    public static function buildFinalNoticeMessage(User $student, array $breakdown, int $total, int $finalThreshold): string
    {
        $detail = self::formatMeritBreakdownDetail($breakdown);

        return sprintf(
            'Final notice — merits: You have reached %d merit(s) (final notice applies at %d or more) (%s). %s This is your final warning. Please complete your required hours and follow the rules and regulations immediately.',
            $total,
            $finalThreshold,
            $student->name,
            $detail
        );
    }

    /**
     * @param  array{undertime: int, excess_absence: int, manual: int, total: int}  $breakdown
     */
    private static function formatMeritBreakdownDetail(array $breakdown): string
    {
        $parts = [];

        if (($breakdown['undertime'] ?? 0) > 0) {
            $parts[] = sprintf(
                '%d from under-time time request(s) filed below 08:00',
                (int) $breakdown['undertime']
            );
        }

        if (($breakdown['excess_absence'] ?? 0) > 0) {
            $parts[] = sprintf(
                '%d from approved absent day(s) over your allowable absence balance',
                (int) $breakdown['excess_absence']
            );
        }

        if (($breakdown['manual'] ?? 0) > 0) {
            $parts[] = sprintf(
                '%d added manually by administration',
                (int) $breakdown['manual']
            );
        }

        return $parts !== []
            ? implode('; ', $parts).'.'
            : 'Please review your attendance and leave records.';
    }
}
