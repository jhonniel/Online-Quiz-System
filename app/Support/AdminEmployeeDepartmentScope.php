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

    /**
     * Employee Documents (payslip, file request, NDA, etc.) are not limited by department scope.
     */
    public static function bypassesDepartmentScopeForDocuments(?User $admin): bool
    {
        if ($admin === null) {
            return false;
        }

        if ($admin->isSuperAdmin()) {
            return true;
        }

        return $admin->canAccessAnyEmployeeDocumentFeature();
    }

    public static function isRestrictedForDocuments(?User $admin): bool
    {
        if (self::bypassesDepartmentScopeForDocuments($admin)) {
            return false;
        }

        return self::isRestricted($admin);
    }

    public static function employeeDepartmentId(?User $employee): ?int
    {
        if (! $employee || $employee->role !== 'employee') {
            return null;
        }

        if ($employee->department_id !== null) {
            return (int) $employee->department_id;
        }

        $employee->loadMissing('departmentPosition:id,department_id');
        $departmentId = $employee->departmentPosition?->department_id;

        return $departmentId !== null ? (int) $departmentId : null;
    }

    public static function canAccessEmployee(?User $admin, ?User $employee): bool
    {
        if (! $employee || $employee->role !== 'employee') {
            return false;
        }

        $allowedDepartmentIds = self::normalizedAllowedDepartmentIds($admin);
        if ($allowedDepartmentIds === null) {
            return true;
        }

        $employeeDepartmentId = self::employeeDepartmentId($employee);
        if ($employeeDepartmentId === null) {
            return false;
        }

        return in_array($employeeDepartmentId, $allowedDepartmentIds, true);
    }

    public static function canAccessEmployeeForDocuments(?User $admin, ?User $employee): bool
    {
        if (! $employee || $employee->role !== 'employee') {
            return false;
        }

        if (self::bypassesDepartmentScopeForDocuments($admin)) {
            return true;
        }

        return self::canAccessEmployee($admin, $employee);
    }

    public static function applyToEmployeeQuery($query, ?User $admin): void
    {
        $allowedDepartmentIds = self::normalizedAllowedDepartmentIds($admin);
        if ($allowedDepartmentIds === null) {
            return;
        }

        $query->where(function ($scopedQuery) use ($allowedDepartmentIds) {
            $scopedQuery->whereIn('department_id', $allowedDepartmentIds)
                ->orWhereHas('departmentPosition', function ($positionQuery) use ($allowedDepartmentIds) {
                    $positionQuery->whereIn('department_id', $allowedDepartmentIds);
                });
        });
    }

    public static function applyToEmployeeQueryForDocuments($query, ?User $admin): void
    {
        if (self::bypassesDepartmentScopeForDocuments($admin)) {
            return;
        }

        self::applyToEmployeeQuery($query, $admin);
    }

    /**
     * @return list<int>|null
     */
    private static function normalizedAllowedDepartmentIds(?User $admin): ?array
    {
        $allowedDepartmentIds = self::allowedDepartmentIds($admin);
        if ($allowedDepartmentIds === null) {
            return null;
        }

        return array_values(array_unique(array_map('intval', $allowedDepartmentIds)));
    }
}
