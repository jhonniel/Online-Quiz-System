<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmployeeDocumentRequestTypes
{
    public const SETTING_KEY = 'employee_document_request_types';

    /**
     * @return list<array{key: string, label: string, enabled: bool, sort: int}>
     */
    public static function defaultTypes(): array
    {
        $rows = [
            ['key' => 'certificate_of_employment', 'label' => 'Certificate of Employment', 'enabled' => true],
            ['key' => 'certificate_of_good_standing', 'label' => 'Certificate of Good Standing', 'enabled' => true],
            ['key' => 'employment_verification', 'label' => 'Employment Verification Letter', 'enabled' => true],
            ['key' => 'salary_certificate', 'label' => 'Salary / Compensation Certificate', 'enabled' => true],
            ['key' => 'other', 'label' => 'Other document', 'enabled' => true],
        ];

        return self::assignSortOrder($rows);
    }

    /**
     * All configured types (including disabled), for admin settings.
     *
     * @return list<array{key: string, label: string, enabled: bool}>
     */
    public static function all(): array
    {
        $stored = Setting::get(self::SETTING_KEY);

        if (! is_array($stored) || $stored === []) {
            return self::defaultTypes();
        }

        return self::sortTypes(self::normalizeTypes($stored));
    }

    /**
     * Enabled types for employee dropdown: key => label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (self::all() as $type) {
            if (! ($type['enabled'] ?? false)) {
                continue;
            }
            $key = $type['key'] ?? '';
            $label = trim((string) ($type['label'] ?? ''));
            if ($key !== '' && $label !== '') {
                $labels[$key] = $label;
            }
        }

        return $labels;
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::labels());
    }

    public static function label(?string $type): string
    {
        if ($type === null || $type === '') {
            return 'Document';
        }

        foreach (self::all() as $row) {
            if (($row['key'] ?? '') === $type) {
                $label = trim((string) ($row['label'] ?? ''));

                return $label !== '' ? $label : self::humanizeKey($type);
            }
        }

        return self::humanizeKey($type);
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $submitted
     */
    public static function persistFromRequest(?array $submitted): void
    {
        if ($submitted === null) {
            return;
        }

        $normalized = self::normalizeTypes($submitted);

        $enabledCount = count(array_filter($normalized, fn (array $row): bool => (bool) ($row['enabled'] ?? false)));

        if ($enabledCount < 1) {
            throw ValidationException::withMessages([
                'employee_document_request_types' => 'At least one document type must be enabled.',
            ]);
        }

        Setting::set(
            self::SETTING_KEY,
            $normalized,
            'json',
            'Employee document request types shown in the employee portal and File Request admin'
        );
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return list<array{key: string, label: string, enabled: bool, sort: int}>
     */
    public static function normalizeTypes(array $rows): array
    {
        $rows = array_values(array_filter($rows, is_array(...)));

        usort($rows, fn (array $a, array $b): int => (int) ($a['sort'] ?? 0) <=> (int) ($b['sort'] ?? 0));

        $normalized = [];
        $usedKeys = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = trim((string) ($row['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $key = trim((string) ($row['key'] ?? ''));
            if ($key === '') {
                $key = Str::slug($label, '_');
            }
            $key = strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace('-', '_', $key)) ?? '');
            if ($key === '') {
                $key = 'document_type';
            }

            $baseKey = $key;
            $suffix = 2;
            while (in_array($key, $usedKeys, true)) {
                $key = $baseKey.'_'.$suffix;
                $suffix++;
            }
            $usedKeys[] = $key;

            if (! array_key_exists('enabled', $row)) {
                $enabled = false;
            } else {
                $enabled = filter_var($row['enabled'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($enabled === null) {
                    $enabled = ! in_array($row['enabled'], [0, '0', 'false', false], true);
                }
            }

            $normalized[] = [
                'key' => Str::limit($key, 80, ''),
                'label' => Str::limit($label, 120, ''),
                'enabled' => $enabled,
                'sort' => count($normalized),
            ];
        }

        return self::assignSortOrder($normalized);
    }

    /**
     * @param  list<array{key: string, label: string, enabled: bool, sort?: int}>  $rows
     * @return list<array{key: string, label: string, enabled: bool, sort: int}>
     */
    private static function sortTypes(array $rows): array
    {
        usort($rows, fn (array $a, array $b): int => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));

        return $rows;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{key: string, label: string, enabled: bool, sort: int}>
     */
    private static function assignSortOrder(array $rows): array
    {
        foreach ($rows as $index => &$row) {
            $row['sort'] = $index;
        }
        unset($row);

        return $rows;
    }

    private static function humanizeKey(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }
}
