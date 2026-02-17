<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use App\Models\UserSession;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserActivityController extends Controller
{
    /**
     * Display user activity logs
     */
    public function index(Request $request)
    {
        $query = UserActivity::with('user');

        // Filter by activity type
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by IP address
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get filter options
        $activityTypes = UserActivity::distinct()->pluck('activity_type')->sort();
        $users = User::where('role', 'user')->orderBy('name')->get();
        $ipAddresses = UserActivity::distinct()->pluck('ip_address')->filter()->sort();

        return view('admin.user-activity.index', compact(
            'activities',
            'activityTypes',
            'users',
            'ipAddresses'
        ));
    }

    /**
     * Display user sessions
     */
    public function sessions(Request $request)
    {
        $query = UserSession::with('user');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('login_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('login_at', '<=', $request->date_to);
        }

        $sessions = $query->orderBy('last_activity_at', 'desc')->paginate(50);

        // Get filter options
        $statuses = ['active', 'idle', 'offline'];
        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.user-activity.sessions', compact(
            'sessions',
            'statuses',
            'users'
        ));
    }

    /**
     * Get user activity statistics
     */
    public function statistics()
    {
        $stats = [
            'total_activities' => UserActivity::count(),
            'today_activities' => UserActivity::whereDate('created_at', today())->count(),
            'login_count_today' => UserActivity::where('activity_type', 'login')
                ->whereDate('created_at', today())->count(),
            'logout_count_today' => UserActivity::where('activity_type', 'logout')
                ->whereDate('created_at', today())->count(),
            'unique_users_today' => UserActivity::whereDate('created_at', today())
                ->distinct()->count('user_id'),
            'online_users' => UserSession::where('status', 'active')
                ->where('last_activity_at', '>=', now()->subMinutes(5))->count(),
            'idle_users' => UserSession::where('status', 'active')
                ->where('last_activity_at', '>=', now()->subMinutes(15))
                ->where('last_activity_at', '<', now()->subMinutes(5))->count(),
        ];

        // Activity by hour (last 24 hours) - SQLite compatible
        $activityByHour = UserActivity::where('created_at', '>=', now()->subDay())
            ->selectRaw('strftime("%H", created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour');

        // Activity by day (last 30 days) - SQLite compatible
        $activityByDay = UserActivity::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('date(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        // Top active users
        $topActiveUsers = UserActivity::selectRaw('user_id, COUNT(*) as activity_count')
            ->with('user')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('user_id')
            ->orderBy('activity_count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.user-activity.statistics', compact(
            'stats',
            'activityByHour',
            'activityByDay',
            'topActiveUsers'
        ));
    }

    /**
     * Clean up old activity logs
     */
    public function cleanup(Request $request)
    {
        $days = $request->input('days', 30);
        $deletedActivities = UserActivity::where('created_at', '<', now()->subDays($days))->delete();
        $deletedSessions = UserSession::where('last_activity_at', '<', now()->subDays($days))->delete();

        return redirect()->back()->with('success',
            "Cleaned up {$deletedActivities} activity records and {$deletedSessions} session records older than {$days} days."
        );
    }
}
