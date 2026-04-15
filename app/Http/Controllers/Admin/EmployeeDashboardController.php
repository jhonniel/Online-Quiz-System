<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request)
    {
        $authUser = auth()->user();

        if (!$authUser->isAdmin() && !$authUser->canAccessEmployeeManagement()) {
            abort(403, 'Access denied. You do not have permission to access Employee Management.');
        }

        $allowedDepartmentIds = $authUser->canAccessEmployeeManagement()
            ? $authUser->getAllowedDepartmentIds()
            : null;

        $employeeQuery = User::query()
            ->where('role', 'employee');

        if ($allowedDepartmentIds !== null) {
            $employeeQuery->whereIn('department_id', $allowedDepartmentIds);
        }

        $employeeIds = $employeeQuery->pluck('id');
        $currentYear = now()->year;

        $leaveBaseQuery = LeaveRequest::query()
            ->whereIn('user_id', $employeeIds);

        $rawTypeCounts = (clone $leaveBaseQuery)
            ->select(
                'type',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            )
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $stats = [
            'total_employees' => (clone $employeeQuery)->count(),
            'active_employees' => (clone $employeeQuery)->where('is_active', true)->count(),
            'pending_leave_requests' => (clone $leaveBaseQuery)->where('status', 'pending')->count(),
            'approved_this_month' => (clone $leaveBaseQuery)
                ->where('status', 'approved')
                ->whereBetween('start_date', [$monthStart, $monthEnd])
                ->count(),
            'rejected_this_month' => (clone $leaveBaseQuery)
                ->where('status', 'rejected')
                ->whereBetween('start_date', [$monthStart, $monthEnd])
                ->count(),
            'employees_on_leave_today' => (clone $leaveBaseQuery)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->distinct('user_id')
                ->count('user_id'),
        ];

        $recentLeaveRequests = (clone $leaveBaseQuery)
            ->with(['user:id,name,email,department_id'])
            ->latest()
            ->take(10)
            ->get();

        $employees = (clone $employeeQuery)
            ->with(['department:id,name', 'university:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'department_id', 'university_id', 'is_active', 'created_at']);

        // All Employees Data: counts should be for the current year only.
        $leaveBaseQueryForEmployeesTable = (clone $leaveBaseQuery)
            ->whereYear('start_date', $currentYear);

        $employeeLeaveStats = (clone $leaveBaseQueryForEmployeesTable)
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            )
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $employeeTypeRaw = (clone $leaveBaseQueryForEmployeesTable)
            ->select('user_id', 'type', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id', 'type')
            ->get()
            ->groupBy('user_id');

        $typeLabels = [
            'vacation_leave' => 'Vacation Leave',
            'sick_leave' => 'Sick Leave',
            'work_from_home' => 'Work From Home',
            'absent' => 'Absent',
            'overtime' => 'Overtime',
            'offset' => 'Offset',
            'additional_time' => 'Additional Time',
            'travel' => 'Travel',
            'other' => 'Other',
        ];

        // Ensure dashboard always shows all leave types, even when counts are zero.
        $typeCountsByKey = $rawTypeCounts->keyBy('type');
        $typeCounts = collect(array_keys($typeLabels))->map(function ($type) use ($typeCountsByKey) {
            $row = $typeCountsByKey->get($type);
            return (object) [
                'type' => $type,
                'total' => (int) ($row->total ?? 0),
                'pending' => (int) ($row->pending ?? 0),
                'approved' => (int) ($row->approved ?? 0),
                'rejected' => (int) ($row->rejected ?? 0),
            ];
        });

        // Per employee, include all leave types with zero defaults.
        $employeeTypeCounts = [];
        foreach ($employees as $employee) {
            $rows = collect($employeeTypeRaw->get($employee->id, []))->keyBy('type');
            $employeeTypeCounts[$employee->id] = collect(array_keys($typeLabels))->mapWithKeys(function ($type) use ($rows) {
                return [$type => (int) (($rows->get($type)->total ?? 0))];
            })->all();
        }

        // Balances for All Employees Data (current year).
        $defaultVacation = (float) \App\Models\Setting::get('default_vacation_balance', 15);
        $defaultSick = (float) \App\Models\Setting::get('default_sick_leave_balance', 10);
        $today = Carbon::today();

        // Used leave credits (days) for the current year.
        $usedLeaveByUser = [];
        $approvedLeaveCreditRequests = LeaveRequest::whereIn('user_id', $employeeIds)
            ->whereIn('type', ['leave', 'vacation_leave', 'sick_leave'])
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->get(['id', 'user_id', 'start_date', 'end_date']);
        foreach ($approvedLeaveCreditRequests as $lr) {
            $usedLeaveByUser[$lr->user_id] = ($usedLeaveByUser[$lr->user_id] ?? 0) + (int) $lr->days;
        }

        // Overtime earned from DTRs (minutes): sum(max(total_hours - 8, 0)) for dates up to today.
        // Note: some installs store total_hours as "HH:MM", so we parse defensively.
        $dtrOvertimeMinutesByUser = [];
        $dtrs = \App\Models\Dtr::whereIn('user_id', $employeeIds)
            ->whereDate('date', '<=', $today)
            ->get(['user_id', 'total_hours']);
        foreach ($dtrs as $dtr) {
            $rawTotal = $dtr->total_hours;
            $total = 0.0;
            if (is_numeric($rawTotal)) {
                $total = (float) $rawTotal;
            } else {
                $txt = trim((string) $rawTotal);
                if (preg_match('/^([0-9]{1,3}):([0-9]{2})$/', $txt, $m)) {
                    $total = ((int) $m[1]) + (((int) $m[2]) / 60);
                } else {
                    $total = (float) $txt;
                }
            }
            $dailyOvertime = max($total - 8.0, 0);
            $dtrOvertimeMinutesByUser[$dtr->user_id] = ($dtrOvertimeMinutesByUser[$dtr->user_id] ?? 0) + (int) round($dailyOvertime * 60);
        }

        // Overtime from approved overtime leave requests (minutes) up to today.
        $overtimeLeaveMinutesByUser = [];
        $approvedOvertimeRequests = LeaveRequest::whereIn('user_id', $employeeIds)
            ->where('type', 'overtime')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->get(['user_id', 'reason']);
        foreach ($approvedOvertimeRequests as $ot) {
            $raw = (string) ($ot->reason ?? '');
            if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                $overtimeLeaveMinutesByUser[$ot->user_id] = ($overtimeLeaveMinutesByUser[$ot->user_id] ?? 0) + ($h * 60 + $mPart);
            }
        }

        // Offsets deduct from overtime (minutes).
        $offsetMinutesByUser = [];
        $approvedOffsetRequests = LeaveRequest::whereIn('user_id', $employeeIds)
            ->where('type', 'offset')
            ->where('status', 'approved')
            ->get(['user_id', 'reason', 'start_date', 'end_date']);
        foreach ($approvedOffsetRequests as $off) {
            $raw = (string) ($off->reason ?? '');
            if (preg_match('/Hours to Deduct:\s*([0-9]{2}):([0-9]{2})/', $raw, $m)) {
                $mins = ((int) $m[1]) * 60 + ((int) $m[2]);
            } else {
                $mins = (int) round(((int) $off->days * 8) * 60);
            }
            $offsetMinutesByUser[$off->user_id] = ($offsetMinutesByUser[$off->user_id] ?? 0) + $mins;
        }

        $employeeBalances = [];
        foreach ($employees as $employee) {
            $leaveBalance = LeaveBalance::firstOrCreateWithCarryover(
                (int) $employee->id,
                (int) $currentYear,
                (float) $defaultVacation,
                (float) $defaultSick
            );
            $allowance = (float) $leaveBalance->vacation_allowance + (float) $leaveBalance->sick_allowance;
            $used = (int) ($usedLeaveByUser[$employee->id] ?? 0);
            $leaveRemaining = max($allowance - $used, 0);

            $otMinutes = (int) ($dtrOvertimeMinutesByUser[$employee->id] ?? 0)
                + (int) ($overtimeLeaveMinutesByUser[$employee->id] ?? 0)
                - (int) ($offsetMinutesByUser[$employee->id] ?? 0);
            $otSign = $otMinutes < 0 ? '-' : '';
            $otAbs = abs($otMinutes);
            $otH = intdiv($otAbs, 60);
            $otM = $otAbs % 60;
            $overtimeFormatted = $otSign . sprintf('%02d:%02d', $otH, $otM);

            $employeeBalances[$employee->id] = [
                'leave_remaining' => $leaveRemaining,
                'overtime_formatted' => $overtimeFormatted,
            ];
        }

        return view('admin.employee-management.dashboard', [
            'stats' => $stats,
            'typeCounts' => $typeCounts,
            'typeLabels' => $typeLabels,
            'recentLeaveRequests' => $recentLeaveRequests,
            'employees' => $employees,
            'employeeLeaveStats' => $employeeLeaveStats,
            'employeeTypeCounts' => $employeeTypeCounts,
            'employeeBalances' => $employeeBalances,
        ]);
    }
}

