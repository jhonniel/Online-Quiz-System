<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Dtr;
use App\Models\EvaluationForm;
use App\Models\EvaluationSubmission;
use App\Models\HiringApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestLog;
use App\Models\News;
use App\Models\QuizAssignment;
use App\Models\Setting;
use App\Models\TicketReport;
use App\Models\University;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\StudentOjtPostCompletionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $evaluationAvailable = false;
        $evaluationFormTitle = null;
        $studentTrainingStats = null;
        $studentTrainingCharts = null;
        $studentResubmissionRequests = collect();
        $studentOjtAccessCountdown = null;
        $studentLeaveBalanceSummary = null;
        $employeeLeaveSummary = null;
        $employeeLeaveCharts = null;
        $employeeResubmissionRequests = collect();

        // Technician users get a ticket-focused dashboard.
        if ($user->role === 'technician') {
            $assignedTicketsQuery = TicketReport::query()
                ->where('assigned_to_user_id', $user->id);

            $assignedTotal = (clone $assignedTicketsQuery)->count();
            $assignedOpen = (clone $assignedTicketsQuery)
                ->whereIn('status', [
                    TicketReport::STATUS_OPEN,
                    TicketReport::STATUS_PROCESSING,
                    TicketReport::STATUS_NEEDS_INVESTIGATION,
                ])->count();
            $assignedResolved = (clone $assignedTicketsQuery)
                ->whereIn('status', [
                    TicketReport::STATUS_RESOLVED,
                    TicketReport::STATUS_CLOSED,
                ])->count();
            $assignedPendingPayment = (clone $assignedTicketsQuery)
                ->where('payment_status', TicketReport::PAYMENT_STATUS_PENDING)
                ->count();

            $recentAssignedTickets = (clone $assignedTicketsQuery)
                ->latest()
                ->take(10)
                ->get();

            return view('user.technician-dashboard', compact(
                'assignedTotal',
                'assignedOpen',
                'assignedResolved',
                'assignedPendingPayment',
                'recentAssignedTickets'
            ));
        }

        // Teachers get analytics on dashboard (students list is on My Students page).
        if ($user->role === 'teacher') {
            $teacherData = $this->getTeacherSchoolData($user);

            return view('user.teacher-dashboard', [
                'totalStudents' => $teacherData['totalStudents'],
                'ongoingInternships' => $teacherData['ongoingInternships'],
                'completedInternships' => $teacherData['completedInternships'],
                'schoolName' => $teacherData['schoolName'],
                'teacherCharts' => $teacherData['charts'],
                'studentsApprovedAbsentRanking' => $teacherData['studentsApprovedAbsentRanking'],
                'nextExitConferenceDate' => $teacherData['nextExitConferenceDate'],
                'pendingApplicationsCount' => $teacherData['pendingApplicationsCount'],
            ]);
        }

        $canViewAssignedQuizzes = $user->canViewAssignedQuizzes();
        $assignedQuizzes = collect();
        $allQuizzes = collect();
        $completedQuizzes = 0;
        $pendingQuizzes = 0;
        $totalQuizzes = 0;
        $ongoingQuiz = null;

        if ($canViewAssignedQuizzes) {
            $assignedQuizzes = QuizAssignment::where('user_id', $user->id)
                ->with(['quiz.creator'])
                ->whereHas('quiz', function ($query) {
                    $query->where('is_active', true);
                })
                ->get();

            $allQuizzes = \App\Models\Quiz::query()
                ->where('is_active', true)
                ->whereHas('assignments', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['creator', 'assignments' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }])
                ->orderBy('title')
                ->get();

            $completedQuizzes = $assignedQuizzes->where('is_completed', true)->count();
            $pendingQuizzes = $assignedQuizzes->where('is_completed', false)->count();
            $totalQuizzes = $allQuizzes->count();

            $ongoingQuiz = QuizAssignment::where('user_id', $user->id)
                ->where('status', 'in_progress')
                ->where('is_completed', false)
                ->with('quiz')
                ->first();
        }

        if ($user->role === 'student' && Schema::hasColumn('users', 'ojt_requirement_met_at')) {
            $studentOjtAccessCountdown = app(StudentOjtPostCompletionService::class)
                ->studentAccountDisableCountdownForDashboard($user);
        }

        if ($user->role === 'student' && Schema::hasTable('evaluation_forms') && Schema::hasTable('evaluation_submissions')) {
            $requiredHours = (float) ($user->required_training_hours ?? 0);
            $loggedHours = (float) Dtr::query()->where('user_id', $user->id)->sum('total_hours');
            $activeEvaluationForm = EvaluationForm::query()->where('is_active', true)->latest('updated_at')->first();

            $isForced = Schema::hasColumn('users', 'evaluation_forced_at') && ! empty($user->evaluation_forced_at);
            if ($activeEvaluationForm && (($requiredHours > 0 && $loggedHours >= $requiredHours) || $isForced)) {
                $submitted = EvaluationSubmission::query()
                    ->where('evaluation_form_id', $activeEvaluationForm->id)
                    ->where('user_id', $user->id)
                    ->exists();
                $evaluationAvailable = ! $submitted;
                $evaluationFormTitle = $activeEvaluationForm->title;
            }
        }

        if ($user->role === 'student') {
            $requiredHours = (float) ($user->required_training_hours ?? 0);
            $loggedHours = (float) Dtr::query()->where('user_id', $user->id)->sum('total_hours');
            $remainingHours = max($requiredHours - $loggedHours, 0);

            $recentDtrRows = Dtr::query()
                ->where('user_id', $user->id)
                ->whereDate('date', '>=', now()->subDays(30)->toDateString())
                ->where('total_hours', '>', 0)
                ->get(['date', 'total_hours']);
            $activeDays = $recentDtrRows->pluck('date')->unique()->count();
            $avgHoursPerActiveDay = $activeDays > 0
                ? ((float) $recentDtrRows->sum('total_hours')) / $activeDays
                : 0.0;

            $estimatedEndDate = null;
            if ($remainingHours > 0) {
                $dailyHoursForEstimate = $avgHoursPerActiveDay > 0 ? $avgHoursPerActiveDay : 8.0;
                $neededDays = (int) ceil($remainingHours / $dailyHoursForEstimate);
                $estimatedEndDate = now()->timezone(config('app.timezone'))->startOfDay()->addWeekdays(max($neededDays, 1));
            }

            $adminOjtTargetEnd = Schema::hasColumn('users', 'ojt_target_end_date')
                ? $user->ojt_target_end_date
                : null;

            /**
             * When admin never set ojt_target_end_date: required_training_hours at 8 h per weekday,
             * counted from the calendar date of the student’s first DTR row (earliest `date` in DTR).
             * No DTR rows yet: same idea from today using remaining hours (matches full requirement).
             */
            $possibleExitConferenceDate = null;
            $possibleExitConferenceWeekdays = null;
            if ($adminOjtTargetEnd === null && $requiredHours > 0 && $remainingHours > 0) {
                $tz = config('app.timezone');
                $weekdaysForFullRequirement = max(1, (int) ceil($requiredHours / 8.0));
                $firstDtrDate = Dtr::query()
                    ->where('user_id', $user->id)
                    ->min('date');

                if ($firstDtrDate !== null) {
                    $anchor = Carbon::parse($firstDtrDate)->timezone($tz)->startOfDay();
                    $possibleExitConferenceDate = $anchor->copy()->addWeekdays($weekdaysForFullRequirement);
                    $possibleExitConferenceWeekdays = $weekdaysForFullRequirement;
                } else {
                    $todayStart = now()->timezone($tz)->startOfDay();
                    $weekdaysForRemaining = max(1, (int) ceil($remainingHours / 8.0));
                    $possibleExitConferenceDate = $todayStart->copy()->addWeekdays($weekdaysForRemaining);
                    $possibleExitConferenceWeekdays = $weekdaysForRemaining;
                }
            }

            $months = collect(range(5, 0))->map(fn ($index) => now()->subMonths($index)->startOfMonth());
            $months = $months->push(now()->startOfMonth())->values();
            $monthlyLabels = $months->map(fn (Carbon $month) => $month->format('M Y'))->values();
            $monthlyHours = $months->mapWithKeys(fn (Carbon $month) => [
                $month->format('Y-m') => 0.0,
            ]);

            $monthlyDtrRows = Dtr::query()
                ->where('user_id', $user->id)
                ->whereDate('date', '>=', $months->first()->toDateString())
                ->get(['date', 'total_hours']);

            foreach ($monthlyDtrRows as $row) {
                $key = Carbon::parse($row->date)->format('Y-m');
                if ($monthlyHours->has($key)) {
                    $monthlyHours[$key] = (float) $monthlyHours[$key] + (float) ($row->total_hours ?? 0);
                }
            }

            $studentTrainingStats = [
                'required_hours' => $requiredHours,
                'logged_hours' => $loggedHours,
                'remaining_hours' => $remainingHours,
                'progress_percent' => $requiredHours > 0 ? min(($loggedHours / $requiredHours) * 100, 100) : 0,
                'estimated_end_date' => $estimatedEndDate,
                'ojt_target_end_date' => $adminOjtTargetEnd,
                'possible_exit_conference_date' => $possibleExitConferenceDate,
                'possible_exit_conference_weekdays' => $possibleExitConferenceWeekdays,
            ];

            $studentTrainingCharts = [
                'progress' => [
                    'labels' => ['Required Hours', 'Logged Hours', 'Remaining Hours'],
                    'values' => [$requiredHours, $loggedHours, $remainingHours],
                ],
                'monthly' => [
                    'labels' => $monthlyLabels->all(),
                    'values' => $monthlyHours->values()->map(fn ($value) => round((float) $value, 2))->all(),
                ],
            ];

            // Requests that were sent back by admin for correction/resubmission.
            $studentResubmissionRequests = LeaveRequest::query()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->whereNotNull('reviewed_at')
                ->whereHas('logs', function ($q) {
                    $q->where('action', 'resubmission_requested');
                })
                ->latest('updated_at')
                ->with('reviewer')
                ->take(3)
                ->get();

            $approvedAbsentDays = (float) LeaveRequest::query()
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('type', 'absent')
                ->get()
                ->sum('days');
            $allowableAbsences = Schema::hasColumn('users', 'student_absence_allowance')
                ? User::normalizedStudentAbsenceAllowance($user->student_absence_allowance)
                : User::DEFAULT_STUDENT_ABSENCE_ALLOWANCE;

            $studentLeaveBalanceSummary = [
                'allowable_absences' => round($allowableAbsences, 2),
                'approved_absent_days' => round($approvedAbsentDays, 2),
                'remaining_absence_balance' => round(max($allowableAbsences - $approvedAbsentDays, 0), 2),
            ];
        }

        if ($user->role === 'employee') {
            $employeeLeaveBaseQuery = LeaveRequest::query()
                ->where('user_id', $user->id);

            $currentYear = now()->year;
            $defaultVacation = (float) Setting::get('default_vacation_balance', 15);
            $defaultSick = (float) Setting::get('default_sick_leave_balance', 10);
            $leaveBalance = LeaveBalance::firstOrCreateWithCarryover(
                (int) $user->id,
                (int) $currentYear,
                (float) $defaultVacation,
                (float) $defaultSick
            );
            $leaveAllowance = (float) $leaveBalance->vacation_allowance + (float) $leaveBalance->sick_allowance;
            $usedLeaveCredits = (float) LeaveRequest::query()
                ->where('user_id', $user->id)
                ->whereIn('type', ['leave', 'vacation_leave', 'sick_leave'])
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->get()
                ->sum('days');
            $leaveCreditsRemaining = max($leaveAllowance - $usedLeaveCredits, 0);

            $today = Carbon::today();
            $overtimeEarnedMinutes = 0;
            $approvedOvertimeRequests = LeaveRequest::query()
                ->where('user_id', $user->id)
                ->where('type', 'overtime')
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->get(['reason']);
            foreach ($approvedOvertimeRequests as $requestItem) {
                $raw = (string) ($requestItem->reason ?? '');
                if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                    [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                    $overtimeEarnedMinutes += $h * 60 + $mPart;
                }
            }
            $offsetDeductHours = (float) LeaveRequest::query()
                ->where('user_id', $user->id)
                ->where('type', 'offset')
                ->where('status', 'approved')
                ->get()
                ->sum('offset_hours_needed');
            $overtimeBalanceMinutes = $overtimeEarnedMinutes - (int) round($offsetDeductHours * 60);
            $otSign = $overtimeBalanceMinutes < 0 ? '-' : '';
            $otAbs = abs($overtimeBalanceMinutes);
            $otH = intdiv($otAbs, 60);
            $otM = $otAbs % 60;

            $employeeLeaveSummary = [
                'total_requests' => (clone $employeeLeaveBaseQuery)->count(),
                'pending_requests' => (clone $employeeLeaveBaseQuery)->where('status', 'pending')->count(),
                'approved_requests' => (clone $employeeLeaveBaseQuery)->where('status', 'approved')->count(),
                'rejected_requests' => (clone $employeeLeaveBaseQuery)->where('status', 'rejected')->count(),
                'resubmission_requests' => (clone $employeeLeaveBaseQuery)->awaitingUserResubmission()->count(),
                'leave_credits_year' => (int) $currentYear,
                'leave_credits_allowance' => round($leaveAllowance, 2),
                'leave_credits_used' => round($usedLeaveCredits, 2),
                'leave_credits_remaining' => round($leaveCreditsRemaining, 2),
                'overtime_balance_minutes' => (int) $overtimeBalanceMinutes,
                'overtime_balance_formatted' => $otSign.sprintf('%02d:%02d', $otH, $otM),
            ];

            $employeeResubmissionRequests = LeaveRequest::query()
                ->where('user_id', $user->id)
                ->awaitingUserResubmission()
                ->latest('updated_at')
                ->with('reviewer')
                ->take(5)
                ->get();

            $months = collect(range(5, 0))->map(fn ($index) => now()->subMonths($index)->startOfMonth());
            $months = $months->push(now()->startOfMonth())->values();
            $monthlyLabels = $months->map(fn (Carbon $month) => $month->format('M Y'))->values();
            $monthlyCounts = $months->mapWithKeys(fn (Carbon $month) => [
                $month->format('Y-m') => 0,
            ]);

            $monthlyLeaveRows = LeaveRequest::query()
                ->where('user_id', $user->id)
                ->whereDate('created_at', '>=', $months->first()->toDateString())
                ->get(['created_at']);

            foreach ($monthlyLeaveRows as $row) {
                $key = Carbon::parse($row->created_at)->format('Y-m');
                if ($monthlyCounts->has($key)) {
                    $monthlyCounts[$key] = (int) $monthlyCounts[$key] + 1;
                }
            }

            $employeeLeaveCharts = [
                'status' => [
                    'labels' => ['Pending', 'Approved', 'Rejected', 'Resubmission'],
                    'values' => [
                        (int) ($employeeLeaveSummary['pending_requests'] ?? 0),
                        (int) ($employeeLeaveSummary['approved_requests'] ?? 0),
                        (int) ($employeeLeaveSummary['rejected_requests'] ?? 0),
                        (int) ($employeeLeaveSummary['resubmission_requests'] ?? 0),
                    ],
                ],
                'monthly' => [
                    'labels' => $monthlyLabels->all(),
                    'values' => array_values($monthlyCounts->all()),
                ],
            ];
        }

        return view('user.dashboard', compact(
            'canViewAssignedQuizzes',
            'allQuizzes',
            'assignedQuizzes',
            'completedQuizzes',
            'pendingQuizzes',
            'totalQuizzes',
            'ongoingQuiz',
            'evaluationAvailable',
            'evaluationFormTitle',
            'studentOjtAccessCountdown',
            'studentTrainingStats',
            'studentTrainingCharts',
            'studentResubmissionRequests',
            'studentLeaveBalanceSummary',
            'employeeLeaveSummary',
            'employeeLeaveCharts',
            'employeeResubmissionRequests'
        ));
    }

    /**
     * Display the Term of Reference (TOR) PDF in an iframe
     */
    public function tor()
    {
        return view('user.tor');
    }

    /**
     * Student confirms they have read and agree to the rules and regulations (this session).
     */
    public function acknowledgeRulesRegulations(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'student', 403);

        $request->session()->put('student_rules_regulations_pending', false);

        try {
            UserActivity::logActivity($user, 'action', 'rules_regulations_acknowledged', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'role' => $user->role,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log rules_regulations_acknowledged', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function teacherStudents(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->role === 'teacher', 403);

        $teacherData = $this->getTeacherSchoolData($user);
        $search = trim((string) $request->query('search', ''));
        $students = $teacherData['students'];

        if ($search !== '') {
            $keyword = mb_strtolower($search);
            $students = $students->filter(function ($student) use ($keyword) {
                $name = mb_strtolower((string) ($student->name ?? ''));
                $email = mb_strtolower((string) ($student->email ?? ''));

                return str_contains($name, $keyword) || str_contains($email, $keyword);
            })->values();
        }

        return view('user.teacher-students', [
            'students' => $students,
            'schoolName' => $teacherData['schoolName'],
            'search' => $search,
        ]);
    }

    public function teacherPendingApplications()
    {
        $user = auth()->user();
        abort_unless($user->role === 'teacher', 403);

        $university = $user->university;

        $applications = $this->hiringApplicationsForTeacherSchoolListingQuery($user)
            ->with(['hiringPosition'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 1 WHEN 'accepted' THEN 2 WHEN 'interview_scheduled' THEN 3 ELSE 4 END")
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('user.teacher-pending-applications', [
            'applications' => $applications,
            'schoolName' => $university?->name,
        ]);
    }

    /**
     * Student applications visible on the teacher list: pending, accepted, or interview scheduled
     * for applicants whose school field matches the teacher's university (see apply).
     */
    private function hiringApplicationsForTeacherSchoolListingQuery(User $teacher): Builder
    {
        $query = HiringApplication::query()->whereIn('status', [
            'pending',
            'accepted',
            'interview_scheduled',
        ]);
        $this->applyTeacherSchoolToHiringApplicationsQuery($query, $teacher);

        return $query;
    }

    private function applyTeacherSchoolToHiringApplicationsQuery(Builder $query, User $teacher): void
    {
        /** @var University|null $university */
        $university = $teacher->university;

        if (! $university instanceof University) {
            $query->whereRaw('1 = 0');

            return;
        }

        $fullName = $university->full_name;
        $nameTrim = trim((string) $university->name);
        $nameNorm = mb_strtolower($nameTrim);
        $nameLike = $nameTrim !== ''
            ? '%'.addcslashes($nameTrim, '%_\\').'%'
            : null;

        $query->where(function ($q) use ($fullName, $nameNorm, $nameLike): void {
            $q->where('school', $fullName);
            if ($nameNorm !== '') {
                $q->orWhereRaw('LOWER(TRIM(COALESCE(school, ?))) = ?', ['', $nameNorm]);
            }
            if ($nameLike !== null) {
                $q->orWhere('school', 'LIKE', $nameLike);
            }
        });
    }

    public function teacherNews()
    {
        $user = auth()->user();
        abort_unless($user->role === 'teacher', 403);

        // Mark announcements as seen when teacher opens the page.
        if (Schema::hasColumn('users', 'teacher_announcements_seen_at')) {
            if ($user->teacher_announcements_seen_at === null || $user->teacher_announcements_seen_at->lt(now()->subMinute())) {
                User::whereKey($user->id)->update([
                    'teacher_announcements_seen_at' => now(),
                ]);
                $user->teacher_announcements_seen_at = now();
            }
        }

        $news = News::query()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->latest('published_at')
            ->latest('created_at')
            ->paginate(10);

        return view('user.teacher-news', compact('news'));
    }

    public function teacherExcusedRequests(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->role === 'teacher', 403);

        $students = User::query()
            ->where('role', 'student')
            ->where('is_active', true)
            ->where('university_id', $user->university_id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 10;
        }

        $pastRequests = LeaveRequest::query()
            ->where('type', 'absent')
            ->whereHas('logs', function ($query) use ($user): void {
                $query->where('action', 'filed_by_teacher')
                    ->where('performed_by', $user->id);
            })
            ->with([
                'user:id,name,email',
                'logs' => function ($query) use ($user): void {
                    $query->where('action', 'filed_by_teacher')
                        ->where('performed_by', $user->id)
                        ->with('performer:id,name,email')
                        ->orderByDesc('created_at');
                },
            ])
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'requests_page')
            ->withQueryString();

        return view('user.teacher-excused-requests', [
            'students' => $students,
            'schoolName' => optional($user->university)->name,
            'pastRequests' => $pastRequests,
            'perPage' => $perPage,
        ]);
    }

    public function storeTeacherExcusedRequest(Request $request)
    {
        $teacher = auth()->user();
        abort_unless($teacher->role === 'teacher', 403);

        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) use ($teacher) {
                    $query
                        ->where('role', 'student')
                        ->where('is_active', true)
                        ->where('university_id', $teacher->university_id);
                }),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'supporting_documents' => ['nullable', 'array', 'max:5'],
            'supporting_documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        [$supportingPaths, $legacySupportingPath] = $this->storeSupportingDocumentsFromRequest($request);
        $createdCount = 0;

        foreach ($validated['student_ids'] as $studentId) {
            $student = User::query()
                ->where('id', $studentId)
                ->where('role', 'student')
                ->where('is_active', true)
                ->where('university_id', $teacher->university_id)
                ->first();

            if (! $student) {
                continue;
            }

            $leaveRequest = LeaveRequest::create([
                'user_id' => $student->id,
                'type' => 'absent',
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? $validated['start_date'],
                'reason' => trim("Teacher excused request by {$teacher->name} ({$teacher->email}).\n\n".$validated['reason']),
                'supporting_document_path' => $legacySupportingPath,
                'supporting_document_paths' => $supportingPaths,
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]);

            LeaveRequestLog::create([
                'leave_request_id' => $leaveRequest->id,
                'action' => 'filed_by_teacher',
                'status_before' => null,
                'status_after' => 'pending',
                'notes' => 'Filed by teacher on behalf of student',
                'performed_by' => $teacher->id,
            ]);

            $createdCount++;
        }

        if ($createdCount === 0) {
            return redirect()->back()
                ->withErrors(['student_ids' => 'No requests were created. Please select valid students from your assigned school.'])
                ->withInput();
        }

        return redirect(url('/teacher/excused-requests'))
            ->with('success', "Excused request submitted for {$createdCount} student(s). Waiting for admin review.");
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
                Log::warning('Teacher excused supporting document store failed, skipping file', [
                    'error' => $e->getMessage(),
                    'disk' => $disk,
                    'teacher_id' => auth()->id(),
                ]);
            }
        }

        return [$paths, $paths[0] ?? null];
    }

    private function getTeacherSchoolData(User $teacher): array
    {
        if (! $teacher->university_id) {
            return [
                'totalStudents' => 0,
                'activeStudents' => 0,
                'ongoingInternships' => 0,
                'completedInternships' => 0,
                'students' => collect(),
                'studentsApprovedAbsentRanking' => collect(),
                'schoolName' => null,
                'nextExitConferenceDate' => null,
                'pendingApplicationsCount' => 0,
                'charts' => [
                    'studentStatus' => [
                        'labels' => ['Active', 'Inactive'],
                        'values' => [0, 0],
                    ],
                    'internshipStatus' => [
                        'labels' => ['Ongoing', 'Completed / No Required Hours'],
                        'values' => [0, 0],
                    ],
                    'monthlyHours' => [
                        'labels' => [],
                        'values' => [],
                    ],
                    'internshipHoursSummary' => [
                        'labels' => ['Required', 'Logged', 'Remaining'],
                        'values' => [0, 0, 0],
                    ],
                ],
            ];
        }

        $studentBaseQuery = User::query()
            ->where('role', 'student')
            ->where('university_id', $teacher->university_id);

        $totalStudents = (clone $studentBaseQuery)->count();
        $activeStudents = (clone $studentBaseQuery)->where('is_active', true)->count();

        $dtrTotals = Dtr::query()
            ->selectRaw('user_id, COALESCE(SUM(total_hours), 0) as logged_hours')
            ->groupBy('user_id');

        $students = User::query()
            ->with(['department', 'university'])
            ->leftJoinSub($dtrTotals, 'dtr_totals', function ($join) {
                $join->on('dtr_totals.user_id', '=', 'users.id');
            })
            ->where('users.role', 'student')
            ->where('users.university_id', $teacher->university_id)
            ->select([
                'users.*',
                DB::raw('COALESCE(dtr_totals.logged_hours, 0) as logged_hours'),
            ])
            ->orderBy('users.name')
            ->get();

        $studentIds = $students->pluck('id')->filter()->values();
        $approvedAbsentDaysByStudent = collect();
        if ($studentIds->isNotEmpty()) {
            $approvedAbsentDaysByStudent = LeaveRequest::query()
                ->whereIn('user_id', $studentIds)
                ->where('type', 'absent')
                ->where('status', 'approved')
                ->get(['user_id', 'start_date', 'end_date'])
                ->groupBy('user_id')
                ->map(function ($requests) {
                    return (int) $requests->sum(function ($request) {
                        $start = Carbon::parse($request->start_date);
                        $end = $request->end_date ? Carbon::parse($request->end_date) : $start;

                        return $start->diffInDays($end) + 1;
                    });
                });
        }

        $recentDtrByStudent = collect();
        if ($studentIds->isNotEmpty()) {
            $recentDtrByStudent = Dtr::query()
                ->whereIn('user_id', $studentIds)
                ->whereDate('date', '>=', now()->subDays(30)->toDateString())
                ->where('total_hours', '>', 0)
                ->get(['user_id', 'date', 'total_hours'])
                ->groupBy('user_id');
        }

        $students = $students->map(function ($student) use ($recentDtrByStudent, $approvedAbsentDaysByStudent) {
            $required = (float) ($student->required_training_hours ?? 0);
            $logged = (float) ($student->logged_hours ?? 0);
            $remaining = max($required - $logged, 0);

            $recentEntries = $recentDtrByStudent->get($student->id, collect());
            $activeDays = $recentEntries->pluck('date')->unique()->count();
            $avgHoursPerDay = $activeDays > 0
                ? ((float) $recentEntries->sum('total_hours')) / $activeDays
                : 0.0;

            $estimatedEndDate = null;
            if ($remaining > 0 && $avgHoursPerDay > 0) {
                $neededDays = (int) ceil($remaining / $avgHoursPerDay);
                $estimatedEndDate = now()->addWeekdays($neededDays)->toDateString();
            }

            $student->remaining_hours = $remaining;
            $student->estimated_end_date = $estimatedEndDate;
            $student->approved_absent_days = (int) ($approvedAbsentDaysByStudent[$student->id] ?? 0);

            return $student;
        });

        $ongoingInternships = $students->filter(function ($student) {
            $required = (float) ($student->required_training_hours ?? 0);
            $logged = (float) ($student->logged_hours ?? 0);

            return $required > 0 && $logged < $required;
        })->count();
        $completedInternships = max($totalStudents - $ongoingInternships, 0);
        $totalRequiredHours = (float) $students->sum(fn ($student) => (float) ($student->required_training_hours ?? 0));
        $totalLoggedHours = (float) $students->sum(fn ($student) => (float) ($student->logged_hours ?? 0));
        $totalRemainingHours = max($totalRequiredHours - $totalLoggedHours, 0);

        $months = collect(range(5, 0))->map(fn ($index) => now()->subMonths($index)->startOfMonth());
        $months = $months->push(now()->startOfMonth())->values();
        $monthlyLabels = $months->map(fn (Carbon $month) => $month->format('M Y'))->values();
        $monthlyHours = $months->mapWithKeys(fn (Carbon $month) => [
            $month->format('Y-m') => 0.0,
        ]);

        if ($studentIds->isNotEmpty()) {
            $dtrRows = Dtr::query()
                ->whereIn('user_id', $studentIds)
                ->whereDate('date', '>=', $months->first()->toDateString())
                ->get(['date', 'total_hours']);

            foreach ($dtrRows as $row) {
                $key = Carbon::parse($row->date)->format('Y-m');
                if ($monthlyHours->has($key)) {
                    $monthlyHours[$key] = (float) $monthlyHours[$key] + (float) ($row->total_hours ?? 0);
                }
            }
        }

        $studentsApprovedAbsentRanking = $students
            ->sort(function (User $a, User $b) {
                $ca = (int) ($a->approved_absent_days ?? 0);
                $cb = (int) ($b->approved_absent_days ?? 0);
                if ($ca !== $cb) {
                    return $cb <=> $ca;
                }

                return strcasecmp((string) $a->name, (string) $b->name);
            })
            ->values();

        $nextExitConferenceDate = null;
        $today = now()->startOfDay();
        foreach ($students as $student) {
            if (empty($student->ojt_target_end_date)) {
                continue;
            }
            $d = Carbon::parse($student->ojt_target_end_date)->startOfDay();
            if ($d->lt($today)) {
                continue;
            }
            if ($nextExitConferenceDate === null || $d->lt($nextExitConferenceDate)) {
                $nextExitConferenceDate = $d;
            }
        }

        $teacher->loadMissing('university');
        $pendingApplicationsCount = $this->hiringApplicationsForTeacherSchoolListingQuery($teacher)->count();

        $charts = [
            'studentStatus' => [
                'labels' => ['Active', 'Inactive'],
                'values' => [$activeStudents, max($totalStudents - $activeStudents, 0)],
            ],
            'internshipStatus' => [
                'labels' => ['Ongoing', 'Completed'],
                'values' => [$ongoingInternships, $completedInternships],
            ],
            'monthlyHours' => [
                'labels' => $monthlyLabels->all(),
                'values' => array_map(
                    static fn ($value) => round((float) $value, 2),
                    array_values($monthlyHours->all())
                ),
            ],
            'internshipHoursSummary' => [
                'labels' => ['Required', 'Logged', 'Remaining'],
                'values' => [
                    round($totalRequiredHours, 2),
                    round($totalLoggedHours, 2),
                    round($totalRemainingHours, 2),
                ],
            ],
        ];

        return [
            'totalStudents' => $totalStudents,
            'activeStudents' => $activeStudents,
            'ongoingInternships' => $ongoingInternships,
            'completedInternships' => $completedInternships,
            'students' => $students,
            'studentsApprovedAbsentRanking' => $studentsApprovedAbsentRanking,
            'nextExitConferenceDate' => $nextExitConferenceDate,
            'pendingApplicationsCount' => $pendingApplicationsCount,
            'schoolName' => optional($teacher->university)->name,
            'charts' => $charts,
        ];
    }
}
