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

    public static function validationRule(): string
    {
        return 'in:'.implode(',', self::ALL);
    }

    public static function isStaff(?string $role): bool
    {
        return in_array($role, self::STAFF, true);
    }
}
