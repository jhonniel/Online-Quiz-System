<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LeaveRequestStatusUpdate;
use App\Models\Department;
use App\Models\Dtr;
use App\Models\DtrDeficit;
use App\Models\LeaveBalance;
use App\Support\WorkFromHomeQuota;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestLog;
use App\Models\Setting;
use App\Models\User;
use App\Rules\ClickUpTasksUrlsOnly;
use App\Services\LeaveRequestStaleResubmissionService;
use App\Services\MailConfigService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class LeaveRequestController extends Controller
{
    /** Max inclusive days processed per leave request when syncing DTR (prevents memory exhaustion). */
    private const MAX_LEAVE_DTR_DAYS_PER_REQUEST = 366;

    /**
     * Display a listing of all leave requests.
     */
    public function index(Request $request)
    {
        $this->reconcilePendingAdditionalTimeRollbacks();

        $user = $this->requireAuthUser();
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $query = LeaveRequest::with([
            'user',
            'reviewer',
            'approvedBy.performer',
            'rejectedBy.performer',
            'resubmissionRequestedBy.performer',
            'logs' => fn ($q) => $q->where('action', 'filed_by_admin')->latest('id')->limit(1),
        ])
            ->whereHas('user', function ($q) {
                $q->where('role', 'employee');
            });

        // Apply department restrictions if user has Employee Management with restrictions
        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                $query->whereHas('user', function ($q) use ($allowedDepartmentIds) {
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
                $query->whereHas('user', function ($q) use ($selectedDeptId) {
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
        $baseQuery = LeaveRequest::whereHas('user', function ($q) {
            $q->where('role', 'employee');
        });

        // Apply department restrictions to stats
        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                $baseQuery->whereHas('user', function ($q) use ($allowedDepartmentIds) {
                    $q->whereIn('department_id', $allowedDepartmentIds);
                });
            }
        }

        if ($request->has('department_id') && $request->department_id) {
            $selectedDeptId = $request->department_id;
            if ($user->canManageDepartment($selectedDeptId)) {
                $baseQuery->whereHas('user', function ($q) use ($selectedDeptId) {
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
            'for_more_verification' => (clone $baseQuery)->where('status', 'for_more_verification')->count(),
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

            $leaveBalance = \App\Models\LeaveBalance::firstOrCreateWithCarryover(
                (int) $user->id,
                (int) $currentYear,
                (float) $defaultVacation,
                (float) $defaultSick
            );

            $usedVacation = $this->sumApprovedLeaveDaysForUser($user->id, 'vacation_leave', $currentYear);
            $usedSick = $this->sumApprovedLeaveDaysForUser($user->id, 'sick_leave', $currentYear);
            $usedLeaveCredits = $this->sumApprovedLeaveDaysForUser($user->id, ['vacation_leave', 'sick_leave'], $currentYear);

            $combinedAllowance = (float) $leaveBalance->vacation_allowance + (float) $leaveBalance->sick_allowance;

            // Only exclude a pending WFH request from the balance preview; approved requests must count as used.
            $excludeWfhRequestId = ($leaveRequest->type === 'work_from_home' && ! $leaveRequest->isApproved())
                ? (int) $leaveRequest->id
                : null;

            $wfhBalance = WorkFromHomeQuota::balanceForMonth(
                (int) $user->id,
                $leaveRequest->start_date?->copy(),
                $excludeWfhRequestId
            );

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
                'leave' => [
                    'allowance' => $combinedAllowance,
                    'used' => $usedLeaveCredits,
                    'remaining' => max($combinedAllowance - $usedLeaveCredits, 0),
                ],
                'work_from_home' => $wfhBalance,
            ];

            // Establish current date for completed-week checks
            $today = Carbon::today();

            // Overtime balance is based on approved overtime requests
            // minus approved offset requests (using "Hours to Deduct" from the reason field).
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

            // Get approved offset leave requests that should deduct from overtime balance
            $approvedOffsetRequests = LeaveRequest::where('user_id', $user->id)
                ->where('type', 'offset')
                ->where('status', 'approved')
                ->get();

            $offsetMinutes = 0;
            foreach ($approvedOffsetRequests as $offsetRequest) {
                $raw = $offsetRequest->reason ?? '';
                // Parse "Hours to Deduct: HH:MM" from reason (same pattern used in DTR controller)
                if (preg_match('/Hours to Deduct:\s*([0-9]{2}):([0-9]{2})/', $raw, $m)) {
                    $offsetMinutes += (int) $m[1] * 60 + (int) $m[2];
                } else {
                    // Fallback: use days * 8 hours if no explicit HH:MM pattern is present
                    $offsetMinutes += (int) round(($offsetRequest->days * 8) * 60);
                }
            }

            // Net overtime hours = earned overtime - approved offsets (can be negative)
            $totalOvertimeHours = ($overtimeFromLeavesMinutes - $offsetMinutes) / 60;

            // Build set of weeks where overtime was earned (only from approved overtime leave requests)
            $overtimeWeekKeys = [];
            foreach ($approvedOvertimeRequests as $otRequest) {
                $weekStart = $otRequest->start_date->copy()->startOfWeek()->toDateString();
                $overtimeWeekKeys[$weekStart] = true;
            }

            // Format overtime balance (can be negative)
            $absOvertimeMinutes = (int) round(abs($totalOvertimeHours) * 60);
            $overtimeHoursPart = intdiv($absOvertimeMinutes, 60);
            $overtimeMinutesPart = $absOvertimeMinutes % 60;
            $overtimeFormatted = sprintf('%02d:%02d', $overtimeHoursPart, $overtimeMinutesPart);

            // Check if this is an offset request and if the duration exceeds overtime balance
            $hasNegativeBalance = false;
            if ($leaveRequest->type === 'offset' && $leaveRequest->isPending()) {
                // Calculate offset hours needed from "Hours to Deduct" if present, otherwise days * 8.
                $offsetHoursNeeded = $leaveRequest->days * 8;
                $raw = $leaveRequest->reason ?? '';
                if (preg_match('/Hours to Deduct:\s*([0-9]{2}):([0-9]{2})/', $raw, $m)) {
                    $offsetHoursNeeded = (int) $m[1] + ((int) $m[2] / 60);
                }

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

                return $sign.sprintf('%02d:%02d', $h, $m);
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

        // Load recent activity only (avoid loading unbounded log history into memory)
        $leaveRequest->load([
            'logs' => fn ($q) => $q->with('performer')->orderByDesc('created_at')->limit(200),
        ]);

        $leaveTypeOptions = collect(LeaveRequest::adminSelectableTypesForRole($user->role))
            ->map(fn (string $type) => [
                'value' => $type,
                'label' => LeaveRequest::labelForType($type),
            ])
            ->all();

        return view('admin.leave-requests.show', compact(
            'leaveRequest',
            'balances',
            'overtimeFormatted',
            'signatories',
            'studentTime',
            'hasNegativeBalance',
            'leaveTypeOptions'
        ));
    }

    /**
     * Change the request type from the admin details page.
     */
    public function updateType(Request $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->load('user');
        $this->assertCanManageLeaveRequestSubject($leaveRequest);

        if ($leaveRequest->status === 'approved') {
            return redirect('/admin/leave-requests/'.$leaveRequest->id)
                ->withErrors(['type' => 'Unable to update request type for an approved request.']);
        }

        $allowedTypes = LeaveRequest::adminSelectableTypesForRole($leaveRequest->user->role);

        $validated = $request->validate([
            'type' => ['required', Rule::in($allowedTypes)],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $newType = $validated['type'];
        $oldType = $leaveRequest->type;

        if ($oldType === $newType) {
            return redirect('/admin/leave-requests/'.$leaveRequest->id)
                ->with('info', 'Request type is already set to '.LeaveRequest::labelForType($newType).'.');
        }

        $wasApproved = $leaveRequest->status === 'approved';

        if ($wasApproved && $this->leaveRequestInclusiveDayCount($leaveRequest) > self::MAX_LEAVE_DTR_DAYS_PER_REQUEST) {
            return redirect('/admin/leave-requests/'.$leaveRequest->id)
                ->withErrors([
                    'type' => 'This request spans more than '.self::MAX_LEAVE_DTR_DAYS_PER_REQUEST.' days. Shorten the date range before changing type on an approved request.',
                ]);
        }

        if ($wasApproved) {
            $this->revertApprovedCreditOnResubmission($leaveRequest, true);
        }

        $leaveRequest->update(['type' => $newType]);

        if ($wasApproved) {
            $this->applyApprovedCreditsForType($leaveRequest);
        }

        $logNotes = trim((string) ($validated['admin_notes'] ?? ''));
        if ($logNotes === '') {
            $logNotes = sprintf(
                'Changed from %s to %s.',
                LeaveRequest::labelForType($oldType),
                LeaveRequest::labelForType($newType)
            );
        }

        LeaveRequestLog::create([
            'leave_request_id' => $leaveRequest->id,
            'action' => 'type_changed',
            'status_before' => $leaveRequest->status,
            'status_after' => $leaveRequest->status,
            'notes' => $logNotes,
            'performed_by' => Auth::id(),
            'changes' => [
                'type' => [
                    'from' => $oldType,
                    'to' => $newType,
                ],
            ],
        ]);

        $message = 'Request type updated to '.LeaveRequest::labelForType($newType).'.';
        if ($wasApproved) {
            $message .= ' DTR credits were adjusted for the new type.';
        }

        return redirect('/admin/leave-requests/'.$leaveRequest->id)
            ->with('success', $message);
    }

    /**
     * Change start/end dates from the admin details page (any date, including past).
     */
    public function updateDates(Request $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->load('user');
        $this->assertCanManageLeaveRequestSubject($leaveRequest);

        if ($leaveRequest->status === 'approved') {
            return redirect('/admin/leave-requests/'.$leaveRequest->id)
                ->withErrors(['start_date' => 'Unable to update dates for an approved request.']);
        }

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $newStart = Carbon::parse($validated['start_date'])->startOfDay();
        $newEnd = ! empty($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->startOfDay()
            : $newStart->copy();

        $oldStart = $leaveRequest->start_date->copy()->startOfDay();
        $oldEnd = ($leaveRequest->end_date ?? $leaveRequest->start_date)->copy()->startOfDay();

        if ($oldStart->toDateString() === $newStart->toDateString()
            && $oldEnd->toDateString() === $newEnd->toDateString()) {
            return redirect('/admin/leave-requests/'.$leaveRequest->id)
                ->with('info', 'Dates are already set to the selected range.');
        }

        $wasApproved = $leaveRequest->status === 'approved';
        $newDayCount = (int) $newStart->diffInDays($newEnd) + 1;

        if ($wasApproved && $newDayCount > self::MAX_LEAVE_DTR_DAYS_PER_REQUEST) {
            return redirect('/admin/leave-requests/'.$leaveRequest->id)
                ->withErrors([
                    'end_date' => 'The new date range spans more than '.self::MAX_LEAVE_DTR_DAYS_PER_REQUEST.' days. Shorten the range before updating an approved request.',
                ])
                ->withInput();
        }

        if ($wasApproved) {
            $this->revertApprovedCreditOnResubmission($leaveRequest, true);
        }

        $leaveRequest->update([
            'start_date' => $newStart->toDateString(),
            'end_date' => $newEnd->toDateString(),
        ]);
        $leaveRequest->refresh();

        if ($wasApproved) {
            $this->applyApprovedCreditsForType($leaveRequest);
        }

        $logNotes = trim((string) ($validated['admin_notes'] ?? ''));
        if ($logNotes === '') {
            $logNotes = sprintf(
                'Changed dates from %s – %s to %s – %s.',
                $this->formatLeaveDateForAdminLog($oldStart),
                $this->formatLeaveDateForAdminLog($oldEnd),
                $this->formatLeaveDateForAdminLog($newStart),
                $this->formatLeaveDateForAdminLog($newEnd)
            );
        }

        LeaveRequestLog::create([
            'leave_request_id' => $leaveRequest->id,
            'action' => 'dates_changed',
            'status_before' => $leaveRequest->status,
            'status_after' => $leaveRequest->status,
            'notes' => $logNotes,
            'performed_by' => Auth::id(),
            'changes' => [
                'start_date' => [
                    'from' => $oldStart->toDateString(),
                    'to' => $newStart->toDateString(),
                ],
                'end_date' => [
                    'from' => $oldEnd->toDateString(),
                    'to' => $newEnd->toDateString(),
                ],
            ],
        ]);

        $message = 'Request dates updated to '
            .$this->formatLeaveDateForAdminLog($newStart)
            .' – '
            .$this->formatLeaveDateForAdminLog($newEnd)
            .'.';
        if ($wasApproved) {
            $message .= ' DTR credits were adjusted for the new date range.';
        }

        return redirect('/admin/leave-requests/'.$leaveRequest->id)
            ->with('success', $message);
    }

    /**
     * Calendar view of leave requests for easier tracking.
     */
    public function calendar(Request $request)
    {
        $user = $this->requireAuthUser();

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
            ->whereHas('user', function ($q) use ($departmentId, $user) {
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
                if (! isset($days[$key])) {
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
        if (! empty($week)) {
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
        $user = $this->requireAuthUser();

        // Admin-side filing for employees: allow any leave type and any date (including past dates).
        $allowedTypes = [
            'vacation_leave',
            'sick_leave',
            'work_from_home',
            'absent',
            'overtime',
            'offset',
            'additional_time',
            'travel',
            'other',
        ];

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
            'reason' => ['required_if:type,travel', 'nullable', 'string', 'max:1000'],
            'travel_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            // Same structured fields as employee leave-requests/create
            'overtime_hours' => ['required_if:type,overtime', 'nullable', 'regex:/^\d{2}:\d{2}$/'],
            'overtime_dates' => ['required_if:type,overtime', 'nullable', 'string', 'max:255'],
            'overtime_tasks' => ['required_if:type,overtime', 'nullable', 'string', 'max:2000', new ClickUpTasksUrlsOnly],
            'wfh_mode' => ['required_if:type,work_from_home', 'nullable', 'in:working_remotely,request_to_be_excused'],
            'wfh_address' => ['required_if:type,work_from_home', 'nullable', 'string', 'max:255'],
            'wfh_tasks' => ['required_if:type,work_from_home', 'nullable', 'string', 'max:2000', new ClickUpTasksUrlsOnly],
            'offset_hours' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'supporting_documents' => ['nullable', 'array', 'max:5'],
            'supporting_documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($validated['type'] === 'offset') {
            $offsetText = trim((string) ($validated['offset_hours'] ?? ''));
            if ($offsetText !== '' && $this->parseHourMinuteToMinutesForAdminFiling($offsetText) <= 0) {
                return redirect()->back()
                    ->withErrors(['offset_hours' => 'Please enter valid hours to deduct in HH:MM format (e.g., 08:00).'])
                    ->withInput();
            }
        }

        if ($validated['type'] === 'overtime' && ! $this->requestHasSupportingDocumentUploads($request)) {
            return redirect()->back()
                ->withErrors(['supporting_documents' => 'Supporting document is required for Overtime requests.'])
                ->withInput();
        }

        $reasonToStore = $this->buildReasonStringForAdminFiledEmployeeLeave($validated);

        $employeeIds = $validated['user_ids'];

        // Validate that user can manage all selected employees' departments
        foreach ($employeeIds as $employeeId) {
            $employee = User::findOrFail($employeeId);
            if (! $user->canManageDepartment($employee->department_id)) {
                return redirect()->back()
                    ->withErrors(['user_ids' => "You don't have permission to file leave for employees in this department."])
                    ->withInput();
            }
        }

        // Admins can file leave requests for any date (including past dates) for any leave type
        // No date restrictions for admin-filed leave requests

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

            // Balance check (shared Leave Credits pool) for vacation and sick leave
            if (in_array($validated['type'], ['vacation_leave', 'sick_leave'], true)) {
                $leaveBalance = LeaveBalance::firstOrCreateWithCarryover(
                    (int) $employee->id,
                    (int) $currentYear,
                    (float) $defaultVacation,
                    (float) $defaultSick
                );

                $usedLeaveCredits = LeaveRequest::where('user_id', $employee->id)
                    ->whereIn('type', ['vacation_leave', 'sick_leave'])
                    ->where('status', 'approved')
                    ->whereYear('start_date', $currentYear)
                    ->get()
                    ->sum->days;

                $combinedAllowance = (float) $leaveBalance->vacation_allowance + (float) $leaveBalance->sick_allowance;
                $remainingLeaveCredits = max($combinedAllowance - $usedLeaveCredits, 0);

                if ($daysRequested > $remainingLeaveCredits) {
                    $failedEmployees[] = [
                        'name' => $employee->name,
                        'reason' => "only has {$remainingLeaveCredits} day(s) of Leave Credits remaining. Requested {$daysRequested} day(s).",
                    ];

                    continue;
                }
            }

            // All requests (including travel) are pending until explicitly approved
            $travelHours = $validated['type'] === 'travel' ? (float) ($validated['travel_hours'] ?? 8.0) : null;
            [$supportingPaths, $legacySupportingPath] = $this->storeSupportingDocumentsFromRequest($request);
            $leaveRequest = LeaveRequest::create([
                'user_id' => $employee->id,
                'type' => $validated['type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? $validated['start_date'],
                'reason' => $reasonToStore,
                'travel_hours' => $travelHours,
                'supporting_document_path' => $legacySupportingPath,
                'supporting_document_paths' => $supportingPaths,
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]);

            LeaveRequestLog::create([
                'leave_request_id' => $leaveRequest->id,
                'action' => 'filed_by_admin',
                'status_before' => null,
                'status_after' => 'pending',
                'notes' => 'Filed by admin on behalf of employee',
                'performed_by' => Auth::id(),
            ]);

            Log::info('Admin filed leave request for employee', [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $employee->id,
                'admin_id' => Auth::id(),
                'type' => $validated['type'],
                'status' => 'pending',
            ]);

            $createdCount++;
        }

        // Prepare success/error messages
        $message = '';
        if ($createdCount > 0) {
            $message = "Leave request filed for {$createdCount} employee(s).".($validated['type'] === 'travel' ? ' Travel requests are subject to approval.' : '');
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

        if (
            $leaveRequest->type === 'work_from_home'
            && $leaveRequest->user?->role === 'employee'
            && ! $leaveRequest->logs()->where('action', 'filed_by_admin')->exists()
        ) {
            $wfhQuotaError = WorkFromHomeQuota::validateApproval($leaveRequest);
            if ($wfhQuotaError !== null) {
                return redirect('/admin/leave-requests/'.$leaveRequest->id)
                    ->withErrors(['approval' => $wfhQuotaError]);
            }
        }

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

        $this->applyApprovedCreditsForType($leaveRequest);

        // Send email notification to employee
        try {
            MailConfigService::configure();

            Log::info('Sending leave request approval email to user', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id,
                'user_name' => $leaveRequest->user->name,
            ]);

            Mail::to($leaveRequest->user->email)->send(
                new LeaveRequestStatusUpdate(
                    $leaveRequest,
                    'approved',
                    $this->normalizedLeaveRequestMailNotes($leaveRequest->admin_notes)
                )
            );

            Log::info('Leave request approval email sent successfully', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send leave request approval email', [
                'user_email' => $leaveRequest->user->email ?? 'unknown',
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
                'exception' => $e::class,
            ]);
            // Don't fail the request if email fails
        }

        return redirect('/admin/leave-requests/'.$leaveRequest->id)
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
            return redirect('/admin/leave-requests/'.$leaveRequest->id)
                ->with('error', 'Force accept is only available for offset requests.');
        }

        // Only allow for pending requests
        if (! $leaveRequest->isPending()) {
            return redirect('/admin/leave-requests/'.$leaveRequest->id)
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
            'admin_notes' => ($request->admin_notes ?? '').' [Force Accepted - Negative Balance]',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Log the force approval action
        LeaveRequestLog::create([
            'leave_request_id' => $leaveRequest->id,
            'action' => 'approved',
            'status_before' => $statusBefore,
            'status_after' => 'approved',
            'notes' => ($request->admin_notes ?? '').' [Force Accepted - Negative Balance]',
            'performed_by' => Auth::id(),
        ]);

        // Send email notification to employee
        try {
            MailConfigService::configure();

            Log::info('Sending leave request force acceptance email to user', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id,
                'user_name' => $leaveRequest->user->name,
            ]);

            Mail::to($leaveRequest->user->email)->send(
                new LeaveRequestStatusUpdate(
                    $leaveRequest,
                    'approved',
                    $this->normalizedLeaveRequestMailNotes($leaveRequest->admin_notes)
                )
            );

            Log::info('Leave request force acceptance email sent successfully', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send leave request force acceptance email', [
                'user_email' => $leaveRequest->user->email ?? 'unknown',
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
                'exception' => $e::class,
            ]);
            // Don't fail the request if email fails
        }

        return redirect('/admin/leave-requests/'.$leaveRequest->id)
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
                'user_name' => $leaveRequest->user->name,
            ]);

            Mail::to($leaveRequest->user->email)->send(
                new LeaveRequestStatusUpdate(
                    $leaveRequest,
                    'rejected',
                    $this->normalizedLeaveRequestMailNotes($leaveRequest->admin_notes)
                )
            );

            Log::info('Leave request rejection email sent successfully', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send leave request rejection email', [
                'user_email' => $leaveRequest->user->email ?? 'unknown',
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
                'exception' => $e::class,
            ]);
            // Don't fail the request if email fails
        }

        return redirect('/admin/leave-requests/'.$leaveRequest->id)
            ->with('success', 'Leave request rejected successfully.');
    }

    /**
     * Mark a leave request as requiring further verification.
     */
    public function verify(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $leaveRequest->load('user');
        $statusBefore = $leaveRequest->status;

        $leaveRequest->update([
            'status' => 'for_more_verification',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        LeaveRequestLog::create([
            'leave_request_id' => $leaveRequest->id,
            'action' => 'for_more_verification',
            'status_before' => $statusBefore,
            'status_after' => 'for_more_verification',
            'notes' => $request->admin_notes,
            'performed_by' => Auth::id(),
        ]);

        try {
            MailConfigService::configure();

            Log::info('Sending leave request for-more-verification email to user', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id,
                'user_name' => $leaveRequest->user->name,
            ]);

            Mail::to($leaveRequest->user->email)->send(
                new LeaveRequestStatusUpdate(
                    $leaveRequest,
                    'for_more_verification',
                    $this->normalizedLeaveRequestMailNotes($leaveRequest->admin_notes)
                )
            );

            Log::info('Leave request for-more-verification email sent successfully', [
                'user_email' => $leaveRequest->user->email,
                'leave_request_id' => $leaveRequest->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send leave request verification email', [
                'user_email' => $leaveRequest->user->email ?? 'unknown',
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
                'exception' => $e::class,
            ]);
            // Don't fail the request if email fails
        }

        return redirect('/admin/leave-requests/'.$leaveRequest->id)
            ->with('success', 'Leave request marked for more verification.');
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

        // If request was already approved, revert any previously credited DTR time
        // when moving it to Resubmission Requested (pending).
        if ($statusBefore === 'approved') {
            // Force rollback on the actual approved -> resubmission transition.
            // Do not skip because of prior reconciliation markers.
            $this->revertApprovedCreditOnResubmission($leaveRequest, true);
        }

        $adminNotes = $request->admin_notes
            ? ($leaveRequest->admin_notes ? $leaveRequest->admin_notes."\n\n[Resubmission Request]: ".$request->admin_notes : $request->admin_notes)
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

        $leaveRequest->refresh();

        app(LeaveRequestStaleResubmissionService::class)->notifyUserResubmissionRequested(
            $leaveRequest,
            $this->normalizedLeaveRequestMailNotes($leaveRequest->admin_notes)
        );

        return redirect('/admin/leave-requests/'.$leaveRequest->id)
            ->with('success', 'Leave request marked for resubmission. The employee will need to correct any errors.');
    }

    /**
     * Display a listing of all student leave requests.
     */
    public function studentIndex(Request $request)
    {
        $this->reconcilePendingAdditionalTimeRollbacks();
        $user = $this->requireAuthUser();
        $allowedDepartmentIds = $user->canAccessStudentManagement()
            ? $user->getAllowedStudentDepartmentIds()
            : null;

        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $query = LeaveRequest::with(['user', 'reviewer'])
            ->whereHas('user', function ($q) use ($allowedDepartmentIds) {
                $q->where('role', 'student');
                if ($allowedDepartmentIds !== null) {
                    $q->whereIn('department_id', $allowedDepartmentIds);
                }
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
        $baseQuery = LeaveRequest::whereHas('user', function ($q) {
            $q->where('role', 'student');
        });
        if ($allowedDepartmentIds !== null) {
            $baseQuery->whereHas('user', function ($q) use ($allowedDepartmentIds) {
                $q->whereIn('department_id', $allowedDepartmentIds);
            });
        }
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
            'for_more_verification' => (clone $baseQuery)->where('status', 'for_more_verification')->count(),
        ];

        $students = \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->when($allowedDepartmentIds !== null, function ($q) use ($allowedDepartmentIds) {
                $q->whereIn('department_id', $allowedDepartmentIds);
            })
            ->orderBy('name')
            ->get();

        return view('admin.leave-requests.student-index', compact('leaveRequests', 'stats', 'students', 'search', 'perPage'));
    }

    /**
     * Calendar view of student leave requests for easier tracking.
     */
    public function studentCalendar(Request $request)
    {
        $user = $this->requireAuthUser();
        $allowedDepartmentIds = $user->canAccessStudentManagement()
            ? $user->getAllowedStudentDepartmentIds()
            : null;

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
            ->whereHas('user', function ($q) use ($allowedDepartmentIds) {
                $q->where('role', 'student');
                if ($allowedDepartmentIds !== null) {
                    $q->whereIn('department_id', $allowedDepartmentIds);
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

        if ($studentId) {
            $studentFilterQuery = User::where('id', $studentId)->where('role', 'student');
            if ($allowedDepartmentIds !== null) {
                $studentFilterQuery->whereIn('department_id', $allowedDepartmentIds);
            }
            if ($studentFilterQuery->exists()) {
                $leaveQuery->where('user_id', $studentId);
            }
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
                if (! isset($days[$key])) {
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
        if (! empty($week)) {
            $weeks[] = $week;
        }

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // Students list for sidebar filter (students only)
        // Only show students who haven't met their required training hours
        $allStudents = \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->when($allowedDepartmentIds !== null, function ($q) use ($allowedDepartmentIds) {
                $q->whereIn('department_id', $allowedDepartmentIds);
            })
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
            if (! $student->is_active) {
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
        $user = $this->requireAuthUser();
        $allowedDepartmentIds = $user->canAccessStudentManagement()
            ? $user->getAllowedStudentDepartmentIds()
            : null;

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
            'supporting_documents' => ['nullable', 'array', 'max:5'],
            'supporting_documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
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
            if ($allowedDepartmentIds !== null && ! in_array($student->department_id, $allowedDepartmentIds, true)) {
                continue;
            }

            [$supportingPaths, $legacySupportingPath] = $this->storeSupportingDocumentsFromRequest($request);
            $leaveRequest = LeaveRequest::create([
                'user_id' => $student->id,
                'type' => $validated['type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? $validated['start_date'],
                'reason' => $validated['reason'] ?? '',
                'supporting_document_path' => $legacySupportingPath,
                'supporting_document_paths' => $supportingPaths,
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

        if ($createdCount === 0) {
            return redirect()->back()->withErrors([
                'student_ids' => 'No leave requests were created. Selected students are outside your assigned departments.',
            ])->withInput();
        }

        return redirect()->back()->with('success', "Leave request filed for {$createdCount} student(s).");
    }

    /**
     * Credit Additional Time leave requests to DTR total hours.
     * 1 day = 8.00 hours added to total_hours; overtime recalculated.
     */
    private function applyAdditionalTimeToDtr(LeaveRequest $leaveRequest): void
    {
        $rawReason = (string) ($leaveRequest->reason ?? '');
        if (preg_match('/Additional Time Hours:\s*(\d{1,3}):(\d{2})/', $rawReason, $m)) {
            $hours = (int) $m[1];
            $minutes = (int) $m[2];
            if ($minutes >= 0 && $minutes <= 59) {
                $hoursToCredit = $hours + ($minutes / 60);
                if ($hoursToCredit > 0) {
                    $date = Carbon::parse($leaveRequest->start_date);
                    $dtr = $this->findOrCreateDtrRecord(
                        (int) $leaveRequest->user_id,
                        $date->toDateString(),
                        'present'
                    );

                    $existingTotal = (float) ($dtr->total_hours ?? 0);
                    $newTotal = $existingTotal + $hoursToCredit;
                    $dtr->total_hours = $newTotal;
                    $dtr->overtime_hours = max($newTotal - 8.0, 0);
                    $additionalTimeRemark = "Additional Time ({$m[1]}:{$m[2]})";
                    $existingRemarks = (string) ($dtr->remarks ?? '');
                    if ($existingRemarks === '') {
                        $dtr->remarks = $additionalTimeRemark;
                    } elseif (strpos($existingRemarks, $additionalTimeRemark) === false) {
                        $dtr->remarks = $existingRemarks.'; '.$additionalTimeRemark;
                    }
                    $dtr->save();

                    return;
                }
            }
        }

        foreach ($this->iterateLeaveRequestDates($leaveRequest) as $date) {
            $dtr = $this->findOrCreateDtrRecord(
                (int) $leaveRequest->user_id,
                $date->toDateString(),
                'present'
            );

            $existingTotal = (float) $dtr->total_hours;
            $newTotal = $existingTotal + 8.0; // 1 day = 8 hours
            $dtr->total_hours = $newTotal;
            $dtr->overtime_hours = max($newTotal - 8.0, 0);
            $existingRemarks = (string) ($dtr->remarks ?? '');
            $additionalTimeRemark = 'Additional Time';
            if ($existingRemarks === '') {
                $dtr->remarks = $additionalTimeRemark;
            } elseif (strpos($existingRemarks, $additionalTimeRemark) === false) {
                $dtr->remarks = $existingRemarks.'; '.$additionalTimeRemark;
            }

            $dtr->save();
        }
    }

    /**
     * Revert Additional Time credits from DTR when moving an approved request
     * back to resubmission (pending).
     */
    private function revertAdditionalTimeFromDtr(LeaveRequest $leaveRequest, bool $force = false): void
    {
        $alreadyReverted = LeaveRequestLog::where('leave_request_id', $leaveRequest->id)
            ->where('action', 'additional_time_reverted')
            ->exists();
        if (! $force && $alreadyReverted) {
            return;
        }

        $didRevert = false;
        $rawReason = (string) ($leaveRequest->reason ?? '');
        if (preg_match('/Additional Time Hours:\s*(\d{1,3}):(\d{2})/', $rawReason, $m)) {
            $hours = (int) $m[1];
            $minutes = (int) $m[2];
            if ($minutes >= 0 && $minutes <= 59) {
                $hoursToDeduct = $hours + ($minutes / 60);
                if ($hoursToDeduct > 0) {
                    $remarkToken = "Additional Time ({$hours}:".str_pad((string) $minutes, 2, '0', STR_PAD_LEFT).')';
                    $date = Carbon::parse($leaveRequest->start_date);
                    $dtr = Dtr::where('user_id', $leaveRequest->user_id)
                        ->whereDate('date', $date->toDateString())
                        ->first();

                    if ($dtr) {
                        $existingRemarks = (string) ($dtr->remarks ?? '');
                        $existingTotal = (float) ($dtr->total_hours ?? 0);
                        $newTotal = max($existingTotal - $hoursToDeduct, 0);
                        $dtr->total_hours = $newTotal;
                        $dtr->overtime_hours = max($newTotal - 8.0, 0);
                        if ($existingRemarks !== '' && strpos($existingRemarks, $remarkToken) !== false) {
                            $dtr->remarks = trim(str_replace([$remarkToken.'; ', '; '.$remarkToken, $remarkToken], '', $existingRemarks));
                        }
                        $dtr->save();
                        $didRevert = true;
                    }

                    if ($didRevert) {
                        LeaveRequestLog::create([
                            'leave_request_id' => $leaveRequest->id,
                            'action' => 'additional_time_reverted',
                            'status_before' => 'approved',
                            'status_after' => 'pending',
                            'notes' => 'Reverted credited Additional Time from DTR during resubmission request.',
                            'performed_by' => Auth::id(),
                        ]);
                    }

                    return;
                }
            }
        }

        foreach ($this->iterateLeaveRequestDates($leaveRequest) as $date) {
            $dtr = Dtr::where('user_id', $leaveRequest->user_id)
                ->whereDate('date', $date->toDateString())
                ->first();

            if (! $dtr) {
                continue;
            }

            $existingRemarks = (string) ($dtr->remarks ?? '');

            $existingTotal = (float) ($dtr->total_hours ?? 0);
            $newTotal = max($existingTotal - 8.0, 0);
            $dtr->total_hours = $newTotal;
            $dtr->overtime_hours = max($newTotal - 8.0, 0);
            if ($existingRemarks !== '' && strpos($existingRemarks, 'Additional Time') !== false) {
                $dtr->remarks = trim(str_replace(['Additional Time; ', '; Additional Time', 'Additional Time'], '', $existingRemarks));
            }
            $dtr->save();
            $didRevert = true;
        }

        if ($didRevert) {
            LeaveRequestLog::create([
                'leave_request_id' => $leaveRequest->id,
                'action' => 'additional_time_reverted',
                'status_before' => 'approved',
                'status_after' => 'pending',
                'notes' => 'Reverted credited Additional Time from DTR during resubmission request.',
                'performed_by' => Auth::id(),
            ]);
        }
    }

    /**
     * One-pass reconciliation for already pending resubmission Additional Time requests.
     */
    private function reconcilePendingAdditionalTimeRollbacks(): void
    {
        LeaveRequest::query()
            ->select(['id', 'user_id', 'type', 'status', 'start_date', 'end_date', 'reason', 'travel_hours'])
            ->whereIn('type', ['additional_time', 'leave', 'vacation_leave', 'sick_leave', 'travel'])
            ->where('status', 'pending')
            ->whereHas('logs', function ($q) {
                $q->where('action', 'approved');
            })
            ->whereHas('logs', function ($q) {
                $q->where('action', 'resubmission_requested');
            })
            ->whereDoesntHave('logs', function ($q) {
                $q->whereIn('action', ['additional_time_reverted', 'leave_time_reverted', 'travel_time_reverted']);
            })
            ->orderBy('id')
            ->chunkById(25, function ($pending): void {
                foreach ($pending as $leaveRequest) {
                    if ($this->leaveRequestInclusiveDayCount($leaveRequest) > self::MAX_LEAVE_DTR_DAYS_PER_REQUEST) {
                        Log::warning('Skipped leave DTR reconciliation: date span too large', [
                            'leave_request_id' => $leaveRequest->id,
                        ]);

                        continue;
                    }

                    $this->revertApprovedCreditOnResubmission($leaveRequest);
                }
            });
    }

    private function revertApprovedCreditOnResubmission(LeaveRequest $leaveRequest, bool $force = false): void
    {
        if ($leaveRequest->type === 'additional_time') {
            $this->revertAdditionalTimeFromDtr($leaveRequest, $force);

            return;
        }

        if (in_array($leaveRequest->type, ['leave', 'vacation_leave', 'sick_leave'], true)) {
            $this->revertLeaveTimeFromDtr($leaveRequest, $force);

            return;
        }

        if ($leaveRequest->type === 'travel') {
            $this->revertTravelTimeFromDtr($leaveRequest, (float) ($leaveRequest->travel_hours ?? 8.0), $force);
        }
    }

    /**
     * Apply DTR credits for an already-approved request based on its current type.
     */
    private function applyApprovedCreditsForType(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->type === 'additional_time') {
            $this->applyAdditionalTimeToDtr($leaveRequest);

            return;
        }

        if (in_array($leaveRequest->type, ['leave', 'vacation_leave', 'sick_leave'], true)) {
            $this->applyLeaveTimeToDtr($leaveRequest);

            return;
        }

        if ($leaveRequest->type === 'travel') {
            $this->applyTravelTimeToDtr($leaveRequest, (float) ($leaveRequest->travel_hours ?? 8.0));
        }
    }

    /**
     * Ensure the authenticated admin may manage this leave request's subject user.
     */
    private function assertCanManageLeaveRequestSubject(LeaveRequest $leaveRequest): void
    {
        $authUser = $this->requireAuthUser();
        $subject = $leaveRequest->user;

        if (! $subject) {
            abort(404, 'Leave request user not found.');
        }

        if ($subject->role === 'employee' && $authUser->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $authUser->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null && ! in_array($subject->department_id, $allowedDepartmentIds, true)) {
                abort(403, 'You do not have permission to manage leave requests for this department.');
            }

            return;
        }

        if ($subject->role === 'student' && $authUser->canAccessStudentManagement()) {
            $allowedDepartmentIds = $authUser->getAllowedStudentDepartmentIds();
            if ($allowedDepartmentIds !== null && ! in_array($subject->department_id, $allowedDepartmentIds, true)) {
                abort(403, 'You do not have permission to manage leave requests for this department.');
            }
        }
    }

    private function revertLeaveTimeFromDtr(LeaveRequest $leaveRequest, bool $force = false): void
    {
        $alreadyReverted = LeaveRequestLog::where('leave_request_id', $leaveRequest->id)
            ->where('action', 'leave_time_reverted')
            ->exists();
        if (! $force && $alreadyReverted) {
            return;
        }

        $hoursPerDay = 8.0;

        $didRevert = false;
        foreach ($this->iterateLeaveRequestDates($leaveRequest) as $date) {
            $dtr = Dtr::where('user_id', $leaveRequest->user_id)
                ->whereDate('date', $date->toDateString())
                ->first();

            if (! $dtr) {
                continue;
            }

            $existingTotal = (float) ($dtr->total_hours ?? 0);
            $newTotal = max($existingTotal - $hoursPerDay, 0);
            $dtr->total_hours = $newTotal;
            $dtr->overtime_hours = max($newTotal - 8.0, 0);
            $dtr->save();
            $didRevert = true;
        }

        if ($didRevert) {
            LeaveRequestLog::create([
                'leave_request_id' => $leaveRequest->id,
                'action' => 'leave_time_reverted',
                'status_before' => 'approved',
                'status_after' => 'pending',
                'notes' => 'Reverted credited leave time from DTR during resubmission request.',
                'performed_by' => Auth::id(),
            ]);
        }
    }

    private function revertTravelTimeFromDtr(LeaveRequest $leaveRequest, float $hoursPerDay = 8.0, bool $force = false): void
    {
        $alreadyReverted = LeaveRequestLog::where('leave_request_id', $leaveRequest->id)
            ->where('action', 'travel_time_reverted')
            ->exists();
        if (! $force && $alreadyReverted) {
            return;
        }

        $didRevert = false;
        foreach ($this->iterateLeaveRequestDates($leaveRequest) as $date) {
            $dtr = Dtr::where('user_id', $leaveRequest->user_id)
                ->whereDate('date', $date->toDateString())
                ->first();

            if (! $dtr) {
                continue;
            }

            $existingTotal = (float) ($dtr->total_hours ?? 0);
            $newTotal = max($existingTotal - $hoursPerDay, 0);
            $dtr->total_hours = $newTotal;
            $dtr->overtime_hours = max($newTotal - 8.0, 0);
            $dtr->save();
            $didRevert = true;
        }

        if ($didRevert) {
            LeaveRequestLog::create([
                'leave_request_id' => $leaveRequest->id,
                'action' => 'travel_time_reverted',
                'status_before' => 'approved',
                'status_after' => 'pending',
                'notes' => 'Reverted credited travel time from DTR during resubmission request.',
                'performed_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Apply travel leave time to DTR records.
     * Each day gets custom hours (default 8.0) added to DTR with travel status.
     */
    private function applyTravelTimeToDtr(LeaveRequest $leaveRequest, float $hoursPerDay = 8.0): void
    {
        foreach ($this->iterateLeaveRequestDates($leaveRequest) as $date) {
            $dtr = $this->findOrCreateDtrRecord(
                (int) $leaveRequest->user_id,
                $date->toDateString(),
                'travel'
            );

            $existingTotal = (float) ($dtr->total_hours ?? 0);
            $newTotal = $existingTotal + $hoursPerDay;
            $dtr->total_hours = $newTotal;
            $dtr->overtime_hours = max($newTotal - 8.0, 0);

            // Update remarks to include travel information
            $existingRemarks = $dtr->remarks ?? '';
            $travelRemark = "Travel Leave ({$hoursPerDay}h)";
            if (! empty($existingRemarks) && strpos($existingRemarks, $travelRemark) === false) {
                $dtr->remarks = $existingRemarks.'; '.$travelRemark;
            } elseif (empty($existingRemarks)) {
                $dtr->remarks = $travelRemark;
            }

            $dtr->status = 'travel';

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
        $leaveTypeLabel = $leaveRequest->type === 'vacation_leave'
            ? 'Vacation Leave'
            : ($leaveRequest->type === 'sick_leave' ? 'Sick Leave' : 'Leave');

        foreach ($this->iterateLeaveRequestDates($leaveRequest) as $date) {
            $dtr = $this->findOrCreateDtrRecord(
                (int) $leaveRequest->user_id,
                $date->toDateString(),
                'on_leave'
            );

            $existingTotal = (float) ($dtr->total_hours ?? 0);
            $newTotal = $existingTotal + 8.0; // Add 8 hours for the leave day
            $dtr->total_hours = $newTotal;
            $dtr->overtime_hours = max($newTotal - 8.0, 0);

            // Update remarks to include leave information
            $existingRemarks = $dtr->remarks ?? '';
            $leaveRemark = "Approved {$leaveTypeLabel}";
            if (! empty($existingRemarks) && strpos($existingRemarks, $leaveRemark) === false) {
                $dtr->remarks = $existingRemarks.'; '.$leaveRemark;
            } elseif (empty($existingRemarks)) {
                $dtr->remarks = $leaveRemark;
            }

            if (empty($dtr->status)) {
                $dtr->status = 'on_leave';
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
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Find or create a DTR record safely under unique(user_id,date).
     */
    private function findOrCreateDtrRecord(int $userId, string $date, string $defaultStatus = 'present'): Dtr
    {
        $existing = Dtr::where('user_id', $userId)
            ->whereDate('date', $date)
            ->first();
        if ($existing) {
            return $existing;
        }

        try {
            return Dtr::create([
                'user_id' => $userId,
                'date' => $date,
                'status' => $defaultStatus,
                'total_hours' => 0,
                'overtime_hours' => 0,
            ]);
        } catch (QueryException $e) {
            if (! $this->isDtrUniqueConstraintError($e)) {
                throw $e;
            }

            $existing = Dtr::where('user_id', $userId)
                ->whereDate('date', $date)
                ->first();
            if ($existing) {
                return $existing;
            }

            throw $e;
        }
    }

    private function isDtrUniqueConstraintError(QueryException $e): bool
    {
        $msg = $e->getMessage();

        return str_contains($msg, 'dtrs.user_id, dtrs.date')
            || str_contains($msg, 'UNIQUE constraint failed');
    }

    /**
     * Parse HH:MM into total minutes (admin calendar filing — mirrors user leave form).
     */
    private function parseHourMinuteToMinutesForAdminFiling(string $text): int
    {
        $text = trim($text);
        if (! preg_match('/^(\d{1,3}):(\d{2})$/', $text, $m)) {
            return 0;
        }
        $h = (int) $m[1];
        $min = (int) $m[2];
        if ($min < 0 || $min > 59) {
            return 0;
        }

        return $h * 60 + $min;
    }

    /**
     * Build stored reason text for admin-filed employee leave (same structure as employee-side forms).
     */
    private function buildReasonStringForAdminFiledEmployeeLeave(array $validated): string
    {
        $type = $validated['type'];
        $reasonToStore = trim((string) ($validated['reason'] ?? ''));

        if ($type === 'overtime') {
            $details = "Overtime Request Details:\n";
            $details .= 'Total Overtime Hours: '.($validated['overtime_hours'] ?? '')."\n";
            $details .= 'Overtime Dates: '.($validated['overtime_dates'] ?? '')."\n";
            $details .= "Tasks / ClickUp Links:\n".($validated['overtime_tasks'] ?? '')."\n";
            if ($reasonToStore !== '') {
                $details .= "\nAdditional Explanation:\n".$reasonToStore;
            }

            return $details;
        }

        if ($type === 'work_from_home') {
            $modeLabel = ($validated['wfh_mode'] ?? '') === 'request_to_be_excused'
                ? 'Request to be excused'
                : 'Working remotely';

            $details = "Work From Home Request Details:\n";
            $details .= 'Mode: '.$modeLabel."\n";
            $details .= 'Remote Address: '.($validated['wfh_address'] ?? '')."\n";
            $details .= 'Work Dates: '.($validated['start_date'] ?? '').' to '.($validated['end_date'] ?? $validated['start_date'])."\n";
            $details .= "Tasks / ClickUp Links:\n".($validated['wfh_tasks'] ?? '')."\n";
            if ($reasonToStore !== '') {
                $details .= "\nAdditional Explanation:\n".$reasonToStore;
            }

            return $details;
        }

        if ($type === 'offset') {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = $validated['end_date']
                ? Carbon::parse($validated['end_date'])
                : $startDate;
            $days = $startDate->diffInDays($endDate) + 1;

            $offsetText = trim((string) ($validated['offset_hours'] ?? ''));
            if ($offsetText !== '') {
                $offsetHours = $offsetText;
            } else {
                $totalHours = $days * 8;
                $offsetHours = sprintf('%02d:00', $totalHours);
            }

            $details = "Offset Request Details:\n";
            $details .= 'Duration: '.$days.' '.($days === 1 ? 'day' : 'days')."\n";
            $details .= 'Hours to Deduct: '.$offsetHours."\n";
            if ($reasonToStore !== '') {
                $details .= "\nReason:\n".$reasonToStore;
            }

            return $details;
        }

        if ($type === 'travel') {
            return 'Location of travel: '.$reasonToStore;
        }

        return $reasonToStore;
    }

    private function requestHasSupportingDocumentUploads(Request $request): bool
    {
        $files = $request->file('supporting_documents', []);

        return is_array($files) && collect($files)->filter()->isNotEmpty();
    }

    /**
     * @return array{0: list<string>, 1: string|null}
     */
    private function storeSupportingDocumentsFromRequest(Request $request): array
    {
        $paths = [];
        $supportDir = 'leave-supporting-docs';
        $assetDisk = 'digitalocean';
        $doConfigured = ! empty(env('DIGITALOCEAN_SPACES_KEY') ?: env('DO_SPACES_KEY'))
            && ! empty(env('DIGITALOCEAN_SPACES_SECRET') ?: env('DO_SPACES_SECRET'))
            && ! empty(env('DIGITALOCEAN_SPACES_BUCKET') ?: env('DO_SPACES_BUCKET'));

        if ($doConfigured) {
            $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
            $supportDir = $assetRoot ? $assetRoot.'/'.$supportDir : $supportDir;
        }

        $disk = $doConfigured ? $assetDisk : config('filesystems.default', 'local');
        $files = $request->file('supporting_documents', []);

        foreach ($files as $file) {
            try {
                $storedPath = $file->store($supportDir, $disk);
                if ($storedPath) {
                    $paths[] = $storedPath;
                }
            } catch (\Throwable $e) {
                Log::warning('Admin-filed leave request supporting document store failed, skipping file', [
                    'error' => $e->getMessage(),
                    'disk' => $disk,
                ]);
            }
        }

        return [$paths, $paths[0] ?? null];
    }

    private function formatLeaveDateForAdminLog(Carbon $date): string
    {
        return $date->format('M j, Y');
    }

    /**
     * Trim reviewer notes for leave status emails; return null when empty so the template can omit the section.
     */
    private function normalizedLeaveRequestMailNotes(?string $notes): ?string
    {
        $trimmed = trim((string) ($notes ?? ''));

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Inclusive day count for a leave request (start through end).
     */
    private function leaveRequestInclusiveDayCount(LeaveRequest $leaveRequest): int
    {
        $start = Carbon::parse($leaveRequest->start_date)->startOfDay();
        $end = $leaveRequest->end_date
            ? Carbon::parse($leaveRequest->end_date)->startOfDay()
            : $start->copy();

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        return (int) $start->diffInDays($end) + 1;
    }

    /**
     * Iterate each calendar day in a leave request, capped to avoid memory exhaustion.
     *
     * @return \Generator<int, Carbon>
     */
    private function iterateLeaveRequestDates(LeaveRequest $leaveRequest): \Generator
    {
        $start = Carbon::parse($leaveRequest->start_date)->startOfDay();
        $end = $leaveRequest->end_date
            ? Carbon::parse($leaveRequest->end_date)->startOfDay()
            : $start->copy();

        if ($end->lt($start)) {
            [$start, $end] = [$end->copy(), $start];
        }

        if ($this->leaveRequestInclusiveDayCount($leaveRequest) > self::MAX_LEAVE_DTR_DAYS_PER_REQUEST) {
            Log::warning('Leave request date span exceeds DTR processing limit; capping days processed.', [
                'leave_request_id' => $leaveRequest->id,
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ]);
            $end = $start->copy()->addDays(self::MAX_LEAVE_DTR_DAYS_PER_REQUEST - 1);
        }

        foreach (CarbonPeriod::create($start, $end) as $date) {
            yield $date;
        }
    }

    /**
     * Sum approved leave days for balance display without loading full models.
     *
     * @param  string|list<string>  $types
     */
    private function sumApprovedLeaveDaysForUser(int $userId, string|array $types, int $year): int
    {
        $types = is_array($types) ? $types : [$types];

        return (int) LeaveRequest::query()
            ->where('user_id', $userId)
            ->whereIn('type', $types)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->get(['start_date', 'end_date'])
            ->sum(fn (LeaveRequest $request) => min(
                $this->leaveRequestInclusiveDayCount($request),
                self::MAX_LEAVE_DTR_DAYS_PER_REQUEST
            ));
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

        if (! $filedByAdminLog) {
            return redirect('/admin/leave-requests')
                ->with('error', 'This leave request cannot be deleted. Only requests filed by admins can be deleted.');
        }

        // Check if the current admin is the one who filed it
        if ($filedByAdminLog->performed_by !== Auth::id()) {
            return redirect('/admin/leave-requests')
                ->with('error', 'You can only delete leave requests that you filed.');
        }

        // Delete the leave request (logs will be cascade deleted)
        $leaveRequest->delete();

        return redirect('/admin/leave-requests')
            ->with('success', 'Leave request deleted successfully.');
    }
}
