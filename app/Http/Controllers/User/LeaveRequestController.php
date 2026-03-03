<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\Dtr;
use App\Services\MailConfigService;
use App\Mail\LeaveRequestNotification;
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

        $overtimeFormatted = null;
        $overtimeWindowLabel = null;
        $studentTime = null;
        $approvedAbsentCount = 0;

        if ($user->role === 'employee') {
            // Overtime balance is ONLY based on approved overtime leave requests (DTR overtime is ignored)
            // Overtime Credited Window is ONLY used for expiration logic, NOT for counting
            $months = $user->overtime_months_credited ?? 12;
            $today = Carbon::today();

            $totalOvertimeHours = 0;

            // Get approved overtime leave requests for completed weeks only (count all, window only for expiration)
            $approvedOvertimeRequests = LeaveRequest::where('user_id', $userId)
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

            // Approved absent count (for display only)
            $approvedAbsentCount = LeaveRequest::where('user_id', $userId)
                ->where('type', 'absent')
                ->where('status', 'approved')
                ->count();

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

            // Build label for the overtime window
            if ($months === 12) {
                $overtimeWindowLabel = 'This Year';
            } else {
                $overtimeWindowLabel = "Last {$months} month(s)";
            }
        } elseif ($user->role === 'student') {
            // Student: compute DTR time summary for list page
            $requiredHours = (float) ($user->required_training_hours ?? 0);
            $totalDtrHours = Dtr::where('user_id', $userId)->sum('total_hours');
            $remainingHours = $requiredHours - $totalDtrHours; // can be negative (over-completed)

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

        return view('user.leave-requests.index', compact(
            'leaveRequests',
            'stats',
            'balances',
            'overtimeFormatted',
            'overtimeWindowLabel',
            'studentTime',
            'approvedAbsentCount'
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

        $balances = null;
        if ($user->role === 'employee') {
            $balances = $this->getEmployeeLeaveBalances($user);
        }

        return view('user.leave-requests.create', compact('balances'));
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

        // Define allowed types based on role (only employees can file travel)
        $allowedTypes = $user->role === 'student'
            ? ['additional_time', 'absent', 'other']
            : ['vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime', 'offset'];
        if ($user->role === 'employee') {
            $allowedTypes[] = 'travel';
        }

        $startDateRules = ['required', 'date'];
        $endDateRules = ['nullable', 'date', 'after_or_equal:start_date'];
        $typeInput = $request->input('type');
        // Travel (employee): only today or past dates; full-access admin can file travel for any date via admin panel
        if ($typeInput === 'travel') {
            $startDateRules[] = 'before_or_equal:today';
            $endDateRules[] = 'before_or_equal:today';
        } elseif (!($typeInput === 'sick_leave' || $typeInput === 'overtime' || ($user->role === 'student' && $typeInput === 'additional_time'))) {
            $startDateRules[] = 'after_or_equal:today';
        }

        $validated = $request->validate([
            'type' => ['required', 'in:' . implode(',', $allowedTypes)],
            'start_date' => $startDateRules,
            'end_date' => $endDateRules,
            // Reason is REQUIRED for overtime (used as the clear explanation of extra hours)
            'reason' => 'required_if:type,travel|nullable|string|max:1000',
            'supporting_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'overtime_hours' => 'required_if:type,overtime|nullable|regex:/^\\d{2}:\\d{2}$/',
            'overtime_dates' => 'required_if:type,overtime|nullable|string|max:255',
            'overtime_tasks' => 'required_if:type,overtime|nullable|string|max:2000',
            'wfh_mode' => 'required_if:type,work_from_home|nullable|in:working_remotely,request_to_be_excused',
            'wfh_address' => 'required_if:type,work_from_home|nullable|string|max:255',
            'wfh_tasks' => 'required_if:type,work_from_home|nullable|string|max:2000',
            'offset_hours' => 'nullable|regex:/^\\d{2}:\\d{2}$/',
            'travel_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
        ]);

        // Only employees can file travel; reject if someone bypasses the form
        if ($validated['type'] === 'travel' && $user->role !== 'employee') {
            return redirect()->back()
                ->withErrors(['type' => 'Only employees can file travel leave requests.'])
                ->withInput();
        }

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

            // Always calculate offset hours from days: 1 day = 8 hours
            $totalHours = $days * 8;
            $offsetHours = sprintf('%02d:00', $totalHours);

            $details = "Offset Request Details:\n";
            $details .= "Duration: " . $days . " " . ($days == 1 ? 'day' : 'days') . "\n";
            $details .= "Hours to Deduct: " . $offsetHours . "\n";

            if (!empty($reasonToStore)) {
                $details .= "\nReason:\n" . $reasonToStore;
            }

            $reasonToStore = $details;
        } elseif ($validated['type'] === 'travel') {
            $reasonToStore = 'Location of travel: ' . trim($validated['reason'] ?? '');
        }

        // Balance check: Vacation Leave, Sick Leave, Offset only (employees)
        if (in_array($validated['type'], ['vacation_leave', 'sick_leave', 'offset']) && $user->role === 'employee') {
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = $validated['end_date']
                ? \Carbon\Carbon::parse($validated['end_date'])
                : $startDate;
            $daysRequested = $startDate->diffInDays($endDate) + 1;

            if ($validated['type'] === 'vacation_leave' || $validated['type'] === 'sick_leave') {
                $bal = $this->getEmployeeLeaveBalances($user);
                if ($validated['type'] === 'vacation_leave') {
                    if ($bal['vacation_remaining'] <= 0) {
                        return redirect()->back()
                            ->withErrors(['type' => 'No balance to file for that type of request.'])
                            ->withInput();
                    }
                    if ($daysRequested > $bal['vacation_remaining']) {
                        return redirect()->back()
                            ->withErrors(['end_date' => "You only have {$bal['vacation_remaining']} day(s) of Vacation Leave remaining. You cannot request {$daysRequested} day(s)."])
                            ->withInput();
                    }
                } else {
                    if ($bal['sick_remaining'] <= 0) {
                        return redirect()->back()
                            ->withErrors(['type' => 'No balance to file for that type of request.'])
                            ->withInput();
                    }
                    if ($daysRequested > $bal['sick_remaining']) {
                        return redirect()->back()
                            ->withErrors(['end_date' => "You only have {$bal['sick_remaining']} day(s) of Sick Leave remaining. You cannot request {$daysRequested} day(s)."])
                            ->withInput();
                    }
                }
            } else {
                // Offset: check overtime balance
                $offsetHoursNeeded = $daysRequested * 8;
                $overtimeHours = $this->getEmployeeOvertimeBalanceHours($user);
                if ($overtimeHours <= 0) {
                    return redirect()->back()
                        ->withErrors(['type' => 'No balance to file for that type of request.'])
                        ->withInput();
                }
                if ($offsetHoursNeeded > $overtimeHours) {
                    $h = (int) $overtimeHours;
                    $m = (int) (($overtimeHours - $h) * 60);
                    $hoursFormatted = sprintf('%02d:%02d', $h, $m);
                    return redirect()->back()
                        ->withErrors(['end_date' => "You only have {$hoursFormatted} hours of overtime balance. You cannot request {$daysRequested} day(s) (" . ($daysRequested * 8) . " hours)."])
                        ->withInput();
                }
            }
        }

        // Handle supporting document (only stored if provided)
        $supportingPath = null;
        if ($request->hasFile('supporting_document')) {
            $supportDir = 'leave-supporting-docs';
            $assetDisk = 'digitalocean';
            $doConfigured = !empty(env('DIGITALOCEAN_SPACES_KEY') ?: env('DO_SPACES_KEY'))
                && !empty(env('DIGITALOCEAN_SPACES_SECRET') ?: env('DO_SPACES_SECRET'))
                && !empty(env('DIGITALOCEAN_SPACES_BUCKET') ?: env('DO_SPACES_BUCKET'));
            if ($doConfigured) {
                $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
                $supportDir = $assetRoot ? $assetRoot . '/' . $supportDir : $supportDir;
            }
            try {
                $supportingPath = $request->file('supporting_document')->store(
                    $supportDir,
                    $doConfigured ? $assetDisk : config('filesystems.default', 'local')
                );
            } catch (\Throwable $e) {
                Log::warning('Leave request supporting document store failed, saving request without file', [
                    'error' => $e->getMessage(),
                    'disk' => $doConfigured ? $assetDisk : 'local',
                ]);
                $supportingPath = null;
            }
        }

        $travelHours = $validated['type'] === 'travel' ? (float) ($validated['travel_hours'] ?? 8.0) : null;
        $leaveRequest = LeaveRequest::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? $validated['start_date'],
            'reason' => $reasonToStore,
            'travel_hours' => $travelHours,
            'supporting_document_path' => $supportingPath,
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        // Send email notification to admin(s) if setting is configured (for any requester role)
        // Clear cache first to ensure we get the latest settings
        \Illuminate\Support\Facades\Cache::forget('setting.leave_admin_notification_email');
        $adminEmailsStr = \App\Models\Setting::get('leave_admin_notification_email', '');

        // Also try direct database query as fallback
        if (empty($adminEmailsStr)) {
            $setting = \App\Models\Setting::where('key', 'leave_admin_notification_email')->first();
            $adminEmailsStr = $setting ? $setting->value : '';
        }

        Log::info('Leave request notification - checking admin emails', [
            'admin_emails_str' => $adminEmailsStr,
            'leave_request_id' => $leaveRequest->id
        ]);

        if (!empty($adminEmailsStr) && trim($adminEmailsStr) !== '') {
            $adminEmails = array_filter(array_map('trim', explode(',', $adminEmailsStr)));
            $validEmails = array_filter($adminEmails, function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            });

            Log::info('Leave request notification - parsed emails', [
                'total_emails' => count($adminEmails),
                'valid_emails' => count($validEmails),
                'valid_emails_list' => $validEmails
            ]);

            if (!empty($validEmails)) {
                try {
                    // Configure mail settings before sending
                    MailConfigService::configure();

                    // Log mail configuration for debugging
                    Log::info('Leave request notification - mail configuration', [
                        'mail_driver' => config('mail.default'),
                        'mail_from' => config('mail.from.address'),
                        'valid_emails_count' => count($validEmails)
                    ]);

                    $sentCount = 0;
                    $failedCount = 0;
                    foreach ($validEmails as $email) {
                        try {
                            // Send email synchronously (not queued) to ensure immediate delivery
                            Mail::to($email)->send(new LeaveRequestNotification($leaveRequest));
                            $sentCount++;
                            Log::info('Leave request notification email sent successfully', [
                                'email' => $email,
                                'leave_request_id' => $leaveRequest->id
                            ]);
                        } catch (\Exception $emailException) {
                            $failedCount++;
                            Log::error('Failed to send leave request notification email to individual admin', [
                                'email' => $email,
                                'error' => $emailException->getMessage(),
                                'leave_request_id' => $leaveRequest->id,
                                'trace' => $emailException->getTraceAsString()
                            ]);
                            // Continue sending to other emails even if one fails
                        }
                    }
                    if ($sentCount > 0) {
                        Log::info('Leave request notification emails sent', [
                            'sent' => $sentCount,
                            'failed' => $failedCount,
                            'total' => count($validEmails),
                            'leave_request_id' => $leaveRequest->id
                        ]);
                    } else {
                        Log::warning('Leave request notification - no valid emails found after parsing', [
                            'admin_emails_str' => $adminEmailsStr,
                            'parsed_emails' => $adminEmails,
                            'valid_emails' => $validEmails,
                            'leave_request_id' => $leaveRequest->id
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to configure mail or send leave request notification emails: ' . $e->getMessage(), [
                        'leave_request_id' => $leaveRequest->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Don't fail the request if email fails
                }
            } else {
                Log::info('Leave request notification - no admin emails configured', [
                    'leave_request_id' => $leaveRequest->id
                ]);
            }
        }

        return redirect('/leave-requests')
            ->with('success', 'Leave request submitted successfully. It will be reviewed by an administrator.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        // Allow employees and students to access
        $user = Auth::user();
        if (!in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can view leave requests.');
        }

        // Ensure the user can only view their own requests
        if ($leaveRequest->user_id !== Auth::id()) {
            abort(403, 'You can only view your own leave requests.');
        }

        $leaveRequest->load('reviewer');
        $user->load('department');

        // Optional student time summary based on DTR
        $studentTime = null;
        if ($user->role === 'student') {
            $requiredHours = (float) ($user->required_training_hours ?? 0);

            // Sum all DTR total_hours for this student
            $totalDtrHours = \App\Models\Dtr::where('user_id', $user->id)->sum('total_hours');

            $remainingHours = $requiredHours - $totalDtrHours; // can be negative (over-completed)

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
        $user = Auth::user();
        $immediateSupervisor = 'CHARMAINE JOY ROSATACE'; // Default fallback

        if ($user && $user->department && $user->department->supervisor_name) {
            $immediateSupervisor = $user->department->supervisor_name;
        } else {
            $immediateSupervisor = \App\Models\Setting::get('leave_immediate_supervisor', 'CHARMAINE JOY ROSATACE');
        }

        $signatories = [
            'immediate_supervisor' => $immediateSupervisor,
            'hr_admin' => \App\Models\Setting::get('leave_hr_admin', 'MAY GRACE ACOSTA'),
            'cto' => \App\Models\Setting::get('leave_cto', 'NITISH KHEMANI'),
        ];

        return view('user.leave-requests.show', compact('leaveRequest', 'signatories', 'studentTime'));
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
            return redirect('/leave-requests/' . $leaveRequest->id)
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
            return redirect('/leave-requests/' . $leaveRequest->id)
                ->withErrors(['error' => 'You can only update leave requests that have been requested for resubmission.']);
        }

        // Define allowed types based on role (only employees can file travel)
        $allowedTypes = $user->role === 'student'
            ? ['additional_time', 'absent', 'other']
            : ['vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime', 'offset'];
        if ($user->role === 'employee') {
            $allowedTypes[] = 'travel';
        }

        $startDateRules = ['required', 'date'];
        $endDateRules = ['nullable', 'date', 'after_or_equal:start_date'];
        $typeInput = $request->input('type');
        // Travel (employee): only today or past dates
        if ($typeInput === 'travel') {
            $startDateRules[] = 'before_or_equal:today';
            $endDateRules[] = 'before_or_equal:today';
        } elseif (!($typeInput === 'sick_leave' || $typeInput === 'overtime' || ($user->role === 'student' && $typeInput === 'additional_time'))) {
            $startDateRules[] = 'after_or_equal:today';
        }

        $validated = $request->validate([
            'type' => ['required', 'in:' . implode(',', $allowedTypes)],
            'start_date' => $startDateRules,
            'end_date' => $endDateRules,
            'reason' => 'required_if:type,travel|nullable|string|max:1000',
            'supporting_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'overtime_hours' => 'required_if:type,overtime|nullable|regex:/^\\d{2}:\\d{2}$/',
            'overtime_dates' => 'required_if:type,overtime|nullable|string|max:255',
            'overtime_tasks' => 'required_if:type,overtime|nullable|string|max:2000',
            'wfh_mode' => 'required_if:type,work_from_home|nullable|in:working_remotely,request_to_be_excused',
            'wfh_address' => 'required_if:type,work_from_home|nullable|string|max:255',
            'wfh_tasks' => 'required_if:type,work_from_home|nullable|string|max:2000',
            'offset_hours' => 'nullable|regex:/^\\d{2}:\\d{2}$/',
            'travel_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
        ]);

        if ($validated['type'] === 'travel' && $user->role !== 'employee') {
            return redirect()->back()
                ->withErrors(['type' => 'Only employees can file travel leave requests.'])
                ->withInput();
        }

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

            // Always calculate offset hours from days: 1 day = 8 hours
            $totalHours = $days * 8;
            $offsetHours = sprintf('%02d:00', $totalHours);

            $details = "Offset Request Details:\n";
            $details .= "Duration: " . $days . " " . ($days == 1 ? 'day' : 'days') . "\n";
            $details .= "Hours to Deduct: " . $offsetHours . "\n";

            if (!empty($reasonToStore)) {
                $details .= "\nReason:\n" . $reasonToStore;
            }

            $reasonToStore = $details;
        } elseif ($validated['type'] === 'travel') {
            $reasonToStore = 'Location of travel: ' . trim($validated['reason'] ?? '');
        }

        // Handle supporting document (replace if new one provided)
        $supportingPath = $leaveRequest->supporting_document_path;
        if ($request->hasFile('supporting_document')) {
            $assetDisk = 'digitalocean';
            $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
            $supportDir = $assetRoot ? $assetRoot . '/leave-supporting-docs' : 'leave-supporting-docs';

            if ($supportingPath) {
                try {
                    \Illuminate\Support\Facades\Storage::disk($assetDisk)->delete($supportingPath);
                } catch (\Throwable $e) {
                    // ignore delete errors
                }
            }

            $supportingPath = $request->file('supporting_document')->store($supportDir, $assetDisk);
        }

        $travelHours = $validated['type'] === 'travel' ? (float) ($validated['travel_hours'] ?? 8.0) : null;
        // Clear review information when resubmitting
        $leaveRequest->update([
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? $validated['start_date'],
            'reason' => $reasonToStore,
            'travel_hours' => $travelHours,
            'supporting_document_path' => $supportingPath,
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'admin_notes' => null,
        ]);

        return redirect('/leave-requests/' . $leaveRequest->id)
            ->with('success', 'Leave request updated successfully. It will be reviewed again by an administrator.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        // Allow employees and students to access
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

        return redirect('/leave-requests')
            ->with('success', 'Leave request deleted successfully.');
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

            if ($dtr->exists) {
                $existingTotal = (float) ($dtr->total_hours ?? 0);
                $newTotal = $existingTotal + $hoursPerDay;
                $dtr->total_hours = $newTotal;
                $dtr->overtime_hours = max($newTotal - 8.0, 0);
                $existingRemarks = $dtr->remarks ?? '';
                $travelRemark = "Travel Leave ({$hoursPerDay}h)";
                if (!empty($existingRemarks) && strpos($existingRemarks, $travelRemark) === false) {
                    $dtr->remarks = $existingRemarks . '; ' . $travelRemark;
                } elseif (empty($existingRemarks)) {
                    $dtr->remarks = $travelRemark;
                }
                if ($dtr->status !== 'travel') {
                    $dtr->status = 'travel';
                }
            } else {
                $dtr->total_hours = $hoursPerDay;
                $dtr->overtime_hours = max($hoursPerDay - 8.0, 0);
                $dtr->status = 'travel';
                $dtr->remarks = "Travel Leave ({$hoursPerDay}h)";
            }

            $dtr->save();
        }
    }

    /**
     * Get employee overtime balance in hours (from approved overtime leave requests only).
     */
    private function getEmployeeOvertimeBalanceHours($user): float
    {
        $today = Carbon::today();
        $approvedOvertimeRequests = LeaveRequest::where('user_id', $user->id)
            ->where('type', 'overtime')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->get();

        $overtimeFromLeavesMinutes = 0;
        foreach ($approvedOvertimeRequests as $otRequest) {
            $raw = $otRequest->reason ?? '';
            if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                $overtimeFromLeavesMinutes += $h * 60 + $mPart;
            }
        }

        return $overtimeFromLeavesMinutes / 60;
    }

    /**
     * Get employee leave balances for vacation, sick, and overtime (create form and balance validation).
     */
    private function getEmployeeLeaveBalances($user): array
    {
        $currentYear = now()->year;
        $defaultVacation = (float) \App\Models\Setting::get('default_vacation_balance', 15);
        $defaultSick = (float) \App\Models\Setting::get('default_sick_leave_balance', 10);

        $leaveBalance = LeaveBalance::firstOrCreate(
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

        return [
            'vacation_remaining' => max((float) $leaveBalance->vacation_allowance - $usedVacation, 0),
            'sick_remaining' => max((float) $leaveBalance->sick_allowance - $usedSick, 0),
            'overtime_hours' => $this->getEmployeeOvertimeBalanceHours($user),
        ];
    }
}
