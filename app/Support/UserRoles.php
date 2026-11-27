<?php

namespace App\Support;

class UserRoles
{
    public const ALL = [
        'admin',
        'user',
        'student',
        'employee',
        'teacher',
        'applicant',
        'technician',
        'hr',
    ];

    /** Internal staff with department assignment (employee portal + optional admin permissions). */
    public const STAFF = ['employee', 'hr'];

    /**
     * Roles that use the employee-side DTR (Hubstaff import, admin DTR list, personal time records).
     * Admins/HR who work hours must be importable even though their account role is not "employee".
     */
    public const EMPLOYEE_DTR = ['employee', 'admin', 'hr'];

    public static function validationRule(): string
    {
        return 'in:'.implode(',', self::ALL);
    }

    public static function isStaff(?string $role): bool
    {
        return in_array($role, self::STAFF, true);
    }

    public static function isEmployeeDtrRole(?string $role): bool
    {
        return in_array($role, self::EMPLOYEE_DTR, true);
    }
}
