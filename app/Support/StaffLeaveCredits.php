<?php

namespace App\Support;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Setting;
use App\Models\User;

/**
 * Employee leave credit pool for user-facing UI and self-service validation.
 * Uses tenure (0 / 6 / 12) unless an admin configured a custom balance.
 */
final class StaffLeaveCredits
{
    public static function usedLeaveCredits(User $user, ?int $year = null): float
    {
        $year = $year ?? (int) now()->year;

        return (float) LeaveRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['leave', 'vacation_leave', 'sick_leave'])
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->get()
            ->sum->days;
    }

    public static function effectiveAllowance(User $user, ?int $year = null): float
    {
        if (! $user->isStaffMember()) {
            return 0.0;
        }

        $year = $year ?? (int) now()->year;
        $tenureAllowance = (float) EmployeeTenureLeaveCredits::tenureAllowanceCredits($user);

        $balance = LeaveBalance::query()
            ->where('user_id', $user->id)
            ->where('year', $year)
            ->first();

        if ($balance === null) {
            return $user->auto_tenure_leave_credits_enabled ? $tenureAllowance : 0.0;
        }

        $stored = (float) $balance->vacation_allowance + (float) $balance->sick_allowance;

        if (! $user->auto_tenure_leave_credits_enabled) {
            return $stored;
        }

        if (self::isSystemDefaultSeededAllowance($stored)) {
            return $tenureAllowance;
        }

        if (abs($stored - $tenureAllowance) < 0.001) {
            return $tenureAllowance;
        }

        return $stored;
    }

    private static function isSystemDefaultSeededAllowance(float $stored): bool
    {
        $defaultCombined = (float) Setting::get('default_vacation_balance', 15)
            + (float) Setting::get('default_sick_leave_balance', 10);

        return abs($stored - $defaultCombined) < 0.001;
    }

    /**
     * @return array{year: int, allowance: float, used: float, remaining: float}
     */
    public static function summaryForUser(User $user, ?int $year = null): array
    {
        $year = $year ?? (int) now()->year;
        $allowance = self::effectiveAllowance($user, $year);
        $used = self::usedLeaveCredits($user, $year);

        return [
            'year' => $year,
            'allowance' => $allowance,
            'used' => $used,
            'remaining' => max($allowance - $used, 0.0),
        ];
    }
}
