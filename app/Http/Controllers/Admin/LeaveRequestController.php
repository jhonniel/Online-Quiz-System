<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestLog;
use App\Models\Dtr;
use App\Models\DtrDeficit;
use App\Models\Department;
use App\Models\User;
use App\Models\LeaveBalance;
use App\Models\Setting;
use App\Mail\LeaveRequestStatusUpdate;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of all leave requests.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $query = LeaveRequest::with(['user', 'reviewer', 'approvedBy.performer', 'rejectedBy.performer', 'resubmissionRequestedBy.performer', 'logs'])
            ->whereHas('user', function($q) {
                $q->where('role', 'employee');
            });

        // Apply department restrictions if user has Employee Management with restrictions
        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                $query->whereHas('user', function($q) use ($allowedDepartmentIds) {
                    $q->whereIn('department_id', $allowedDepartmentIds);
                });
            }
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by department (user-selected filter)
        if ($request->has('department_id') && $request->department_id) {
            $selectedDeptId = $request->department_id;
            if ($user->canManageDepartment($selectedDeptId)) {
                $query->whereHas('user', function($q) use ($selectedDeptId) {
                    $q->where('department_id', $selectedDeptId);
                });
            }
        }

        // Filter by employee
        if ($request->has('employee') && $request->employee) {
            $query->where('user_id', $request->employee);
        }

        // Search (employee name/email + request fields)
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search)
                        ->orWhere('user_id', (int) $search);
                }

                $q->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        // Statistics - only for employees (apply department restrictions)
        $baseQuery = LeaveRequest::whereHas('user', function($q) {
            $q->where('role', 'employee');
        });

        // Apply department restrictions to stats
        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                $baseQuery->whereHas('user', function($q) use ($allowedDepartmentIds) {
                    $q->whereIn('department_id', $allowedDepartmentIds);
                });
            }
        }

        if ($request->has('department_id') && $request->department_id) {
            $selectedDeptId = $request->department_id;
            if ($user->canManageDepartment($selectedDeptId)) {
                $baseQuery->whereHas('user', function($q) use ($selectedDeptId) {
                    $q->where('department_id', $selectedDeptId);
                });
            }
        }
        if ($request->has('employee') && $request->employee) {
            $baseQuery->where('user_id', $request->employee);
        }
        if ($request->has('type') && $request->type) {
            $baseQuery->where('type', $request->type);
        }
        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search)
                        ->orWhere('user_id', (int) $search);
                }

                $q->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        // Get employees for filter dropdown (respecting department restrictions)
        $employeesQuery = \App\Models\User::where('role', 'employee')
            ->where('is_active', true);

        // Apply department restrictions to employee list
        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                $employeesQuery->whereIn('department_id', $allowedDepartmentIds);
            }
        }

        $employees = $employeesQuery->orderBy('name')->get();

        // Get departments for filter dropdown (only departments user can manage)
        $departmentsQuery = Department::active();

        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                $departmentsQuery->whereIn('id', $allowedDepartmentIds);
            }
        }

        $departments = $departmentsQuery->orderBy('name')->get();

        return view('admin.leave-requests.index', compact('leaveRequests', 'stats', 'employees', 'departments', 'search', 'perPage'));
    }

    /**
     * Display the specified leave request.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['user.department', 'reviewer']);

        $user = $leaveRequest->user;
        $balances = null;
        $overtimeFormatted = null;
        $studentTime = null;
        $hasNegativeBalance = false;

        if ($user->role === 'employee') {
            // Compute current-year leave balances and overtime for this employee
            $currentYear = now()->year;
            $months = $user->overtime_months_credited ?? 12;

            if ($months === 12) {
                $fromDate = now()->copy()->startOfYear();
            } else {
                $fromDate = now()->copy()->subMonths($months)->startOfDay();
            }

            $defaultVacation = (float) \App\Models\Setting::get('default_vacation_balance', 15);
            $defaultSick = (float) \App\Models\Setting::get('default_sick_leave_balance', 10);

            $leaveBalance = \App\Models\LeaveBalance::firstOrCreate(
                ['user_id' => $user->id, 'year' => $currentYear],
                [
                    'vacation_allowance' => $defaultVacation,
                    'sick_allowance' => $defaultSick,
                ]
            );

            $usedVacation = LeaveRequest::where('user_id', $user->id)
                ->where('type', 'vacation_leave')
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->get()
                ->sum->days;

            $usedSick = LeaveRequest::where('user_id', $user->id)
                ->where('type', 'sick_leave')
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->get()
                ->sum->days;

            $balances = [
                'vacation' => [
                    'allowance' => (float) $leaveBalance->vacation_allowance,
                    'used' => $usedVacation,
                    'remaining' => max((float) $leaveBalance->vacation_allowance - $usedVacation, 0),
                ],
                'sick' => [
                    'allowance' => (float) $leaveBalance->sick_allowance,
                    'used' => $usedSick,
                    'remaining' => max((float) $leaveBalance->sick_allowance - $usedSick, 0),
                ],
            ];

            // Establish current date for completed-week checks
            $today = Carbon::today();

            // Overtime balance is ONLY based on approved overtime leave requests (DTR overtime is ignored)
            // Overtime Credited Window is ONLY used for expiration logic, NOT for counting
            $totalOvertimeHours = 0;

            // Get approved overtime leave requests for completed weeks only (count all, window only for expiration)
            $approvedOvertimeRequests = LeaveRequest::where('user_id', $user->id)
                ->where('type', 'overtime')
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today) // completed weeks only
                ->get();

            $overtimeFromLeavesMinutes = 0;
            foreach ($approvedOvertimeRequests as $otRequest) {
                $raw = $otRequest->reason ?? '';
                if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                    [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                    $overtimeFromLeavesMinutes += $h * 60 + $mPart;
                }
            }

            $totalOvertimeHours = $overtimeFromLeavesMinutes / 60;

            // Build set of weeks where overtime was earned (only from approved overtime leave requests)
            $overtimeWeekKeys = [];
            foreach ($approvedOvertimeRequests as $otRequest) {
                $weekStart = $otRequest->start_date->copy()->startOfWeek()->toDateString();
                $overtimeWeekKeys[$weekStart] = true;
            }

            // Overtime balance is ONLY the total of approved overtime requests (no deductions)
            // Format overtime balance
            $absOvertimeMinutes = (int) round(abs($totalOvertimeHours) * 60);
            $overtimeHoursPart = intdiv($absOvertimeMinutes, 60);
            $overtimeMinutesPart = $absOvertimeMinutes % 60;
            $overtimeFormatted = sprintf('%02d:%02d', $overtimeHoursPart, $overtimeMinutesPart);

            // Check if this is an offset request and if the duration exceeds overtime balance
            $hasNegativeBalance = false;
            if ($leaveRequest->type === 'offset' && $leaveRequest->isPending()) {
                // Calculate offset hours needed (days * 8 hours per day)
                $offsetHoursNeeded = $leaveRequest->days * 8;

                // Check if offset hours needed exceeds current overtime balance
                // If yes, approving will result in negative balance
                $hasNegativeBalance = $offsetHoursNeeded > $totalOvertimeHours;
            }
        } elseif ($user->role === 'student') {
            // Student: show total DTR time vs required time set by admin
            $requiredHours = (float) ($user->required_training_hours ?? 0);

            // Sum all DTR total_hours for this student
            $totalDtrHours = \App\Models\Dtr::where('user_id', $user->id)->sum('total_hours');

            // Remaining balance time = Time need - Total Time from DTR (can be negative)
            $remainingHours = $requiredHours - $totalDtrHours;

            $formatHours = function ($decimal) {
                $sign = $decimal < 0 ? '-' : '';
                $minutes = (int) round(abs($decimal) * 60);
                $h = intdiv($minutes, 60);
                $m = $minutes % 60;
                return $sign . sprintf('%02d:%02d', $h, $m);
            };

            $studentTime = [
                'required_hours' => $requiredHours,
                'required_hours_formatted' => $requiredHours > 0 ? $formatHours($requiredHours) : null,
                'total_dtr_hours' => $totalDtrHours,
                'total_dtr_hours_formatted' => $formatHours($totalDtrHours),
                'remaining_hours' => $remainingHours,
                'remaining_hours_formatted' => $requiredHours > 0 ? $formatHours($remainingHours) : null,
            ];
        }

        // Get signatory names - immediate supervisor based on user's department
        $employee = $leaveRequest->user;
        $immediateSupervisor = 'CHARMAINE JOY ROSATACE'; // Default fallback

        if ($employee && $employee->department && $employee->department->supervisor_name) {
            $immediateSupervisor = $employee->department->supervisor_name;
        } else {
            $immediateSupervisor = \App\Models\Setting::get('leave_immediate_supervisor', 'CHARMAINE JOY ROSATACE');
        }

        $signatories = [
            'immediate_supervisor' => $immediateSupervisor,
            'hr_admin' => \App\Models\Setting::get('leave_hr_admin', 'MAY GRACE ACOSTA'),
            'cto' => \App\Models\Setting::get('leave_cto', 'NITISH KHEMANI'),
        ];

        // Load logs with performer relationship
        $leaveRequest->load(['logs.performer']);

        return view('admin.leave-requests.show', compact('leaveRequest', 'balances', 'overtimeFormatted', 'signatories', 'studentTime', 'hasNegativeBalance'));
    }

    /**
     * Calendar view of leave requests for easier tracking.
     */
    public function calendar(Request $request)
    {
        $user = auth()->user();

        // Use Manila timezone for current date/month context
        $nowManila = Carbon::now('Asia/Manila');
        $monthParam = $request->input('month', $nowManila->format('Y-m'));
        $employeeId = $request->input('employee');
        $departmentId = $request->input('department_id');

        try {
            $currentMonth = Carbon::createFromFormat('Y-m', $monthParam, 'Asia/Manila')->startOfMonth();
        } catch (\Exception $e) {
            $currentMonth = $nowManila->copy()->startOfMonth();
        }

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // Extend to full weeks for calendar grid
        $startOfCalendar = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        // Base query for leave requests that intersect the calendar range
        // Only show employee leave requests
        // Wrap OR conditions in a single group so employee filter applies to all.
        $leaveQuery = LeaveRequest::with('user')
            ->whereHas('user', function($q) use ($departmentId, $user) {
                $q->where('role', 'employee');

                // Apply department restrictions if user has Employee Management with restrictions
                if ($user->canAccessEmployeeManagement()) {
                    $allowedDepartmentIds = $user->getAllowedDepartmentIds();
                    if ($allowedDepartmentIds !== null) {
                        $q->whereIn('department_id', $allowedDepartmentIds);
                    }
                }

                // Filter by selected department (user-selected filter)
                if ($departmentId && $user->canManageDepartment($departmentId)) {
                    $q->where('department_id', $departmentId);
                }
            })
            ->where(function ($outer) use ($startOfCalendar, $endOfCalendar) {
                $outer->where(function ($q) use ($startOfCalendar, $endOfCalendar) {
                    // Requests with a start and end date that overlap the calendar window
                    $q->whereDate('start_date', '<=', $endOfCalendar->toDateString())
                      ->whereDate('end_date', '>=', $startOfCalendar->toDateString());
                })->orWhere(function ($q) use ($startOfCalendar, $endOfCalendar) {
                    // Handle single-day requests where end_date is null
                    $q->whereNull('end_date')
                      ->whereDate('start_date', '>=', $startOfCalendar->toDateString())
                      ->whereDate('start_date', '<=', $endOfCalendar->toDateString());
                });
            });

        if ($employeeId) {
            $leaveQuery->where('user_id', $employeeId);
        }

        $leaveRequests = $leaveQuery->get();

        // Prepare map of day => leave entries
        $days = [];
        $period = CarbonPeriod::create($startOfCalendar, $endOfCalendar);

        foreach ($period as $date) {
            $key = $date->toDateString();
            $days[$key] = [
                'date' => $date->copy(),
                'requests' => [],
            ];
        }

        foreach ($leaveRequests as $requestItem) {
            $rangeStart = $requestItem->start_date->copy()->max($startOfCalendar);
            $rangeEnd = ($requestItem->end_date ?? $requestItem->start_date)->copy()->min($endOfCalendar);

            $dayPeriod = CarbonPeriod::create($rangeStart, $rangeEnd);
            foreach ($dayPeriod as $day) {
                $key = $day->toDateString();
                if (!isset($days[$key])) {
                    continue;
                }

                $days[$key]['requests'][] = [
                    'id' => $requestItem->id,
                    'employee' => $requestItem->user,
                    'type_label' => $requestItem->type_label,
                    'status' => $requestItem->status,
                    'reviewed_at' => $requestItem->reviewed_at,
                ];
            }
        }

        $weeks = [];
        $week = [];
        foreach ($days as $day) {
            $week[] = $day;
            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }
        if (!empty($week)) {
            $weeks[] = $week;
        }

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // Employees list for sidebar filter (employees only, respecting department restrictions)
        $employeesQuery = \App\Models\User::where('role', 'employee')
            ->where('is_active', true);

        // Apply department restrictions to employee list
        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                $employeesQuery->whereIn('department_id', $allowedDepartmentIds);
            }
        }

        // Filter by selected department (user-selected filter)
        if ($departmentId && $user->canManageDepartment($departmentId)) {
            $employeesQuery->where('department_id', $departmentId);
        }

        $employees = $employeesQuery->orderBy('name')->get();

        // Get departments for filter dropdown (only departments user can manage)
        $departmentsQuery = Department::active();

        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                $departmentsQuery->whereIn('id', $allowedDepartmentIds);
            }
        }

        $departments = $departmentsQuery->orderBy('name')->get();

        return view('admin.leave-requests.calendar', [
            'currentMonth' => $currentMonth,
            'weeks' => $weeks,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'employees' => $employees,
            'departments' => $departments,
            'selectedEmployeeId' => $employeeId,
            'selectedDepartmentId' => $departmentId,
        ]);
    }

    /**
     * Allow admins to file a leave request on behalf of one or more employees (calendar page).
     */
    public function storeForEmployee(Request $request)
    {
        $user = auth()->user();
        
        // Determine allowed leave types based on user permissions
        $allowedTypes = ['vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime', 'offset'];
        
        // Only super admins (full access) can file travel leave
        if ($user->isSuperAdmin()) {
            $allowedTypes[] = 'travel';
        }
        
        $validated = $request->validate([
            'user_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'user_ids.*' => [
                'required',
                Rule::exists('users', 'id')->where(function ($q) {
                    $q->where('role', 'employee')->where('is_active', true);
                }),
            ],
            'type' => ['required', Rule::in($allowedTypes)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'travel_hours' => ['nullable', 'numeric', 'min:0', 'max:24'], // Custom hours for travel (per day)
        ]);

        // Check if user is trying to file travel but is not a super admin
        if ($validated['type'] === 'travel' && !$user->isSuperAdmin()) {
            return redirect()->back()
                ->withErrors(['type' => 'Only full-access admins can file travel leave requests.'])
                ->withInput();
        }

        $employeeIds = $validated['user_ids'];

        // Validate that user can manage all selected employees' departments
        foreach ($employeeIds as $employeeId) {
            $employee = User::findOrFail($employeeId);
            if (!$user->canManageDepartment($employee->department_id)) {
                return redirect()->back()
                    ->withErrors(['user_ids' => "You don't have permission to file leave for employees in this department."])
                    ->withInput();
            }
        }

        // Allow past dates for sick_leave, overtime, and travel; others must be today or future
        $typeInput = $validated['type'];
        if (!in_array($typeInput, ['sick_leave', 'overtime', 'travel'])) {
            // Re-run validation for start_date with today-or-future rule
            $request->validate([
                'start_date' => ['required', 'date', 'after_or_equal:today'],
            ]);
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = $validated['end_date']
            ? Carbon::parse($validated['end_date'])
            : $startDate;
        $daysRequested = $startDate->diffInDays($endDate) + 1;

        $currentYear = now()->year;
        $defaultVacation = (float) Setting::get('default_vacation_balance', 15);
        $defaultSick = (float) Setting::get('default_sick_leave_balance', 10);

        $createdCount = 0;
        $failedEmployees = [];

        // Process each selected employee
        foreach ($employeeIds as $employeeId) {
            $employee = User::findOrFail($employeeId);

            // Basic balance check for vacation and sick leave
            if (in_array($validated['type'], ['vacation_leave', 'sick_leave'])) {
                $leaveBalance = LeaveBalance::firstOrCreate(
                    ['user_id' => $employee->id, 'year' => $currentYear],
                    [
                        'vacation_allowance' => $defaultVacation,
                        'sick_allowance' => $defaultSick,
                    ]
                );

                $usedVacation = LeaveRequest::where('user_id', $employee->id)
                    ->where('type', 'vacation_leave')
                    ->where('status', 'approved')
                    ->whereYear('start_date', $currentYear)
                    ->get()
                    ->sum->days;

                $usedSick = LeaveRequest::where('user_id', $employee->id)
                    ->where('type', 'sick_leave')
                    ->where('status', 'approved')
                    ->whereYear('start_date', $currentYear)
                    ->get()
                    ->sum->days;

                $remainingVacation = max((float) $leaveBalance->vacation_allowance - $usedVacation, 0);
                $remainingSick = max((float) $leaveBalance->sick_allowance - $usedSick, 0);

                if ($validated['type'] === 'vacation_leave' && $daysRequested > $remainingVacation) {
                    $failedEmployees[] = [
                        'name' => $employee->name,
                        'reason' => "only has {$remainingVacation} day(s) of Vacation Leave remaining. Requested {$daysRequested} day(s)."
                    ];
                    continue;
                }

                if ($validated['type'] === 'sick_leave' && $daysRequested > $remainingSick) {
                    $failedEmployees[] = [
                        'name' => $employee->name,
                        'reason' => "only has {$remainingSick} day(s) of Sick Leave remaining. Requested {$daysRequested} day(s)."
                    ];
                    continue;
                }
            }

            // Determine status - travel requests are auto-approved
            $status = ($validated['type'] === 'travel') ? 'approved' : 'pending';
            $reviewedBy = ($validated['type'] === 'travel') ? Auth::id() : null;
            $reviewedAt = ($validated['type'] === 'travel') ? now() : null;

            // Create leave request for this employee
            $leaveRequest = LeaveRequest::create([
                'user_id' => $employee->id,
                'type' => $validated['type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? $validated['start_date'],
                'reason' => $validated['reason'] ?? '',
                'status' => $status,
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => $reviewedAt,
            ]);

            // Log that an admin filed this request on behalf of the employee
            LeaveRequestLog::create([
                'leave_request_id' => $leaveRequest->id,
                'action' => $status === 'approved' ? 'approved' : 'filed_by_admin',
                'status_before' => null,
                'status_after' => $status,
                'notes' => $status === 'approved' ? 'Travel leave auto-approved and filed by admin' : 'Filed by admin on behalf of employee',
                'performed_by' => Auth::id(),
            ]);

            // If travel type, automatically add hours to DTR
            if ($validated['type'] === 'travel') {
                $this->applyTravelTimeToDtr($leaveRequest, $validated['travel_hours'] ?? 8.0);
            }

            Log::info('Admin filed leave request for employee', [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $employee->id,
                'admin_id' => Auth::id(),
                'type' => $validated['type'],
                'status' => $status,
            ]);

            $createdCount++;
        }

        // Prepare success/error messages
        $message = '';
        if ($createdCount > 0) {
            if ($validated['type'] === 'travel') {
                $message = "Travel leave approved and filed for {$createdCount} employee(s). Hours have been added to DTR.";
            } else {
                $message = "Leave request filed for {$createdCount} employee(s).";
            }
        }
        if (count($failedEmployees) > 0) {
            $failedNames = collect($failedEmployees)->pluck('name')->join(', ');
            $message .= " Failed for: {$failedNames} (insufficient leave balance).";
        }

        if ($createdCount === 0) {
            return back()->withErrors([
                'user_ids' => 'No leave requests were created. All selected employees have insufficient leave balance.',
            ])->withInput();
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Approve a leave request.
     */
    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        // Ensure user relationship is loaded
        $leaveRequest->load('user');

        $statusBefore = $leaveRequest->status;

        $leaveRequest->update([
            'status' => 'approved',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Log the approval action
        LeaveRequestLog::create([
            'leave_request_id' => $leaveRequest->id,
            'action' => 'approved',
            'status_before' => $statusBefore,
            'status_after' => 'approved',
            'notes' => $request->admin_notes,
            'performed_by' => Auth::id(),
        ]);

        // If Additional Time, credit 1 day = 8 hours to DTR per date
        if ($leaveRequest->type === 'additional_time') {
            $this->applyAdditionalTimeToDtr($leaveRequest);
        }

        // If Vacation Leave or Sick Leave, automatically add 8 hours per day to DTR
        if (in_array($leaveRequest->type, ['vacation_leave', 'sick_leave'])) {
            $this->applyLeaveTimeToDtr($leaveRequest);
        }

        // Send email notification to employee
        try {
            MailConfigService::configure();

            Log::info('Sending leave request approval email to user', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id,
                'user_name' => $leaveRequest->user->name
            ]);

            Mail::to($leaveRequest->user->email)->send(
                new LeaveRequestStatusUpdate($leaveRequest, 'approved', $request->admin_notes)
            );

            Log::info('Leave request approval email sent successfully', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send leave request approval email', [
                'user_email' => $leaveRequest->user->email ?? 'unknown',
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't fail the request if email fails
        }

        return redirect()->route('admin.leave-requests.show', $leaveRequest)
            ->with('success', 'Leave request approved successfully.');
    }

    /**
     * Force approve an offset leave request when employee has negative overtime balance.
     * This allows approving offset requests even when it would make the balance more negative.
     */
    public function forceAccept(Request $request, LeaveRequest $leaveRequest)
    {
        // Only allow force accept for offset requests
        if ($leaveRequest->type !== 'offset') {
            return redirect()->route('admin.leave-requests.show', $leaveRequest)
                ->with('error', 'Force accept is only available for offset requests.');
        }

        // Only allow for pending requests
        if (!$leaveRequest->isPending()) {
            return redirect()->route('admin.leave-requests.show', $leaveRequest)
                ->with('error', 'This request has already been processed.');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        // Ensure user relationship is loaded
        $leaveRequest->load('user');

        $statusBefore = $leaveRequest->status;

        $leaveRequest->update([
            'status' => 'approved',
            'admin_notes' => ($request->admin_notes ?? '') . ' [Force Accepted - Negative Balance]',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Log the force approval action
        LeaveRequestLog::create([
            'leave_request_id' => $leaveRequest->id,
            'action' => 'approved',
            'status_before' => $statusBefore,
            'status_after' => 'approved',
            'notes' => ($request->admin_notes ?? '') . ' [Force Accepted - Negative Balance]',
            'performed_by' => Auth::id(),
        ]);

        // Send email notification to employee
        try {
            MailConfigService::configure();

            Log::info('Sending leave request force acceptance email to user', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id,
                'user_name' => $leaveRequest->user->name
            ]);

            Mail::to($leaveRequest->user->email)->send(
                new LeaveRequestStatusUpdate($leaveRequest, 'approved', $request->admin_notes)
            );

            Log::info('Leave request force acceptance email sent successfully', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send leave request force acceptance email', [
                'user_email' => $leaveRequest->user->email ?? 'unknown',
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't fail the request if email fails
        }

        return redirect()->route('admin.leave-requests.show', $leaveRequest)
            ->with('success', 'Offset request force accepted. Negative balance will be applied to employee account.');
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        // Ensure user relationship is loaded
        $leaveRequest->load('user');

        $statusBefore = $leaveRequest->status;

        $leaveRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Log the rejection action
        LeaveRequestLog::create([
            'leave_request_id' => $leaveRequest->id,
            'action' => 'rejected',
            'status_before' => $statusBefore,
            'status_after' => 'rejected',
            'notes' => $request->admin_notes,
            'performed_by' => Auth::id(),
        ]);

        // Send email notification to employee
        try {
            MailConfigService::configure();

            Log::info('Sending leave request rejection email to user', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id,
                'user_name' => $leaveRequest->user->name
            ]);

            Mail::to($leaveRequest->user->email)->send(
                new LeaveRequestStatusUpdate($leaveRequest, 'rejected', $request->admin_notes)
            );

            Log::info('Leave request rejection email sent successfully', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send leave request rejection email', [
                'user_email' => $leaveRequest->user->email ?? 'unknown',
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't fail the request if email fails
        }

        return redirect()->route('admin.leave-requests.show', $leaveRequest)
            ->with('success', 'Leave request rejected successfully.');
    }

    /**
     * Resubmit a leave request (mark as pending again for correction).
     */
    public function resubmit(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        // Ensure user relationship is loaded
        $leaveRequest->load('user');

        $statusBefore = $leaveRequest->status;

        $adminNotes = $request->admin_notes
            ? ($leaveRequest->admin_notes ? $leaveRequest->admin_notes . "\n\n[Resubmission Request]: " . $request->admin_notes : $request->admin_notes)
            : $leaveRequest->admin_notes;

        $leaveRequest->update([
            'status' => 'pending',
            'admin_notes' => $adminNotes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Log the resubmission request action
        LeaveRequestLog::create([
            'leave_request_id' => $leaveRequest->id,
            'action' => 'resubmission_requested',
            'status_before' => $statusBefore,
            'status_after' => 'pending',
            'notes' => $request->admin_notes,
            'performed_by' => Auth::id(),
        ]);

        // Send email notification to employee
        try {
            MailConfigService::configure();

            Log::info('Sending leave request resubmission email to user', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id,
                'user_name' => $leaveRequest->user->name
            ]);

            Mail::to($leaveRequest->user->email)->send(
                new LeaveRequestStatusUpdate($leaveRequest, 'resubmission_requested', $request->admin_notes)
            );

            Log::info('Leave request resubmission email sent successfully', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send leave request resubmission email', [
                'user_email' => $leaveRequest->user->email ?? 'unknown',
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't fail the request if email fails
        }

        return redirect()->route('admin.leave-requests.show', $leaveRequest)
            ->with('success', 'Leave request marked for resubmission. The employee will need to correct any errors.');
    }

    /**
     * Display a listing of all student leave requests.
     */
    public function studentIndex(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $query = LeaveRequest::with(['user', 'reviewer'])
            ->whereHas('user', function($q) {
                $q->where('role', 'student');
            });

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by student
        if ($request->has('student') && $request->student) {
            $query->where('user_id', $request->student);
        }

        // Search (student name/email, request fields, IDs)
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search)
                        ->orWhere('user_id', (int) $search);
                }

                $q->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        // Statistics
        $baseQuery = LeaveRequest::whereHas('user', function($q) {
            $q->where('role', 'student');
        });
        if ($request->has('student') && $request->student) {
            $baseQuery->where('user_id', $request->student);
        }
        if ($request->has('type') && $request->type) {
            $baseQuery->where('type', $request->type);
        }
        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search)
                        ->orWhere('user_id', (int) $search);
                }

                $q->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        $students = \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.leave-requests.student-index', compact('leaveRequests', 'stats', 'students', 'search', 'perPage'));
    }

    /**
     * Calendar view of student leave requests for easier tracking.
     */
    public function studentCalendar(Request $request)
    {
        // Use Manila timezone for current date/month context
        $nowManila = Carbon::now('Asia/Manila');
        $monthParam = $request->input('month', $nowManila->format('Y-m'));
        $studentId = $request->input('student');

        try {
            $currentMonth = Carbon::createFromFormat('Y-m', $monthParam, 'Asia/Manila')->startOfMonth();
        } catch (\Exception $e) {
            $currentMonth = $nowManila->copy()->startOfMonth();
        }

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // Extend to full weeks for calendar grid
        $startOfCalendar = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        // Base query for leave requests that intersect the calendar range (students only)
        $leaveQuery = LeaveRequest::with('user')
            ->whereHas('user', function($q) {
                $q->where('role', 'student');
            })
            ->where(function ($outer) use ($startOfCalendar, $endOfCalendar) {
                $outer->where(function ($q) use ($startOfCalendar, $endOfCalendar) {
                    // Requests with a start and end date that overlap the calendar window
                    $q->whereDate('start_date', '<=', $endOfCalendar->toDateString())
                      ->whereDate('end_date', '>=', $startOfCalendar->toDateString());
                })->orWhere(function ($q) use ($startOfCalendar, $endOfCalendar) {
                    // Handle single-day requests where end_date is null
                    $q->whereNull('end_date')
                      ->whereDate('start_date', '>=', $startOfCalendar->toDateString())
                      ->whereDate('start_date', '<=', $endOfCalendar->toDateString());
                });
            });

        if ($studentId) {
            $leaveQuery->where('user_id', $studentId);
        }

        $leaveRequests = $leaveQuery->get();

        // Prepare map of day => leave entries
        $days = [];
        $period = CarbonPeriod::create($startOfCalendar, $endOfCalendar);

        foreach ($period as $date) {
            $key = $date->toDateString();
            $days[$key] = [
                'date' => $date->copy(),
                'requests' => [],
            ];
        }

        foreach ($leaveRequests as $requestItem) {
            $rangeStart = $requestItem->start_date->copy()->max($startOfCalendar);
            $rangeEnd = ($requestItem->end_date ?? $requestItem->start_date)->copy()->min($endOfCalendar);

            $dayPeriod = CarbonPeriod::create($rangeStart, $rangeEnd);
            foreach ($dayPeriod as $day) {
                $key = $day->toDateString();
                if (!isset($days[$key])) {
                    continue;
                }

                $days[$key]['requests'][] = [
                    'id' => $requestItem->id,
                    'student' => $requestItem->user,
                    'type_label' => $requestItem->type_label,
                    'status' => $requestItem->status,
                ];
            }
        }

        $weeks = [];
        $week = [];
        foreach ($days as $day) {
            $week[] = $day;
            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }
        if (!empty($week)) {
            $weeks[] = $week;
        }

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // Students list for sidebar filter (students only)
        // Only show students who haven't met their required training hours
        $allStudents = \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->get();

        // Get total DTR hours for all students
        $studentIds = $allStudents->pluck('id');
        $totalsByStudent = \App\Models\Dtr::whereIn('user_id', $studentIds)
            ->selectRaw('user_id, COALESCE(SUM(total_hours), 0) as total_hours_sum')
            ->groupBy('user_id')
            ->pluck('total_hours_sum', 'user_id');

        // Filter students: only show those who haven't met their required time
        // (total DTR hours < required_training_hours, or required_training_hours is null/0)
        // Also exclude disabled accounts
        $students = $allStudents->filter(function ($student) use ($totalsByStudent) {
            // Exclude disabled accounts
            if (!$student->is_active) {
                return false;
            }

            $requiredHours = (float) ($student->required_training_hours ?? 0);
            $totalDtrHours = (float) ($totalsByStudent[$student->id] ?? 0);

            // If no required hours set, show the student (they haven't met undefined requirement)
            if ($requiredHours <= 0) {
                return true;
            }

            // Only show if total DTR hours < required hours (hasn't met requirement)
            return $totalDtrHours < $requiredHours;
        })->sortBy('name')->values();

        return view('admin.leave-requests.student-calendar', [
            'currentMonth' => $currentMonth,
            'weeks' => $weeks,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'students' => $students,
            // For filing leave, allow selecting from all active students
            'studentsForFiling' => $allStudents->sortBy('name')->values(),
            'selectedStudent' => $studentId,
        ]);
    }

    /**
     * Allow admins to file a leave request on behalf of one or more students (student calendar page).
     * Supports bulk filing (one request per selected student).
     */
    public function storeForStudent(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => [
                'required',
                Rule::exists('users', 'id')->where(function ($q) {
                    $q->where('role', 'student')->where('is_active', true);
                }),
            ],
            'type' => ['required', Rule::in(['additional_time', 'absent', 'other'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $typeInput = $validated['type'];
        // Allow past dates only for student additional_time; others must be today or future
        if ($typeInput !== 'additional_time') {
            $request->validate([
                'start_date' => ['required', 'date', 'after_or_equal:today'],
            ]);
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : $startDate;
        $daysRequested = $startDate->diffInDays($endDate) + 1;

        $createdCount = 0;

        foreach ($validated['student_ids'] as $studentId) {
            $student = User::findOrFail($studentId);

            $leaveRequest = LeaveRequest::create([
                'user_id' => $student->id,
                'type' => $validated['type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? $validated['start_date'],
                'reason' => $validated['reason'] ?? '',
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]);

            LeaveRequestLog::create([
                'leave_request_id' => $leaveRequest->id,
                'action' => 'filed_by_admin',
                'status_before' => null,
                'status_after' => 'pending',
                'notes' => 'Filed by admin on behalf of student',
                'performed_by' => Auth::id(),
            ]);

            Log::info('Admin filed leave request for student', [
                'leave_request_id' => $leaveRequest->id,
                'student_id' => $student->id,
                'admin_id' => Auth::id(),
                'type' => $validated['type'],
                'days' => $daysRequested,
            ]);

            $createdCount++;
        }

        return redirect()->back()->with('success', "Leave request filed for {$createdCount} student(s).");
    }

    /**
     * Credit Additional Time leave requests to DTR total hours.
     * 1 day = 8.00 hours added to total_hours; overtime recalculated.
     */
    private function applyAdditionalTimeToDtr(LeaveRequest $leaveRequest): void
    {
        $start = Carbon::parse($leaveRequest->start_date);
        $end = $leaveRequest->end_date ? Carbon::parse($leaveRequest->end_date) : $start;
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $dtr = Dtr::firstOrNew([
                'user_id' => $leaveRequest->user_id,
                'date' => $date->toDateString(),
            ]);

            // Default status for new records
            if (!$dtr->exists) {
                $dtr->status = $dtr->status ?? 'present';
            }

            $existingTotal = (float) $dtr->total_hours;
            $newTotal = $existingTotal + 8.0; // 1 day = 8 hours
            $dtr->total_hours = $newTotal;
            $dtr->overtime_hours = max($newTotal - 8.0, 0);

            $dtr->save();
        }
    }

    /**
     * Apply travel leave time to DTR records.
     * Each day gets custom hours (default 8.0) added to DTR with travel status.
     */
    private function applyTravelTimeToDtr(LeaveRequest $leaveRequest, float $hoursPerDay = 8.0): void
    {
        $start = Carbon::parse($leaveRequest->start_date);
        $end = $leaveRequest->end_date ? Carbon::parse($leaveRequest->end_date) : $start;
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $dtr = Dtr::firstOrNew([
                'user_id' => $leaveRequest->user_id,
                'date' => $date->toDateString(),
            ]);

            // If DTR record already exists, add hours to existing total
            // If it doesn't exist, create new record with specified hours
            if ($dtr->exists) {
                $existingTotal = (float) ($dtr->total_hours ?? 0);
                $newTotal = $existingTotal + $hoursPerDay;
                $dtr->total_hours = $newTotal;
                $dtr->overtime_hours = max($newTotal - 8.0, 0);
                
                // Update remarks to include travel information
                $existingRemarks = $dtr->remarks ?? '';
                $travelRemark = "Travel Leave ({$hoursPerDay}h)";
                if (!empty($existingRemarks) && strpos($existingRemarks, $travelRemark) === false) {
                    $dtr->remarks = $existingRemarks . '; ' . $travelRemark;
                } elseif (empty($existingRemarks)) {
                    $dtr->remarks = $travelRemark;
                }
                
                // Set status to travel if not already set
                if ($dtr->status !== 'travel') {
                    $dtr->status = 'travel';
                }
            } else {
                // Create new DTR record with specified hours for travel
                $dtr->total_hours = $hoursPerDay;
                $dtr->overtime_hours = max($hoursPerDay - 8.0, 0);
                $dtr->status = 'travel';
                $dtr->remarks = "Travel Leave ({$hoursPerDay}h)";
            }

            $dtr->save();

            // Recalculate weekly deficit after creating/updating DTR
            $this->calculateAndStoreWeeklyDeficit($leaveRequest->user_id, $date);
        }
    }

    /**
     * Apply vacation leave or sick leave time to DTR records.
     * Each approved day gets 8.0 hours automatically added.
     */
    private function applyLeaveTimeToDtr(LeaveRequest $leaveRequest): void
    {
        $start = Carbon::parse($leaveRequest->start_date);
        $end = $leaveRequest->end_date ? Carbon::parse($leaveRequest->end_date) : $start;
        $period = CarbonPeriod::create($start, $end);

        $leaveTypeLabel = $leaveRequest->type === 'vacation_leave' ? 'Vacation Leave' : 'Sick Leave';

        foreach ($period as $date) {
            $dtr = Dtr::firstOrNew([
                'user_id' => $leaveRequest->user_id,
                'date' => $date->toDateString(),
            ]);

            // If DTR record already exists, add 8 hours to existing total
            // If it doesn't exist, create new record with 8.0 hours
            if ($dtr->exists) {
                $existingTotal = (float) ($dtr->total_hours ?? 0);
                $newTotal = $existingTotal + 8.0; // Add 8 hours for the leave day
                $dtr->total_hours = $newTotal;
                $dtr->overtime_hours = max($newTotal - 8.0, 0);
                
                // Update remarks to include leave information
                $existingRemarks = $dtr->remarks ?? '';
                $leaveRemark = "Approved {$leaveTypeLabel}";
                if (!empty($existingRemarks) && strpos($existingRemarks, $leaveRemark) === false) {
                    $dtr->remarks = $existingRemarks . '; ' . $leaveRemark;
                } elseif (empty($existingRemarks)) {
                    $dtr->remarks = $leaveRemark;
                }
            } else {
                // Create new DTR record with 8.0 hours for the leave day
                $dtr->total_hours = 8.0;
                $dtr->overtime_hours = 0;
                $dtr->status = 'on_leave';
                $dtr->remarks = "Approved {$leaveTypeLabel}";
            }

            $dtr->save();

            // Recalculate weekly deficit after creating/updating DTR
            $this->calculateAndStoreWeeklyDeficit($leaveRequest->user_id, $date);
        }
    }

    /**
     * Calculate and store weekly deficit for a user on a specific date.
     * This matches the logic from DtrController for consistency.
     */
    private function calculateAndStoreWeeklyDeficit(int $userId, Carbon $date): void
    {
        try {
            // Get the week start and end dates (ISO week)
            $weekStart = $date->copy()->startOfWeek();
            $weekEnd = $date->copy()->endOfWeek();
            $today = Carbon::today();

            // Don't calculate deficit for current week (week hasn't ended yet)
            if ($today->lte($weekEnd)) {
                // Current week - don't store deficit
                return;
            }

            // Get all DTR records for this user in this week, excluding future dates
            $weeklyDtrs = Dtr::where('user_id', $userId)
                ->whereDate('date', '>=', $weekStart->toDateString())
                ->whereDate('date', '<=', $weekEnd->toDateString())
                ->whereDate('date', '<=', $today->toDateString()) // Exclude future dates
                ->get();

            // Calculate weekly total hours (only from past and today, not future)
            $weeklyTotalHours = $weeklyDtrs->sum('total_hours');

            // Calculate deficit: 40:00 (2400 minutes) - weekly total
            $weeklyBaseHours = 40.0; // 40 hours = 40:00
            $deficitHours = max(0, $weeklyBaseHours - $weeklyTotalHours);

            // Store or update deficit record (only for completed weeks with actual deficit)
            // If there's no deficit, delete any existing deficit record for this week
            if ($deficitHours > 0) {
                DtrDeficit::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'week_start_date' => $weekStart->toDateString(),
                        'week_end_date' => $weekEnd->toDateString(),
                    ],
                    [
                        'deficit_hours' => $deficitHours,
                        'is_applied' => true,
                    ]
                );
            } else {
                // No deficit - delete any existing deficit record for this week
                DtrDeficit::where('user_id', $userId)
                    ->where('week_start_date', $weekStart->toDateString())
                    ->where('week_end_date', $weekEnd->toDateString())
                    ->delete();
            }
        } catch (\Exception $e) {
            Log::error('Failed to calculate weekly deficit', [
                'user_id' => $userId,
                'date' => $date->toDateString(),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a leave request that was filed by an admin.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        // Check if this request was filed by an admin
        $filedByAdminLog = $leaveRequest->logs()
            ->where('action', 'filed_by_admin')
            ->first();

        if (!$filedByAdminLog) {
            return redirect()->route('admin.leave-requests.index')
                ->with('error', 'This leave request cannot be deleted. Only requests filed by admins can be deleted.');
        }

        // Check if the current admin is the one who filed it
        if ($filedByAdminLog->performed_by !== Auth::id()) {
            return redirect()->route('admin.leave-requests.index')
                ->with('error', 'You can only delete leave requests that you filed.');
        }

        // Delete the leave request (logs will be cascade deleted)
        $leaveRequest->delete();

        return redirect()->route('admin.leave-requests.index')
            ->with('success', 'Leave request deleted successfully.');
    }
}
