<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeePayslip;
use App\Models\User;
use App\Support\AdminEmployeeDepartmentScope;
use App\Support\PayslipCsvImporter;
use App\Support\PayslipGrouper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('employee_email', 'like', "%{$search}%")
                    ->orWhereHas('employee', fn ($eq) => $eq->where('email', 'like', "%{$search}%"));
            });
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
                'May Grace Acosta',
                'Jason V. Labanon',
            ],
        ];

        return $this->csvDownload($rows, 'payslip_import_template_'.date('Y-m-d').'.csv');
    }

    public function destroy(EmployeePayslip $payslip)
    {
        $this->authorizePayslip($payslip);
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

        $payslip->update(array_merge([
            'user_id' => $employee->id,
            'employee_email' => $employee->email,
        ], PayslipCsvImporter::profileFieldsFromEmployee($employee)));

        return redirect()
            ->back()
            ->with('success', 'Payslip linked to '.$employee->name.' successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'payslip_ids' => 'required|array|min:1',
            'payslip_ids.*' => 'integer|exists:employee_payslips,id',
        ]);

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

        $payslips = EmployeePayslip::query()
            ->with(['employee:id,name,email,department_id,department_position_id,date_hired,e_signature_path', 'employee.department:id,name', 'employee.departmentPosition:id,name,department_id'])
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
            ->orderByDesc('period_end')
            ->orderByDesc('period_start')
            ->orderBy('employee_name')
            ->get()
            ->each(fn (EmployeePayslip $payslip) => $payslip->syncProfileFieldsFromEmployee());

        if ($payslips->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'No payslips were found to print.');
        }

        return view('admin.employee-management.payslip.bulk-print', compact('payslips'));
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

    private function applyEmployeeScope($query): void
    {
        AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($query, $this->requireAuthUser());
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
