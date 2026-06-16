<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class LeaveRequestSignatorySettings
{
    public const IMMEDIATE_SUPERVISOR_USER_ID_KEY = 'leave_immediate_supervisor_user_id';

    public const HR_ADMIN_USER_ID_KEY = 'leave_hr_admin_user_id';

    public const CTO_USER_ID_KEY = 'leave_cto_user_id';

    /**
     * @return array{immediate_supervisor: string, hr_admin: string, cto: string}
     */
    public static function signatoriesForLeaveRequest(LeaveRequest $leaveRequest): array
    {
        $leaveRequest->loadMissing('user.department');

        return self::signatoriesForEmployee($leaveRequest->user);
    }

    /**
     * @return array{immediate_supervisor: string, hr_admin: string, cto: string}
     */
    public static function signatoriesForEmployee(?User $employee): array
    {
        return [
            'immediate_supervisor' => self::immediateSupervisorName($employee),
            'hr_admin' => self::hrAdminName(),
            'cto' => self::ctoName(),
        ];
    }

    public static function immediateSupervisorUserId(): ?int
    {
        $id = (int) Setting::get(self::IMMEDIATE_SUPERVISOR_USER_ID_KEY, 0);

        return $id > 0 ? $id : null;
    }

    public static function hrAdminUserId(): ?int
    {
        $id = (int) Setting::get(self::HR_ADMIN_USER_ID_KEY, 0);

        return $id > 0 ? $id : null;
    }

    public static function ctoUserId(): ?int
    {
        $id = (int) Setting::get(self::CTO_USER_ID_KEY, 0);

        return $id > 0 ? $id : null;
    }

    public static function immediateSupervisorUser(?User $employee): ?User
    {
        $employee?->loadMissing('department');

        if ($employee?->department?->supervisor_user_id) {
            $user = self::findSelectableUser((int) $employee->department->supervisor_user_id);
            if ($user !== null) {
                return $user;
            }
        }

        $globalId = self::immediateSupervisorUserId();
        if ($globalId !== null) {
            $user = self::findSelectableUser($globalId);
            if ($user !== null) {
                return $user;
            }
        }

        $fallbackName = trim((string) ($employee?->department?->supervisor_name ?? ''));
        if ($fallbackName === '') {
            $fallbackName = (string) Setting::get('leave_immediate_supervisor', 'CHARMAINE JOY ROSATACE');
        }

        return self::findUserByName($fallbackName);
    }

    public static function hrAdminUser(): ?User
    {
        $assigned = self::hrAdminUserId();
        if ($assigned !== null) {
            $user = self::findSelectableUser($assigned);
            if ($user !== null) {
                return $user;
            }
        }

        return self::findUserByName((string) Setting::get('leave_hr_admin', 'MAY GRACE ACOSTA'));
    }

    public static function ctoUser(): ?User
    {
        $assigned = self::ctoUserId();
        if ($assigned !== null) {
            $user = self::findSelectableUser($assigned);
            if ($user !== null) {
                return $user;
            }
        }

        return self::findUserByName((string) Setting::get('leave_cto', 'NITISH KHEMANI'));
    }

    public static function immediateSupervisorName(?User $employee): string
    {
        $assigned = self::immediateSupervisorUser($employee);
        if ($assigned !== null) {
            return $assigned->name;
        }

        $employee?->loadMissing('department');
        $departmentName = trim((string) ($employee?->department?->supervisor_name ?? ''));
        if ($departmentName !== '') {
            return $departmentName;
        }

        return (string) Setting::get('leave_immediate_supervisor', 'CHARMAINE JOY ROSATACE');
    }

    public static function hrAdminName(): string
    {
        return self::hrAdminUser()?->name
            ?? (string) Setting::get('leave_hr_admin', 'MAY GRACE ACOSTA');
    }

    public static function ctoName(): string
    {
        return self::ctoUser()?->name
            ?? (string) Setting::get('leave_cto', 'NITISH KHEMANI');
    }

    public static function selectableUsersQuery(): Builder
    {
        return User::query()
            ->whereIn('role', ['employee', 'admin', 'hr'])
            ->where('is_active', true)
            ->orderBy('name');
    }

    public static function isSelectableUser(?User $user): bool
    {
        if (! $user || ! $user->is_active) {
            return false;
        }

        return in_array($user->role, ['employee', 'admin', 'hr'], true);
    }

    public static function findUserByName(?string $name): ?User
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $normalized = strtolower(preg_replace('/\s+/', ' ', $name) ?? $name);

        return User::query()
            ->whereIn('role', ['employee', 'admin', 'hr'])
            ->where('is_active', true)
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])
            ->first(['id', 'name', 'role', 'e_signature_path']);
    }

    private static function findSelectableUser(int $id): ?User
    {
        $user = User::query()->find($id);

        if (! self::isSelectableUser($user)) {
            return null;
        }

        if ($user !== null && blank($user->e_signature_path)) {
            $user = User::query()
                ->whereKey($user->id)
                ->first(['id', 'name', 'role', 'e_signature_path']);
        }

        return $user;
    }
}
