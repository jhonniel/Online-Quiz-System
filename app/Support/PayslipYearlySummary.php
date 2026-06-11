<?php

namespace App\Support;

use App\Models\EmployeePayslip;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class PayslipYearlySummary
{
    public const COLUMN_COUNT = 16;

    /** @var list<string> */
    public const CSV_HEADERS = [
        'Employee Name',
        'Date Hired',
        'Total Basic Salary',
        'SSS',
        'HDMF',
        'PHIC',
        '13th Month',
        'Regular OT',
        'Holiday',
        'Gross Salary',
        'Taxable Income',
        'Tax Withheld',
        'CA',
        "Gov't Loans",
        'Loans',
        'Net Pay',
    ];

    public static function sheetTitle(int $year): string
    {
        return 'Payroll Sheet Yr. '.$year;
    }

    /**
     * @param  Collection<int, EmployeePayslip>  $payslips
     */
    public static function resolveCompanyName(Collection $payslips): string
    {
        $fromPayslip = $payslips
            ->pluck('company_name')
            ->map(fn ($name) => trim((string) $name))
            ->first(fn (string $name) => $name !== '');

        $name = $fromPayslip ?: EmployeeDocumentFooter::defaultCompanyName();

        return strtoupper(trim($name));
    }

    /**
     * @param  Collection<int, EmployeePayslip>  $payslips
     * @return array{rows: list<array<string, mixed>>, totals: array<string, float|int>, employee_count: int, payslip_count: int}
     */
    public static function build(Collection $payslips): array
    {
        /** @var array<string, array<string, mixed>> $grouped */
        $grouped = [];

        foreach ($payslips as $payslip) {
            $key = self::employeeGroupKey($payslip);

            if (! array_key_exists($key, $grouped)) {
                $grouped[$key] = self::emptyRow($payslip);
            }

            $row = &$grouped[$key];
            $row['payslip_count']++;
            $row['total_basic_salary'] += self::totalBasicSalaryForPayslip($payslip);
            $row['sss'] += (float) $payslip->sss;
            $row['hdmf'] += (float) $payslip->hdmf;
            $row['phic'] += (float) $payslip->phic;
            $row['thirteenth_month'] += (float) $payslip->thirteenth_month_pay;
            $row['regular_ot'] += (float) $payslip->overtime_pay;
            $row['holiday'] += (float) $payslip->holiday_pay;
            $row['gross_salary'] += (float) $payslip->gross_pay;
            $row['taxable_income'] += self::taxableIncomeForPayslip($payslip);
            $row['tax_withheld'] += (float) $payslip->withholding_tax;
            $row['ca'] += (float) $payslip->ca;
            $row['govt_loans'] += (float) $payslip->govt_loans;
            $row['loans'] += (float) $payslip->loans;
            $row['net_pay'] += (float) $payslip->net_pay;

            $dateHired = $payslip->displayDateHired();
            if ($dateHired !== null && ($row['date_hired'] === null || $dateHired->lt($row['date_hired']))) {
                $row['date_hired'] = $dateHired;
            }
        }

        $rows = collect($grouped)
            ->map(fn (array $row) => self::roundRow($row))
            ->sortBy('employee_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        $rows = self::attachSignatureDataUris($rows);

        $totals = self::emptyTotals();
        foreach ($rows as $row) {
            foreach ($totals as $field => $_) {
                if ($field === 'payslip_count') {
                    $totals[$field] += (int) $row['payslip_count'];

                    continue;
                }

                $totals[$field] += (float) $row[$field];
            }
        }

        $totals = self::roundTotals($totals);

        return [
            'rows' => $rows,
            'totals' => $totals,
            'employee_count' => count($rows),
            'payslip_count' => $payslips->count(),
        ];
    }

    /**
     * @param  array{rows: list<array<string, mixed>>, totals: array<string, float|int>}  $summary
     * @return list<list<string|int|float>>
     */
    public static function csvRows(array $summary, int $year, string $companyName): array
    {
        $columnCount = count(self::CSV_HEADERS);
        $blankRow = array_fill(0, $columnCount, '');

        $rows = [
            array_pad([$companyName], $columnCount, ''),
            array_pad([self::sheetTitle($year)], $columnCount, ''),
            $blankRow,
            self::CSV_HEADERS,
        ];

        foreach ($summary['rows'] as $row) {
            $rows[] = self::csvRowValues($row);
        }

        $totals = $summary['totals'];
        $rows[] = [
            'TOTAL',
            '',
            self::formatAmount($totals['total_basic_salary']),
            self::formatAmount($totals['sss']),
            self::formatAmount($totals['hdmf']),
            self::formatAmount($totals['phic']),
            self::formatAmount($totals['thirteenth_month']),
            self::formatAmount($totals['regular_ot']),
            self::formatAmount($totals['holiday']),
            self::formatAmount($totals['gross_salary']),
            self::formatAmount($totals['taxable_income']),
            self::formatAmount($totals['tax_withheld']),
            self::formatAmount($totals['ca']),
            self::formatAmount($totals['govt_loans']),
            self::formatAmount($totals['loans']),
            self::formatAmount($totals['net_pay']),
        ];

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    public static function csvRowValues(array $row): array
    {
        /** @var Carbon|null $dateHired */
        $dateHired = $row['date_hired'] ?? null;

        return [
            (string) $row['employee_name'],
            $dateHired instanceof Carbon ? $dateHired->format('Y-m-d') : '',
            self::formatAmount($row['total_basic_salary']),
            self::formatAmount($row['sss']),
            self::formatAmount($row['hdmf']),
            self::formatAmount($row['phic']),
            self::formatAmount($row['thirteenth_month']),
            self::formatAmount($row['regular_ot']),
            self::formatAmount($row['holiday']),
            self::formatAmount($row['gross_salary']),
            self::formatAmount($row['taxable_income']),
            self::formatAmount($row['tax_withheld']),
            self::formatAmount($row['ca']),
            self::formatAmount($row['govt_loans']),
            self::formatAmount($row['loans']),
            self::formatAmount($row['net_pay']),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private static function attachSignatureDataUris(array $rows): array
    {
        $userIds = collect($rows)
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($userIds === []) {
            return array_map(function (array $row) {
                $row['signature_data_uri'] = null;

                return $row;
            }, $rows);
        }

        $users = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'e_signature_path'])
            ->keyBy('id');

        return array_map(function (array $row) use ($users) {
            $userId = $row['user_id'] ?? null;
            $user = $userId !== null ? $users->get($userId) : null;
            $row['signature_data_uri'] = $user instanceof User
                ? EmployeeSampleDocument::eSignatureDataUri($user)
                : null;

            return $row;
        }, $rows);
    }

    public static function formatAmount(float|int|string|null $amount): string
    {
        return number_format((float) $amount, 2, '.', ',');
    }

    private static function employeeGroupKey(EmployeePayslip $payslip): string
    {
        if ($payslip->user_id !== null) {
            return 'user:'.$payslip->user_id;
        }

        return 'name:'.EmployeePayslip::normalizeEmployeeName($payslip->employee_name);
    }

    /**
     * @return array<string, mixed>
     */
    private static function emptyRow(EmployeePayslip $payslip): array
    {
        return [
            'employee_name' => $payslip->employee_name,
            'date_hired' => $payslip->displayDateHired(),
            'total_basic_salary' => 0.0,
            'sss' => 0.0,
            'hdmf' => 0.0,
            'phic' => 0.0,
            'thirteenth_month' => 0.0,
            'regular_ot' => 0.0,
            'holiday' => 0.0,
            'gross_salary' => 0.0,
            'taxable_income' => 0.0,
            'tax_withheld' => 0.0,
            'ca' => 0.0,
            'govt_loans' => 0.0,
            'loans' => 0.0,
            'net_pay' => 0.0,
            'payslip_count' => 0,
            'user_id' => $payslip->user_id,
            'signature_data_uri' => null,
        ];
    }

    /**
     * @return array<string, float|int>
     */
    private static function emptyTotals(): array
    {
        return [
            'total_basic_salary' => 0.0,
            'sss' => 0.0,
            'hdmf' => 0.0,
            'phic' => 0.0,
            'thirteenth_month' => 0.0,
            'regular_ot' => 0.0,
            'holiday' => 0.0,
            'gross_salary' => 0.0,
            'taxable_income' => 0.0,
            'tax_withheld' => 0.0,
            'ca' => 0.0,
            'govt_loans' => 0.0,
            'loans' => 0.0,
            'net_pay' => 0.0,
            'payslip_count' => 0,
        ];
    }

    private static function totalBasicSalaryForPayslip(EmployeePayslip $payslip): float
    {
        $rate = (float) ($payslip->rate_per_day ?? 0);
        $days = (float) ($payslip->total_working_days ?? 0);

        if ($rate > 0 && $days > 0) {
            return round($rate * $days + (float) ($payslip->allowances ?? 0), 2);
        }

        return round(max(
            0,
            (float) $payslip->gross_pay
                - (float) $payslip->overtime_pay
                - (float) $payslip->holiday_pay
                - (float) $payslip->thirteenth_month_pay
        ), 2);
    }

    private static function taxableIncomeForPayslip(EmployeePayslip $payslip): float
    {
        return round(max(
            0,
            (float) $payslip->gross_pay
                - (float) $payslip->sss
                - (float) $payslip->phic
                - (float) $payslip->hdmf
        ), 2);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private static function roundRow(array $row): array
    {
        foreach ([
            'total_basic_salary',
            'sss',
            'hdmf',
            'phic',
            'thirteenth_month',
            'regular_ot',
            'holiday',
            'gross_salary',
            'taxable_income',
            'tax_withheld',
            'ca',
            'govt_loans',
            'loans',
            'net_pay',
        ] as $field) {
            $row[$field] = round((float) $row[$field], 2);
        }

        return $row;
    }

    /**
     * @param  array<string, float|int>  $totals
     * @return array<string, float|int>
     */
    private static function roundTotals(array $totals): array
    {
        foreach ($totals as $field => $value) {
            if ($field === 'payslip_count') {
                continue;
            }

            $totals[$field] = round((float) $value, 2);
        }

        return $totals;
    }
}
