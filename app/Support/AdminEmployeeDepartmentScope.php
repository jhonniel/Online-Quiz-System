<?php

namespace App\Support;

use App\Models\User;

final class AdminEmployeeDepartmentScope
{
    /**
     * @return list<int>|null null means all departments
     */
    public static function allowedDepartmentIds(?User $admin): ?array
    {
        return $admin?->getAllowedDepartmentIds();
    }

    public static function isRestricted(?User $admin): bool
    {
        return self::allowedDepartmentIds($admin) !== null;
    }

    public static function canAccessEmployee(?User $admin, ?User $employee): bool
    {
        if (! $employee || $employee->role !== 'employee') {
            return false;
        }

        $allowedDepartmentIds = self::allowedDepartmentIds($admin);
        if ($allowedDepartmentIds === null) {
            return true;
        }

        if ($employee->department_id === null) {
            return false;
        }

        return in_array((int) $employee->department_id, $allowedDepartmentIds, true);
    }

    public static function applyToEmployeeQuery($query, ?User $admin): void
    {
        $allowedDepartmentIds = self::allowedDepartmentIds($admin);
        if ($allowedDepartmentIds !== null) {
            $query->whereIn('department_id', $allowedDepartmentIds);
        }
    }
}
