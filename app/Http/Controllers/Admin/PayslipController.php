<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeePayslip;
use App\Models\User;
use App\Support\AdminEmployeeDepartmentScope;
use App\Support\PayslipCsvImporter;
use App\Support\PayslipGrouper;
use App\Support\PayslipSignatorySettings;
use App\Support\PayslipYearlySummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PayslipController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->scopedPayslipQuery($request);

        if ($request->filled('year')) {
            $year = (int) $request->input('year');
            $query->whereYear('period_end', $year);
        }

        $payslips = $query
            ->orderByDesc('period_end')
            ->orderByDesc('period_start')
            ->orderBy('employee_name')
            ->get()
            ->each(fn (EmployeePayslip $payslip) => $payslip->syncProfileFieldsFromEmployee());

        $groupedPayslips = PayslipGrouper::group($payslips);
        $totalCount = $payslips->count();

        $availableYears = $this->scopedPayslipQuery($request, applyYearFilter: false)
            ->reorder()
            ->pluck('period_end')
            ->map(fn ($date) => (int) $date->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        $employees = $this->scopedEmployeeQuery()
            ->with('department:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'department_id']);

        return view('admin.employee-management.payslip.index', compact(
            'groupedPayslips',
            'totalCount',
            'availableYears',
            'employees'
        ));
    }

    private function scopedPayslipQuery(Request $request, bool $applyYearFilter = true)
    {
        $admin = $this->requireAuthUser();
        $restricted = AdminEmployeeDepartmentScope::isRestrictedForDocuments($admin);

        $query = EmployeePayslip::query()
            ->with(['employee:id,name,email,department_id,department_position_id,date_hired', 'employee.department:id,name', 'employee.departmentPosition:id,name,department_id', 'uploader:id,name'])
            ->where(function ($q) use ($restricted) {
                if ($restricted) {
                    $q->whereHas('employee', function ($employeeQuery) {
                        AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($employeeQuery, $this->requireAuthUser());
                    });
                } else {
                    $q->whereNull('user_id')
                        ->orWhereHas('employee', function ($employeeQuery) {
                            AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($employeeQuery, $this->requireAuthUser());
                        });
                }
            });

        if ($request->filled('search')) {
            $this->applyPayslipEmployeeSearch($query, trim((string) $request->input('search')));
        }

        if ($request->filled('period_start')) {
            $query->whereDate('period_start', '>=', $request->input('period_start'));
        }

        if ($request->filled('period_end')) {
            $query->whereDate('period_end', '<=', $request->input('period_end'));
        }

        if ($applyYearFilter && $request->filled('year')) {
            $query->whereYear('period_end', (int) $request->input('year'));
        }

        return $query;
    }

    public function show(EmployeePayslip $payslip)
    {
        $this->authorizePayslip($payslip);
        $payslip->load(['employee:id,name,email,department_id,department_position_id,date_hired,e_signature_path', 'employee.department:id,name', 'employee.departmentPosition:id,name,department_id', 'uploader']);
        $payslip->syncProfileFieldsFromEmployee();

        $employees = $this->scopedEmployeeQuery()
            ->with('department:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'department_id']);

        return view('admin.employee-management.payslip.show', compact('payslip', 'employees'));
    }

    public function signedPdf(Request $request, EmployeePayslip $payslip)
    {
        $this->authorizePayslip($payslip);
        abort_unless($payslip->isSigned(), 404);

        $disk = $this->resolveDiskForSignedPdf(
            (string) $payslip->signed_document_path,
            (string) ($payslip->storage_disk ?? '')
        );
        abort_if($disk === null, 404);

        $contents = Storage::disk($disk)->get((string) $payslip->signed_document_path);
        abort_if(! is_string($contents), 404);

        $filename = 'payslip-'.$payslip->period_start->format('Y-m-d').'-signed.pdf';
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    public function import(Request $request, PayslipCsvImporter $importer)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        try {
            $result = $importer->import($request->file('csv_file')->getRealPath(), (int) auth()->id(), auth()->user());
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('admin.payslip.index')
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.payslip.index')
                ->with('error', 'Failed to import payslips: '.$e->getMessage());
        }

        $message = "Imported {$result['imported']} payslip(s)";
        if ($result['skipped'] > 0) {
            $message .= ", skipped {$result['skipped']} row(s)";
        }
        $message .= '.';

        $flashKey = $result['imported'] > 0
            ? 'success'
            : (($result['errors'] ?? []) !== [] ? 'error' : 'success');

        return redirect()
            ->route('admin.payslip.index')
            ->with($flashKey, $message)
            ->with('import_errors', $result['errors'] ?? [])
            ->with('import_warnings', $result['warnings'] ?? []);
    }

    public function downloadTemplate()
    {
        $rows = [
            PayslipCsvImporter::HEADERS,
            [
                '2025-11-26',
                '2025-12-25',
                'JHONNIEL R. YGAY',
                'employee@example.com',
                '3863.63',
                '1750',
                '500',
                '200',
                '0',
                '0',
                '7103.43',
                '0',
                '0',
                '0',
                '22',
                '0',
                '0',
                '0',
                '0',
                '85000',
                '75446.43',
                PayslipSignatorySettings::adminOfficerName() ?? 'May Grace Acosta',
                PayslipSignatorySettings::proprietorName() ?? 'Jason V. Labanon',
            ],
        ];

        return $this->csvDownload($rows, 'payslip_import_template_'.date('Y-m-d').'.csv');
    }

    public function yearlySummary(Request $request)
    {
        $availableYears = $this->availablePayslipYears();
        $year = $this->resolveSummaryYear($request, $availableYears);
        $payslips = $this->payslipsForYearlySummary($request, $year);
        $summary = PayslipYearlySummary::build($payslips);
        $companyName = PayslipYearlySummary::resolveCompanyName($payslips);
        $sheetTitle = PayslipYearlySummary::sheetTitle($year);

        return view('admin.employee-management.payslip.yearly-summary', compact(
            'summary',
            'year',
            'availableYears',
            'companyName',
            'sheetTitle'
        ));
    }

    public function yearlySummaryCsv(Request $request)
    {
        $availableYears = $this->availablePayslipYears();
        $year = $this->resolveSummaryYear($request, $availableYears);
        $payslips = $this->payslipsForYearlySummary($request, $year);
        $summary = PayslipYearlySummary::build($payslips);
        $companyName = PayslipYearlySummary::resolveCompanyName($payslips);
        $rows = PayslipYearlySummary::csvRows($summary, $year, $companyName);

        return $this->csvDownload($rows, 'payslip_yearly_summary_'.$year.'_'.date('Y-m-d').'.csv');
    }

    public function destroy(Request $request, EmployeePayslip $payslip)
    {
        $this->authorizePayslip($payslip);
        $this->validateDeletePassword($request);

        $payslip->delete();

        return redirect()
            ->route('admin.payslip.index')
            ->with('success', 'Payslip deleted successfully.');
    }

    public function link(Request $request, EmployeePayslip $payslip)
    {
        $this->authorizePayslip($payslip);

        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:users,id',
        ]);

        $employee = $this->scopedEmployeeQuery()
            ->where('id', $validated['employee_id'])
            ->first();

        abort_unless($employee, 403, 'You do not have permission to link this employee.');

        $duplicate = EmployeePayslip::query()
            ->where('user_id', $employee->id)
            ->where('period_start', $payslip->period_start)
            ->where('period_end', $payslip->period_end)
            ->where('id', '!=', $payslip->id)
            ->exists();

        if ($duplicate) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'This employee already has a payslip for the same cut-off period.');
        }

        $employee->loadMissing(['department:id,name', 'departmentPosition:id,name,department_id']);

        $previousUserId = $payslip->user_id;
        $profileFields = PayslipCsvImporter::profileFieldsFromEmployee($employee);

        $targets = collect([$payslip])
            ->merge($this->relatedPayslipsForLinking($payslip, $previousUserId))
            ->unique('id');

        $updatedCount = 0;
        $skippedCount = 0;
        $signatureResetCount = 0;
        $reassignedAny = false;

        foreach ($targets as $target) {
            if ((int) $target->user_id === (int) $employee->id) {
                continue;
            }

            $hasDuplicatePeriod = EmployeePayslip::query()
                ->where('user_id', $employee->id)
                ->where('period_start', $target->period_start)
                ->where('period_end', $target->period_end)
                ->where('id', '!=', $target->id)
                ->exists();

            if ($hasDuplicatePeriod) {
                $skippedCount++;

                continue;
            }

            $needsSignatureReset = $target->user_id !== null
                && (int) $target->user_id !== (int) $employee->id;

            if ($needsSignatureReset) {
                $reassignedAny = true;
            }

            if ($needsSignatureReset && $target->signed_document_path) {
                $this->deleteStoredSignedPdf(
                    (string) $target->signed_document_path,
                    (string) ($target->storage_disk ?? '')
                );
                $signatureResetCount++;
            }

            $target->update(array_merge([
                'user_id' => $employee->id,
                'employee_email' => $employee->email,
                'signed_at' => $needsSignatureReset ? null : $target->signed_at,
                'signed_document_path' => $needsSignatureReset ? null : $target->signed_document_path,
                'storage_disk' => $needsSignatureReset ? null : $target->storage_disk,
            ], $profileFields));

            $updatedCount++;
        }

        if ($updatedCount === 0) {
            $message = $skippedCount > 0
                ? 'No payslips were updated. '.$skippedCount.' matching record(s) were skipped because '.$employee->name.' already has payslips for those cut-off periods.'
                : 'Payslip is already linked to '.$employee->name.'.';

            return redirect()
                ->back()
                ->with($skippedCount > 0 ? 'error' : 'success', $message);
        }

        $recordLabel = $updatedCount === 1 ? 'Payslip' : $updatedCount.' payslips';

        if ($reassignedAny) {
            $message = $recordLabel.' for '.$payslip->employee_name.' reassigned to '.$employee->name.'.';
            if ($signatureResetCount > 0) {
                $message .= ' '.$signatureResetCount.' signed record(s) were reset; the employee must sign again from their portal.';
            }
        } else {
            $message = $recordLabel.' for '.$payslip->employee_name.' linked to '.$employee->name.' successfully.';
        }

        if ($skippedCount > 0) {
            $message .= ' '.$skippedCount.' matching record(s) were skipped because '.$employee->name.' already has payslips for those cut-off periods.';
        }

        return redirect()
            ->back()
            ->with('success', $message);
    }

    /**
     * Other payslips that share the same CSV employee name and the same prior link state.
     *
     * @return \Illuminate\Support\Collection<int, EmployeePayslip>
     */
    private function relatedPayslipsForLinking(EmployeePayslip $payslip, ?int $previousUserId)
    {
        $normalizedName = EmployeePayslip::normalizeEmployeeName($payslip->employee_name);
        if ($normalizedName === '') {
            return collect();
        }

        $query = EmployeePayslip::query()
            ->where('id', '!=', $payslip->id);

        if ($previousUserId !== null) {
            $query->where(function ($q) use ($previousUserId) {
                $q->where('user_id', $previousUserId)->orWhereNull('user_id');
            });
        } else {
            $query->whereNull('user_id');
        }

        $admin = $this->requireAuthUser();
        if (AdminEmployeeDepartmentScope::isRestrictedForDocuments($admin)) {
            $query->where(function ($q) use ($admin) {
                $q->whereNull('user_id')
                    ->orWhereHas('employee', function ($employeeQuery) use ($admin) {
                        AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($employeeQuery, $admin);
                    });
            });
        }

        return $query
            ->get()
            ->filter(fn (EmployeePayslip $other) => EmployeePayslip::normalizeEmployeeName($other->employee_name) === $normalizedName)
            ->values();
    }

    private function deleteStoredSignedPdf(string $path, string $preferredDisk = ''): void
    {
        if ($path === '') {
            return;
        }

        foreach (array_values(array_unique(array_filter([
            $preferredDisk,
            'digitalocean',
            'public',
            config('filesystems.default', 'local'),
        ]))) as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);

                    return;
                }
            } catch (\Throwable) {
                continue;
            }
        }
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'payslip_ids' => 'required|array|min:1',
            'payslip_ids.*' => 'integer|exists:employee_payslips,id',
        ]);

        $this->validateDeletePassword($request);

        $deletedCount = EmployeePayslip::query()
            ->whereIn('id', $validated['payslip_ids'])
            ->where(function ($q) {
                if (AdminEmployeeDepartmentScope::isRestrictedForDocuments($this->requireAuthUser())) {
                    $q->whereHas('employee', function ($employeeQuery) {
                        AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($employeeQuery, $this->requireAuthUser());
                    });
                } else {
                    $q->whereNull('user_id')
                        ->orWhereHas('employee', function ($employeeQuery) {
                            AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($employeeQuery, $this->requireAuthUser());
                        });
                }
            })
            ->delete();

        if ($deletedCount === 0) {
            return redirect()
                ->back()
                ->with('error', 'No payslips were deleted.');
        }

        $message = $deletedCount === 1
            ? '1 payslip deleted successfully.'
            : "{$deletedCount} payslips deleted successfully.";

        return redirect()
            ->back()
            ->with('success', $message);
    }

    public function bulkPrint(Request $request)
    {
        $validated = $request->validate([
            'payslip_ids' => 'required|array|min:1',
            'payslip_ids.*' => 'integer|exists:employee_payslips,id',
        ]);

        $payslips = $this->payslipsForPrint(
            EmployeePayslip::query()->whereIn('id', $validated['payslip_ids'])
        );

        if ($payslips->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'No payslips were found to print.');
        }

        return view('admin.employee-management.payslip.bulk-print', compact('payslips'));
    }

    public function monthPrint(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        [$year, $month] = array_map('intval', explode('-', $validated['month'], 2));

        $payslips = $this->payslipsForPrint(
            EmployeePayslip::query()
                ->whereYear('period_end', $year)
                ->whereMonth('period_end', $month)
        );

        if ($payslips->isEmpty()) {
            return redirect()
                ->route('admin.payslip.index')
                ->with('error', 'No payslips were found for this month.');
        }

        return view('admin.employee-management.payslip.bulk-print', compact('payslips'));
    }

    public function cutoffPrint(Request $request)
    {
        $validated = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $payslips = $this->payslipsForPrint(
            EmployeePayslip::query()
                ->whereDate('period_start', $validated['period_start'])
                ->whereDate('period_end', $validated['period_end'])
        );

        if ($payslips->isEmpty()) {
            return redirect()
                ->route('admin.payslip.index')
                ->with('error', 'No payslips were found for this cut-off period.');
        }

        return view('admin.employee-management.payslip.bulk-print', compact('payslips'));
    }

    /**
     * @return \Illuminate\Support\Collection<int, EmployeePayslip>
     */
    private function payslipsForPrint($query)
    {
        return $query
            ->with(['employee:id,name,email,department_id,department_position_id,date_hired,e_signature_path', 'employee.department:id,name', 'employee.departmentPosition:id,name,department_id'])
            ->where(function ($q) {
                if (AdminEmployeeDepartmentScope::isRestrictedForDocuments($this->requireAuthUser())) {
                    $q->whereHas('employee', function ($employeeQuery) {
                        AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($employeeQuery, $this->requireAuthUser());
                    });
                } else {
                    $q->whereNull('user_id')
                        ->orWhereHas('employee', function ($employeeQuery) {
                            AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($employeeQuery, $this->requireAuthUser());
                        });
                }
            })
            ->orderByDesc('period_end')
            ->orderByDesc('period_start')
            ->orderBy('employee_name')
            ->get()
            ->each(fn (EmployeePayslip $payslip) => $payslip->syncProfileFieldsFromEmployee());
    }

    private function validateDeletePassword(Request $request): void
    {
        $request->validate([
            'confirm_password' => 'required|string',
        ], [
            'confirm_password.required' => 'Please enter your password to confirm deletion.',
        ]);

        $admin = $this->requireAuthUser();

        if (! Hash::check((string) $request->input('confirm_password'), (string) $admin->password)) {
            throw ValidationException::withMessages([
                'confirm_password' => ['Your password is incorrect.'],
            ]);
        }
    }

    private function authorizePayslip(EmployeePayslip $payslip): void
    {
        $admin = $this->requireAuthUser();
        $payslip->loadMissing('employee');

        if ($payslip->user_id && $payslip->employee) {
            abort_unless(AdminEmployeeDepartmentScope::canAccessEmployeeForDocuments($admin, $payslip->employee), 403);

            return;
        }

        abort_unless(! AdminEmployeeDepartmentScope::isRestrictedForDocuments($admin), 403, 'You do not have permission to access this payslip.');
    }

    private function canAccessEmployee(?User $employee): bool
    {
        return AdminEmployeeDepartmentScope::canAccessEmployeeForDocuments($this->requireAuthUser(), $employee);
    }

    private function scopedEmployeeQuery()
    {
        $query = User::query()->where('role', 'employee');
        AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($query, $this->requireAuthUser());

        return $query;
    }

    private function applyPayslipEmployeeSearch($query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $searchTokens = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $query->where(function ($q) use ($search, $searchTokens) {
            $q->where(fn ($termQ) => $this->applyPayslipEmployeeSearchTerm($termQ, $search));

            if (count($searchTokens) > 1) {
                $q->orWhere(function ($andQ) use ($searchTokens) {
                    foreach ($searchTokens as $token) {
                        $andQ->where(fn ($tokenQ) => $this->applyPayslipEmployeeSearchTerm($tokenQ, $token));
                    }
                });
            }
        });
    }

    private function applyPayslipEmployeeSearchTerm($query, string $term): void
    {
        $like = "%{$term}%";

        $query->where('employee_name', 'like', $like)
            ->orWhere('employee_email', 'like', $like)
            ->orWhere('position', 'like', $like)
            ->orWhereHas('employee', function ($eq) use ($like, $term) {
                $eq->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like);

                if (ctype_digit($term)) {
                    $eq->orWhere('id', (int) $term);
                }
            })
            ->orWhereHas('employee.department', fn ($dq) => $dq->where('name', 'like', $like));

        if (ctype_digit($term)) {
            $query->orWhere('user_id', (int) $term)
                ->orWhere('id', (int) $term);
        }
    }

    private function applyEmployeeScope($query): void
    {
        AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($query, $this->requireAuthUser());
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function availablePayslipYears()
    {
        return $this->scopedPayslipQuery(request(), applyYearFilter: false)
            ->reorder()
            ->pluck('period_end')
            ->map(fn ($date) => (int) $date->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>  $availableYears
     */
    private function resolveSummaryYear(Request $request, $availableYears): int
    {
        $year = (int) $request->input('year', 0);

        if ($year >= 2000 && $year <= 2100) {
            return $year;
        }

        $firstAvailable = $availableYears->first();

        return $firstAvailable !== null ? (int) $firstAvailable : (int) now()->format('Y');
    }

    /**
     * @return \Illuminate\Support\Collection<int, EmployeePayslip>
     */
    private function payslipsForYearlySummary(Request $request, int $year)
    {
        return $this->scopedPayslipQuery($request, applyYearFilter: false)
            ->whereYear('period_end', $year)
            ->orderBy('employee_name')
            ->get()
            ->each(fn (EmployeePayslip $payslip) => $payslip->syncProfileFieldsFromEmployee());
    }

    /**
     * @param  list<list<string>>  $rows
     */
    private function csvDownload(array $rows, string $filename)
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function resolveDiskForSignedPdf(string $path, string $preferredDisk = ''): ?string
    {
        if ($path === '') {
            return null;
        }

        foreach (array_values(array_unique(array_filter([
            $preferredDisk,
            'digitalocean',
            'public',
            'local',
        ]))) as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    return $disk;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }
}
