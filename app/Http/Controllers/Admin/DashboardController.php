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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::where('role', 'user')->count();
        $totalQuizzes = Quiz::count();
        $disabledUsers = User::where('role', 'user')->where('is_active', false)->count();
        $activeUsers = User::where('role', 'user')->where('is_active', true)->count();

        $recentQuizzes = Quiz::with('creator')->latest()->take(5)->get();
        $recentUsers = User::where('role', 'user')->latest()->take(5)->get();

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

        // Top Students by Total Score - Show ALL users who have taken quizzes (all time)
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

        // University Student Count Ranking - Show ALL universities
        $universityRanking = University::withCount('users')
            ->orderBy('users_count', 'desc')
            ->get();

        // Quiz Popularity Ranking (Most Students Taking) - Show ALL quizzes
        $quizPopularity = Quiz::select('quizzes.*', DB::raw('COUNT(DISTINCT quiz_attempts.user_id) as student_count'))
            ->leftJoin('quiz_attempts', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
            ->groupBy('quizzes.id')
            ->orderBy('student_count', 'desc')
            ->get();

        // Quiz Performance Ranking (Average Score) - Show ALL quizzes
        $quizPerformance = Quiz::select('quizzes.*',
                DB::raw('COUNT(DISTINCT quiz_attempts.user_id) as student_count'),
                DB::raw('COALESCE(AVG(quiz_attempts.points_earned), 0) as average_score'),
                DB::raw('COALESCE(MAX(quiz_attempts.points_earned), 0) as highest_score')
            )
            ->leftJoin('quiz_attempts', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
            ->groupBy('quizzes.id')
            ->orderBy('average_score', 'desc')
            ->get();

        // User Activity Statistics - with error handling
        try {
            $activityStats = UserSession::getSessionStats();
            $onlineUsers = UserSession::getOnlineUsers();
            $recentActivities = UserActivity::getRecentActivities(20);
            $todayLogins = UserActivity::getTodayLoginCount();
            $todayLogouts = UserActivity::getTodayLogoutCount();
        } catch (\Exception $e) {
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
