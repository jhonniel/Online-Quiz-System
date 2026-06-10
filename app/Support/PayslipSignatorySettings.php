<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class PayslipSignatorySettings
{
    public const ADMIN_OFFICER_USER_ID_KEY = 'payslip_admin_officer_user_id';

    public const PROPRIETOR_USER_ID_KEY = 'payslip_proprietor_user_id';

    public static function adminOfficerUserId(): ?int
    {
        $id = (int) Setting::get(self::ADMIN_OFFICER_USER_ID_KEY, 0);

        return $id > 0 ? $id : null;
    }

    public static function proprietorUserId(): ?int
    {
        $id = (int) Setting::get(self::PROPRIETOR_USER_ID_KEY, 0);

        return $id > 0 ? $id : null;
    }

    public static function adminOfficerUser(): ?User
    {
        $id = self::adminOfficerUserId();

        return $id ? self::findSelectableUser($id) : null;
    }

    public static function proprietorUser(): ?User
    {
        $id = self::proprietorUserId();

        return $id ? self::findSelectableUser($id) : null;
    }

    public static function adminOfficerName(): ?string
    {
        $name = trim((string) (self::adminOfficerUser()?->name ?? ''));

        return $name !== '' ? $name : null;
    }

    public static function proprietorName(): ?string
    {
        $name = trim((string) (self::proprietorUser()?->name ?? ''));

        return $name !== '' ? $name : null;
    }

    public static function documentEmployerName(): string
    {
        $fromUser = self::proprietorName();
        if ($fromUser !== null) {
            return $fromUser;
        }

        $name = trim((string) Setting::get(
            'policy_employer_name',
            Setting::get('nda_signatory_name', 'Jason V. Labanon')
        ));

        return $name !== '' ? $name : 'Jason V. Labanon';
    }

    public static function documentEmployerPosition(): string
    {
        if (self::proprietorUser() !== null) {
            return 'Proprietor';
        }

        $position = trim((string) Setting::get(
            'policy_employer_position',
            Setting::get('nda_signatory_title', 'Proprietor')
        ));

        return $position !== '' ? $position : 'Proprietor';
    }

    public static function isSelectableUser(?User $user): bool
    {
        if (! $user || ! $user->is_active) {
            return false;
        }

        return in_array($user->role, ['employee', 'admin'], true);
    }

    public static function selectableUsersQuery(): Builder
    {
        return User::query()
            ->whereIn('role', ['employee', 'admin'])
            ->where('is_active', true)
            ->orderBy('name');
    }

    private static function findSelectableUser(int $id): ?User
    {
        $user = User::query()->find($id);

        return self::isSelectableUser($user) ? $user : null;
    }
}
