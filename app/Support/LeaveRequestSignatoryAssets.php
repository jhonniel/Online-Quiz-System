<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\User;

final class LeaveRequestSignatoryAssets
{
    /**
     * @param  array{immediate_supervisor: string, hr_admin: string, cto: string}  $signatoryNames
     * @return array{
     *     employee: array{name: string, role: string, e_signature_data_uri: ?string},
     *     immediate_supervisor: array{name: string, role: string, e_signature_data_uri: ?string},
     *     hr_admin: array{name: string, role: string, e_signature_data_uri: ?string},
     *     cto: array{name: string, role: string, e_signature_data_uri: ?string}
     * }
     */
    public static function forLeaveRequest(LeaveRequest $leaveRequest, array $signatoryNames): array
    {
        $employee = $leaveRequest->user;

        return [
            'employee' => self::block(
                $employee?->name ?? '',
                strtoupper((string) ($employee?->role ?? 'Employee')),
                $employee
            ),
            'immediate_supervisor' => self::block(
                $signatoryNames['immediate_supervisor'] ?? '',
                'Immediate Supervisor',
                self::findUserByName($signatoryNames['immediate_supervisor'] ?? null)
            ),
            'hr_admin' => self::block(
                $signatoryNames['hr_admin'] ?? '',
                'HR Admin',
                self::findUserByName($signatoryNames['hr_admin'] ?? null)
            ),
            'cto' => self::block(
                $signatoryNames['cto'] ?? '',
                'Chief Technology Officer',
                self::findUserByName($signatoryNames['cto'] ?? null)
            ),
        ];
    }

    /**
     * @return array{name: string, role: string, e_signature_data_uri: ?string}
     */
    private static function block(string $name, string $role, ?User $user): array
    {
        return [
            'name' => $name,
            'role' => $role,
            'e_signature_data_uri' => ($user !== null && $user->hasESignature())
                ? EmployeeSampleDocument::eSignatureDataUri($user)
                : null,
        ];
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
}
