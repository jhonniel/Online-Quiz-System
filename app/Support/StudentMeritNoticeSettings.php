<?php

namespace App\Support;

use App\Models\Setting;

final class StudentMeritNoticeSettings
{
    public static function autoNoticesEnabled(): bool
    {
        return (string) Setting::get('student_merit_auto_notices_enabled', 'enabled') === 'enabled';
    }

    /**
     * @return array{warning: int, final: int}
     */
    public static function thresholds(): array
    {
        $warning = max(1, (int) Setting::get('student_merit_violation_warning_threshold', 1));
        $final = max(1, (int) Setting::get('student_merit_final_notice_threshold', 3));

        if ($final <= $warning) {
            $final = $warning + 1;
        }

        return [
            'warning' => $warning,
            'final' => $final,
        ];
    }

    public static function violationWarningThreshold(): int
    {
        return self::thresholds()['warning'];
    }

    public static function finalNoticeThreshold(): int
    {
        return self::thresholds()['final'];
    }
}
