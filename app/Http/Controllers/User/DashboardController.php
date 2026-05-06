<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Dtr;
use App\Models\EvaluationForm;
use App\Models\EvaluationSubmission;
use App\Models\LeaveRequest;
use App\Models\News;
use App\Models\QuizAssignment;
use App\Models\TicketReport;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\LeaveBalance;
use App\Models\Setting;
use App\Services\StudentOjtPostCompletionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
                'activeStudents' => $teacherData['activeStudents'],
                'ongoingInternships' => $teacherData['ongoingInternships'],
                'schoolName' => $teacherData['schoolName'],
                'teacherCharts' => $teacherData['charts'],
            ]);
        }

        // For applicants, only show assigned quizzes (not all available quizzes)
        if ($user->role === 'applicant') {
            // Get only assigned quizzes for applicants
            $assignedQuizzes = QuizAssignment::where('user_id', $user->id)
                ->with(['quiz.creator'])
                ->whereHas('quiz', function ($query) {
                    $query->where('is_active', true);
                })
                ->get();

            $allQuizzes = $assignedQuizzes->map(function ($assignment) {
                return $assignment->quiz;
            });

            $completedQuizzes = $assignedQuizzes->where('is_completed', true)->count();
            $pendingQuizzes = $assignedQuizzes->where('is_completed', false)->count();
            $totalQuizzes = $assignedQuizzes->count(); // Only count assigned quizzes

            // Get ongoing quiz (in progress)
            $ongoingQuiz = QuizAssignment::where('user_id', $user->id)
                ->where('status', 'in_progress')
                ->where('is_completed', false)
                ->with('quiz')
                ->first();
        } else {
            // Students, employees, etc.: only active quizzes that have been assigned to this user
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

            // Get ongoing quiz (in progress)
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

            $approvedLeaveCount = (int) LeaveRequest::query()
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->count();
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
                'approved_leave_count' => $approvedLeaveCount,
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

    private function getTeacherSchoolData(User $teacher): array
    {
        if (! $teacher->university_id) {
            return [
                'totalStudents' => 0,
                'activeStudents' => 0,
                'ongoingInternships' => 0,
                'students' => collect(),
                'schoolName' => null,
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

        $recentDtrByStudent = collect();
        if ($studentIds->isNotEmpty()) {
            $recentDtrByStudent = Dtr::query()
                ->whereIn('user_id', $studentIds)
                ->whereDate('date', '>=', now()->subDays(30)->toDateString())
                ->where('total_hours', '>', 0)
                ->get(['user_id', 'date', 'total_hours'])
                ->groupBy('user_id');
        }

        $students = $students->map(function ($student) use ($recentDtrByStudent) {
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

            return $student;
        });

        $ongoingInternships = $students->filter(function ($student) {
            $required = (float) ($student->required_training_hours ?? 0);
            $logged = (float) ($student->logged_hours ?? 0);

            return $required > 0 && $logged < $required;
        })->count();
        $completedInternships = max($totalStudents - $ongoingInternships, 0);

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

        $charts = [
            'studentStatus' => [
                'labels' => ['Active', 'Inactive'],
                'values' => [$activeStudents, max($totalStudents - $activeStudents, 0)],
            ],
            'internshipStatus' => [
                'labels' => ['Ongoing', 'Completed / No Required Hours'],
                'values' => [$ongoingInternships, $completedInternships],
            ],
            'monthlyHours' => [
                'labels' => $monthlyLabels->all(),
                'values' => array_map(
                    static fn ($value) => round((float) $value, 2),
                    array_values($monthlyHours->all())
                ),
            ],
        ];

        return [
            'totalStudents' => $totalStudents,
            'activeStudents' => $activeStudents,
            'ongoingInternships' => $ongoingInternships,
            'students' => $students,
            'schoolName' => optional($teacher->university)->name,
            'charts' => $charts,
        ];
    }
}
