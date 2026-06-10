<?php

namespace App\Models;

use App\Support\PayslipCsvImporter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePayslip extends Model
{
    protected $fillable = [
        'user_id',
        'uploaded_by',
        'company_name',
        'period_start',
        'period_end',
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
        'total_deductions',
        'total_working_days',
        'overtime_pay',
        'holiday_pay',
        'allowances',
        'thirteenth_month_pay',
        'gross_pay',
        'net_pay',
        'prepared_by',
        'approved_by',
        'signed_at',
        'signed_document_path',
        'storage_disk',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'date_hired' => 'date',
        'rate_per_day' => 'decimal:2',
        'sss' => 'decimal:2',
        'phic' => 'decimal:2',
        'hdmf' => 'decimal:2',
        'late_hours' => 'decimal:2',
        'absences_days' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'ca' => 'decimal:2',
        'govt_loans' => 'decimal:2',
        'loans' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'holiday_pay' => 'decimal:2',
        'allowances' => 'decimal:2',
        'thirteenth_month_pay' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'signed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isLinkedToEmployee(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * Position shown on payslips: the employee's assigned department position.
     */
    public function displayPosition(): ?string
    {
        if ($this->isLinkedToEmployee()) {
            $employee = $this->relationLoaded('employee')
                ? $this->employee
                : $this->employee()->with('departmentPosition:id,name')->first();

            $employee?->loadMissing('departmentPosition:id,name');
            $label = trim((string) $employee?->payslipPositionLabel());
            if ($label !== '') {
                return $label;
            }
        }

        $stored = trim((string) ($this->position ?? ''));

        return $stored !== '' ? $stored : null;
    }

    /**
     * Refresh stored position and date hired from the linked employee profile.
     */
    public function syncProfileFieldsFromEmployee(?User $employee = null): void
    {
        if (! $this->isLinkedToEmployee()) {
            return;
        }

        $employee ??= $this->relationLoaded('employee')
            ? $this->employee
            : $this->employee()->with(['department:id,name', 'departmentPosition:id,name'])->first();

        if (! $employee) {
            return;
        }

        $fields = PayslipCsvImporter::profileFieldsFromEmployee($employee);

        $this->forceFill([
            'position' => $fields['position'],
            'date_hired' => $fields['date_hired'],
        ]);

        if ($this->isDirty(['position', 'date_hired'])) {
            $this->save();
        }
    }

    public function displayDateHired(): ?\Illuminate\Support\Carbon
    {
        if ($this->isLinkedToEmployee()) {
            $employee = $this->relationLoaded('employee')
                ? $this->employee
                : $this->employee()->first(['id', 'date_hired']);

            if ($employee?->date_hired) {
                return $employee->date_hired;
            }
        }

        return $this->date_hired;
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null && ! empty($this->signed_document_path);
    }

    public function receivedBySignatureDataUri(): ?string
    {
        $employee = $this->relationLoaded('employee') ? $this->employee : $this->employee()->first();

        if (! $employee?->hasESignature()) {
            return null;
        }

        return \App\Support\EmployeeSampleDocument::eSignatureDataUri($employee);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function periodLabel(): string
    {
        return $this->period_start->format('F j').' to '.$this->period_end->format('F j, Y');
    }

    public function calculatedTotalDeductions(): float
    {
        return self::sumDeductionComponents(
            $this->sss,
            $this->phic,
            $this->hdmf,
            $this->late_hours,
            $this->absences_days,
            $this->withholding_tax,
            $this->ca,
            $this->govt_loans,
            $this->loans,
        );
    }

    public static function sumDeductionComponents(
        float|int|string|null ...$amounts
    ): float {
        $total = 0.0;

        foreach ($amounts as $amount) {
            $total += (float) $amount;
        }

        return round($total, 2);
    }

    public function formatMoney(float|int|string|null $amount): string
    {
        if ($amount === null || $amount === '' || (float) $amount == 0.0) {
            return '-';
        }

        return number_format((float) $amount, 2);
    }

    public function formatCount(float|int|string|null $value): string
    {
        if ($value === null || $value === '' || (float) $value == 0.0) {
            return '-';
        }

        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }
}
