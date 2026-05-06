<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfessionComment;
use App\Models\ConfessionPost;
use App\Models\ContactMessage;
use App\Models\Dtr;
use App\Models\DtrDeficit;
use App\Models\ErrorLog;
use App\Models\HiringApplication;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\TicketReport;
use App\Models\University;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\UserSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Allow access for super admins or any user with at least one admin permission.
     */
    private function ensureCanAccessDashboard(): void
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            abort(403, 'You do not have permission to view the Admin Dashboard.');
        }
        if ($user->isSuperAdmin() || $user->hasAnyAdminPermission()) {
            return;
        }
        abort(403, 'You do not have permission to view the Admin Dashboard.');
    }

    /**
     * Get chart period from request (day, week, month, year, or custom).
     */
    private function getChartPeriod(): string
    {
        $period = request('chart_period', 'week');

        return in_array($period, ['day', 'week', 'month', 'year', 'custom']) ? $period : 'week';
    }

    /**
     * Get custom date range from request. Returns [from, to] or null if invalid.
     */
    private function getCustomChartRange(): ?array
    {
        $from = request('chart_from');
        $to = request('chart_to');
        if (empty($from) || empty($to)) {
            return null;
        }
        try {
            $fromDate = Carbon::parse($from)->startOfDay();
            $toDate = Carbon::parse($to)->endOfDay();
            if ($fromDate->gt($toDate)) {
                return null;
            }

            return [$fromDate, $toDate];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get date ranges for charts based on period or custom from/to.
     * Returns array of [['label' => ..., 'date' => Carbon, 'start' => ..., 'end' => ...], ...]
     */
    private function getChartDateRanges(string $period): array
    {
        // Custom from/to range
        $customRange = $this->getCustomChartRange();
        if ($customRange !== null) {
            [$fromDate, $toDate] = $customRange;
            $daysDiff = $fromDate->diffInDays($toDate) + 1;
            $ranges = [];

            if ($daysDiff <= 31) {
                // Daily buckets
                for ($date = $fromDate->copy(); $date->lte($toDate); $date->addDay()) {
                    $ranges[] = [
                        'label' => $date->format('M j'),
                        'date' => $date->copy(),
                        'start' => $date->copy()->startOfDay(),
                        'end' => $date->copy()->endOfDay(),
                    ];
                }
            } else {
                // Monthly buckets for longer ranges
                $current = $fromDate->copy()->startOfMonth();
                while ($current->lte($toDate)) {
                    $monthEnd = $current->copy()->endOfMonth();
                    $end = $monthEnd->gt($toDate) ? $toDate->copy() : $monthEnd;
                    $ranges[] = [
                        'label' => $current->format('M Y'),
                        'date' => $current->copy(),
                        'start' => $current->copy()->startOfMonth(),
                        'end' => $end,
                    ];
                    $current->addMonth()->startOfMonth();
                }
            }

            return $ranges;
        }

        $ranges = [];
        if ($period === 'day') {
            for ($h = 23; $h >= 0; $h--) {
                $dt = now()->subHours($h);
                $ranges[] = [
                    'label' => $dt->format('g a'),
                    'date' => $dt,
                    'start' => $dt->copy()->startOfHour(),
                    'end' => $dt->copy()->endOfHour(),
                ];
            }
        } elseif ($period === 'week') {
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $ranges[] = [
                    'label' => $date->format('M j'),
                    'date' => $date,
                    'start' => $date->copy()->startOfDay(),
                    'end' => $date->copy()->endOfDay(),
                ];
            }
        } elseif ($period === 'month') {
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $ranges[] = [
                    'label' => $date->format('M j'),
                    'date' => $date,
                    'start' => $date->copy()->startOfDay(),
                    'end' => $date->copy()->endOfDay(),
                ];
            }
        } else {
            // year: 12 months
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $ranges[] = [
                    'label' => $date->format('M Y'),
                    'date' => $date,
                    'start' => $date->copy()->startOfMonth(),
                    'end' => $date->copy()->endOfMonth(),
                ];
            }
        }

        return $ranges;
    }

    public function index()
    {
        $this->ensureCanAccessDashboard();

        try {
            $totalUsers = User::where('role', 'user')->count();
            $totalQuizzes = Quiz::count();
            $disabledUsers = User::where('role', 'user')->where('is_active', false)->count();
            $activeUsers = User::where('role', 'user')->where('is_active', true)->count();

            // Employee and Student Statistics
            $totalEmployees = User::where('role', 'employee')->count();
            $totalStudents = User::where('role', 'student')->count();
            $activeEmployees = User::where('role', 'employee')->where('is_active', true)->count();
            $activeStudents = User::where('role', 'student')->where('is_active', true)->count();

            // DTR Statistics - with error handling
            try {
                $totalDtrRecords = Dtr::count();
                $employeeDtrRecords = Dtr::whereHas('user', function ($q) {
                    $q->where('role', 'employee');
                })->count();
                $studentDtrRecords = Dtr::whereHas('user', function ($q) {
                    $q->where('role', 'student');
                })->count();
                $todayDtrRecords = Dtr::whereDate('date', today())->count();
            } catch (\Exception $e) {
                Log::warning('DTR statistics error: '.$e->getMessage());
                $totalDtrRecords = 0;
                $employeeDtrRecords = 0;
                $studentDtrRecords = 0;
                $todayDtrRecords = 0;
            }

            // Leave Request Statistics - with error handling
            try {
                $totalLeaveRequests = LeaveRequest::count();
                $pendingLeaveRequests = LeaveRequest::where('status', 'pending')->count();
                $approvedLeaveRequests = LeaveRequest::where('status', 'approved')->count();
                $rejectedLeaveRequests = LeaveRequest::where('status', 'rejected')->count();
                $employeeLeaveRequests = LeaveRequest::whereHas('user', function ($q) {
                    $q->where('role', 'employee');
                })->where('status', 'pending')->count();
                $studentLeaveRequests = LeaveRequest::whereHas('user', function ($q) {
                    $q->where('role', 'student');
                })->where('status', 'pending')->count();

                // Recent Leave Requests
                $recentLeaveRequests = LeaveRequest::with('user')
                    ->latest()
                    ->take(5)
                    ->get();

                // Ongoing and upcoming leave (approved, end_date >= today - all leave that extends into today or future)
                $today = now()->toDateString();
                $ongoingLeaveEmployees = LeaveRequest::with('user')
                    ->where('status', 'approved')
                    ->where('end_date', '>=', $today)
                    ->whereHas('user', fn ($q) => $q->where('role', 'employee'))
                    ->orderBy('start_date')
                    ->get();
                $ongoingLeaveStudents = LeaveRequest::with('user')
                    ->where('status', 'approved')
                    ->where('end_date', '>=', $today)
                    ->whereHas('user', fn ($q) => $q->where('role', 'student'))
                    ->orderBy('start_date')
                    ->get();
            } catch (\Exception $e) {
                Log::warning('Leave request statistics error: '.$e->getMessage());
                $totalLeaveRequests = 0;
                $pendingLeaveRequests = 0;
                $approvedLeaveRequests = 0;
                $rejectedLeaveRequests = 0;
                $employeeLeaveRequests = 0;
                $studentLeaveRequests = 0;
                $recentLeaveRequests = collect();
                $ongoingLeaveEmployees = collect();
                $ongoingLeaveStudents = collect();
            }

            // Students/Employees with time remaining (DTR deficit)
            try {
                $studentsWithDeficit = User::where('role', 'student')
                    ->whereIn('id', DtrDeficit::where('deficit_hours', '>', 0)->select('user_id')->distinct()->pluck('user_id'))
                    ->get()
                    ->map(function ($user) {
                        $user->total_deficit_hours = DtrDeficit::where('user_id', $user->id)->where('deficit_hours', '>', 0)->sum('deficit_hours');

                        return $user;
                    })
                    ->sortByDesc('total_deficit_hours')
                    ->take(10)
                    ->values();
                $employeesWithDeficit = User::where('role', 'employee')
                    ->whereIn('id', DtrDeficit::where('deficit_hours', '>', 0)->select('user_id')->distinct()->pluck('user_id'))
                    ->get()
                    ->map(function ($user) {
                        $user->total_deficit_hours = DtrDeficit::where('user_id', $user->id)->where('deficit_hours', '>', 0)->sum('deficit_hours');

                        return $user;
                    })
                    ->sortByDesc('total_deficit_hours')
                    ->take(10)
                    ->values();
            } catch (\Exception $e) {
                Log::warning('Deficit stats error: '.$e->getMessage());
                $studentsWithDeficit = collect();
                $employeesWithDeficit = collect();
            }

            // Active students with a required training target whose logged DTR time is still below it
            try {
                $userTable = (new User)->getTable();
                $dtrTable = (new Dtr)->getTable();
                $studentsWithTrainingRequirementCount = User::where('role', 'student')
                    ->where('is_active', true)
                    ->where('required_training_hours', '>', 0)
                    ->count();
                $studentsIncompleteTrainingCount = User::where('role', 'student')
                    ->where('is_active', true)
                    ->where('required_training_hours', '>', 0)
                    ->whereRaw(
                        "COALESCE((SELECT SUM({$dtrTable}.total_hours) FROM {$dtrTable} WHERE {$dtrTable}.user_id = {$userTable}.id), 0) < {$userTable}.required_training_hours"
                    )
                    ->count();
            } catch (\Exception $e) {
                Log::warning('Student training completion stats error: '.$e->getMessage());
                $studentsWithTrainingRequirementCount = 0;
                $studentsIncompleteTrainingCount = 0;
            }

            // Recent Quizzes and Users - with error handling
            try {
                $recentQuizzes = Quiz::with('creator')->latest()->take(5)->get();
                $recentUsers = User::where('role', '!=', 'admin')->latest()->take(5)->get();
            } catch (\Exception $e) {
                Log::warning('Recent items error: '.$e->getMessage());
                $recentQuizzes = collect();
                $recentUsers = collect();
            }

            $quizStats = [
                'total' => $totalQuizzes,
                'active' => Quiz::where('is_active', true)->count(),
                'inactive' => Quiz::where('is_active', false)->count(),
            ];

            $userStats = [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'disabled' => $disabledUsers,
            ];

            // Top Students by Total Score - with error handling
            try {
                $topStudents = User::where('role', 'user')
                    ->with(['university'])
                    ->withSum('quizAttemptHistory', 'score')
                    ->withCount('quizAttemptHistory')
                    ->get()
                    ->filter(function ($user) {
                        return ($user->quiz_attempt_history_sum_score ?? 0) > 0;
                    })
                    ->sortByDesc('quiz_attempt_history_sum_score')
                    ->values();
            } catch (\Exception $e) {
                Log::warning('Top students error: '.$e->getMessage());
                $topStudents = collect();
            }

            // University Student Count Ranking - with error handling
            try {
                $universityRanking = University::withCount('users')
                    ->orderBy('users_count', 'desc')
                    ->get();
            } catch (\Exception $e) {
                Log::warning('University ranking error: '.$e->getMessage());
                $universityRanking = collect();
            }

            // Quiz Popularity Ranking - with error handling
            try {
                $quizPopularity = Quiz::select('quizzes.*', DB::raw('COUNT(DISTINCT quiz_attempts.user_id) as student_count'))
                    ->leftJoin('quiz_attempts', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
                    ->groupBy('quizzes.id')
                    ->orderBy('student_count', 'desc')
                    ->get();
            } catch (\Exception $e) {
                Log::warning('Quiz popularity error: '.$e->getMessage());
                $quizPopularity = collect();
            }

            // Quiz Performance Ranking - with error handling
            try {
                $quizPerformance = Quiz::select('quizzes.*',
                    DB::raw('COUNT(DISTINCT quiz_attempts.user_id) as student_count'),
                    DB::raw('COALESCE(AVG(quiz_attempts.points_earned), 0) as average_score'),
                    DB::raw('COALESCE(MAX(quiz_attempts.points_earned), 0) as highest_score')
                )
                    ->leftJoin('quiz_attempts', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
                    ->groupBy('quizzes.id')
                    ->orderBy('average_score', 'desc')
                    ->get();
            } catch (\Exception $e) {
                Log::warning('Quiz performance error: '.$e->getMessage());
                $quizPerformance = collect();
            }

            // Most Active Users ranking: based on Activity Logs, total count of actions per user (all time)
            try {
                $mostActiveUsers = UserActivity::selectRaw('user_id, COUNT(*) as action_count')
                    ->with('user')
                    ->whereHas('user', fn ($q) => $q->where('role', '!=', 'admin'))
                    ->groupBy('user_id')
                    ->orderBy('action_count', 'desc')
                    ->limit(10)
                    ->get();
            } catch (\Exception $e) {
                Log::warning('Most active users error: '.$e->getMessage());
                $mostActiveUsers = collect();
            }

            // User Activity Statistics - with error handling
            try {
                $activityStats = UserSession::getSessionStats();
                $onlineUsers = UserSession::getOnlineUsers();
                $recentActivities = UserActivity::getRecentActivities(20, true);
                $todayLogins = UserActivity::getTodayLoginCount();
                $todayLogouts = UserActivity::getTodayLogoutCount();
            } catch (\Exception $e) {
                Log::warning('User activity statistics error: '.$e->getMessage());
                // Fallback values if activity tracking fails
                $activityStats = ['online' => 0, 'idle' => 0, 'offline' => 0, 'total_today' => 0];
                $onlineUsers = collect();
                $recentActivities = collect();
                $todayLogins = 0;
                $todayLogouts = 0;
            }

            // System-wide stats (Say-it, Contact, Notifications, Tickets, Hiring)
            try {
                $confessionPostsCount = ConfessionPost::count();
                $confessionCommentsCount = ConfessionComment::count();
                $contactMessagesCount = ContactMessage::count();
                $notificationsCount = Notification::count();
                $openTicketsCount = TicketReport::where('status', 'open')->count();
                $pendingHiringCount = HiringApplication::whereIn('status', ['pending', 'screening'])->count();
            } catch (\Exception $e) {
                Log::warning('System stats error: '.$e->getMessage());
                $confessionPostsCount = 0;
                $confessionCommentsCount = 0;
                $contactMessagesCount = 0;
                $notificationsCount = 0;
                $openTicketsCount = 0;
                $pendingHiringCount = 0;
            }

            $chartPeriod = $this->getChartPeriod();
            $customRange = $this->getCustomChartRange();
            if ($customRange !== null) {
                $chartPeriod = 'custom';
            } elseif ($chartPeriod === 'custom') {
                $chartPeriod = 'week'; // Fallback if custom requested but from/to invalid
            }
            $chartFrom = request('chart_from', now()->subDays(6)->format('Y-m-d'));
            $chartTo = request('chart_to', now()->format('Y-m-d'));
            $chartRanges = $this->getChartDateRanges($chartPeriod);

            // Chart data: Logins (filtered by period)
            try {
                $loginChartLabels = [];
                $loginChartData = [];
                foreach ($chartRanges as $r) {
                    $loginChartLabels[] = $r['label'];
                    $loginChartData[] = UserActivity::where('activity_type', 'login')
                        ->whereBetween('created_at', [$r['start'], $r['end']])
                        ->count();
                }
            } catch (\Exception $e) {
                $loginChartLabels = array_column($chartRanges, 'label');
                $loginChartData = array_fill(0, count($chartRanges), 0);
            }

            // Chart data: Quiz attempts (filtered by period)
            try {
                $quizAttemptChartLabels = [];
                $quizAttemptChartData = [];
                foreach ($chartRanges as $r) {
                    $quizAttemptChartLabels[] = $r['label'];
                    $quizAttemptChartData[] = QuizAttempt::whereBetween('created_at', [$r['start'], $r['end']])->count();
                }
            } catch (\Exception $e) {
                $quizAttemptChartLabels = array_column($chartRanges, 'label');
                $quizAttemptChartData = array_fill(0, count($chartRanges), 0);
            }

            // Chart data: User registrations (filtered by period)
            try {
                $userRegChartLabels = [];
                $userRegChartData = [];
                foreach ($chartRanges as $r) {
                    $userRegChartLabels[] = $r['label'];
                    $userRegChartData[] = User::whereBetween('created_at', [$r['start'], $r['end']])->count();
                }
            } catch (\Exception $e) {
                $userRegChartLabels = array_column($chartRanges, 'label');
                $userRegChartData = array_fill(0, count($chartRanges), 0);
            }

            // Chart data: Students vs Employees registrations (filtered by period)
            try {
                $studentRegChartLabels = [];
                $studentRegChartData = [];
                $employeeRegChartData = [];
                foreach ($chartRanges as $r) {
                    $studentRegChartLabels[] = $r['label'];
                    $studentRegChartData[] = User::where('role', 'student')->whereBetween('created_at', [$r['start'], $r['end']])->count();
                    $employeeRegChartData[] = User::where('role', 'employee')->whereBetween('created_at', [$r['start'], $r['end']])->count();
                }
            } catch (\Exception $e) {
                $studentRegChartLabels = array_column($chartRanges, 'label');
                $studentRegChartData = array_fill(0, count($chartRanges), 0);
                $employeeRegChartData = array_fill(0, count($chartRanges), 0);
            }

            // Chart data: Error logs (filtered by period)
            try {
                $errorLogChartLabels = [];
                $errorLogChartData = [];
                foreach ($chartRanges as $r) {
                    $errorLogChartLabels[] = $r['label'];
                    $errorLogChartData[] = ErrorLog::whereBetween('created_at', [$r['start'], $r['end']])->count();
                }
            } catch (\Exception $e) {
                $errorLogChartLabels = array_column($chartRanges, 'label');
                $errorLogChartData = array_fill(0, count($chartRanges), 0);
            }

            // Chart data: DTR records (filtered by period) - DTR uses date column
            try {
                $dtrChartLabels = [];
                $dtrEmployeeChartData = [];
                $dtrStudentChartData = [];
                foreach ($chartRanges as $r) {
                    $dtrChartLabels[] = $r['label'];
                    $dtrEmployeeChartData[] = Dtr::whereBetween('date', [$r['start'], $r['end']])
                        ->whereHas('user', fn ($q) => $q->where('role', 'employee'))
                        ->count();
                    $dtrStudentChartData[] = Dtr::whereBetween('date', [$r['start'], $r['end']])
                        ->whereHas('user', fn ($q) => $q->where('role', 'student'))
                        ->count();
                }
            } catch (\Exception $e) {
                $dtrChartLabels = array_column($chartRanges, 'label');
                $dtrEmployeeChartData = array_fill(0, count($chartRanges), 0);
                $dtrStudentChartData = array_fill(0, count($chartRanges), 0);
            }

            // Chart data: Leave requests by status - Employees (doughnut chart)
            try {
                $leaveRequestEmployeeLabels = ['Pending', 'Approved', 'Rejected'];
                $leaveRequestEmployeeData = [
                    LeaveRequest::where('status', 'pending')->whereHas('user', fn ($q) => $q->where('role', 'employee'))->count(),
                    LeaveRequest::where('status', 'approved')->whereHas('user', fn ($q) => $q->where('role', 'employee'))->count(),
                    LeaveRequest::where('status', 'rejected')->whereHas('user', fn ($q) => $q->where('role', 'employee'))->count(),
                ];
            } catch (\Exception $e) {
                $leaveRequestEmployeeLabels = ['Pending', 'Approved', 'Rejected'];
                $leaveRequestEmployeeData = [0, 0, 0];
            }

            // Chart data: Leave requests by status - Students (doughnut chart)
            try {
                $leaveRequestStudentLabels = ['Pending', 'Approved', 'Rejected'];
                $leaveRequestStudentData = [
                    LeaveRequest::where('status', 'pending')->whereHas('user', fn ($q) => $q->where('role', 'student'))->count(),
                    LeaveRequest::where('status', 'approved')->whereHas('user', fn ($q) => $q->where('role', 'student'))->count(),
                    LeaveRequest::where('status', 'rejected')->whereHas('user', fn ($q) => $q->where('role', 'student'))->count(),
                ];
            } catch (\Exception $e) {
                $leaveRequestStudentLabels = ['Pending', 'Approved', 'Rejected'];
                $leaveRequestStudentData = [0, 0, 0];
            }

            // Chart data: User Activity Logs - total activities (filtered by period)
            try {
                $activityLogLabels = [];
                $activityLogTotalData = [];
                $activityLogGuestTrafficData = [];
                foreach ($chartRanges as $r) {
                    $activityLogLabels[] = $r['label'];
                    $activityLogTotalData[] = UserActivity::whereBetween('created_at', [$r['start'], $r['end']])->count();
                    $activityLogGuestTrafficData[] = UserActivity::where('activity_type', 'page_view')
                        ->whereNull('user_id')
                        ->whereBetween('created_at', [$r['start'], $r['end']])
                        ->count();
                }
            } catch (\Exception $e) {
                $activityLogLabels = array_column($chartRanges, 'label');
                $activityLogTotalData = array_fill(0, count($chartRanges), 0);
                $activityLogGuestTrafficData = array_fill(0, count($chartRanges), 0);
            }

            // Chart data: User Activity Logs by type (filtered by period)
            try {
                $activityLogByTypeLabels = [];
                $activityLogLoginData = [];
                $activityLogLogoutData = [];
                $activityLogPageViewData = [];
                $activityLogGuestPageViewData = [];
                foreach ($chartRanges as $r) {
                    $activityLogByTypeLabels[] = $r['label'];
                    $activityLogLoginData[] = UserActivity::where('activity_type', 'login')->whereBetween('created_at', [$r['start'], $r['end']])->count();
                    $activityLogLogoutData[] = UserActivity::where('activity_type', 'logout')->whereBetween('created_at', [$r['start'], $r['end']])->count();
                    $activityLogPageViewData[] = UserActivity::where('activity_type', 'page_view')->whereBetween('created_at', [$r['start'], $r['end']])->count();
                    $activityLogGuestPageViewData[] = UserActivity::where('activity_type', 'page_view')
                        ->whereNull('user_id')
                        ->whereBetween('created_at', [$r['start'], $r['end']])
                        ->count();
                }
            } catch (\Exception $e) {
                $activityLogByTypeLabels = array_column($chartRanges, 'label');
                $activityLogLoginData = array_fill(0, count($chartRanges), 0);
                $activityLogLogoutData = array_fill(0, count($chartRanges), 0);
                $activityLogPageViewData = array_fill(0, count($chartRanges), 0);
                $activityLogGuestPageViewData = array_fill(0, count($chartRanges), 0);
            }

            // Chart data: Login time trend - logins per bucket (filtered by period)
            try {
                $loginTimeLabels = [];
                $loginTimeData = [];
                foreach ($chartRanges as $r) {
                    $loginTimeLabels[] = $r['label'];
                    $loginTimeData[] = UserActivity::where('activity_type', 'login')
                        ->whereBetween('created_at', [$r['start'], $r['end']])
                        ->count();
                }
            } catch (\Exception $e) {
                $loginTimeLabels = array_column($chartRanges, 'label');
                $loginTimeData = array_fill(0, count($chartRanges), 0);
            }

            // Chart data: Activity type breakdown (pie chart - uses period range)
            try {
                $periodStart = $chartRanges[0]['start'] ?? now()->subDays(7);
                $periodEnd = end($chartRanges)['end'] ?? now();
                $activityTypes = UserActivity::whereBetween('created_at', [$periodStart, $periodEnd])
                    ->select('activity_type', DB::raw('count(*) as count'))
                    ->groupBy('activity_type')
                    ->orderByDesc('count')
                    ->get();
                $activityTypeLabels = $activityTypes->pluck('activity_type')->map(fn ($t) => ucfirst(str_replace('_', ' ', $t)))->values()->all();
                $activityTypeData = $activityTypes->pluck('count')->values()->all();
                if (empty($activityTypeLabels)) {
                    $activityTypeLabels = ['Login', 'Logout'];
                    $activityTypeData = [0, 0];
                }
            } catch (\Exception $e) {
                $activityTypeLabels = ['Login', 'Logout'];
                $activityTypeData = [0, 0];
            }

            return view('admin.dashboard', compact(
                'chartPeriod',
                'chartFrom',
                'chartTo',
                'totalUsers',
                'totalQuizzes',
                'disabledUsers',
                'activeUsers',
                'totalEmployees',
                'totalStudents',
                'activeEmployees',
                'activeStudents',
                'totalDtrRecords',
                'employeeDtrRecords',
                'studentDtrRecords',
                'todayDtrRecords',
                'totalLeaveRequests',
                'pendingLeaveRequests',
                'approvedLeaveRequests',
                'rejectedLeaveRequests',
                'employeeLeaveRequests',
                'studentLeaveRequests',
                'recentLeaveRequests',
                'ongoingLeaveEmployees',
                'ongoingLeaveStudents',
                'studentsWithDeficit',
                'studentsWithTrainingRequirementCount',
                'studentsIncompleteTrainingCount',
                'employeesWithDeficit',
                'recentQuizzes',
                'recentUsers',
                'quizStats',
                'userStats',
                'topStudents',
                'universityRanking',
                'quizPopularity',
                'quizPerformance',
                'activityStats',
                'onlineUsers',
                'recentActivities',
                'todayLogins',
                'todayLogouts',
                'confessionPostsCount',
                'confessionCommentsCount',
                'contactMessagesCount',
                'notificationsCount',
                'openTicketsCount',
                'pendingHiringCount',
                'loginChartLabels',
                'loginChartData',
                'quizAttemptChartLabels',
                'quizAttemptChartData',
                'userRegChartLabels',
                'userRegChartData',
                'studentRegChartLabels',
                'studentRegChartData',
                'employeeRegChartData',
                'errorLogChartLabels',
                'errorLogChartData',
                'dtrChartLabels',
                'dtrEmployeeChartData',
                'dtrStudentChartData',
                'leaveRequestEmployeeLabels',
                'leaveRequestEmployeeData',
                'leaveRequestStudentLabels',
                'leaveRequestStudentData',
                'activityTypeLabels',
                'activityTypeData',
                'loginTimeLabels',
                'loginTimeData',
                'activityLogLabels',
                'activityLogTotalData',
                'activityLogGuestTrafficData',
                'activityLogByTypeLabels',
                'activityLogLoginData',
                'activityLogLogoutData',
                'activityLogPageViewData',
                'activityLogGuestPageViewData',
                'mostActiveUsers'
            ));
        } catch (\Exception $e) {
            Log::error('Dashboard error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Return minimal data to prevent complete failure
            return view('admin.dashboard', [
                'chartPeriod' => 'week',
                'chartFrom' => now()->subDays(6)->format('Y-m-d'),
                'chartTo' => now()->format('Y-m-d'),
                'totalUsers' => 0,
                'totalQuizzes' => 0,
                'disabledUsers' => 0,
                'activeUsers' => 0,
                'totalEmployees' => 0,
                'totalStudents' => 0,
                'activeEmployees' => 0,
                'activeStudents' => 0,
                'totalDtrRecords' => 0,
                'employeeDtrRecords' => 0,
                'studentDtrRecords' => 0,
                'todayDtrRecords' => 0,
                'totalLeaveRequests' => 0,
                'pendingLeaveRequests' => 0,
                'approvedLeaveRequests' => 0,
                'rejectedLeaveRequests' => 0,
                'employeeLeaveRequests' => 0,
                'studentLeaveRequests' => 0,
                'recentLeaveRequests' => collect(),
                'ongoingLeaveEmployees' => collect(),
                'ongoingLeaveStudents' => collect(),
                'studentsWithDeficit' => collect(),
                'studentsWithTrainingRequirementCount' => 0,
                'studentsIncompleteTrainingCount' => 0,
                'employeesWithDeficit' => collect(),
                'recentQuizzes' => collect(),
                'recentUsers' => collect(),
                'quizStats' => ['total' => 0, 'active' => 0, 'inactive' => 0],
                'userStats' => ['total' => 0, 'active' => 0, 'disabled' => 0],
                'topStudents' => collect(),
                'universityRanking' => collect(),
                'quizPopularity' => collect(),
                'quizPerformance' => collect(),
                'activityStats' => ['online' => 0, 'idle' => 0, 'offline' => 0, 'total_today' => 0],
                'onlineUsers' => collect(),
                'recentActivities' => collect(),
                'todayLogins' => 0,
                'todayLogouts' => 0,
                'confessionPostsCount' => 0,
                'confessionCommentsCount' => 0,
                'contactMessagesCount' => 0,
                'notificationsCount' => 0,
                'openTicketsCount' => 0,
                'pendingHiringCount' => 0,
                'loginChartLabels' => [],
                'loginChartData' => [],
                'quizAttemptChartLabels' => [],
                'quizAttemptChartData' => [],
                'userRegChartLabels' => [],
                'userRegChartData' => [],
                'studentRegChartLabels' => [],
                'studentRegChartData' => [],
                'employeeRegChartData' => [],
                'errorLogChartLabels' => [],
                'errorLogChartData' => [],
                'dtrChartLabels' => [],
                'dtrEmployeeChartData' => [],
                'dtrStudentChartData' => [],
                'leaveRequestEmployeeLabels' => [],
                'leaveRequestEmployeeData' => [],
                'leaveRequestStudentLabels' => [],
                'leaveRequestStudentData' => [],
                'activityTypeLabels' => [],
                'activityTypeData' => [],
                'loginTimeLabels' => [],
                'loginTimeData' => [],
                'activityLogLabels' => [],
                'activityLogTotalData' => [],
                'activityLogGuestTrafficData' => [],
                'activityLogByTypeLabels' => [],
                'activityLogLoginData' => [],
                'activityLogLogoutData' => [],
                'activityLogPageViewData' => [],
                'activityLogGuestPageViewData' => [],
                'mostActiveUsers' => collect(),
            ])->with('error', 'Some dashboard data could not be loaded. Please refresh the page.');
        }
    }

    /**
     * Get real-time user activity data
     */
    public function getActivityData()
    {
        $this->ensureCanAccessDashboard();

        try {
            $activityStats = UserSession::getSessionStats();
            $onlineUsers = UserSession::getOnlineUsers();
            $recentActivities = UserActivity::getRecentActivities(10, true);

            return response()->json([
                'activityStats' => $activityStats,
                'onlineUsers' => $onlineUsers->map(function ($session) {
                    return [
                        'id' => $session->user->id,
                        'name' => $session->user->name,
                        'email' => $session->user->email,
                        'last_activity' => $session->last_activity_at->diffForHumans(),
                        'current_page' => $session->metadata['current_page'] ?? 'Unknown',
                        'ip_address' => $session->ip_address,
                        'login_time' => $session->login_at->diffForHumans(),
                    ];
                }),
                'recentActivities' => $recentActivities->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'user_name' => optional($activity->user)->name ?? 'Guest',
                        'activity_type' => $activity->activity_type,
                        'action' => $activity->action,
                        'page_url' => $activity->page_url,
                        'created_at' => $activity->created_at->diffForHumans(),
                        'ip_address' => $activity->ip_address,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'activityStats' => ['online' => 0, 'idle' => 0, 'offline' => 0, 'total_today' => 0],
                'onlineUsers' => [],
                'recentActivities' => [],
            ]);
        }
    }
}
