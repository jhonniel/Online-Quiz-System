<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\Dtr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Allow employees and students to access
        $user = Auth::user();
        if (!in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can access leave requests.');
        }

        $user = Auth::user();
        $userId = $user->id;
        $currentYear = now()->year;

        $leaveRequests = LeaveRequest::where('user_id', $userId)
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Basic stats
        $stats = [
            'total' => LeaveRequest::where('user_id', $userId)->count(),
            'pending' => LeaveRequest::where('user_id', $userId)->where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('user_id', $userId)->where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('user_id', $userId)->where('status', 'rejected')->count(),
        ];

        // Ensure a leave balance record exists for this user & year (auto-recurring each year)
        $defaultVacation = (float) \App\Models\Setting::get('default_vacation_balance', 15);
        $defaultSick = (float) \App\Models\Setting::get('default_sick_leave_balance', 10);
        
        $leaveBalance = LeaveBalance::firstOrCreate(
            ['user_id' => $userId, 'year' => $currentYear],
            [
                'vacation_allowance' => $defaultVacation,
                'sick_allowance' => $defaultSick,
            ]
        );

        // Used leave days for the current year (approved only)
        $usedVacation = LeaveRequest::where('user_id', $userId)
            ->where('type', 'vacation_leave')
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->get()
            ->sum->days;

        $usedSick = LeaveRequest::where('user_id', $userId)
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

        // Determine overtime credited window based on user setting
        $months = $user->overtime_months_credited ?? 12;
        if ($months === 12) {
            $fromDate = now()->copy()->startOfYear();
        } else {
            $fromDate = now()->copy()->subMonths($months)->startOfDay();
        }

        // Overtime summary from DTR + approved overtime leave requests for the configured window,
        // minus any approved Offset requests (each Offset consumes 8 hours)
        $dtrOvertimeQuery = Dtr::where('user_id', $userId);
        if ($months === 12) {
            $dtrOvertimeQuery->whereYear('date', $currentYear);
        } else {
            $dtrOvertimeQuery->whereDate('date', '>=', $fromDate->toDateString());
        }
        $totalOvertimeHours = $dtrOvertimeQuery->sum('overtime_hours');

        // Add overtime coming from approved overtime leave requests (HH:MM in reason)
        $approvedOvertimeRequestsQuery = LeaveRequest::where('user_id', $userId)
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
        $deficitQuery = \App\Models\DtrDeficit::where('user_id', $userId)
            ->where('is_applied', true);
        
        if ($months === 12) {
            $deficitQuery->whereYear('week_start_date', $currentYear);
        } else {
            $deficitQuery->whereDate('week_start_date', '>=', $fromDate->toDateString());
        }
        
        $totalDeficitHours = $deficitQuery->sum('deficit_hours');
        $totalOvertimeHours = $totalOvertimeHours - $totalDeficitHours;

        $approvedOffsetQuery = LeaveRequest::where('user_id', $userId)
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

        // Build label for the overtime window
        if ($months === 12) {
            $overtimeWindowLabel = 'This Year';
        } else {
            $overtimeWindowLabel = "Last {$months} month(s)";
        }

        return view('user.leave-requests.index', compact(
            'leaveRequests',
            'stats',
            'balances',
            'overtimeFormatted',
            'overtimeWindowLabel'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only allow employees to access
        $user = Auth::user();
        if (!in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can create leave requests.');
        }

        return view('user.leave-requests.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Allow employees and students to access
        if (!in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can create leave requests.');
        }

        // Define allowed types based on role
        $allowedTypes = $user->role === 'student' 
            ? ['additional_time', 'absent', 'other']
            : ['vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime', 'offset'];

        $validated = $request->validate([
            'type' => ['required', 'in:' . implode(',', $allowedTypes)],
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            // Reason is REQUIRED for overtime (used as the clear explanation of extra hours)
            'reason' => 'nullable|string|max:1000',
            'overtime_hours' => 'required_if:type,overtime|nullable|regex:/^\\d{2}:\\d{2}$/',
            'overtime_dates' => 'required_if:type,overtime|nullable|string|max:255',
            'overtime_tasks' => 'required_if:type,overtime|nullable|string|max:2000',
            'wfh_mode' => 'required_if:type,work_from_home|nullable|in:working_remotely,request_to_be_excused',
            'wfh_address' => 'required_if:type,work_from_home|nullable|string|max:255',
            'wfh_tasks' => 'required_if:type,work_from_home|nullable|string|max:2000',
            'offset_hours' => 'nullable|regex:/^\\d{2}:\\d{2}$/',
        ]);

        // Build reason – include structured details when type is overtime or WFH
        $reasonToStore = $validated['reason'] ?? '';

        if ($validated['type'] === 'overtime') {
            $details = "Overtime Request Details:\n";
            $details .= "Total Overtime Hours: " . ($validated['overtime_hours'] ?? '') . "\n";
            $details .= "Overtime Dates: " . ($validated['overtime_dates'] ?? '') . "\n";
            $details .= "Tasks / ClickUp Links:\n" . ($validated['overtime_tasks'] ?? '') . "\n";

            if (!empty($reasonToStore)) {
                $details .= "\nAdditional Explanation:\n" . $reasonToStore;
            }

            $reasonToStore = $details;
        } elseif ($validated['type'] === 'work_from_home') {
            $modeLabel = $validated['wfh_mode'] === 'request_to_be_excused'
                ? 'Request to be excused'
                : 'Working remotely';

            $details = "Work From Home Request Details:\n";
            $details .= "Mode: " . $modeLabel . "\n";
            $details .= "Remote Address: " . ($validated['wfh_address'] ?? '') . "\n";
            $details .= "Work Dates: " . ($validated['start_date'] ?? '') . ' to ' . ($validated['end_date'] ?? $validated['start_date']) . "\n";
            $details .= "Tasks / ClickUp Links:\n" . ($validated['wfh_tasks'] ?? '') . "\n";

            if (!empty($reasonToStore)) {
                $details .= "\nAdditional Explanation:\n" . $reasonToStore;
            }

            $reasonToStore = $details;
        } elseif ($validated['type'] === 'offset') {
            // Calculate duration in days
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = $validated['end_date'] 
                ? \Carbon\Carbon::parse($validated['end_date'])
                : $startDate;
            $days = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end dates

            // If offset_hours is provided, use it; otherwise calculate as days * 8 hours
            if (!empty($validated['offset_hours'])) {
                $offsetHours = $validated['offset_hours'];
            } else {
                // Calculate: 1 day = 08:00
                $totalHours = $days * 8;
                $offsetHours = sprintf('%02d:00', $totalHours);
            }

            $details = "Offset Request Details:\n";
            $details .= "Duration: " . $days . " " . ($days == 1 ? 'day' : 'days') . "\n";
            $details .= "Hours to Deduct: " . $offsetHours . "\n";

            if (!empty($reasonToStore)) {
                $details .= "\nReason:\n" . $reasonToStore;
            }

            $reasonToStore = $details;
        }

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? $validated['start_date'],
            'reason' => $reasonToStore,
            'status' => 'pending',
        ]);

        return redirect()->route('user.leave-requests.index')
            ->with('success', 'Leave request submitted successfully. It will be reviewed by an administrator.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        // Only allow employees to access
        if (Auth::user()->role !== 'employee') {
            abort(403, 'Only employees can view leave requests.');
        }

        // Ensure the user can only view their own requests
        if ($leaveRequest->user_id !== Auth::id()) {
            abort(403, 'You can only view your own leave requests.');
        }

        $leaveRequest->load('reviewer');

        // Get signatory names from settings
        $signatories = [
            'immediate_supervisor' => \App\Models\Setting::get('leave_immediate_supervisor', 'CHARMAINE JOY ROSATACE'),
            'hr_admin' => \App\Models\Setting::get('leave_hr_admin', 'MAY GRACE ACOSTA'),
            'cto' => \App\Models\Setting::get('leave_cto', 'NITISH KHEMANI'),
        ];

        return view('user.leave-requests.show', compact('leaveRequest', 'signatories'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        // Only allow employees to access
        $user = Auth::user();
        if (!in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can edit leave requests.');
        }

        // Ensure the user can only edit their own requests
        if ($leaveRequest->user_id !== Auth::id()) {
            abort(403, 'You can only edit your own leave requests.');
        }

        // Only allow editing of pending requests that were previously reviewed (resubmission)
        if (!$leaveRequest->isPending() || !$leaveRequest->reviewed_at) {
            return redirect()->route('user.leave-requests.show', $leaveRequest)
                ->withErrors(['error' => 'You can only edit leave requests that have been requested for resubmission.']);
        }

        // Parse existing data for pre-filling
        $editData = [
            'type' => $leaveRequest->type,
            'start_date' => $leaveRequest->start_date->format('Y-m-d'),
            'end_date' => $leaveRequest->end_date ? $leaveRequest->end_date->format('Y-m-d') : null,
            'reason' => '',
            'overtime_hours' => '',
            'overtime_dates' => '',
            'overtime_tasks' => '',
            'wfh_mode' => '',
            'wfh_address' => '',
            'wfh_tasks' => '',
            'offset_hours' => '',
        ];

        $raw = $leaveRequest->reason ?? '';
        
        if ($leaveRequest->type === 'overtime') {
            if (preg_match('/Total Overtime Hours:\s*(.+)/', $raw, $m)) {
                $editData['overtime_hours'] = trim($m[1]);
            }
            if (preg_match('/Overtime Dates:\s*(.+)/', $raw, $m)) {
                $editData['overtime_dates'] = trim($m[1]);
            }
            if (preg_match('/Tasks \/ ClickUp Links:\s*(.+?)(?:\n+Additional Explanation:|\z)/s', $raw, $m)) {
                $editData['overtime_tasks'] = trim($m[1]);
            }
            if (preg_match('/Additional Explanation:\s*(.+)\z/s', $raw, $m)) {
                $editData['reason'] = trim($m[1]);
            }
        } elseif ($leaveRequest->type === 'work_from_home') {
            if (preg_match('/Mode:\s*(.+)/', $raw, $m)) {
                $mode = trim($m[1]);
                $editData['wfh_mode'] = $mode === 'Request to be excused' ? 'request_to_be_excused' : 'working_remotely';
            }
            if (preg_match('/Remote Address:\s*(.+)/', $raw, $m)) {
                $editData['wfh_address'] = trim($m[1]);
            }
            if (preg_match('/Tasks \/ ClickUp Links:\s*(.+?)(?:\n+Additional Explanation:|\z)/s', $raw, $m)) {
                $editData['wfh_tasks'] = trim($m[1]);
            }
            if (preg_match('/Additional Explanation:\s*(.+)\z/s', $raw, $m)) {
                $editData['reason'] = trim($m[1]);
            }
        } elseif ($leaveRequest->type === 'offset') {
            if (preg_match('/Hours to Deduct:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                $editData['offset_hours'] = trim($m[1]);
            }
            if (preg_match('/Reason:\s*(.+)\z/s', $raw, $m)) {
                $editData['reason'] = trim($m[1]);
            }
        } else {
            // For other types, use the reason as-is
            $editData['reason'] = $raw;
        }

        return view('user.leave-requests.edit', compact('leaveRequest', 'editData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        // Only allow employees to access
        $user = Auth::user();
        if (!in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can update leave requests.');
        }

        // Ensure the user can only update their own requests
        if ($leaveRequest->user_id !== Auth::id()) {
            abort(403, 'You can only update your own leave requests.');
        }

        // Only allow updating of pending requests that were previously reviewed (resubmission)
        if (!$leaveRequest->isPending() || !$leaveRequest->reviewed_at) {
            return redirect()->route('user.leave-requests.show', $leaveRequest)
                ->withErrors(['error' => 'You can only update leave requests that have been requested for resubmission.']);
        }

        // Define allowed types based on role
        $allowedTypes = $user->role === 'student' 
            ? ['additional_time', 'absent', 'other']
            : ['vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime', 'offset'];

        $validated = $request->validate([
            'type' => ['required', 'in:' . implode(',', $allowedTypes)],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
            'overtime_hours' => 'required_if:type,overtime|nullable|regex:/^\\d{2}:\\d{2}$/',
            'overtime_dates' => 'required_if:type,overtime|nullable|string|max:255',
            'overtime_tasks' => 'required_if:type,overtime|nullable|string|max:2000',
            'wfh_mode' => 'required_if:type,work_from_home|nullable|in:working_remotely,request_to_be_excused',
            'wfh_address' => 'required_if:type,work_from_home|nullable|string|max:255',
            'wfh_tasks' => 'required_if:type,work_from_home|nullable|string|max:2000',
            'offset_hours' => 'nullable|regex:/^\\d{2}:\\d{2}$/',
        ]);

        // Build reason – include structured details when type is overtime or WFH
        $reasonToStore = $validated['reason'] ?? '';

        if ($validated['type'] === 'overtime') {
            $details = "Overtime Request Details:\n";
            $details .= "Total Overtime Hours: " . ($validated['overtime_hours'] ?? '') . "\n";
            $details .= "Overtime Dates: " . ($validated['overtime_dates'] ?? '') . "\n";
            $details .= "Tasks / ClickUp Links:\n" . ($validated['overtime_tasks'] ?? '') . "\n";

            if (!empty($reasonToStore)) {
                $details .= "\nAdditional Explanation:\n" . $reasonToStore;
            }

            $reasonToStore = $details;
        } elseif ($validated['type'] === 'work_from_home') {
            $modeLabel = $validated['wfh_mode'] === 'request_to_be_excused'
                ? 'Request to be excused'
                : 'Working remotely';

            $details = "Work From Home Request Details:\n";
            $details .= "Mode: " . $modeLabel . "\n";
            $details .= "Remote Address: " . ($validated['wfh_address'] ?? '') . "\n";
            $details .= "Work Dates: " . ($validated['start_date'] ?? '') . ' to ' . ($validated['end_date'] ?? $validated['start_date']) . "\n";
            $details .= "Tasks / ClickUp Links:\n" . ($validated['wfh_tasks'] ?? '') . "\n";

            if (!empty($reasonToStore)) {
                $details .= "\nAdditional Explanation:\n" . $reasonToStore;
            }

            $reasonToStore = $details;
        } elseif ($validated['type'] === 'offset') {
            // Calculate duration in days
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = $validated['end_date'] 
                ? \Carbon\Carbon::parse($validated['end_date'])
                : $startDate;
            $days = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end dates

            // If offset_hours is provided, use it; otherwise calculate as days * 8 hours
            if (!empty($validated['offset_hours'])) {
                $offsetHours = $validated['offset_hours'];
            } else {
                // Calculate: 1 day = 08:00
                $totalHours = $days * 8;
                $offsetHours = sprintf('%02d:00', $totalHours);
            }

            $details = "Offset Request Details:\n";
            $details .= "Duration: " . $days . " " . ($days == 1 ? 'day' : 'days') . "\n";
            $details .= "Hours to Deduct: " . $offsetHours . "\n";

            if (!empty($reasonToStore)) {
                $details .= "\nReason:\n" . $reasonToStore;
            }

            $reasonToStore = $details;
        }

        // Clear review information when resubmitting
        $leaveRequest->update([
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? $validated['start_date'],
            'reason' => $reasonToStore,
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'admin_notes' => null, // Clear admin notes on resubmission
        ]);

        return redirect()->route('user.leave-requests.show', $leaveRequest)
            ->with('success', 'Leave request updated successfully. It will be reviewed again by an administrator.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        // Only allow employees to access
        $user = Auth::user();
        if (!in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can delete leave requests.');
        }

        // Ensure the user can only delete their own requests
        if ($leaveRequest->user_id !== Auth::id()) {
            abort(403, 'You can only delete your own leave requests.');
        }

        // Only allow deletion of pending requests
        if (!$leaveRequest->isPending()) {
            return redirect()->back()
                ->withErrors(['error' => 'You can only delete pending leave requests.']);
        }

        $leaveRequest->delete();

        return redirect()->route('user.leave-requests.index')
            ->with('success', 'Leave request deleted successfully.');
    }
}
