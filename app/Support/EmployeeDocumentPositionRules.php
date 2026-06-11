<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;

final class EmployeeDocumentPositionRules
{
    /** @var array<string, string> */
    public const SETTING_KEYS = [
        'policy' => 'employee_policy_position_rules',
        'contract' => 'employee_contract_position_rules',
    ];

    public static function supports(string $documentType): bool
    {
        return isset(self::SETTING_KEYS[$documentType]);
    }

    public static function settingKey(string $documentType): string
    {
        return self::SETTING_KEYS[$documentType] ?? '';
    }

    /**
     * @return list<array{position_ids: list<int>, policies: list<string>, content_html: string, policies_text: string, job_description_duties: list<string>, job_description_text: string}>
     */
    public static function all(string $documentType): array
    {
        if (! self::supports($documentType)) {
            return [];
        }

        $raw = Setting::get(self::settingKey($documentType), '[]');
        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;

        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->map(fn (mixed $rule): array => self::normalizeRule($documentType, is_array($rule) ? $rule : []))
            ->filter(fn (array $rule): bool => $rule['position_ids'] !== [])
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $rules
     */
    public static function saveFromRequest(string $documentType, array $rules): void
    {
        abort_unless(self::supports($documentType), 404);

        $normalized = collect($rules)
            ->map(fn (mixed $rule): array => self::normalizeRule($documentType, is_array($rule) ? $rule : []))
            ->filter(fn (array $rule): bool => $rule['position_ids'] !== [])
            ->values()
            ->all();

        $labels = [
            'policy' => 'Employee Policy position-specific content',
            'contract' => 'Employee Agreement position-specific content',
        ];

        Setting::set(
            self::settingKey($documentType),
            json_encode($normalized, JSON_UNESCAPED_UNICODE),
            'json',
            $labels[$documentType]
        );
        Setting::clearCache();
    }

    /**
     * @return array{policies: list<string>, content_html: string, job_description_duties: list<string>}
     */
    public static function extrasForUser(User $user, string $documentType): array
    {
        $positionId = $user->department_position_id;
        if ($positionId === null) {
            return ['policies' => [], 'content_html' => '', 'job_description_duties' => []];
        }

        $policies = [];
        $contentParts = [];
        $jobDescriptionDuties = [];

        foreach (self::all($documentType) as $rule) {
            if (! in_array((int) $positionId, $rule['position_ids'], true)) {
                continue;
            }

            foreach ($rule['policies'] as $policy) {
                $policies[] = $policy;
            }

            if ($rule['content_html'] !== '') {
                $contentParts[] = $rule['content_html'];
            }

            foreach ($rule['job_description_duties'] as $duty) {
                $jobDescriptionDuties[] = $duty;
            }
        }

        return [
            'policies' => array_values(array_unique($policies)),
            'content_html' => implode("\n", $contentParts),
            'job_description_duties' => array_values(array_unique($jobDescriptionDuties)),
        ];
    }

    /**
     * @return list<string>
     */
    public static function policiesForUser(User $user): array
    {
        $extras = self::extrasForUser($user, 'policy');

        return array_values(array_unique(array_merge(
            EmployeePolicyDocument::POLICIES,
            $extras['policies']
        )));
    }

    public static function contentHtmlForUser(User $user, string $documentType): string
    {
        return self::extrasForUser($user, $documentType)['content_html'];
    }

    /**
     * @return list<string>
     */
    public static function jobDescriptionDutiesForUser(User $user): array
    {
        return self::extrasForUser($user, 'contract')['job_description_duties'];
    }

    /**
     * @param  array<string, mixed>  $rule
     * @return array{position_ids: list<int>, policies: list<string>, content_html: string, policies_text: string, job_description_duties: list<string>, job_description_text: string}
     */
    public static function normalizeRule(string $documentType, array $rule): array
    {
        $positionIds = collect($rule['position_ids'] ?? [])
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        $policies = $documentType === 'policy'
            ? self::parseLineInput($rule['policies_text'] ?? $rule['policies'] ?? '')
            : [];

        $jobDescriptionDuties = $documentType === 'contract'
            ? self::parseLineInput($rule['job_description_text'] ?? $rule['job_description_duties'] ?? '')
            : [];

        $contentHtml = EmployeeDocumentTemplateSanitizer::sanitize(trim((string) ($rule['content_html'] ?? '')));

        return [
            'position_ids' => $positionIds,
            'policies' => $policies,
            'content_html' => $contentHtml,
            'policies_text' => implode("\n", $policies),
            'job_description_duties' => $jobDescriptionDuties,
            'job_description_text' => implode("\n", $jobDescriptionDuties),
        ];
    }

    /**
     * @param  mixed  $input
     * @return list<string>
     */
    private static function parseLineInput(mixed $input): array
    {
        if (is_array($input)) {
            return array_values(array_filter(array_map(
                fn (mixed $line): string => trim((string) $line),
                $input
            )));
        }

        if (! is_string($input)) {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', $input) ?: []
        )));
    }
}
