<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Dtr;
use App\Models\EvaluationForm;
use App\Models\EvaluationSubmission;
use App\Models\News;
use App\Models\QuizAssignment;
use App\Models\TicketReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $evaluationAvailable = false;
        $evaluationFormTitle = null;

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
                ->whereHas('quiz', function($query) {
                    $query->where('is_active', true);
                })
                ->get();

            $allQuizzes = $assignedQuizzes->map(function($assignment) {
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
            // For other roles (student, employee), show all active quizzes
            $allQuizzes = \App\Models\Quiz::where('is_active', true)
                ->with(['creator', 'assignments' => function($query) {
                    $query->where('user_id', auth()->id());
                }])
                ->get();

            // Get assigned quizzes for statistics
            $assignedQuizzes = QuizAssignment::where('user_id', $user->id)
                ->with(['quiz.creator'])
                ->whereHas('quiz', function($query) {
                    $query->where('is_active', true);
                })
                ->get();

            $completedQuizzes = $assignedQuizzes->where('is_completed', true)->count();
            $pendingQuizzes = $assignedQuizzes->where('is_completed', false)->count();
            $totalQuizzes = $allQuizzes->count(); // Show total available quizzes

            // Get ongoing quiz (in progress)
            $ongoingQuiz = QuizAssignment::where('user_id', $user->id)
                ->where('status', 'in_progress')
                ->where('is_completed', false)
                ->with('quiz')
                ->first();
        }

        if ($user->role === 'student' && Schema::hasTable('evaluation_forms') && Schema::hasTable('evaluation_submissions')) {
            $requiredHours = (float) ($user->required_training_hours ?? 0);
            $loggedHours = (float) Dtr::query()->where('user_id', $user->id)->sum('total_hours');
            $activeEvaluationForm = EvaluationForm::query()->where('is_active', true)->latest('updated_at')->first();

            $isForced = Schema::hasColumn('users', 'evaluation_forced_at') && !empty($user->evaluation_forced_at);
            if ($activeEvaluationForm && (($requiredHours > 0 && $loggedHours >= $requiredHours) || $isForced)) {
                $submitted = EvaluationSubmission::query()
                    ->where('evaluation_form_id', $activeEvaluationForm->id)
                    ->where('user_id', $user->id)
                    ->exists();
                $evaluationAvailable = !$submitted;
                $evaluationFormTitle = $activeEvaluationForm->title;
            }
        }

        return view('user.dashboard', compact(
            'allQuizzes',
            'assignedQuizzes',
            'completedQuizzes',
            'pendingQuizzes',
            'totalQuizzes',
            'ongoingQuiz',
            'evaluationAvailable',
            'evaluationFormTitle'
        ));
    }

    /**
     * Display the Term of Reference (TOR) PDF in an iframe
     */
    public function tor()
    {
        return view('user.tor');
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
        if (!$teacher->university_id) {
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
