<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\User;

final class LeaveRequestSignatoryAssets
{
    /**
     * @return array{
     *     employee: array{name: string, role: string, e_signature_data_uri: ?string},
     *     immediate_supervisor: array{name: string, role: string, e_signature_data_uri: ?string},
     *     hr_admin: array{name: string, role: string, e_signature_data_uri: ?string},
     *     cto: array{name: string, role: string, e_signature_data_uri: ?string}
     * }
     */
    public static function forLeaveRequest(LeaveRequest $leaveRequest): array
    {
        $employee = $leaveRequest->user;
        $employee?->loadMissing('department');

        return [
            'employee' => self::block(
                $employee?->name ?? '',
                strtoupper((string) ($employee?->role ?? 'Employee')),
                $employee
            ),
            'immediate_supervisor' => self::block(
                LeaveRequestSignatorySettings::immediateSupervisorName($employee),
                'Immediate Supervisor',
                LeaveRequestSignatorySettings::immediateSupervisorUser($employee)
            ),
            'hr_admin' => self::block(
                LeaveRequestSignatorySettings::hrAdminName(),
                'HR Admin',
                LeaveRequestSignatorySettings::hrAdminUser()
            ),
            'cto' => self::block(
                LeaveRequestSignatorySettings::ctoName(),
                'Chief Technology Officer',
                LeaveRequestSignatorySettings::ctoUser()
            ),
        ];
    }

    /**
     * @return array{name: string, role: string, e_signature_data_uri: ?string}
     */
    private static function block(string $name, string $role, ?User $user): array
    {
        if ($user !== null && blank($user->e_signature_path)) {
            $user = User::query()
                ->whereKey($user->id)
                ->first(['id', 'name', 'role', 'e_signature_path']);
        }

        return [
            'name' => $name,
            'role' => $role,
            'e_signature_data_uri' => ($user !== null && $user->hasESignature())
                ? EmployeeSampleDocument::eSignatureDataUri($user)
                : null,
        ];
    }
}
