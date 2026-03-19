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

        $typeCounts = (clone $leaveBaseQuery)
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

        return view('admin.employee-management.dashboard', [
            'stats' => $stats,
            'typeCounts' => $typeCounts,
            'typeLabels' => $typeLabels,
            'recentLeaveRequests' => $recentLeaveRequests,
            'employees' => $employees,
            'employeeLeaveStats' => $employeeLeaveStats,
        ]);
    }
}

