<?php

namespace App\Support;

use App\Models\EmployeePayslip;
use App\Models\Setting;
use App\Models\User;
use App\Support\AdminEmployeeDepartmentScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class PayslipCsvImporter
{
    /** @var array<string, int>|null */
    private ?array $linkedEmployeeIdsByName = null;

    private ?User $importAdmin = null;

    private string $csvDelimiter = ',';

    /** @var list<string> */
    public const HEADERS = [
        'cutt_off_start',
        'cutt_off_end',
        'employee_name',
        'employee_email',
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
     * @return array{imported: int, skipped: int, errors: list<string>, warnings: list<string>}
     */
    public function import(string $path, int $uploadedByUserId, ?User $admin = null): array
    {
        $this->importAdmin = $admin;
        $this->linkedEmployeeIdsByName = null;
        $this->csvDelimiter = $this->detectDelimiterFromFile($path);
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Unable to read the CSV file.');
        }

        $headerRow = fgetcsv($handle, 0, $this->csvDelimiter);
        if ($headerRow === false) {
            fclose($handle);
            throw new \RuntimeException('The CSV file is empty.');
        }

        $headerRow = $this->sanitizeHeaderRow($headerRow);
        $columnMap = $this->mapHeaders($headerRow);
        $companyName = trim((string) Setting::get('system_name', config('app.name', 'System')));
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $warnings = [];
        $rowNumber = 1;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, $this->csvDelimiter)) !== false) {
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

                if (AdminEmployeeDepartmentScope::isRestrictedForDocuments($this->importAdmin) && $user === null) {
                    $errors[] = 'Row '.$rowNumber.': employee not found in your allowed departments. Use the exact login email for an employee you manage.';
                    $skipped++;

                    continue;
                }

                if ($user !== null && ! $this->adminCanImportForEmployee($user)) {
                    $label = trim($data['employee_name']) !== '' ? $data['employee_name'] : ($user->name ?: 'employee');
                    $errors[] = 'Row '.$rowNumber.': '.$label.' is outside your allowed departments.';
                    $skipped++;

                    continue;
                }

                if ($user !== null) {
                    $profileIssue = $this->validateEmployeeProfileForPayslip($user, $data['employee_name'], $rowNumber);
                    if ($profileIssue !== null) {
                        $errors[] = $profileIssue;
                        $skipped++;

                        continue;
                    }

                    $data = array_merge($data, self::profileFieldsFromEmployee($user));
                } else {
                    $data['position'] = null;
                    $data['date_hired'] = null;
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

                $existing = $this->findExistingPayslip($user, $data);
                if ($existing !== null) {
                    $skipped++;
                    $warnings[] = "Row {$rowNumber}: payslip already exists for {$data['employee_name']} ({$data['period_start']} to {$data['period_end']}), skipped.";

                    continue;
                }

                EmployeePayslip::create($data);
                $imported++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);

            throw $e;
        }

        fclose($handle);

        return compact('imported', 'skipped', 'errors', 'warnings');
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
                $found = $map === []
                    ? 'none (the file may use the wrong delimiter — save as CSV UTF-8 or use the system template)'
                    : implode(', ', array_keys($map));

                throw new \InvalidArgumentException(
                    'Missing required CSV column: '.$required.'. Found columns: '.$found.'. Download the payslip template from this page and match the header row exactly.'
                );
            }
        }

        return $map;
    }

    private function normalizeHeader(string $header): string
    {
        $header = $this->stripBom(trim($header));
        $header = strtolower($header);
        $header = str_replace(["'", '’', '`', '"'], '', $header);
        $header = str_replace([' ', '-'], '_', $header);
        $header = preg_replace('/_+/', '_', $header) ?? $header;
        $header = trim($header, '_');

        $aliases = [
            'period_start' => 'cutt_off_start',
            'period_end' => 'cutt_off_end',
            'cutoff_start' => 'cutt_off_start',
            'cutoff_end' => 'cutt_off_end',
            'cut_off_start' => 'cutt_off_start',
            'cut_off_end' => 'cutt_off_end',
            'cutoff_period_start' => 'cutt_off_start',
            'cutoff_period_end' => 'cutt_off_end',
            'cut_off_period_start' => 'cutt_off_start',
            'cut_off_period_end' => 'cutt_off_end',
            'start_cut_off' => 'cutt_off_start',
            'end_cut_off' => 'cutt_off_end',
            'email' => 'employee_email',
            'employee_email_address' => 'employee_email',
            'employee_e_mail' => 'employee_email',
            'cash_advance' => 'ca',
            'govt_loan' => 'govt_loans',
            'govt_loans' => 'govt_loans',
            'government_loans' => 'govt_loans',
            'government_loan' => 'govt_loans',
            'loan' => 'loans',
            '13th_month' => 'thirteenth_month_pay',
            '13th_month_pay' => 'thirteenth_month_pay',
            'thirteenth_month' => 'thirteenth_month_pay',
            'employee' => 'employee_name',
            'name' => 'employee_name',
            'full_name' => 'employee_name',
        ];

        return $aliases[$header] ?? $header;
    }

    /**
     * @param  list<string|null>  $headerRow
     * @return list<string|null>
     */
    private function sanitizeHeaderRow(array $headerRow): array
    {
        return array_map(function ($header) {
            if ($header === null) {
                return null;
            }

            return $this->stripBom(trim((string) $header));
        }, $headerRow);
    }

    private function stripBom(string $value): string
    {
        if (str_starts_with($value, "\xEF\xBB\xBF")) {
            $value = substr($value, 3);
        }

        return $value;
    }

    private function detectDelimiterFromFile(string $path): string
    {
        $sample = @file_get_contents($path, false, null, 0, 8192);
        if (! is_string($sample) || $sample === '') {
            return ',';
        }

        $sample = str_replace(["\r\n", "\r"], "\n", $sample);
        $firstLine = strtok($sample, "\n") ?: '';

        return $this->detectDelimiter($firstLine);
    }

    private function detectDelimiter(string $line): string
    {
        $line = $this->stripBom($line);
        $candidates = [',', ';', "\t"];
        $best = ',';
        $bestCount = -1;

        foreach ($candidates as $delimiter) {
            $count = substr_count($line, $delimiter);
            if ($count > $bestCount) {
                $bestCount = $count;
                $best = $delimiter;
            }
        }

        return $best;
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
            $missing = [];
            if ($employeeName === '') {
                $missing[] = 'employee_name';
            }
            if ($periodStartRaw === '') {
                $missing[] = 'cutt_off_start';
            }
            if ($periodEndRaw === '') {
                $missing[] = 'cutt_off_end';
            }

            $errors[] = 'Row '.$rowNumber.': missing required value(s): '.implode(', ', $missing).'. Check that dates are filled in and column headers match the template.';

            return null;
        }

        try {
            $periodStart = Carbon::parse($periodStartRaw)->toDateString();
            $periodEnd = Carbon::parse($periodEndRaw)->toDateString();
        } catch (\Throwable $e) {
            $errors[] = "Row {$rowNumber}: invalid cut-off dates.";

            return null;
        }

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'employee_email' => $value('employee_email') ?: null,
            'employee_name' => $employeeName,
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
        $employeeColumns = ['id', 'name', 'email', 'date_hired', 'department_id', 'department_position_id'];
        $baseQuery = User::query()
            ->where('role', 'employee')
            ->with(['department:id,name', 'departmentPosition:id,name,department_id']);

        if ($this->importAdmin !== null && AdminEmployeeDepartmentScope::isRestrictedForDocuments($this->importAdmin)) {
            AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($baseQuery, $this->importAdmin);
        }

        if ($email) {
            $byEmail = (clone $baseQuery)
                ->whereRaw('LOWER(email) = ?', [strtolower($email)])
                ->first($employeeColumns);

            if ($byEmail) {
                return $byEmail;
            }

            // Email provided but no matching account — import as unlinked; do not guess by name.
            return null;
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
        $linkedPayslipQuery = EmployeePayslip::query()
            ->whereNotNull('user_id')
            ->orderByDesc('updated_at');

        if ($this->importAdmin !== null && AdminEmployeeDepartmentScope::isRestrictedForDocuments($this->importAdmin)) {
            $linkedPayslipQuery->whereHas('employee', function ($employeeQuery) {
                AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($employeeQuery, $this->importAdmin);
            });
        }

        $linkedPayslipQuery
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
        $user = User::query()
            ->whereKey($user->id)
            ->with(['department:id,name', 'departmentPosition:id,name,department_id'])
            ->first(['id', 'name', 'email', 'department_id', 'department_position_id', 'date_hired']);

        if (! $user) {
            return 'Row '.$rowNumber.': matched employee account no longer exists.';
        }

        $missing = [];

        if ($user->date_hired === null) {
            $missing[] = 'date hired';
        }

        if ($user->department_id === null || trim((string) $user->department?->name) === '') {
            $missing[] = 'department';
        }

        if ($user->department_position_id === null || trim((string) $user->payslipPositionLabel()) === '') {
            $missing[] = 'position';
        }

        if ($missing === []) {
            return null;
        }

        $label = trim($employeeName) !== '' ? $employeeName : ($user->name ?: 'employee');

        return 'Row '.$rowNumber.': '.$label.' is missing employee profile data ('.implode(', ', $missing).'). Set department, assigned position, and date hired on the user profile before importing.';
    }

    private function adminCanImportForEmployee(User $employee): bool
    {
        return AdminEmployeeDepartmentScope::canAccessEmployeeForDocuments($this->importAdmin, $employee);
    }

    private function normalizeName(string $name): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', $name) ?? $name));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function findExistingPayslip(?User $user, array $data): ?EmployeePayslip
    {
        $matchPeriod = fn ($query) => $query
            ->whereDate('period_start', $data['period_start'])
            ->whereDate('period_end', $data['period_end']);

        if ($user !== null) {
            $byUser = EmployeePayslip::query()
                ->where('user_id', $user->id)
                ->where($matchPeriod)
                ->first();

            if ($byUser !== null) {
                return $byUser;
            }
        }

        return EmployeePayslip::query()
            ->where('employee_name', $data['employee_name'])
            ->whereNull('user_id')
            ->where($matchPeriod)
            ->first();
    }

    /**
     * Payslip position uses the employee's assigned department position.
     *
     * @return array{position: ?string, date_hired: ?string}
     */
    public static function profileFieldsFromEmployee(User $user): array
    {
        $user->loadMissing(['department:id,name', 'departmentPosition:id,name,department_id']);

        $position = trim((string) $user->payslipPositionLabel());

        return [
            'position' => $position !== '' ? $position : null,
            'date_hired' => $user->date_hired?->toDateString(),
        ];
    }
}
