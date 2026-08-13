<?php

namespace App\Support;

use App\Models\LeaveBalance;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class EmployeeTenureLeaveCredits
{
    public const TIER_SIX_MONTHS = 5;

    public const TIER_ONE_YEAR = 10;

    public static function eligibleTierForUser(User $user, ?Carbon $asOf = null): int
    {
        $asOf = ($asOf ?? now())->copy()->startOfDay();

        if (! $user->isStaffMember() || ! $user->is_active || $user->date_hired === null) {
            return 0;
        }

        $hired = $user->date_hired->copy()->startOfDay();

        if ((int) $hired->diffInYears($asOf) >= 1) {
            return self::TIER_ONE_YEAR;
        }

        if ((int) $hired->diffInMonths($asOf) >= 6) {
            return self::TIER_SIX_MONTHS;
        }

        return 0;
    }

    /**
     * Apply tenure leave credits when the employee crosses a milestone.
     *
     * @return int|null The tier applied, or null when nothing changed.
     */
    public static function syncForUser(User $user, ?Carbon $asOf = null): ?int
    {
        if (! $user->auto_tenure_leave_credits_enabled) {
            return null;
        }

        $eligibleTier = self::eligibleTierForUser($user, $asOf);
        if ($eligibleTier === 0) {
            return null;
        }

        $lastTier = (int) ($user->auto_tenure_leave_credits_last_tier ?? 0);
        if ($eligibleTier <= $lastTier) {
            return null;
        }

        $referenceDate = $asOf ?? now();
        $defaultVacation = (float) Setting::get('default_vacation_balance', 0);
        $defaultSick = (float) Setting::get('default_sick_leave_balance', 0);

        $leaveBalance = LeaveBalance::firstOrCreateWithCarryover(
            (int) $user->id,
            (int) $referenceDate->year,
            $defaultVacation,
            $defaultSick
        );

        $leaveBalance->update([
            'vacation_allowance' => (float) $eligibleTier,
            'sick_allowance' => 0,
        ]);

        $user->forceFill(['auto_tenure_leave_credits_last_tier' => $eligibleTier])->saveQuietly();

        return $eligibleTier;
    }

    /**
     * @return array{checked: int, applied: int}
     */
    public static function syncAllEnabled(): array
    {
        $stats = ['checked' => 0, 'applied' => 0];

        User::query()
            ->where('auto_tenure_leave_credits_enabled', true)
            ->where('is_active', true)
            ->whereIn('role', UserRoles::STAFF)
            ->whereNotNull('date_hired')
            ->chunkById(100, function ($users) use (&$stats): void {
                foreach ($users as $user) {
                    $stats['checked']++;
                    if (self::syncForUser($user) !== null) {
                        $stats['applied']++;
                    }
                }
            });

        return $stats;
    }

    public static function tierLabel(int $tier): ?string
    {
        return match ($tier) {
            self::TIER_SIX_MONTHS => '5 leave credits (6+ months employed)',
            self::TIER_ONE_YEAR => '10 leave credits (1+ year employed)',
            default => null,
        };
    }

    public static function eligibilitySummary(User $user): string
    {
        if (! $user->auto_tenure_leave_credits_enabled) {
            return 'Automatic tenure leave credits are disabled. An admin can enable this per employee.';
        }

        if (! $user->is_active) {
            return 'Account must be active to receive automatic tenure leave credits.';
        }

        if ($user->date_hired === null) {
            return 'Set Date Hired to determine when tenure leave credits can apply.';
        }

        $eligibleTier = self::eligibleTierForUser($user);
        $lastTier = (int) ($user->auto_tenure_leave_credits_last_tier ?? 0);

        if ($eligibleTier === 0) {
            $sixMonthDate = $user->date_hired->copy()->startOfDay()->addMonths(6)->format('F j, Y');
            $oneYearDate = $user->date_hired->copy()->startOfDay()->addYear()->format('F j, Y');

            return "Not yet eligible. Next milestones: 5 credits on {$sixMonthDate}, 10 credits on {$oneYearDate}.";
        }

        $eligibleLabel = self::tierLabel($eligibleTier);
        if ($lastTier >= $eligibleTier) {
            return "Latest eligible tier already applied: {$eligibleLabel}.";
        }

        return "Currently eligible for {$eligibleLabel}. Credits apply when you save or during the next daily sync.";
    }
}
