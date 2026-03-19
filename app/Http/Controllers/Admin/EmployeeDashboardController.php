<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        $employeeLeaveStats = (clone $leaveBaseQuery)
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

        $employeeTypeRaw = (clone $leaveBaseQuery)
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

        return view('admin.employee-management.dashboard', [
            'stats' => $stats,
            'typeCounts' => $typeCounts,
            'typeLabels' => $typeLabels,
            'recentLeaveRequests' => $recentLeaveRequests,
            'employees' => $employees,
            'employeeLeaveStats' => $employeeLeaveStats,
            'employeeTypeCounts' => $employeeTypeCounts,
        ]);
    }
}

