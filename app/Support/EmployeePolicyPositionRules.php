<?php

namespace App\Support;

use App\Models\User;

/**
 * @deprecated Use EmployeeDocumentPositionRules directly.
 */
final class EmployeePolicyPositionRules
{
    public const SETTING_KEY = 'employee_policy_position_rules';

    public static function all(): array
    {
        return EmployeeDocumentPositionRules::all('policy');
    }

    /**
     * @param  list<array<string, mixed>>  $rules
     */
    public static function saveFromRequest(array $rules): void
    {
        EmployeeDocumentPositionRules::saveFromRequest('policy', $rules);
    }

    public static function extrasForUser(User $user): array
    {
        return EmployeeDocumentPositionRules::extrasForUser($user, 'policy');
    }

    public static function policiesForUser(User $user): array
    {
        return EmployeeDocumentPositionRules::policiesForUser($user);
    }

    /**
     * @param  array<string, mixed>  $rule
     * @return array{position_ids: list<int>, policies: list<string>, content_html: string, policies_text: string}
     */
    public static function normalizeRule(array $rule): array
    {
        return EmployeeDocumentPositionRules::normalizeRule('policy', $rule);
    }
}
