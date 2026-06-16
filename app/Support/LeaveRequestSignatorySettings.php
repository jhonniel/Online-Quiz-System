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

    public const LETTER_ADDRESSEE_KEY = 'leave_letter_addressee';

    /**
     * @return array{
     *     letter_addressee: string,
     *     signatories: array{immediate_supervisor: string, hr_admin: string, cto: string}
     * }
     */
    public static function letterContext(?User $employee): array
    {
        return [
            'letter_addressee' => self::letterAddresseeLine(),
            'signatories' => self::signatoriesForEmployee($employee),
        ];
    }

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
            'immediate_supervisor' => self::immediateSupervisorDisplayName($employee),
            'hr_admin' => self::hrAdminDisplayName(),
            'cto' => self::ctoDisplayName(),
        ];
    }

    public static function letterAddresseeLine(): string
    {
        $override = trim((string) Setting::get(self::LETTER_ADDRESSEE_KEY, ''));
        $name = $override !== '' ? $override : self::hrAdminDisplayName();

        if ($name === '' || $name === '—') {
            return 'Dear HR Admin,';
        }

        return 'Dear Ms. '.$name.',';
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
            $fallbackName = trim((string) Setting::get('leave_immediate_supervisor', ''));
        }

        return $fallbackName !== '' ? self::findUserByName($fallbackName) : null;
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

        $fallbackName = trim((string) Setting::get('leave_hr_admin', ''));

        return $fallbackName !== '' ? self::findUserByName($fallbackName) : null;
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

        $fallbackName = trim((string) Setting::get('leave_cto', ''));

        return $fallbackName !== '' ? self::findUserByName($fallbackName) : null;
    }

    public static function immediateSupervisorDisplayName(?User $employee): string
    {
        $employee?->loadMissing('department');

        if ($employee?->department?->supervisor_user_id) {
            $user = self::findSelectableUser((int) $employee->department->supervisor_user_id);
            if ($user !== null) {
                return $user->name;
            }
        }

        $globalId = self::immediateSupervisorUserId();
        if ($globalId !== null) {
            $user = self::findSelectableUser($globalId);
            if ($user !== null) {
                return $user->name;
            }
        }

        $departmentName = trim((string) ($employee?->department?->supervisor_name ?? ''));
        if ($departmentName !== '') {
            return $departmentName;
        }

        $settingName = trim((string) Setting::get('leave_immediate_supervisor', ''));

        return $settingName !== '' ? $settingName : '—';
    }

    public static function hrAdminDisplayName(): string
    {
        $assigned = self::hrAdminUserId();
        if ($assigned !== null) {
            $user = self::findSelectableUser($assigned);
            if ($user !== null) {
                return $user->name;
            }
        }

        $settingName = trim((string) Setting::get('leave_hr_admin', ''));

        return $settingName !== '' ? $settingName : '—';
    }

    public static function ctoDisplayName(): string
    {
        $assigned = self::ctoUserId();
        if ($assigned !== null) {
            $user = self::findSelectableUser($assigned);
            if ($user !== null) {
                return $user->name;
            }
        }

        $settingName = trim((string) Setting::get('leave_cto', ''));

        return $settingName !== '' ? $settingName : '—';
    }

    public static function immediateSupervisorName(?User $employee): string
    {
        return self::immediateSupervisorDisplayName($employee);
    }

    public static function hrAdminName(): string
    {
        return self::hrAdminDisplayName();
    }

    public static function ctoName(): string
    {
        return self::ctoDisplayName();
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

        $query = User::query()
            ->whereIn('role', ['employee', 'admin', 'hr'])
            ->where('is_active', true);

        $normalized = self::normalizeSignatoryName($name);

        $exact = (clone $query)
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])
            ->first(['id', 'name', 'role', 'e_signature_path']);

        if ($exact !== null) {
            return $exact;
        }

        $tokens = self::signatoryNameTokens($name);
        if ($tokens === []) {
            return null;
        }

        $candidates = $query->get(['id', 'name', 'role', 'e_signature_path']);

        $matches = $candidates->filter(function (User $user) use ($tokens) {
            $userTokens = self::signatoryNameTokens($user->name);

            return $userTokens !== [] && $userTokens === $tokens;
        });

        if ($matches->isEmpty()) {
            return null;
        }

        return $matches
            ->sortByDesc(fn (User $user) => $user->hasESignature() ? 1 : 0)
            ->sortBy('name')
            ->first();
    }

    /**
     * @return list<string>
     */
    private static function signatoryNameTokens(string $name): array
    {
        $normalized = self::normalizeSignatoryName($name);
        $normalized = str_replace([',', '.'], ' ', $normalized);
        $parts = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $parts = array_values(array_filter($parts, fn (string $part) => $part !== ''));
        sort($parts, SORT_STRING);

        return $parts;
    }

    private static function normalizeSignatoryName(string $name): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name);

        return strtolower($normalized);
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
