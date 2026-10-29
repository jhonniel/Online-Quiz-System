<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\AdminScopedDashboardCharts;
use App\Support\DtrApprovedLeaveRemark;
use App\Support\EmployeeRecordsLedger;
use App\Support\StaffLeaveCredits;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request)
    {
        $authUser = auth()->user();
        $allowedDepartmentIds = $this->authorizeDashboardAccess($authUser);

        $employeeQuery = User::query()
            ->where('role', 'employee');

        if ($allowedDepartmentIds !== null) {
            $employeeQuery->whereIn('department_id', $allowedDepartmentIds);
        }

        $employeeIds = $employeeQuery->pluck('id');
        $currentYear = now()->year;
        $leaveDaysStartDate = (string) $request->query('leave_days_start_date', '');
        $leaveDaysEndDate = (string) $request->query('leave_days_end_date', '');
        $leaveDaysSort = (string) $request->query('leave_days_sort', 'total_desc');
        $leaveDaysTypes = collect((array) $request->query('leave_days_type', []))
            ->map(fn ($v) => (string) $v)
            ->filter(fn ($v) => $v !== '')
            ->values()
            ->all();

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

        // All Employees Data lists active employees unless "Show inactive" is on; Leave Request Days is always active only.
        $showInactiveEmployees = $request->boolean('show_inactive');
        $tableEmployees = (clone $employeeQuery)
            ->when(! $showInactiveEmployees, fn ($q) => $q->where('is_active', true))
            ->with(['department:id,name', 'university:id,name'])
            ->orderBy('name')
            ->get([
                'id', 'name', 'email', 'role', 'department_id', 'university_id', 'is_active', 'created_at',
                'date_hired', 'auto_tenure_leave_credits_enabled',
            ]);
        $employees = $tableEmployees->where('is_active', true)->values();

        // All Employees Data: leave days filed in the current year. Overtime and Additional Time
        // are hours credited, not days off, so they are not counted as leave.
        $timeCreditTypes = ['overtime', 'additional_time'];
        $employeesTableRequests = (clone $leaveBaseQuery)
            ->whereYear('start_date', $currentYear)
            ->whereNotIn('type', $timeCreditTypes)
            ->get(['user_id', 'type', 'status', 'start_date', 'end_date'])
            ->groupBy('user_id');

        $employeeLeaveStats = $employeesTableRequests->map(fn ($requests) => (object) [
            'total' => (int) $requests->sum->days,
            'pending' => (int) $requests->whereIn('status', ['pending', 'for_more_verification'])->sum->days,
            'approved' => (int) $requests->where('status', 'approved')->sum->days,
            'rejected' => (int) $requests->where('status', 'rejected')->sum->days,
        ]);

        $typeLabels = [
            'leave' => 'Leave',
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

        // Approved leave totals by type, summed by request days.
        $approvedDaysTotals = LeaveRequest::query()
            ->whereIn('user_id', $employeeIds)
            ->where('status', 'approved')
            ->get(['type', 'start_date', 'end_date'])
            ->groupBy('type')
            ->map(function ($requests) {
                return (int) $requests->sum(function ($request) {
                    $start = Carbon::parse($request->start_date);
                    $end = $request->end_date ? Carbon::parse($request->end_date) : $start;

                    return $start->diffInDays($end) + 1;
                });
            });
        $approvedDaysByType = collect(array_keys($typeLabels))->map(function ($type) use ($approvedDaysTotals) {
            return (object) [
                'type' => $type,
                'approved_days' => (int) ($approvedDaysTotals[$type] ?? 0),
            ];
        });

        // Per employee, approved leave days for each type, counting only days inside the From/To range.
        $rangeStart = $leaveDaysStartDate !== '' ? Carbon::parse($leaveDaysStartDate)->startOfDay() : null;
        $rangeEnd = $leaveDaysEndDate !== '' ? Carbon::parse($leaveDaysEndDate)->startOfDay() : null;

        $leaveDaysQuery = LeaveRequest::query()
            ->whereIn('user_id', $employeeIds)
            ->where('status', 'approved');
        if ($rangeEnd !== null) {
            $leaveDaysQuery->whereDate('start_date', '<=', $rangeEnd->toDateString());
        }
        if ($rangeStart !== null) {
            $leaveDaysQuery->where(function ($q) use ($rangeStart) {
                $q->whereDate('end_date', '>=', $rangeStart->toDateString())
                    ->orWhere(function ($single) use ($rangeStart) {
                        $single->whereNull('end_date')
                            ->whereDate('start_date', '>=', $rangeStart->toDateString());
                    });
            });
        }
        $validLeaveDaysTypes = array_values(array_filter($leaveDaysTypes, function ($type) use ($typeLabels) {
            return array_key_exists($type, $typeLabels);
        }));
        $leaveRequestsByEmployee = $leaveDaysQuery
            ->get(['id', 'user_id', 'type', 'start_date', 'end_date', 'reviewed_at', 'created_at', 'updated_at'])
            ->groupBy('user_id');

        $employeeLeaveDaysByType = [];
        foreach ($employees as $employee) {
            $daysByType = array_fill_keys(array_keys($typeLabels), 0);
            $latestByDate = [];

            foreach ($leaveRequestsByEmployee->get($employee->id, []) as $leaveRequest) {
                foreach ($this->leaveRequestDatesWithin($leaveRequest, $rangeStart, $rangeEnd) as $dateKey) {
                    if (in_array($leaveRequest->type, $timeCreditTypes, true)) {
                        if (array_key_exists($leaveRequest->type, $daysByType)) {
                            $daysByType[$leaveRequest->type]++;
                        }

                        continue;
                    }

                    // When a leave type was changed by filing again for the same day, only the latest counts.
                    DtrApprovedLeaveRemark::assignLatestLeaveRequestForDate($latestByDate, (int) $employee->id, $dateKey, $leaveRequest);
                }
            }

            foreach ($latestByDate[$employee->id] ?? [] as $leaveRequest) {
                if (array_key_exists($leaveRequest->type, $daysByType)) {
                    $daysByType[$leaveRequest->type]++;
                }
            }

            if (! empty($validLeaveDaysTypes)) {
                foreach (array_keys($daysByType) as $type) {
                    if (! in_array($type, $validLeaveDaysTypes, true)) {
                        $daysByType[$type] = 0;
                    }
                }
            }

            $employeeLeaveDaysByType[$employee->id] = $daysByType;
        }

        $leaveDaysEmployees = $employees->map(function ($employee) use ($employeeLeaveDaysByType) {
            $daysByType = $employeeLeaveDaysByType[$employee->id] ?? [];
            $totalDays = (int) collect($daysByType)->sum();

            return [
                'employee' => $employee,
                'days_by_type' => $daysByType,
                'total_days' => $totalDays,
            ];
        });

        if ($leaveDaysSort === 'total_asc') {
            $leaveDaysEmployees = $leaveDaysEmployees
                ->sortBy('total_days')
                ->values();
        } elseif ($leaveDaysSort === 'name_desc') {
            $leaveDaysEmployees = $leaveDaysEmployees
                ->sortByDesc(function ($row) {
                    return strtolower((string) ($row['employee']->name ?? ''));
                })
                ->values();
        } elseif ($leaveDaysSort === 'name_asc') {
            $leaveDaysEmployees = $leaveDaysEmployees
                ->sortBy(function ($row) {
                    return strtolower((string) ($row['employee']->name ?? ''));
                })
                ->values();
        } else {
            // Default: highest total approved leave days first.
            $leaveDaysEmployees = $leaveDaysEmployees
                ->sortByDesc('total_days')
                ->values();
        }

        // Per employee leave days by type (current year), with zero defaults.
        $employeesTableTypeLabels = array_diff_key($typeLabels, array_flip($timeCreditTypes));
        $employeeTypeCounts = [];
        foreach ($tableEmployees as $employee) {
            $requests = collect($employeesTableRequests->get($employee->id, []));
            $employeeTypeCounts[$employee->id] = collect(array_keys($employeesTableTypeLabels))->mapWithKeys(function ($type) use ($requests) {
                return [$type => (int) $requests->where('type', $type)->sum->days];
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

        // Overtime balance (usable for offset) is ONLY based on approved overtime leave requests
        // minus approved offset requests (do NOT include DTR overtime here).
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
        foreach ($tableEmployees as $employee) {
            LeaveBalance::firstOrCreateWithCarryover(
                (int) $employee->id,
                (int) $currentYear,
                (float) $defaultVacation,
                (float) $defaultSick
            );
            // Same allowance the employee sees on their own Leave page (tenure-based unless an admin set a custom balance).
            $allowance = StaffLeaveCredits::effectiveAllowance($employee, (int) $currentYear);
            $used = (int) ($usedLeaveByUser[$employee->id] ?? 0);
            $leaveRemaining = max($allowance - $used, 0);

            $otMinutes = (int) ($overtimeLeaveMinutesByUser[$employee->id] ?? 0)
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

        $chartBundle = app(AdminScopedDashboardCharts::class)->forEmployees(
            $employeeIds->all(),
            $request
        );

        return view('admin.employee-management.dashboard', [
            'stats' => $stats,
            'chartPeriod' => $chartBundle['chartPeriod'],
            'chartFrom' => $chartBundle['chartFrom'],
            'chartTo' => $chartBundle['chartTo'],
            'scopedChartPayload' => $chartBundle['payload'],
            'typeCounts' => $typeCounts,
            'approvedDaysByType' => $approvedDaysByType,
            'employeeLeaveDaysByType' => $employeeLeaveDaysByType,
            'typeLabels' => $typeLabels,
            'employeesTableTypeLabels' => $employeesTableTypeLabels,
            'recentLeaveRequests' => $recentLeaveRequests,
            'employees' => $tableEmployees,
            'showInactiveEmployees' => $showInactiveEmployees,
            'employeeLeaveStats' => $employeeLeaveStats,
            'employeeTypeCounts' => $employeeTypeCounts,
            'employeeBalances' => $employeeBalances,
            'leaveDaysStartDate' => $leaveDaysStartDate,
            'leaveDaysEndDate' => $leaveDaysEndDate,
            'leaveDaysSort' => $leaveDaysSort,
            'leaveDaysTypes' => $validLeaveDaysTypes,
            'leaveDaysEmployees' => $leaveDaysEmployees,
        ]);
    }

    /**
     * Leave credit and overtime ledger for one employee, shown in the dashboard modal.
     */
    public function ledger(Request $request, User $employee)
    {
        $allowedDepartmentIds = $this->authorizeDashboardAccess($request->user());

        if ($employee->role !== 'employee') {
            abort(404);
        }
        if ($allowedDepartmentIds !== null && ! in_array((int) $employee->department_id, array_map('intval', $allowedDepartmentIds), true)) {
            abort(403, 'You do not have access to this employee.');
        }

        $yearInput = (string) $request->query('year', (string) now()->year);
        $year = $yearInput === 'all' ? null : (int) $yearInput;
        if ($year !== null && $year <= 0) {
            $year = (int) now()->year;
        }

        return response()->json(EmployeeRecordsLedger::modalPayload($employee, $year));
    }

    /**
     * @return list<int>|null Department IDs the user is limited to, or null for all departments.
     */
    private function authorizeDashboardAccess(?User $authUser): ?array
    {
        if (! $authUser || (! $authUser->isAdmin() && ! $authUser->canAccessEmployeeManagement())) {
            abort(403, 'Access denied. You do not have permission to access Employee Management.');
        }

        return $authUser->canAccessEmployeeManagement()
            ? $authUser->getAllowedDepartmentIds()
            : null;
    }

    /**
     * Calendar days of a leave request, limited to the optional range.
     *
     * @return list<string> Y-m-d keys
     */
    private function leaveRequestDatesWithin(LeaveRequest $leaveRequest, ?Carbon $rangeStart, ?Carbon $rangeEnd): array
    {
        $start = Carbon::parse($leaveRequest->start_date)->startOfDay();
        $end = $leaveRequest->end_date ? Carbon::parse($leaveRequest->end_date)->startOfDay() : $start->copy();
        if ($end->lt($start)) {
            $end = $start->copy();
        }
        if ($rangeStart !== null && $start->lt($rangeStart)) {
            $start = $rangeStart->copy();
        }
        if ($rangeEnd !== null && $end->gt($rangeEnd)) {
            $end = $rangeEnd->copy();
        }

        $dates = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $dates[] = $day->toDateString();
        }

        return $dates;
    }
}

