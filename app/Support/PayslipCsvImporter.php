<?php

namespace App\Support;

use App\Models\EmployeePayslip;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class PayslipCsvImporter
{
    /** @var array<string, int>|null */
    private ?array $linkedEmployeeIdsByName = null;

    /** @var list<string> */
    public const HEADERS = [
        'cutt_off_start',
        'cutt_off_end',
        'employee_name',
        'employee_email',
        'position',
        'date_hired',
        'rate_per_day',
        'sss',
        'phic',
        'hdmf',
        'late_hours',
        'absences_days',
        'withholding_tax',
        'ca',
        'govt_loans',
        'loans',
        'total_working_days',
        'overtime_pay',
        'holiday_pay',
        'allowances',
        'thirteenth_month_pay',
        'gross_pay',
        'net_pay',
        'prepared_by',
        'approved_by',
    ];

    /**
     * @return array{imported: int, updated: int, skipped: int, errors: list<string>}
     */
    public function import(string $path, int $uploadedByUserId): array
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
        $companyName = trim((string) Setting::get('system_name', config('app.name', 'System')));
        $imported = 0;
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

                $data = $this->parseRow($row, $columnMap, $rowNumber, $errors);
                if ($data === null) {
                    $skipped++;

                    continue;
                }

                $user = $this->resolveEmployee($data['employee_email'], $data['employee_name']);

                if ($user !== null) {
                    $profileIssue = $this->validateEmployeeProfileForPayslip($user, $data['employee_name'], $rowNumber);
                    if ($profileIssue !== null) {
                        $errors[] = $profileIssue;
                        $skipped++;

                        continue;
                    }

                    $data['date_hired'] = $user->date_hired->toDateString();
                    $data['position'] = trim((string) $user->department?->name);
                }

                $data['user_id'] = $user?->id;
                $data['uploaded_by'] = $uploadedByUserId;
                $data['company_name'] = $companyName;
                $data['total_deductions'] = EmployeePayslip::sumDeductionComponents(
                    $data['sss'],
                    $data['phic'],
                    $data['hdmf'],
                    $data['late_hours'],
                    $data['absences_days'],
                    $data['withholding_tax'],
                    $data['ca'],
                    $data['govt_loans'],
                    $data['loans'],
                );

                $matchAttributes = $user
                    ? ['user_id' => $user->id, 'period_start' => $data['period_start'], 'period_end' => $data['period_end']]
                    : ['employee_name' => $data['employee_name'], 'period_start' => $data['period_start'], 'period_end' => $data['period_end'], 'user_id' => null];

                $existing = EmployeePayslip::query()->where($matchAttributes)->first();
                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    EmployeePayslip::create($data);
                    $imported++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);

            throw $e;
        }

        fclose($handle);

        return compact('imported', 'updated', 'skipped', 'errors');
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

        foreach (self::HEADERS as $required) {
            if (! array_key_exists($required, $map)) {
                throw new \InvalidArgumentException('Missing required CSV column: '.$required);
            }
        }

        return $map;
    }

    private function normalizeHeader(string $header): string
    {
        $header = strtolower(trim($header));
        $header = str_replace(["'", '’', '`'], '', $header);
        $header = str_replace([' ', '-'], '_', $header);
        $header = preg_replace('/_+/', '_', $header) ?? $header;

        $aliases = [
            'period_start' => 'cutt_off_start',
            'period_end' => 'cutt_off_end',
            'cutoff_start' => 'cutt_off_start',
            'cutoff_end' => 'cutt_off_end',
            'cut_off_start' => 'cutt_off_start',
            'cut_off_end' => 'cutt_off_end',
            'email' => 'employee_email',
            'employee_email_address' => 'employee_email',
            'cash_advance' => 'ca',
            'govt_loan' => 'govt_loans',
            'govt_loans' => 'govt_loans',
            'government_loans' => 'govt_loans',
            'government_loan' => 'govt_loans',
            'loan' => 'loans',
            '13th_month' => 'thirteenth_month_pay',
            '13th_month_pay' => 'thirteenth_month_pay',
            'thirteenth_month' => 'thirteenth_month_pay',
        ];

        return $aliases[$header] ?? $header;
    }

    /**
     * @param  list<string|null>  $row
     * @param  array<string, int>  $columnMap
     * @param  list<string>  $errors
     * @return array<string, mixed>|null
     */
    private function parseRow(array $row, array $columnMap, int $rowNumber, array &$errors): ?array
    {
        $value = fn (string $key): string => trim((string) ($row[$columnMap[$key]] ?? ''));

        $employeeName = $value('employee_name');
        $periodStartRaw = $value('cutt_off_start');
        $periodEndRaw = $value('cutt_off_end');

        if ($employeeName === '' || $periodStartRaw === '' || $periodEndRaw === '') {
            $errors[] = "Row {$rowNumber}: employee_name, cutt_off_start, and cutt_off_end are required.";

            return null;
        }

        try {
            $periodStart = Carbon::parse($periodStartRaw)->toDateString();
            $periodEnd = Carbon::parse($periodEndRaw)->toDateString();
        } catch (\Throwable $e) {
            $errors[] = "Row {$rowNumber}: invalid cut-off dates.";

            return null;
        }

        $dateHired = null;
        $dateHiredRaw = $value('date_hired');
        if ($dateHiredRaw !== '' && ! in_array(strtolower($dateHiredRaw), ['-', 'n/a', 'na'], true)) {
            try {
                $dateHired = Carbon::parse($dateHiredRaw)->toDateString();
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNumber}: invalid date_hired.";

                return null;
            }
        }

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'employee_email' => $value('employee_email') ?: null,
            'employee_name' => $employeeName,
            'position' => $value('position') ?: null,
            'date_hired' => $dateHired,
            'rate_per_day' => $this->parseAmount($value('rate_per_day')),
            'sss' => $this->parseAmount($value('sss')),
            'phic' => $this->parseAmount($value('phic')),
            'hdmf' => $this->parseAmount($value('hdmf')),
            'late_hours' => $this->parseAmount($value('late_hours')),
            'absences_days' => $this->parseAmount($value('absences_days')),
            'withholding_tax' => $this->parseAmount($value('withholding_tax')),
            'ca' => $this->parseAmount($value('ca')),
            'govt_loans' => $this->parseAmount($value('govt_loans')),
            'loans' => $this->parseAmount($value('loans')),
            'total_working_days' => (int) round($this->parseAmount($value('total_working_days'))),
            'overtime_pay' => $this->parseAmount($value('overtime_pay')),
            'holiday_pay' => $this->parseAmount($value('holiday_pay')),
            'allowances' => $this->parseAmount($value('allowances')),
            'thirteenth_month_pay' => $this->parseAmount($value('thirteenth_month_pay')),
            'gross_pay' => $this->parseAmount($value('gross_pay')),
            'net_pay' => $this->parseAmount($value('net_pay')),
            'prepared_by' => $value('prepared_by') ?: null,
            'approved_by' => $value('approved_by') ?: null,
        ];
    }

    private function parseAmount(string $raw): float
    {
        $raw = trim($raw);
        if ($raw === '' || $raw === '-' || strcasecmp($raw, 'n/a') === 0) {
            return 0.0;
        }

        $normalized = str_replace([',', ' '], '', $raw);

        return is_numeric($normalized) ? (float) $normalized : 0.0;
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

    private function resolveEmployee(?string $email, string $name): ?User
    {
        $employeeColumns = ['id', 'name', 'email', 'date_hired', 'department_id'];
        $baseQuery = User::query()
            ->where('role', 'employee')
            ->with('department:id,name');

        if ($email) {
            $byEmail = (clone $baseQuery)
                ->whereRaw('LOWER(email) = ?', [strtolower($email)])
                ->first($employeeColumns);

            if ($byEmail) {
                return $byEmail;
            }
        }

        $normalizedName = $this->normalizeName($name);
        if ($normalizedName === '') {
            return null;
        }

        $linkedUserId = $this->linkedEmployeeIdsByName()[$normalizedName] ?? null;
        if ($linkedUserId !== null) {
            $byPriorLink = (clone $baseQuery)->find($linkedUserId, $employeeColumns);
            if ($byPriorLink) {
                return $byPriorLink;
            }
        }

        return (clone $baseQuery)
            ->get($employeeColumns)
            ->first(fn (User $user) => $this->normalizeName($user->name) === $normalizedName);
    }

    /**
     * Names from previously linked payslips mapped to employee user IDs.
     *
     * @return array<string, int>
     */
    private function linkedEmployeeIdsByName(): array
    {
        if ($this->linkedEmployeeIdsByName !== null) {
            return $this->linkedEmployeeIdsByName;
        }

        $map = [];
        EmployeePayslip::query()
            ->whereNotNull('user_id')
            ->orderByDesc('updated_at')
            ->get(['employee_name', 'user_id'])
            ->each(function (EmployeePayslip $payslip) use (&$map): void {
                $key = $this->normalizeName($payslip->employee_name);
                if ($key !== '' && ! array_key_exists($key, $map)) {
                    $map[$key] = (int) $payslip->user_id;
                }
            });

        $this->linkedEmployeeIdsByName = $map;

        return $map;
    }

    private function validateEmployeeProfileForPayslip(User $user, string $employeeName, int $rowNumber): ?string
    {
        $missing = [];

        if ($user->date_hired === null) {
            $missing[] = 'date hired';
        }

        if ($user->department_id === null || trim((string) $user->department?->name) === '') {
            $missing[] = 'department';
        }

        if ($missing === []) {
            return null;
        }

        $label = trim($employeeName) !== '' ? $employeeName : ($user->name ?: 'employee');

        return 'Row '.$rowNumber.': '.$label.' is missing employee profile data ('.implode(', ', $missing).'). Update the user profile before importing payslips.';
    }

    private function normalizeName(string $name): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', $name) ?? $name));
    }
}
