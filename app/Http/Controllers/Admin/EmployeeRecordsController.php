<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Support\AdminEmployeeDepartmentScope;
use App\Support\EmployeeRecordsLedger;
use Illuminate\Http\Request;

class EmployeeRecordsController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();
        $yearInput = (string) $request->input('year', (string) now()->year);
        $year = $yearInput === 'all' ? (int) now()->year : (int) $yearInput;
        if ($year <= 0) {
            $year = (int) now()->year;
        }
        $search = trim((string) $request->input('search', ''));

        $query = User::query()
            ->with(['department', 'departmentPosition.department'])
            ->whereIn('role', ['employee', 'hr'])
            ->orderBy('name');

        if ($admin?->canAccessEmployeeManagement()) {
            AdminEmployeeDepartmentScope::applyToEmployeeQuery($query, $admin);
        }

        if ($request->filled('department_id')) {
            $departmentId = (int) $request->department_id;
            $query->where(function ($scoped) use ($departmentId) {
                $scoped->where('department_id', $departmentId)
                    ->orWhereHas('departmentPosition', fn ($q) => $q->where('department_id', $departmentId));
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate(20)->withQueryString();

        $summaries = [];
        foreach ($employees as $employee) {
            $leave = EmployeeRecordsLedger::leaveCreditSummary((int) $employee->id, $year);
            $overtime = EmployeeRecordsLedger::overtimeSummary((int) $employee->id);
            $summaries[$employee->id] = [
                'leave' => $leave,
                'overtime' => $overtime,
            ];
        }

        $departments = Department::active()->orderBy('name')->get();

        return view('admin.employee-records.index', compact(
            'employees',
            'summaries',
            'departments',
            'year',
            'search'
        ));
    }

    public function show(Request $request, User $employee)
    {
        $admin = $request->user();

        if (! EmployeeRecordsLedger::isTrackableEmployee($employee)) {
            abort(404);
        }

        if ($admin?->canAccessEmployeeManagement()
            && ! AdminEmployeeDepartmentScope::canAccessEmployee($admin, $employee)) {
            abort(403);
        }

        $yearInput = (string) $request->input('year', (string) now()->year);
        $allYears = $yearInput === 'all';
        $year = $allYears ? null : (int) $yearInput;
        $summaryYear = $year ?? (int) now()->year;
        $tab = (string) $request->input('tab', 'leave');

        $leaveSummary = EmployeeRecordsLedger::leaveCreditSummary((int) $employee->id, $summaryYear);
        $overtimeSummary = EmployeeRecordsLedger::overtimeSummary((int) $employee->id);
        $leaveLedger = EmployeeRecordsLedger::leaveCreditLedger((int) $employee->id, $year);
        $overtimeLedger = EmployeeRecordsLedger::overtimeLedger((int) $employee->id, $year);
        $offsetLedger = EmployeeRecordsLedger::offsetLedger((int) $employee->id, $year);
        $activityLogs = EmployeeRecordsLedger::activityLogs((int) $employee->id, $year);
        $offsetActivityLogs = EmployeeRecordsLedger::offsetActivityLogs((int) $employee->id, $year);

        return view('admin.employee-records.show', compact(
            'employee',
            'year',
            'allYears',
            'summaryYear',
            'tab',
            'leaveSummary',
            'overtimeSummary',
            'leaveLedger',
            'overtimeLedger',
            'offsetLedger',
            'activityLogs',
            'offsetActivityLogs'
        ));
    }
}
