<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\LeaveRequestNotification;
use App\Models\Dtr;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestLog;
use App\Models\User;
use App\Rules\ClickUpTasksUrlsOnly;
use App\Services\MailConfigService;
use App\Support\LeaveRequestSignatorySettings;
use App\Support\TimeRequestOvertimeLeaveImport;
use App\Support\WorkFromHomeQuota;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Allow employees and students to access
        $user = Auth::user();
        if (! in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can access leave requests.');
        }

        // Safety reconciliation:
        // If an Additional Time request is already in "Resubmission Requested"
        // state (pending + reviewed_at), ensure previously credited DTR time
        // is rolled back. This is idempotent because we key off DTR remarks.
        if ($user->role === 'student') {
            $this->reconcileStudentAdditionalTimeResubmissions((int) $user->id);
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

        $leaveBalance = LeaveBalance::firstOrCreateWithCarryover(
            (int) $userId,
            (int) $currentYear,
            (float) $defaultVacation,
            (float) $defaultSick
        );

        // Unified leave credits (Vacation + Sick + Leave)
        $usedLeaveCredits = LeaveRequest::where('user_id', $userId)
            ->whereIn('type', ['leave', 'vacation_leave', 'sick_leave'])
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->get()
            ->sum->days;

        $totalAllowance = (float) $leaveBalance->vacation_allowance + (float) $leaveBalance->sick_allowance;
        $wfhBalance = WorkFromHomeQuota::balanceForMonth((int) $userId);
        $balances = [
            'leave' => [
                'allowance' => $totalAllowance,
                'used' => $usedLeaveCredits,
                'remaining' => max($totalAllowance - $usedLeaveCredits, 0),
            ],
            'work_from_home' => $wfhBalance,
        ];

        $overtimeFormatted = null;
        $overtimeWindowLabel = null;
        $studentTime = null;
        $approvedAbsentCount = 0;

        if ($user->isStaffMember()) {
            // Overtime balance is based on approved overtime leave requests (DTR overtime is ignored)
            // minus approved offset leave requests ("Hours to Deduct" in the reason field).
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

            // Subtract approved offset requests
            $approvedOffsetRequests = LeaveRequest::where('user_id', $userId)
                ->where('type', 'offset')
                ->where('status', 'approved')
                ->get();

            $offsetMinutes = 0;
            foreach ($approvedOffsetRequests as $offsetRequest) {
                $raw = $offsetRequest->reason ?? '';
                if (preg_match('/Hours to Deduct:\s*([0-9]{2}):([0-9]{2})/', $raw, $m)) {
                    $offsetMinutes += (int) $m[1] * 60 + (int) $m[2];
                } else {
                    $offsetMinutes += (int) round(($offsetRequest->days * 8) * 60);
                }
            }

            $totalOvertimeHours = ($overtimeFromLeavesMinutes - $offsetMinutes) / 60;

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

            // Format overtime balance (can be negative)
            $sign = $totalOvertimeHours < 0 ? '-' : '';
            $absOvertimeMinutes = (int) round(abs($totalOvertimeHours) * 60);
            $overtimeHoursPart = intdiv($absOvertimeMinutes, 60);
            $overtimeMinutesPart = $absOvertimeMinutes % 60;
            $overtimeFormatted = $sign.sprintf('%02d:%02d', $overtimeHoursPart, $overtimeMinutesPart);

            // Build label for the overtime window
            if ($months === 12) {
                $overtimeWindowLabel = 'This Year';
            } else {
                $overtimeWindowLabel = "Last {$months} month(s)";
            }
        } elseif ($user->role === 'student') {
            // Student: compute DTR time summary for list page
            $requiredHours = (float) ($user->required_training_hours ?? 0);
            $totalDtrHoursRaw = (float) Dtr::where('user_id', $userId)->sum('total_hours');
            $rollbackHours = $this->getPendingResubmissionRollbackHours($userId);
            $totalDtrHours = max($totalDtrHoursRaw - $rollbackHours, 0);
            $remainingHours = $requiredHours - $totalDtrHours; // can be negative (over-completed)

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
        if (! in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can create leave requests.');
        }

        $balances = null;
        if ($user->isStaffMember()) {
            $balances = $this->getEmployeeLeaveBalances($user);
        }

        $showNoBalanceModalOnLoad = (bool) session('show_no_balance_modal', false);

        return view('user.leave-requests.create', compact('balances', 'showNoBalanceModalOnLoad'));
    }

    /**
     * Current-month WFH balance (approved requests auto-deduct from allowance).
     */
    public function wfhBalance(Request $request)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'employee') {
            abort(403, 'Only employees have a Work From Home balance.');
        }

        $reference = $request->filled('start_date')
            ? \Carbon\Carbon::parse($request->input('start_date'), 'Asia/Manila')
            : \Carbon\Carbon::now('Asia/Manila');

        $excludeId = $request->filled('exclude')
            ? (int) $request->input('exclude')
            : null;

        return response()->json(
            WorkFromHomeQuota::balanceForMonth((int) $user->id, $reference, $excludeId)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Allow employees and students to access
        if (! in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can create leave requests.');
        }

        // Define allowed types based on role (only employees can file travel)
        $allowedTypes = $user->role === 'student'
            ? ['additional_time', 'absent', 'overtime', 'other']
            : ['vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime', 'offset'];
        if ($user->isStaffMember()) {
            $allowedTypes[] = 'travel';
        }

        $startDateRules = ['required', 'date'];
        $endDateRules = ['nullable', 'date', 'after_or_equal:start_date'];
        $typeInput = $request->input('type');
        // Travel (employee): only today or past dates; full-access admin can file travel for any date via admin panel
        if ($typeInput === 'travel') {
            $startDateRules[] = 'before_or_equal:today';
            $endDateRules[] = 'before_or_equal:today';
        } elseif ($this->usesStructuredHoursRequestFormat((string) $typeInput, $user)) {
            $startDateRules[] = 'after_or_equal:'.now()->subDays(7)->toDateString();
            $startDateRules[] = 'before_or_equal:today';
            $endDateRules = ['required', 'date', 'after_or_equal:start_date', 'before_or_equal:today'];
        } elseif (! ($typeInput === 'overtime' || $typeInput === 'sick_leave' || ($user->role === 'student' && $typeInput === 'additional_time'))) {
            $startDateRules[] = 'after_or_equal:today';
        }

        $overtimeSpecificDateItemRules = $this->usesStructuredHoursRequestFormat((string) $typeInput, $user)
            ? $this->overtimeSpecificDateItemRules()
            : ['date'];

        $hoursLabel = $this->structuredHoursRequestLabel((string) $typeInput, $user);

        $validated = $request->validate([
            'type' => ['required', 'in:'.implode(',', $allowedTypes)],
            'start_date' => $startDateRules,
            'end_date' => $endDateRules,
            'reason' => 'required_if:type,travel|nullable|string|max:1000',
            'supporting_documents' => ['nullable', 'array', 'max:5'],
            'supporting_documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'overtime_hours' => [
                Rule::requiredIf(fn () => $this->usesStructuredHoursRequestFormat((string) $request->input('type'), $user)),
                'nullable',
                'regex:/^\\d{2}:\\d{2}$/',
            ],
            'overtime_specific_dates' => [
                Rule::requiredIf(fn () => $this->usesStructuredHoursRequestFormat((string) $request->input('type'), $user)),
                'array',
                'min:1',
            ],
            'overtime_specific_dates.*' => $overtimeSpecificDateItemRules,
            'overtime_tasks' => [
                Rule::requiredIf(fn () => $this->usesStructuredHoursRequestFormat((string) $request->input('type'), $user)),
                'nullable',
                'string',
                'max:2000',
                new ClickUpTasksUrlsOnly,
            ],
            'wfh_mode' => 'required_if:type,work_from_home|nullable|in:working_remotely,request_to_be_excused',
            'wfh_address' => 'required_if:type,work_from_home|nullable|string|max:255',
            'wfh_tasks' => ['required_if:type,work_from_home', 'nullable', 'string', 'max:2000', new ClickUpTasksUrlsOnly],
            'offset_hours' => 'nullable|regex:/^\\d{2}:\\d{2}$/',
            'travel_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
        ], $this->structuredHoursValidationMessages($hoursLabel));

        // Only employees can file travel; reject if someone bypasses the form
        if ($validated['type'] === 'travel' && $user->role !== 'employee') {
            return redirect()->back()
                ->withErrors(['type' => 'Only employees can file travel leave requests.'])
                ->withInput();
        }

        if ($this->usesStructuredHoursRequestFormat($validated['type'], $user)) {
            $overtimeInputValidationError = $this->validateOvertimeInputData($validated);
            if ($overtimeInputValidationError !== null) {
                return redirect()->back()->withErrors($overtimeInputValidationError)->withInput();
            }

            $overtimeDateValidationError = $this->validateOvertimeDateSelection(
                (string) ($validated['start_date'] ?? ''),
                (string) ($validated['end_date'] ?? ''),
                $validated['overtime_specific_dates'] ?? []
            );
            if ($overtimeDateValidationError !== null) {
                return redirect()->back()->withErrors($overtimeDateValidationError)->withInput();
            }
        }

        $supportingDocumentsError = $this->supportingDocumentsRequiredForOvertimeError($request);
        if ($supportingDocumentsError !== null) {
            return redirect()->back()->withErrors($supportingDocumentsError)->withInput();
        }

        $requestTypeInputValidationError = $this->validateRequestTypeInputData($validated);
        if ($requestTypeInputValidationError !== null) {
            return redirect()->back()->withErrors($requestTypeInputValidationError)->withInput();
        }

        if ($user->isStaffMember() && $validated['type'] === 'work_from_home') {
            $wfhQuotaError = WorkFromHomeQuota::validateEmployeeRequest(
                (int) $user->id,
                (string) $validated['start_date'],
                isset($validated['end_date']) ? (string) $validated['end_date'] : null
            );
            if ($wfhQuotaError !== null) {
                return redirect()->back()
                    ->withErrors(['type' => $wfhQuotaError])
                    ->withInput()
                    ->with('show_no_balance_modal', str_starts_with($wfhQuotaError, 'No balance:'));
            }
        }

        if ($user->role === 'student' && $validated['type'] === 'absent') {
            $requestedDays = $this->calculateLeaveRequestDays(
                (string) $validated['start_date'],
                isset($validated['end_date']) ? (string) $validated['end_date'] : null
            );
            $remainingAbsenceBalance = $this->getStudentRemainingAbsenceBalance(
                (int) $user->id,
                User::normalizedStudentAbsenceAllowance($user->student_absence_allowance)
            );

            if ($remainingAbsenceBalance <= 0) {
                return redirect()->back()
                    ->withErrors(['type' => 'You cannot file an Absent leave request because your allowable absences balance is 0.'])
                    ->withInput();
            }

            if ($requestedDays > $remainingAbsenceBalance) {
                return redirect()->back()
                    ->withErrors(['type' => 'Requested absent days exceed your remaining allowable absences balance.'])
                    ->withInput();
            }
        }

        // Build reason – include structured details when type is overtime or WFH
        $reasonToStore = $validated['reason'] ?? '';

        if ($this->usesStructuredHoursRequestFormat($validated['type'], $user)) {
            $reasonToStore = $this->buildStructuredHoursRequestReason(
                $this->structuredHoursRequestLabel($validated['type'], $user),
                $validated,
                $reasonToStore
            );
        } elseif ($validated['type'] === 'work_from_home') {
            $modeLabel = $validated['wfh_mode'] === 'request_to_be_excused'
                ? 'Request to be excused'
                : 'Working remotely';

            $details = "Work From Home Request Details:\n";
            $details .= 'Mode: '.$modeLabel."\n";
            $details .= 'Remote Address: '.($validated['wfh_address'] ?? '')."\n";
            $details .= 'Work Dates: '.($validated['start_date'] ?? '').' to '.($validated['end_date'] ?? $validated['start_date'])."\n";
            $details .= "Tasks / ClickUp Links:\n".($validated['wfh_tasks'] ?? '')."\n";

            if (! empty($reasonToStore)) {
                $details .= "\nAdditional Explanation:\n".$reasonToStore;
            }

            $reasonToStore = $details;
        } elseif ($validated['type'] === 'offset') {
            // Calculate duration in days
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = $validated['end_date']
                ? \Carbon\Carbon::parse($validated['end_date'])
                : $startDate;
            $days = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end dates

            // Offset: allow optional hourly deduction; otherwise 1 day = 8 hours
            $offsetText = trim((string) ($validated['offset_hours'] ?? ''));
            if ($offsetText !== '') {
                $offsetHours = $offsetText;
            } else {
                $totalHours = $days * 8;
                $offsetHours = sprintf('%02d:00', $totalHours);
            }

            $details = "Offset Request Details:\n";
            $details .= 'Duration: '.$days.' '.($days == 1 ? 'day' : 'days')."\n";
            $details .= 'Hours to Deduct: '.$offsetHours."\n";

            if (! empty($reasonToStore)) {
                $details .= "\nReason:\n".$reasonToStore;
            }

            $reasonToStore = $details;
        } elseif ($validated['type'] === 'travel') {
            $reasonToStore = 'Location of travel: '.trim($validated['reason'] ?? '');
        }

        // Balance check: Vacation Leave, Sick Leave, Offset only (employees)
        if (in_array($validated['type'], ['vacation_leave', 'sick_leave', 'offset']) && $user->isStaffMember()) {
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = $validated['end_date']
                ? \Carbon\Carbon::parse($validated['end_date'])
                : $startDate;
            $daysRequested = $startDate->diffInDays($endDate) + 1;

            if (in_array($validated['type'], ['vacation_leave', 'sick_leave'], true)) {
                $bal = $this->getEmployeeLeaveBalances($user);
                if ($bal['leave_remaining'] <= 0) {
                    return redirect()->back()
                        ->withErrors(['type' => 'No balance to file for that type of request.'])
                        ->withInput();
                }
                if ($daysRequested > $bal['leave_remaining']) {
                    return redirect()->back()
                        ->withErrors(['end_date' => "You only have {$bal['leave_remaining']} day(s) of Leave Credits remaining. You cannot request {$daysRequested} day(s)."])
                        ->withInput();
                }
            } else {
                // Offset: check overtime balance
                $offsetText = trim((string) ($validated['offset_hours'] ?? ''));
                $offsetMinutesNeeded = $offsetText !== ''
                    ? $this->parseHourMinuteToMinutes($offsetText)
                    : ($daysRequested * 8 * 60);

                if ($offsetText !== '' && $offsetMinutesNeeded <= 0) {
                    return redirect()->back()
                        ->withErrors(['offset_hours' => 'Please enter valid hours to deduct in HH:MM format (e.g., 08:00).'])
                        ->withInput();
                }

                $offsetHoursNeeded = $offsetMinutesNeeded / 60;
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
                    $requestedLabel = $offsetText !== ''
                        ? "{$offsetText} hour(s)."
                        : "{$daysRequested} day(s) (".($daysRequested * 8).' hours).';

                    return redirect()->back()
                        ->withErrors(['end_date' => "You only have {$hoursFormatted} hours of overtime balance. You cannot request {$requestedLabel}"])
                        ->withInput();
                }
            }
        }

        [$supportingPaths, $legacySupportingPath] = $this->storeSupportingDocumentsFromRequest($request);

        $travelHours = $validated['type'] === 'travel' ? (float) ($validated['travel_hours'] ?? 8.0) : null;
        $leaveRequest = LeaveRequest::create([
            'user_id' => Auth::id(),
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
            'leave_request_id' => $leaveRequest->id,
        ]);

        if (! empty($adminEmailsStr) && trim($adminEmailsStr) !== '') {
            $adminEmails = array_filter(array_map('trim', explode(',', $adminEmailsStr)));
            $validEmails = array_filter($adminEmails, function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            });

            Log::info('Leave request notification - parsed emails', [
                'total_emails' => count($adminEmails),
                'valid_emails' => count($validEmails),
                'valid_emails_list' => $validEmails,
            ]);

            if (! empty($validEmails)) {
                try {
                    // Configure mail settings before sending
                    MailConfigService::configure();

                    // Log mail configuration for debugging
                    Log::info('Leave request notification - mail configuration', [
                        'mail_driver' => config('mail.default'),
                        'mail_from' => config('mail.from.address'),
                        'valid_emails_count' => count($validEmails),
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
                                'leave_request_id' => $leaveRequest->id,
                            ]);
                        } catch (\Exception $emailException) {
                            $failedCount++;
                            Log::error('Failed to send leave request notification email to individual admin', [
                                'email' => $email,
                                'error' => $emailException->getMessage(),
                                'leave_request_id' => $leaveRequest->id,
                                'trace' => $emailException->getTraceAsString(),
                            ]);
                            // Continue sending to other emails even if one fails
                        }
                    }
                    if ($sentCount > 0) {
                        Log::info('Leave request notification emails sent', [
                            'sent' => $sentCount,
                            'failed' => $failedCount,
                            'total' => count($validEmails),
                            'leave_request_id' => $leaveRequest->id,
                        ]);
                    } else {
                        Log::warning('Leave request notification - no valid emails found after parsing', [
                            'admin_emails_str' => $adminEmailsStr,
                            'parsed_emails' => $adminEmails,
                            'valid_emails' => $validEmails,
                            'leave_request_id' => $leaveRequest->id,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to configure mail or send leave request notification emails: '.$e->getMessage(), [
                        'leave_request_id' => $leaveRequest->id,
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Don't fail the request if email fails
                }
            } else {
                Log::info('Leave request notification - no admin emails configured', [
                    'leave_request_id' => $leaveRequest->id,
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
        if (! in_array($user->role, ['employee', 'student'])) {
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
            $totalDtrHoursRaw = (float) \App\Models\Dtr::where('user_id', $user->id)->sum('total_hours');
            $rollbackHours = $this->getPendingResubmissionRollbackHours((int) $user->id);
            $totalDtrHours = max($totalDtrHoursRaw - $rollbackHours, 0);

            $remainingHours = $requiredHours - $totalDtrHours; // can be negative (over-completed)

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

        $letterContext = LeaveRequestSignatorySettings::letterContext($leaveRequest->user);
        $signatories = $letterContext['signatories'];
        $letterAddressee = $letterContext['letter_addressee'];

        $leaveRequestActivityLogs = $this->leaveRequestActivityLogsForRequester($leaveRequest);

        return view('user.leave-requests.show', compact('leaveRequest', 'signatories', 'letterAddressee', 'studentTime', 'leaveRequestActivityLogs'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        // Only allow employees to access
        $user = Auth::user();
        if (! in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can edit leave requests.');
        }

        // Ensure the user can only edit their own requests
        if ($leaveRequest->user_id !== Auth::id()) {
            abort(403, 'You can only edit your own leave requests.');
        }

        // Only allow editing of pending requests that were previously reviewed (resubmission)
        if (! $leaveRequest->isPending() || ! $leaveRequest->reviewed_at) {
            return redirect('/leave-requests/'.$leaveRequest->id)
                ->withErrors(['error' => 'You can only edit leave requests that have been requested for resubmission.']);
        }

        // Parse existing data for pre-filling
        $editData = [
            'type' => $leaveRequest->type,
            'start_date' => $leaveRequest->start_date->format('Y-m-d'),
            'end_date' => $leaveRequest->end_date ? $leaveRequest->end_date->format('Y-m-d') : null,
            'reason' => '',
            'additional_time_mode' => 'fixed_date',
            'additional_time_total_hours' => '',
            'overtime_hours' => '',
            'overtime_specific_dates' => [],
            'overtime_tasks' => '',
            'wfh_mode' => '',
            'wfh_address' => '',
            'wfh_tasks' => '',
            'offset_hours' => '',
        ];

        $raw = $leaveRequest->reason ?? '';

        if (in_array($leaveRequest->type, ['overtime', 'additional_time'], true)) {
            $parsed = $this->parseStructuredHoursRequestReason($raw);
            $editData['overtime_hours'] = $parsed['overtime_hours'];
            $editData['overtime_specific_dates'] = $parsed['overtime_specific_dates'];
            $editData['overtime_tasks'] = $parsed['overtime_tasks'];
            $editData['reason'] = $parsed['reason'];
            if ($parsed['start_date']) {
                $editData['start_date'] = $parsed['start_date'];
            }
            if ($parsed['end_date']) {
                $editData['end_date'] = $parsed['end_date'];
            }
            if ($user->role === 'student' && $leaveRequest->type === 'overtime') {
                $editData['type'] = 'additional_time';
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

        $leaveRequestActivityLogs = $this->leaveRequestActivityLogsForRequester($leaveRequest);

        $lockOvertimeDates = $leaveRequest->type === 'overtime'
            && ! $this->overtimeOriginalDatesWithinSevenDayWindow($leaveRequest);
        if ($leaveRequest->type === 'overtime') {
            $editData['start_date'] = $leaveRequest->start_date->format('Y-m-d');
            $editData['end_date'] = ($leaveRequest->end_date ?? $leaveRequest->start_date)->format('Y-m-d');
        }

        return view('user.leave-requests.edit', compact(
            'leaveRequest',
            'editData',
            'leaveRequestActivityLogs',
            'lockOvertimeDates'
        ));
    }

    /**
     * Complete overtime details for a leave request created from Record Attendance.
     */
    public function completeAttendanceOvertime(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        if (! in_array($user->role, ['student'], true)) {
            abort(403, 'Only students can complete attendance overtime requests.');
        }

        if ($leaveRequest->user_id !== Auth::id()) {
            abort(403, 'You can only complete your own leave requests.');
        }

        if (! $leaveRequest->needsAttendanceOvertimeCompletion()) {
            return redirect()->route('user.leave-requests.show', $leaveRequest)
                ->with('success', 'This overtime request is already complete.');
        }

        $formData = $this->attendanceOvertimeFormDataFromLeaveRequest($leaveRequest);

        return view('user.leave-requests.complete-attendance-overtime', [
            'leaveRequest' => $leaveRequest,
            'formData' => $formData,
        ]);
    }

    /**
     * Store completed overtime details from Record Attendance.
     */
    public function storeAttendanceOvertimeCompletion(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        if (! in_array($user->role, ['student'], true)) {
            abort(403, 'Only students can complete attendance overtime requests.');
        }

        if ($leaveRequest->user_id !== Auth::id()) {
            abort(403, 'You can only complete your own leave requests.');
        }

        if (! $leaveRequest->needsAttendanceOvertimeCompletion()) {
            return redirect()->route('user.leave-requests.show', $leaveRequest)
                ->with('success', 'This overtime request is already complete.');
        }

        $lockedForm = $this->attendanceOvertimeFormDataFromLeaveRequest($leaveRequest);
        $lockedHours = $lockedForm['overtime_hours'];
        $lockedStart = $lockedForm['start_date'];
        $lockedEnd = $lockedForm['end_date'];

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'overtime_hours' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'overtime_specific_dates' => ['required', 'array', 'min:1'],
            'overtime_specific_dates.*' => [
                'date',
                'after_or_equal:'.$lockedStart,
                'before_or_equal:'.$lockedEnd,
            ],
            'overtime_tasks' => ['nullable', 'string', 'max:2000', new ClickUpTasksUrlsOnly],
            'supporting_documents' => ['nullable', 'array', 'max:5'],
            'supporting_documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'reason.required' => 'Please provide a reason for this overtime.',
            'overtime_specific_dates.required' => 'Please select at least one overtime date.',
            'overtime_specific_dates.*.after_or_equal' => 'Selected dates must match the date(s) filed in Record Attendance.',
            'overtime_specific_dates.*.before_or_equal' => 'Selected dates must match the date(s) filed in Record Attendance.',
        ]);

        if (trim($validated['overtime_hours']) !== $lockedHours) {
            return redirect()->back()
                ->withErrors(['overtime_hours' => 'Overtime hours must match the amount filed in Record Attendance ('.$lockedHours.').'])
                ->withInput();
        }

        if ($this->parseHourMinuteToMinutes(trim($validated['overtime_hours'])) <= 0) {
            return redirect()->back()
                ->withErrors(['overtime_hours' => 'Please enter a valid overtime duration in HH:MM.'])
                ->withInput();
        }

        $overtimeDateValidationError = $this->validateAttendanceOvertimeDateCompletion(
            $lockedStart,
            $lockedEnd,
            $validated['overtime_specific_dates'] ?? []
        );
        if ($overtimeDateValidationError !== null) {
            return redirect()->back()->withErrors($overtimeDateValidationError)->withInput();
        }

        [$storedPaths, $primaryPath] = $this->storeSupportingDocumentsFromRequest($request);

        $dayTotalHours = TimeRequestOvertimeLeaveImport::parseDayTotalHoursFromReason($leaveRequest->reason);

        $reasonToStore = TimeRequestOvertimeLeaveImport::buildCompletedAttendanceOvertimeReason(
            $lockedStart,
            trim($validated['overtime_hours']),
            $dayTotalHours,
            $validated['overtime_specific_dates'] ?? [],
            trim((string) ($validated['overtime_tasks'] ?? '')),
            (string) $validated['reason']
        );

        $updatePayload = [
            'start_date' => $lockedStart,
            'end_date' => $lockedEnd,
            'reason' => $reasonToStore,
            'attendance_overtime_completed_at' => now(),
        ];

        if ($storedPaths !== []) {
            $updatePayload['supporting_document_paths'] = $storedPaths;
            $updatePayload['supporting_document_path'] = $primaryPath;
        }

        $leaveRequest->update($updatePayload);

        return redirect()->route('user.leave-requests.show', $leaveRequest)
            ->with('success', 'Overtime details submitted. Your request is now pending admin approval.');
    }

    /**
     * @return array{overtime_hours: string, start_date: string, end_date: string, overtime_specific_dates: array<int, string>, overtime_tasks: string, reason: string, day_total_hours: string|null}
     */
    private function attendanceOvertimeFormDataFromLeaveRequest(LeaveRequest $leaveRequest): array
    {
        $raw = $leaveRequest->reason ?? '';
        $data = [
            'overtime_hours' => '',
            'start_date' => $leaveRequest->start_date->format('Y-m-d'),
            'end_date' => ($leaveRequest->end_date ?? $leaveRequest->start_date)->format('Y-m-d'),
            'overtime_specific_dates' => [$leaveRequest->start_date->format('Y-m-d')],
            'overtime_tasks' => '',
            'reason' => '',
            'day_total_hours' => null,
        ];

        if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
            $data['overtime_hours'] = trim($m[1]);
        }
        if (preg_match('/Overtime Dates:\s*(.+)/', $raw, $m)) {
            $data['overtime_specific_dates'] = collect(explode(',', trim($m[1])))
                ->map(fn ($value) => trim($value))
                ->filter(fn ($value) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1)
                ->values()
                ->all();
        }
        if (preg_match('/Day total filed in attendance:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
            $data['day_total_hours'] = trim($m[1]);
        }

        if ($data['start_date'] !== $data['end_date']) {
            $period = new \Carbon\CarbonPeriod($data['start_date'], $data['end_date']);
            $data['overtime_specific_dates'] = collect($period)
                ->map(fn (\Carbon\Carbon $day) => $day->format('Y-m-d'))
                ->values()
                ->all();
        }

        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        // Only allow employees to access
        $user = Auth::user();
        if (! in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can update leave requests.');
        }

        // Ensure the user can only update their own requests
        if ($leaveRequest->user_id !== Auth::id()) {
            abort(403, 'You can only update your own leave requests.');
        }

        // Only allow updating of pending requests that were previously reviewed (resubmission)
        if (! $leaveRequest->isPending() || ! $leaveRequest->reviewed_at) {
            return redirect('/leave-requests/'.$leaveRequest->id)
                ->withErrors(['error' => 'You can only update leave requests that have been requested for resubmission.']);
        }

        // Define allowed types based on role (only employees can file travel)
        $allowedTypes = $user->role === 'student'
            ? ['additional_time', 'absent', 'overtime', 'other']
            : ['vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime', 'offset'];
        if ($user->isStaffMember()) {
            $allowedTypes[] = 'travel';
        }

        $startDateRules = ['required', 'date'];
        $endDateRules = ['nullable', 'date', 'after_or_equal:start_date'];
        $typeInput = $request->input('type');
        $lockOvertimeDatesOnResubmission = $leaveRequest->type === 'overtime'
            && $this->usesStructuredHoursRequestFormat((string) $typeInput, $user)
            && ! $this->overtimeOriginalDatesWithinSevenDayWindow($leaveRequest);

        // Travel (employee): only today or past dates
        if ($typeInput === 'travel') {
            $startDateRules[] = 'before_or_equal:today';
            $endDateRules[] = 'before_or_equal:today';
        } elseif ($lockOvertimeDatesOnResubmission) {
            // Original overtime dates are outside the 7-day window — keep them locked.
            $endDateRules = ['required', 'date', 'after_or_equal:start_date'];
        } elseif ($this->usesStructuredHoursRequestFormat((string) $typeInput, $user)) {
            $startDateRules[] = 'after_or_equal:'.now()->subDays(7)->toDateString();
            $startDateRules[] = 'before_or_equal:today';
            $endDateRules = ['required', 'date', 'after_or_equal:start_date', 'before_or_equal:today'];
        } elseif (! ($typeInput === 'overtime' || $typeInput === 'sick_leave' || ($user->role === 'student' && $typeInput === 'additional_time'))) {
            $startDateRules[] = 'after_or_equal:today';
        }

        $overtimeSpecificDateItemRules = $lockOvertimeDatesOnResubmission
            ? ['date']
            : ($this->usesStructuredHoursRequestFormat((string) $typeInput, $user)
                ? $this->overtimeSpecificDateItemRules()
                : ['date']);

        $hoursLabel = $this->structuredHoursRequestLabel((string) $typeInput, $user);

        $validated = $request->validate([
            'type' => ['required', 'in:'.implode(',', $allowedTypes)],
            'start_date' => $startDateRules,
            'end_date' => $endDateRules,
            'reason' => 'required_if:type,travel|nullable|string|max:1000',
            'supporting_documents' => ['nullable', 'array', 'max:5'],
            'supporting_documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'overtime_hours' => [
                Rule::requiredIf(fn () => $this->usesStructuredHoursRequestFormat((string) $request->input('type'), $user)),
                'nullable',
                'regex:/^\\d{2}:\\d{2}$/',
            ],
            'overtime_specific_dates' => [
                Rule::requiredIf(fn () => $this->usesStructuredHoursRequestFormat((string) $request->input('type'), $user)),
                'array',
                'min:1',
            ],
            'overtime_specific_dates.*' => $overtimeSpecificDateItemRules,
            'overtime_tasks' => [
                Rule::requiredIf(fn () => $this->usesStructuredHoursRequestFormat((string) $request->input('type'), $user)),
                'nullable',
                'string',
                'max:2000',
                new ClickUpTasksUrlsOnly,
            ],
            'wfh_mode' => 'required_if:type,work_from_home|nullable|in:working_remotely,request_to_be_excused',
            'wfh_address' => 'required_if:type,work_from_home|nullable|string|max:255',
            'wfh_tasks' => ['required_if:type,work_from_home', 'nullable', 'string', 'max:2000', new ClickUpTasksUrlsOnly],
            'offset_hours' => 'nullable|regex:/^\\d{2}:\\d{2}$/',
            'travel_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
        ], $this->structuredHoursValidationMessages($hoursLabel));

        if ($validated['type'] === 'travel' && $user->role !== 'employee') {
            return redirect()->back()
                ->withErrors(['type' => 'Only employees can file travel leave requests.'])
                ->withInput();
        }

        if ($this->usesStructuredHoursRequestFormat($validated['type'], $user)) {
            $lockOvertimeDatesOnResubmission = $leaveRequest->type === 'overtime'
                && ! $this->overtimeOriginalDatesWithinSevenDayWindow($leaveRequest);

            if ($lockOvertimeDatesOnResubmission) {
                $validated['start_date'] = $leaveRequest->start_date->format('Y-m-d');
                $validated['end_date'] = ($leaveRequest->end_date ?? $leaveRequest->start_date)->format('Y-m-d');
            }

            $overtimeInputValidationError = $this->validateOvertimeInputData($validated);
            if ($overtimeInputValidationError !== null) {
                return redirect()->back()->withErrors($overtimeInputValidationError)->withInput();
            }

            $overtimeDateValidationError = $this->validateOvertimeDateSelection(
                (string) ($validated['start_date'] ?? ''),
                (string) ($validated['end_date'] ?? ''),
                $validated['overtime_specific_dates'] ?? [],
                $lockOvertimeDatesOnResubmission
            );
            if ($overtimeDateValidationError !== null) {
                return redirect()->back()->withErrors($overtimeDateValidationError)->withInput();
            }
        }

        $supportingDocumentsError = $this->supportingDocumentsRequiredForOvertimeError($request, $leaveRequest);
        if ($supportingDocumentsError !== null) {
            return redirect()->back()->withErrors($supportingDocumentsError)->withInput();
        }

        $requestTypeInputValidationError = $this->validateRequestTypeInputData($validated);
        if ($requestTypeInputValidationError !== null) {
            return redirect()->back()->withErrors($requestTypeInputValidationError)->withInput();
        }

        if ($user->isStaffMember() && $validated['type'] === 'work_from_home') {
            $wfhQuotaError = WorkFromHomeQuota::validateEmployeeRequest(
                (int) $user->id,
                (string) $validated['start_date'],
                isset($validated['end_date']) ? (string) $validated['end_date'] : null,
                (int) $leaveRequest->id
            );
            if ($wfhQuotaError !== null) {
                return redirect()->back()
                    ->withErrors(['type' => $wfhQuotaError])
                    ->withInput()
                    ->with('show_no_balance_modal', str_starts_with($wfhQuotaError, 'No balance:'));
            }
        }

        if ($user->role === 'student' && $validated['type'] === 'absent') {
            $requestedDays = $this->calculateLeaveRequestDays(
                (string) $validated['start_date'],
                isset($validated['end_date']) ? (string) $validated['end_date'] : null
            );
            $remainingAbsenceBalance = $this->getStudentRemainingAbsenceBalance(
                (int) $user->id,
                User::normalizedStudentAbsenceAllowance($user->student_absence_allowance)
            );

            if ($remainingAbsenceBalance <= 0) {
                return redirect()->back()
                    ->withErrors(['type' => 'You cannot file an Absent leave request because your allowable absences balance is 0.'])
                    ->withInput();
            }

            if ($requestedDays > $remainingAbsenceBalance) {
                return redirect()->back()
                    ->withErrors(['type' => 'Requested absent days exceed your remaining allowable absences balance.'])
                    ->withInput();
            }
        }

        // Build reason – include structured details when type is overtime or WFH
        $reasonToStore = $validated['reason'] ?? '';

        if ($this->usesStructuredHoursRequestFormat($validated['type'], $user)) {
            $reasonToStore = $this->buildStructuredHoursRequestReason(
                $this->structuredHoursRequestLabel($validated['type'], $user),
                $validated,
                $reasonToStore
            );
        } elseif ($validated['type'] === 'work_from_home') {
            $modeLabel = $validated['wfh_mode'] === 'request_to_be_excused'
                ? 'Request to be excused'
                : 'Working remotely';

            $details = "Work From Home Request Details:\n";
            $details .= 'Mode: '.$modeLabel."\n";
            $details .= 'Remote Address: '.($validated['wfh_address'] ?? '')."\n";
            $details .= 'Work Dates: '.($validated['start_date'] ?? '').' to '.($validated['end_date'] ?? $validated['start_date'])."\n";
            $details .= "Tasks / ClickUp Links:\n".($validated['wfh_tasks'] ?? '')."\n";

            if (! empty($reasonToStore)) {
                $details .= "\nAdditional Explanation:\n".$reasonToStore;
            }

            $reasonToStore = $details;
        } elseif ($validated['type'] === 'offset') {
            // Calculate duration in days
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = $validated['end_date']
                ? \Carbon\Carbon::parse($validated['end_date'])
                : $startDate;
            $days = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end dates

            // Offset: allow optional hourly deduction; otherwise 1 day = 8 hours
            $offsetText = trim((string) ($validated['offset_hours'] ?? ''));
            if ($offsetText !== '') {
                $offsetHours = $offsetText;
            } else {
                $totalHours = $days * 8;
                $offsetHours = sprintf('%02d:00', $totalHours);
            }

            $details = "Offset Request Details:\n";
            $details .= 'Duration: '.$days.' '.($days == 1 ? 'day' : 'days')."\n";
            $details .= 'Hours to Deduct: '.$offsetHours."\n";

            if (! empty($reasonToStore)) {
                $details .= "\nReason:\n".$reasonToStore;
            }

            $reasonToStore = $details;
        } elseif ($validated['type'] === 'travel') {
            $reasonToStore = 'Location of travel: '.trim($validated['reason'] ?? '');
        }

        if ($user->role === 'student'
            && $leaveRequest->type === 'overtime'
            && $validated['type'] === 'additional_time') {
            $validated['type'] = 'overtime';
        }

        // Balance check for employee resubmissions:
        // Vacation Leave and Sick Leave share the same Leave Credits pool.
        if ($user->isStaffMember() && in_array($validated['type'], ['vacation_leave', 'sick_leave'], true)) {
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = $validated['end_date']
                ? \Carbon\Carbon::parse($validated['end_date'])
                : $startDate;
            $daysRequested = $startDate->diffInDays($endDate) + 1;

            $bal = $this->getEmployeeLeaveBalances($user);
            if (($bal['leave_remaining'] ?? 0) <= 0) {
                return redirect()->back()
                    ->withErrors(['type' => 'No balance to file for that type of request.'])
                    ->withInput();
            }
            if ($daysRequested > ($bal['leave_remaining'] ?? 0)) {
                $remaining = $bal['leave_remaining'] ?? 0;

                return redirect()->back()
                    ->withErrors(['end_date' => "You only have {$remaining} day(s) of Leave Credits remaining. You cannot request {$daysRequested} day(s)."])
                    ->withInput();
            }
        }

        // Handle supporting documents (replace all when new files are uploaded)
        $supportingPaths = $leaveRequest->all_supporting_document_paths;
        $legacySupportingPath = $leaveRequest->supporting_document_path;
        if ($request->hasFile('supporting_documents')) {
            $this->deleteSupportingDocumentPaths($supportingPaths);
            [$supportingPaths, $legacySupportingPath] = $this->storeSupportingDocumentsFromRequest($request);
        }

        $travelHours = $validated['type'] === 'travel' ? (float) ($validated['travel_hours'] ?? 8.0) : null;
        $statusBeforeSubmit = $leaveRequest->status;
        // Clear review information when resubmitting; keep admin_notes so the requester still sees prior reviewer feedback in the activity history and on file.
        $leaveRequest->update([
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
            'action' => 'requester_resubmitted',
            'status_before' => $statusBeforeSubmit,
            'status_after' => 'pending',
            'notes' => 'Requester revised and saved the request following a resubmission request (awaiting administrator review).',
            'performed_by' => (int) Auth::id(),
        ]);

        return redirect('/leave-requests/'.$leaveRequest->id)
            ->with('success', 'Leave request updated successfully. It will be reviewed again by an administrator.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        // Allow employees and students to access
        $user = Auth::user();
        if (! in_array($user->role, ['employee', 'student'])) {
            abort(403, 'Only employees and students can delete leave requests.');
        }

        // Ensure the user can only delete their own requests
        if ($leaveRequest->user_id !== Auth::id()) {
            abort(403, 'You can only delete your own leave requests.');
        }

        // Only allow deletion of pending requests
        if (! $leaveRequest->isPending()) {
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
                if (! empty($existingRemarks) && strpos($existingRemarks, $travelRemark) === false) {
                    $dtr->remarks = $existingRemarks.'; '.$travelRemark;
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
     * Get employee overtime balance in hours.
     *
     * Calculated as:
     *  - Sum of "Total Overtime Hours: HH:MM" from approved overtime requests
     *  - Minus "Hours to Deduct: HH:MM" (or days * 8) from approved offset requests.
     */
    private function getEmployeeOvertimeBalanceHours($user): float
    {
        $today = Carbon::today();
        // Overtime earned from uploaded DTRs (hours above 8.0 per day).
        // This is important because admins can upload DTRs without creating overtime leave requests.
        $dtrOvertimeMinutes = 0;
        $dtrs = Dtr::where('user_id', $user->id)
            ->whereDate('date', '<=', $today)
            ->get();
        foreach ($dtrs as $dtr) {
            $rawTotal = $dtr->total_hours;
            $total = 0.0;
            if (is_numeric($rawTotal)) {
                $total = (float) $rawTotal;
            } else {
                $txt = trim((string) $rawTotal);
                // Some installs store total_hours as "HH:MM"
                if (preg_match('/^([0-9]{1,3}):([0-9]{2})$/', $txt, $m)) {
                    $total = ((int) $m[1]) + (((int) $m[2]) / 60);
                } else {
                    // Fallback best-effort
                    $total = (float) $txt;
                }
            }
            $dailyOvertime = max($total - 8.0, 0);
            $dtrOvertimeMinutes += (int) round($dailyOvertime * 60);
        }

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

        // Subtract approved offset requests
        $approvedOffsetRequests = LeaveRequest::where('user_id', $user->id)
            ->where('type', 'offset')
            ->where('status', 'approved')
            ->get();

        $offsetMinutes = 0;
        foreach ($approvedOffsetRequests as $offsetRequest) {
            $raw = $offsetRequest->reason ?? '';
            if (preg_match('/Hours to Deduct:\s*([0-9]{2}):([0-9]{2})/', $raw, $m)) {
                $offsetMinutes += (int) $m[1] * 60 + (int) $m[2];
            } else {
                // Fallback: use days * 8 hours when no explicit HH:MM is present
                $offsetMinutes += (int) round(($offsetRequest->days * 8) * 60);
            }
        }

        return ($dtrOvertimeMinutes + $overtimeFromLeavesMinutes - $offsetMinutes) / 60;
    }

    /**
     * Get employee leave balances for vacation, sick, and overtime (create form and balance validation).
     */
    private function getEmployeeLeaveBalances($user): array
    {
        $currentYear = now()->year;
        $defaultVacation = (float) \App\Models\Setting::get('default_vacation_balance', 15);
        $defaultSick = (float) \App\Models\Setting::get('default_sick_leave_balance', 10);

        $leaveBalance = LeaveBalance::firstOrCreateWithCarryover(
            (int) $user->id,
            (int) $currentYear,
            (float) $defaultVacation,
            (float) $defaultSick
        );

        $usedLeave = LeaveRequest::where('user_id', $user->id)
            ->whereIn('type', ['leave', 'vacation_leave', 'sick_leave'])
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->get()
            ->sum->days;

        $totalAllowance = (float) $leaveBalance->vacation_allowance + (float) $leaveBalance->sick_allowance;

        $wfhBalance = WorkFromHomeQuota::balanceForMonth((int) $user->id);

        return [
            'leave_remaining' => max($totalAllowance - $usedLeave, 0),
            'leave_allowance' => $totalAllowance,
            'overtime_hours' => $this->getEmployeeOvertimeBalanceHours($user),
            'work_from_home' => $wfhBalance,
            'work_from_home_remaining' => $wfhBalance['remaining'],
        ];
    }

    /**
     * Reconcile Additional Time credits that should be removed after resubmission request.
     */
    private function reconcileStudentAdditionalTimeResubmissions(int $userId): void
    {
        $requests = LeaveRequest::where('user_id', $userId)
            ->where('type', 'additional_time')
            ->where('status', 'pending')
            ->whereHas('logs', function ($q) {
                $q->where('action', 'approved');
            })
            ->whereHas('logs', function ($q) {
                $q->where('action', 'resubmission_requested');
            })
            ->whereDoesntHave('logs', function ($q) {
                $q->where('action', 'additional_time_reverted');
            })
            ->get();

        foreach ($requests as $leaveRequest) {
            $this->revertAdditionalTimeCreditForResubmission($leaveRequest);
        }
    }

    private function revertAdditionalTimeCreditForResubmission(LeaveRequest $leaveRequest): void
    {
        $alreadyReverted = LeaveRequestLog::where('leave_request_id', $leaveRequest->id)
            ->where('action', 'additional_time_reverted')
            ->exists();
        if ($alreadyReverted) {
            return;
        }

        $didRevert = false;
        $rawReason = (string) ($leaveRequest->reason ?? '');
        if (preg_match('/Additional Time Hours:\s*(\d{1,3}):(\d{2})/', $rawReason, $m)) {
            $hoursText = sprintf('%d:%02d', (int) $m[1], (int) $m[2]);
            $minutes = $this->parseHourMinuteToMinutes($hoursText);
            if ($minutes <= 0) {
                return;
            }

            $hoursToDeduct = $minutes / 60;
            $remarkToken = "Additional Time ({$hoursText})";
            $date = Carbon::parse($leaveRequest->start_date)->toDateString();
            $dtr = Dtr::where('user_id', $leaveRequest->user_id)
                ->whereDate('date', $date)
                ->first();

            if (! $dtr) {
                return;
            }

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

            if ($didRevert) {
                LeaveRequestLog::create([
                    'leave_request_id' => $leaveRequest->id,
                    'action' => 'additional_time_reverted',
                    'status_before' => 'approved',
                    'status_after' => 'pending',
                    'notes' => 'Reconciled and reverted Additional Time credit from DTR.',
                    'performed_by' => Auth::id(),
                ]);
            }

            return;
        }

        $start = Carbon::parse($leaveRequest->start_date);
        $end = $leaveRequest->end_date ? Carbon::parse($leaveRequest->end_date) : $start;
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
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
                'notes' => 'Reconciled and reverted Additional Time credit from DTR.',
                'performed_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Parse HH:MM time text into total minutes.
     */
    private function parseHourMinuteToMinutes(string $value): int
    {
        $value = trim($value);
        if (! preg_match('/^(\d{1,3}):(\d{2})$/', $value, $m)) {
            return 0;
        }

        $hours = (int) $m[1];
        $minutes = (int) $m[2];
        if ($minutes < 0 || $minutes > 59) {
            return 0;
        }

        return ($hours * 60) + $minutes;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, string>|null
     */
    private function validateOvertimeInputData(array $validated): ?array
    {
        $hoursText = trim((string) ($validated['overtime_hours'] ?? ''));
        if ($hoursText === '') {
            return ['overtime_hours' => 'Overtime hours is required for Overtime requests.'];
        }
        if ($this->parseHourMinuteToMinutes($hoursText) <= 0) {
            return ['overtime_hours' => 'Please enter a valid overtime duration in HH:MM (example: 01:30).'];
        }

        $tasksText = trim((string) ($validated['overtime_tasks'] ?? ''));
        if ($tasksText === '') {
            return ['overtime_tasks' => 'Please provide your overtime task details/links.'];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, string>|null
     */
    private function validateRequestTypeInputData(array $validated): ?array
    {
        $type = (string) ($validated['type'] ?? '');

        if ($type === 'work_from_home') {
            $address = trim((string) ($validated['wfh_address'] ?? ''));
            if ($address === '') {
                return ['wfh_address' => 'Remote address is required for Work From Home requests.'];
            }

            $tasks = trim((string) ($validated['wfh_tasks'] ?? ''));
            if ($tasks === '') {
                return ['wfh_tasks' => 'Please provide your work tasks/links for Work From Home requests.'];
            }
        }

        if ($type === 'travel') {
            $location = trim((string) ($validated['reason'] ?? ''));
            if ($location === '') {
                return ['reason' => 'Location of travel is required for Travel requests.'];
            }

            $travelHours = (float) ($validated['travel_hours'] ?? 8.0);
            if ($travelHours <= 0) {
                return ['travel_hours' => 'Travel hours must be greater than 0.'];
            }
        }

        if ($type === 'offset') {
            $offsetText = trim((string) ($validated['offset_hours'] ?? ''));
            if ($offsetText !== '' && $this->parseHourMinuteToMinutes($offsetText) <= 0) {
                return ['offset_hours' => 'Please enter valid hours to deduct in HH:MM format (example: 08:00).'];
            }
        }

        return null;
    }

    /**
     * @return array<string, string>|null
     */
    private function supportingDocumentsRequiredForOvertimeError(Request $request, ?LeaveRequest $existing = null): ?array
    {
        $user = Auth::user();
        $type = (string) $request->input('type');
        if (! $user || ! $this->usesStructuredHoursRequestFormat($type, $user)) {
            return null;
        }

        $files = $request->file('supporting_documents', []);
        $hasNewUploads = is_array($files) && collect($files)->filter()->isNotEmpty();

        $hasExisting = $existing !== null && (
            ! empty($existing->all_supporting_document_paths)
            || filled($existing->supporting_document_path)
        );

        if (! $hasNewUploads && ! $hasExisting) {
            $label = $this->structuredHoursRequestLabel($type, $user);

            return ['supporting_documents' => "Supporting document is required for {$label} requests."];
        }

        return null;
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
                Log::warning('Leave request supporting document store failed, skipping file', [
                    'error' => $e->getMessage(),
                    'disk' => $disk,
                ]);
            }
        }

        return [$paths, $paths[0] ?? null];
    }

    /**
     * @param  list<string>  $paths
     */
    private function deleteSupportingDocumentPaths(array $paths): void
    {
        if (empty($paths)) {
            return;
        }

        $assetDisk = 'digitalocean';
        $doConfigured = ! empty(env('DIGITALOCEAN_SPACES_KEY') ?: env('DO_SPACES_KEY'))
            && ! empty(env('DIGITALOCEAN_SPACES_SECRET') ?: env('DO_SPACES_SECRET'))
            && ! empty(env('DIGITALOCEAN_SPACES_BUCKET') ?: env('DO_SPACES_BUCKET'));

        $disk = $doConfigured ? $assetDisk : config('filesystems.default', 'local');

        foreach ($paths as $path) {
            try {
                \Illuminate\Support\Facades\Storage::disk($disk)->delete($path);
            } catch (\Throwable $e) {
                // Ignore delete failures; stale files should not block updates.
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function overtimeSpecificDateItemRules(): array
    {
        $earliest = now()->subDays(7)->toDateString();

        return ['date', 'after_or_equal:'.$earliest, 'before_or_equal:today'];
    }

    /**
     * Dates for overtime completed from Record Attendance (no 7-day / today window).
     *
     * @param  array<int, mixed>  $specificDates
     * @return array<string, string>|null
     */
    private function validateAttendanceOvertimeDateCompletion(string $lockedStart, string $lockedEnd, array $specificDates): ?array
    {
        try {
            $start = \Carbon\Carbon::parse($lockedStart)->startOfDay();
            $end = \Carbon\Carbon::parse($lockedEnd)->startOfDay();
        } catch (\Throwable) {
            return ['overtime_specific_dates' => 'Invalid overtime date from Record Attendance.'];
        }

        $normalized = collect($specificDates)
            ->map(fn ($date) => trim((string) $date))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return ['overtime_specific_dates' => 'Select at least one overtime date from Record Attendance.'];
        }

        foreach ($normalized as $date) {
            try {
                $picked = \Carbon\Carbon::parse($date)->startOfDay();
            } catch (\Throwable) {
                return ['overtime_specific_dates' => 'One or more selected overtime dates are invalid.'];
            }

            if ($picked->lt($start) || $picked->gt($end)) {
                return ['overtime_specific_dates' => 'Selected dates must be within the date(s) filed in Record Attendance only.'];
            }
        }

        return null;
    }

    /**
     * Whether the original overtime start/end dates fall within the last 7 days through today.
     */
    private function overtimeOriginalDatesWithinSevenDayWindow(LeaveRequest $leaveRequest): bool
    {
        if (! $leaveRequest->start_date) {
            return false;
        }

        $today = now()->startOfDay();
        $earliest = $today->copy()->subDays(7);
        $start = $leaveRequest->start_date->copy()->startOfDay();
        $end = ($leaveRequest->end_date ?? $leaveRequest->start_date)->copy()->startOfDay();

        return $start->gte($earliest)
            && $start->lte($today)
            && $end->gte($earliest)
            && $end->lte($today)
            && $end->gte($start);
    }

    /**
     * Validate overtime range and specific selected dates.
     *
     * @param  array<int, mixed>  $specificDates
     * @return array<string, string>|null
     */
    private function validateOvertimeDateSelection(
        string $startDate,
        string $endDate,
        array $specificDates,
        bool $allowOutsideSevenDayWindow = false
    ): ?array {
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->startOfDay();
        } catch (\Throwable) {
            return ['start_date' => 'Please choose a valid overtime date range.'];
        }

        $today = now()->startOfDay();
        $earliest = $today->copy()->subDays(7);

        if (! $allowOutsideSevenDayWindow) {
            if ($start->lt($earliest) || $start->gt($today)) {
                return ['start_date' => 'For overtime, the start date must be within the last 7 days up to today.'];
            }
            if ($end->lt($start) || $end->gt($today)) {
                return ['end_date' => 'For overtime, the end date must be within the selected range and not later than today.'];
            }
        } elseif ($end->lt($start)) {
            return ['end_date' => 'For overtime, the end date must be the same as or after the start date.'];
        }

        $normalized = collect($specificDates)
            ->map(fn ($date) => trim((string) $date))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return ['overtime_specific_dates' => 'Select at least one specific overtime date from the chosen range.'];
        }

        foreach ($normalized as $date) {
            try {
                $picked = Carbon::parse($date)->startOfDay();
            } catch (\Throwable) {
                return ['overtime_specific_dates' => 'One or more selected overtime dates are invalid.'];
            }

            if ($picked->lt($start) || $picked->gt($end)) {
                return ['overtime_specific_dates' => 'Selected overtime dates must be inside the chosen start/end range only.'];
            }

            if (! $allowOutsideSevenDayWindow) {
                if ($picked->gt($today)) {
                    return ['overtime_specific_dates' => 'Overtime cannot be filed for future dates.'];
                }

                if ($picked->lt($earliest)) {
                    return ['overtime_specific_dates' => 'Each overtime date must be within the last 7 days through today.'];
                }
            }
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $specificDates
     */
    private function normalizedSpecificOvertimeDates(array $specificDates): string
    {
        return collect($specificDates)
            ->map(fn ($date) => trim((string) $date))
            ->filter(fn ($date) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1)
            ->unique()
            ->sort()
            ->values()
            ->implode(', ');
    }

    private function calculateLeaveRequestDays(string $startDate, ?string $endDate = null): float
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->startOfDay() : $start->copy();

        return (float) ($start->diffInDays($end) + 1);
    }

    private function getStudentRemainingAbsenceBalance(int $userId, float $allowableAbsences): float
    {
        $approvedAbsentDays = (float) LeaveRequest::query()
            ->where('user_id', $userId)
            ->where('type', 'absent')
            ->where('status', 'approved')
            ->countingTowardAbsenceMerits()
            ->get()
            ->sum('days');

        return max($allowableAbsences - $approvedAbsentDays, 0);
    }

    private function getPendingResubmissionRollbackHours(int $userId): float
    {
        $requests = LeaveRequest::where('user_id', $userId)
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
            ->get();

        $total = 0.0;
        foreach ($requests as $request) {
            if ($request->type === 'travel') {
                $total += ((float) ($request->travel_hours ?? 8.0)) * $request->days;

                continue;
            }
            if (in_array($request->type, ['leave', 'vacation_leave', 'sick_leave'], true)) {
                $total += 8.0 * $request->days;

                continue;
            }

            $raw = (string) ($request->reason ?? '');
            if (preg_match('/Additional Time Hours:\s*(\d{1,3}):(\d{2})/', $raw, $m)) {
                $hours = (int) $m[1];
                $minutes = (int) $m[2];
                if ($minutes >= 0 && $minutes <= 59) {
                    $total += $hours + ($minutes / 60);

                    continue;
                }
            }
            $total += 8.0 * $request->days;
        }

        return $total;
    }

    /**
     * Activity log entries for the requester UI (oldest first).
     *
     * @return \Illuminate\Support\Collection<int, LeaveRequestLog>
     */
    private function leaveRequestActivityLogsForRequester(LeaveRequest $leaveRequest)
    {
        return LeaveRequestLog::query()
            ->where('leave_request_id', $leaveRequest->id)
            ->with('performer')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    private function usesStructuredHoursRequestFormat(string $type, User $user): bool
    {
        return $type === 'overtime' || ($user->role === 'student' && $type === 'additional_time');
    }

    private function structuredHoursRequestLabel(string $type, User $user): string
    {
        return ($user->role === 'student' && $type === 'additional_time') ? 'Additional Time' : 'Overtime';
    }

    /**
     * @return array<string, string>
     */
    private function structuredHoursValidationMessages(string $hoursLabel): array
    {
        return [
            'overtime_hours.required_if' => "{$hoursLabel} hours is required.",
            'overtime_hours.regex' => "{$hoursLabel} hours must be in HH:MM format (example: 02:30).",
            'overtime_specific_dates.required_if' => 'Please select at least one date.',
            'overtime_specific_dates.array' => 'Invalid date selection.',
            'overtime_specific_dates.min' => 'Please select at least one date.',
            'overtime_specific_dates.*.date' => 'One or more dates are invalid.',
            'overtime_specific_dates.*.before_or_equal' => 'Dates cannot be in the future.',
            'overtime_specific_dates.*.after_or_equal' => 'Each date must be within the last 7 days.',
            'overtime_tasks.required_if' => 'Please provide your task details/links.',
            'overtime_tasks.max' => 'Task details must not exceed 2000 characters.',
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function buildStructuredHoursRequestReason(string $label, array $validated, ?string $additionalExplanation): string
    {
        $details = "{$label} Request Details:\n";
        $details .= "Total {$label} Hours: ".($validated['overtime_hours'] ?? '')."\n";
        $details .= "{$label} Date Range: ".($validated['start_date'] ?? '').' to '.($validated['end_date'] ?? $validated['start_date'] ?? '')."\n";
        $details .= "{$label} Dates: ".$this->normalizedSpecificOvertimeDates($validated['overtime_specific_dates'] ?? [])."\n";
        $details .= "Tasks / ClickUp Links:\n".($validated['overtime_tasks'] ?? '')."\n";

        if (! empty($additionalExplanation)) {
            $details .= "\nAdditional Explanation:\n".$additionalExplanation;
        }

        return $details;
    }

    /**
     * @return array{
     *     overtime_hours: string,
     *     start_date: string|null,
     *     end_date: string|null,
     *     overtime_specific_dates: array<int, string>,
     *     overtime_tasks: string,
     *     reason: string
     * }
     */
    private function parseStructuredHoursRequestReason(string $raw): array
    {
        $data = [
            'overtime_hours' => '',
            'start_date' => null,
            'end_date' => null,
            'overtime_specific_dates' => [],
            'overtime_tasks' => '',
            'reason' => '',
        ];

        if (preg_match('/Total (?:Overtime|Additional Time) Hours:\s*(.+)/i', $raw, $m)) {
            $data['overtime_hours'] = trim($m[1]);
        } elseif (preg_match('/Additional Time Hours:\s*([0-9]{1,3}:[0-9]{2})/i', $raw, $m)) {
            $data['overtime_hours'] = trim($m[1]);
        }
        if (preg_match('/(?:Overtime|Additional Time) Date Range:\s*(\d{4}-\d{2}-\d{2})\s+to\s+(\d{4}-\d{2}-\d{2})/i', $raw, $m)) {
            $data['start_date'] = trim($m[1]);
            $data['end_date'] = trim($m[2]);
        }
        if (preg_match('/(?:Overtime|Additional Time) Dates:\s*(.+)/i', $raw, $m)) {
            $data['overtime_specific_dates'] = collect(explode(',', trim($m[1])))
                ->map(fn ($value) => trim($value))
                ->filter(fn ($value) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1)
                ->values()
                ->all();
        }
        if (preg_match('/Tasks \/ ClickUp Links:\s*(.+?)(?:\n+Additional Explanation:|\n+Reason:|\z)/s', $raw, $m)) {
            $data['overtime_tasks'] = trim($m[1]);
        }
        if (preg_match('/Additional Explanation:\s*(.+)\z/s', $raw, $m)) {
            $data['reason'] = trim($m[1]);
        } elseif (preg_match('/Reason:\s*(.+)\z/s', $raw, $m)) {
            $data['reason'] = trim($m[1]);
        }

        return $data;
    }
}
