<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Quiz;
use App\Models\QuizAssignment;
use App\Models\QuizAttempt;
use App\Models\University;
use App\Models\UserActivity;
use App\Models\UserSession;
use App\Models\Dtr;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
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
                $employeeDtrRecords = Dtr::whereHas('user', function($q) {
                    $q->where('role', 'employee');
                })->count();
                $studentDtrRecords = Dtr::whereHas('user', function($q) {
                    $q->where('role', 'student');
                })->count();
                $todayDtrRecords = Dtr::whereDate('date', today())->count();
            } catch (\Exception $e) {
                \Log::warning('DTR statistics error: ' . $e->getMessage());
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
                $employeeLeaveRequests = LeaveRequest::whereHas('user', function($q) {
                    $q->where('role', 'employee');
                })->where('status', 'pending')->count();
                $studentLeaveRequests = LeaveRequest::whereHas('user', function($q) {
                    $q->where('role', 'student');
                })->where('status', 'pending')->count();
                
                // Recent Leave Requests
                $recentLeaveRequests = LeaveRequest::with('user')
                    ->latest()
                    ->take(5)
                    ->get();
            } catch (\Exception $e) {
                \Log::warning('Leave request statistics error: ' . $e->getMessage());
                $totalLeaveRequests = 0;
                $pendingLeaveRequests = 0;
                $approvedLeaveRequests = 0;
                $rejectedLeaveRequests = 0;
                $employeeLeaveRequests = 0;
                $studentLeaveRequests = 0;
                $recentLeaveRequests = collect();
            }

            // Recent Quizzes and Users - with error handling
            try {
                $recentQuizzes = Quiz::with('creator')->latest()->take(5)->get();
                $recentUsers = User::where('role', 'user')->latest()->take(5)->get();
            } catch (\Exception $e) {
                \Log::warning('Recent items error: ' . $e->getMessage());
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
                    ->filter(function($user) {
                        return ($user->quiz_attempt_history_sum_score ?? 0) > 0;
                    })
                    ->sortByDesc('quiz_attempt_history_sum_score')
                    ->values();
            } catch (\Exception $e) {
                \Log::warning('Top students error: ' . $e->getMessage());
                $topStudents = collect();
            }

            // University Student Count Ranking - with error handling
            try {
                $universityRanking = University::withCount('users')
                    ->orderBy('users_count', 'desc')
                    ->get();
            } catch (\Exception $e) {
                \Log::warning('University ranking error: ' . $e->getMessage());
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
                \Log::warning('Quiz popularity error: ' . $e->getMessage());
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
                \Log::warning('Quiz performance error: ' . $e->getMessage());
                $quizPerformance = collect();
            }

            // User Activity Statistics - with error handling
            try {
                $activityStats = UserSession::getSessionStats();
                $onlineUsers = UserSession::getOnlineUsers();
                $recentActivities = UserActivity::getRecentActivities(20);
                $todayLogins = UserActivity::getTodayLoginCount();
                $todayLogouts = UserActivity::getTodayLogoutCount();
            } catch (\Exception $e) {
                \Log::warning('User activity statistics error: ' . $e->getMessage());
                // Fallback values if activity tracking fails
                $activityStats = ['online' => 0, 'idle' => 0, 'offline' => 0, 'total_today' => 0];
                $onlineUsers = collect();
                $recentActivities = collect();
                $todayLogins = 0;
                $todayLogouts = 0;
            }

            return view('admin.dashboard', compact(
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
                'todayLogouts'
            ));
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return minimal data to prevent complete failure
            return view('admin.dashboard', [
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
            ])->with('error', 'Some dashboard data could not be loaded. Please refresh the page.');
        }

        return view('admin.dashboard', compact(
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
            'todayLogouts'
        ));
    }

    /**
     * Get real-time user activity data
     */
    public function getActivityData()
    {
        try {
            $activityStats = UserSession::getSessionStats();
            $onlineUsers = UserSession::getOnlineUsers();
            $recentActivities = UserActivity::getRecentActivities(10);

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
                        'login_time' => $session->login_at->diffForHumans()
                    ];
                }),
                'recentActivities' => $recentActivities->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'user_name' => $activity->user->name,
                        'activity_type' => $activity->activity_type,
                        'action' => $activity->action,
                        'page_url' => $activity->page_url,
                        'created_at' => $activity->created_at->diffForHumans(),
                        'ip_address' => $activity->ip_address
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'activityStats' => ['online' => 0, 'idle' => 0, 'offline' => 0, 'total_today' => 0],
                'onlineUsers' => [],
                'recentActivities' => []
            ]);
        }
    }
}
