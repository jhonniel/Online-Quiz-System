<?php

namespace App\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class EmployeeProfileCsvImporter
{
    public const DEFAULT_PASSWORD = 'password';

    /** @var list<string> */
    public const HEADERS = [
        'email',
        'employee_name',
        'date_hired',
        'tin',
        'sss',
        'hdmf',
        'phic',
    ];

    /**
     * @return array{created: int, updated: int, skipped: int, errors: list<string>}
     */
    public function import(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Unable to read the CSV file.');
        }

        $headerRow = fgetcsv($handle);
        if ($headerRow === false) {
            fclose($handle);
            throw new \RuntimeException('The CSV file is empty.');
        }

        $columnMap = $this->mapHeaders($headerRow);
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $value = fn (string $key): string => trim((string) ($row[$columnMap[$key] ?? -1] ?? ''));

                $email = $value('email');
                $employeeName = $value('employee_name');

                if ($email === '' && $employeeName === '') {
                    $errors[] = "Row {$rowNumber}: email or employee_name is required.";
                    $skipped++;

                    continue;
                }

                $profilePayload = $this->buildProfilePayload($value, $rowNumber, $errors, $skipped);
                if ($profilePayload === false) {
                    continue;
                }

                $resolved = $this->resolveOrCreateEmployee($email, $employeeName, $rowNumber, $errors, $skipped);
                if ($resolved === null) {
                    continue;
                }

                ['user' => $user, 'wasCreated' => $wasCreated] = $resolved;

                if ($profilePayload !== []) {
                    $user->update($profilePayload);
                }

                if ($wasCreated) {
                    $created++;

                    continue;
                }

                if ($profilePayload === []) {
                    $errors[] = "Row {$rowNumber}: employee already exists and no data to import.";
                    $skipped++;

                    continue;
                }

                $updated++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);

            throw $e;
        }

        fclose($handle);

        return compact('created', 'updated', 'skipped', 'errors');
    }

    /**
     * @param  callable(string): string  $value
     * @param  list<string>  $errors
     * @return array<string, mixed>|false
     */
    private function buildProfilePayload(callable $value, int $rowNumber, array &$errors, int &$skipped): array|false
    {
        $payload = [];

        $employeeName = $value('employee_name');
        if ($employeeName !== '') {
            $payload['name'] = $employeeName;
        }

        $dateHiredRaw = $value('date_hired');
        if ($dateHiredRaw !== '' && ! in_array(strtolower($dateHiredRaw), ['-', 'n/a', 'na'], true)) {
            try {
                $payload['date_hired'] = Carbon::parse($dateHiredRaw)->toDateString();
            } catch (\Throwable) {
                $errors[] = "Row {$rowNumber}: invalid date_hired.";
                $skipped++;

                return false;
            }
        }

        foreach ([
            'tin' => 'tin',
            'sss' => 'sss_number',
            'hdmf' => 'hdmf_number',
            'phic' => 'phic_number',
        ] as $csvKey => $dbKey) {
            $raw = $value($csvKey);
            if ($raw !== '' && ! in_array(strtolower($raw), ['-', 'n/a', 'na'], true)) {
                $payload[$dbKey] = $raw;
            }
        }

        return $payload;
    }

    /**
     * @param  list<string>  $errors
     * @return array{user: User, wasCreated: bool}|null
     */
    private function resolveOrCreateEmployee(string $email, string $employeeName, int $rowNumber, array &$errors, int &$skipped): ?array
    {
        if ($email !== '') {
            $existingUser = User::query()
                ->whereRaw('LOWER(email) = ?', [strtolower($email)])
                ->first();

            if ($existingUser) {
                if ($existingUser->role !== 'employee') {
                    $errors[] = "Row {$rowNumber}: email already belongs to a non-employee account ({$email}).";
                    $skipped++;

                    return null;
                }

                return ['user' => $existingUser, 'wasCreated' => false];
            }

            if ($employeeName === '') {
                $errors[] = "Row {$rowNumber}: employee_name is required to create a new employee ({$email}).";
                $skipped++;

                return null;
            }

            $user = User::create([
                'name' => $employeeName,
                'email' => $email,
                'password' => self::DEFAULT_PASSWORD,
                'role' => 'employee',
                'is_active' => true,
                'is_approved' => true,
            ]);

            return ['user' => $user, 'wasCreated' => true];
        }

        $user = $this->resolveEmployeeByName($employeeName);
        if (! $user) {
            $errors[] = "Row {$rowNumber}: employee not found ({$employeeName}). Provide email to create a new employee account.";
            $skipped++;

            return null;
        }

        return ['user' => $user, 'wasCreated' => false];
    }

    /**
     * @param  list<string|null>  $headerRow
     * @return array<string, int>
     */
    private function mapHeaders(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $index => $header) {
            $normalized = $this->normalizeHeader((string) $header);
            if ($normalized !== '') {
                $map[$normalized] = $index;
            }
        }

        if (! array_key_exists('email', $map) && ! array_key_exists('employee_name', $map)) {
            throw new \InvalidArgumentException('CSV must include an email or employee_name column.');
        }

        return $map;
    }

    private function normalizeHeader(string $header): string
    {
        $header = strtolower(trim($header));
        $header = str_replace([' ', '-'], '_', $header);

        $aliases = [
            'employee_email' => 'email',
            'name' => 'employee_name',
            'full_name' => 'employee_name',
            'sss_number' => 'sss',
            'hdmf_number' => 'hdmf',
            'phic_number' => 'phic',
            'pagibig' => 'hdmf',
            'pag_ibig' => 'hdmf',
        ];

        $header = preg_replace('/_+/', '_', $header) ?? $header;

        return $aliases[$header] ?? $header;
    }

    /**
     * @param  list<string|null>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function resolveEmployeeByName(string $name): ?User
    {
        if ($name === '') {
            return null;
        }

        $normalizedName = $this->normalizeName($name);

        return User::query()
            ->where('role', 'employee')
            ->get(['id', 'name'])
            ->first(fn (User $user) => $this->normalizeName($user->name) === $normalizedName);
    }

    private function normalizeName(string $name): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', $name) ?? $name));
    }
}
