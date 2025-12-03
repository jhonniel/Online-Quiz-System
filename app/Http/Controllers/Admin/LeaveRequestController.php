<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Mail\LeaveRequestStatusUpdate;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of all leave requests.
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['user', 'reviewer']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by employee
        if ($request->has('employee') && $request->employee) {
            $query->where('user_id', $request->employee);
        }

        // Search by employee name or email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics
        $baseQuery = LeaveRequest::query();
        if ($request->has('employee') && $request->employee) {
            $baseQuery->where('user_id', $request->employee);
        }
        if ($request->has('type') && $request->type) {
            $baseQuery->where('type', $request->type);
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        $employees = \App\Models\User::where('role', 'employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.leave-requests.index', compact('leaveRequests', 'stats', 'employees'));
    }

    /**
     * Display the specified leave request.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['user', 'reviewer']);

        // Compute current-year leave balances and overtime for this employee
        $employee = $leaveRequest->user;
        $currentYear = now()->year;
        $months = $employee->overtime_months_credited ?? 12;

        if ($months === 12) {
            $fromDate = now()->copy()->startOfYear();
        } else {
            $fromDate = now()->copy()->subMonths($months)->startOfDay();
        }

        $defaultVacation = (float) \App\Models\Setting::get('default_vacation_balance', 15);
        $defaultSick = (float) \App\Models\Setting::get('default_sick_leave_balance', 10);
        
        $leaveBalance = \App\Models\LeaveBalance::firstOrCreate(
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

        $dtrOvertimeQuery = \App\Models\Dtr::where('user_id', $employee->id);
        if ($months === 12) {
            $dtrOvertimeQuery->whereYear('date', $currentYear);
        } else {
            $dtrOvertimeQuery->whereDate('date', '>=', $fromDate->toDateString());
        }
        $totalOvertimeHours = $dtrOvertimeQuery->sum('overtime_hours');

        // Add overtime coming from approved overtime leave requests (HH:MM in reason)
        $approvedOvertimeRequestsQuery = LeaveRequest::where('user_id', $employee->id)
            ->where('type', 'overtime')
            ->where('status', 'approved');

        if ($months === 12) {
            $approvedOvertimeRequestsQuery->whereYear('start_date', $currentYear);
        } else {
            $approvedOvertimeRequestsQuery->whereDate('start_date', '>=', $fromDate->toDateString());
        }

        $approvedOvertimeRequests = $approvedOvertimeRequestsQuery->get();

        $overtimeFromLeavesMinutes = 0;
        foreach ($approvedOvertimeRequests as $otRequest) {
            $raw = $otRequest->reason ?? '';
            if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                $overtimeFromLeavesMinutes += $h * 60 + $mPart;
            }
        }

        $totalOvertimeHours += $overtimeFromLeavesMinutes / 60;

        // Subtract deficit hours from overtime balance (allow negative values)
        $deficitQuery = \App\Models\DtrDeficit::where('user_id', $employee->id)
            ->where('is_applied', true);
        
        if ($months === 12) {
            $deficitQuery->whereYear('week_start_date', $currentYear);
        } else {
            $deficitQuery->whereDate('week_start_date', '>=', $fromDate->toDateString());
        }
        
        $totalDeficitHours = $deficitQuery->sum('deficit_hours');
        $totalOvertimeHours = $totalOvertimeHours - $totalDeficitHours;

        $approvedOffsetQuery = LeaveRequest::where('user_id', $employee->id)
            ->where('type', 'offset')
            ->where('status', 'approved');

        if ($months === 12) {
            $approvedOffsetQuery->whereYear('start_date', $currentYear);
        } else {
            $approvedOffsetQuery->whereDate('start_date', '>=', $fromDate->toDateString());
        }

        $approvedOffsetRequests = $approvedOffsetQuery->get();

        // Parse offset hours from reason field (each offset may have different hours)
        $offsetHoursUsed = 0;
        foreach ($approvedOffsetRequests as $offsetRequest) {
            $raw = $offsetRequest->reason ?? '';
            if (preg_match('/Hours to Deduct:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                $offsetHoursUsed += $h + ($mPart / 60);
            } else {
                // Fallback: if format not found, use 8 hours (for old records)
                $offsetHoursUsed += 8;
            }
        }

        $netOvertimeHours = $totalOvertimeHours - $offsetHoursUsed;

        // Format overtime (handle negative values)
        $isNegative = $netOvertimeHours < 0;
        $absOvertimeMinutes = (int) round(abs($netOvertimeHours) * 60);
        $overtimeHoursPart = intdiv($absOvertimeMinutes, 60);
        $overtimeMinutesPart = $absOvertimeMinutes % 60;
        $overtimeFormatted = ($isNegative ? '-' : '') . sprintf('%02d:%02d', $overtimeHoursPart, $overtimeMinutesPart);

        // Get signatory names from settings
        $signatories = [
            'immediate_supervisor' => \App\Models\Setting::get('leave_immediate_supervisor', 'CHARMAINE JOY ROSATACE'),
            'hr_admin' => \App\Models\Setting::get('leave_hr_admin', 'MAY GRACE ACOSTA'),
            'cto' => \App\Models\Setting::get('leave_cto', 'NITISH KHEMANI'),
        ];

        return view('admin.leave-requests.show', compact('leaveRequest', 'balances', 'overtimeFormatted', 'signatories'));
    }

    /**
     * Calendar view of leave requests for easier tracking.
     */
    public function calendar(Request $request)
    {
        // Use Manila timezone for current date/month context
        $nowManila = Carbon::now('Asia/Manila');
        $monthParam = $request->input('month', $nowManila->format('Y-m'));
        $employeeId = $request->input('employee');

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
        // Wrap OR conditions in a single group so employee filter applies to all.
        $leaveQuery = LeaveRequest::with('user')
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

        // Employees list for sidebar filter (employees only)
        $employees = \App\Models\User::where('role', 'employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.leave-requests.calendar', [
            'currentMonth' => $currentMonth,
            'weeks' => $weeks,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'employees' => $employees,
            'selectedEmployeeId' => $employeeId,
        ]);
    }

    /**
     * Approve a leave request.
     */
    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $leaveRequest->update([
            'status' => 'approved',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Send email notification to employee
        try {
            MailConfigService::configure();
            Mail::to($leaveRequest->user->email)->send(
                new LeaveRequestStatusUpdate($leaveRequest, 'approved', $request->admin_notes)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send leave request approval email: ' . $e->getMessage());
            // Don't fail the request if email fails
        }

        return redirect()->route('admin.leave-requests.show', $leaveRequest)
            ->with('success', 'Leave request approved successfully.');
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Send email notification to employee
        try {
            MailConfigService::configure();
            Mail::to($leaveRequest->user->email)->send(
                new LeaveRequestStatusUpdate($leaveRequest, 'rejected', $request->admin_notes)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send leave request rejection email: ' . $e->getMessage());
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

        $adminNotes = $request->admin_notes 
            ? ($leaveRequest->admin_notes ? $leaveRequest->admin_notes . "\n\n[Resubmission Request]: " . $request->admin_notes : $request->admin_notes)
            : $leaveRequest->admin_notes;

        $leaveRequest->update([
            'status' => 'pending',
            'admin_notes' => $adminNotes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Send email notification to employee
        try {
            MailConfigService::configure();
            Mail::to($leaveRequest->user->email)->send(
                new LeaveRequestStatusUpdate($leaveRequest, 'pending', $request->admin_notes)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send leave request resubmission email: ' . $e->getMessage());
            // Don't fail the request if email fails
        }

        return redirect()->route('admin.leave-requests.show', $leaveRequest)
            ->with('success', 'Leave request marked for resubmission. The employee will need to correct any errors.');
    }
}
