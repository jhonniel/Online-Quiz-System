<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => Notification::count(),
            'unread' => Notification::unread()->count(),
            'friend_requests' => Notification::byType('friend_request')->count(),
            'messages' => Notification::byType('message')->count(),
            'admin_notifications' => Notification::byType('admin_notification')->count(),
            'system_updates' => Notification::byType('system_update')->count(),
            'bug_alerts' => Notification::byType('bug_alert')->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'stats'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        $roles = User::query()
            ->select('role')
            ->distinct()
            ->orderBy('role')
            ->pluck('role')
            ->values();

        $roleLabels = [
            'admin' => 'Administrator',
            'student' => 'Student',
            'employee' => 'Employee',
            'hr' => 'HR',
            'teacher' => 'Teacher',
            'applicant' => 'Applicant',
            'technician' => 'Technician',
            'user' => 'User',
        ];

        return view('admin.notifications.create', compact('users', 'roles', 'roleLabels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:admin_notification,system_update,bug_alert,custom',
            'recipient_type' => 'required|in:all_roles,specific_roles,specific_users,all,specific',
            'roles' => 'required_if:recipient_type,specific_roles|array|min:1',
            'roles.*' => ['string', Rule::exists('users', 'role')],
            'user_ids' => 'required_if:recipient_type,specific_users,specific|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $createdCount = 0;
        $recipientType = (string) $request->recipient_type;
        if ($recipientType === 'all') {
            $recipientType = 'all_roles';
        } elseif ($recipientType === 'specific') {
            $recipientType = 'specific_users';
        }

        if ($recipientType === 'all_roles') {
            // Send to all users across all roles.
            $users = User::query()->get();
            foreach ($users as $user) {
                Notification::createAdminNotification(
                    $user->id,
                    $request->title,
                    $request->message,
                    $request->type
                );
                $createdCount++;
            }
        } elseif ($recipientType === 'specific_roles') {
            $roles = $request->roles ?? [];
            $users = User::query()->whereIn('role', $roles)->get();
            foreach ($users as $user) {
                Notification::createAdminNotification(
                    $user->id,
                    $request->title,
                    $request->message,
                    $request->type
                );
                $createdCount++;
            }
        } else {
            // Send to specific users
            $userIds = $request->user_ids ?? [];
            foreach ($userIds as $userId) {
                Notification::createAdminNotification(
                    $userId,
                    $request->title,
                    $request->message,
                    $request->type
                );
                $createdCount++;
            }
        }

        $recipientText = match ($recipientType) {
            'all_roles' => 'all users across all roles',
            'specific_roles' => 'users in selected roles',
            default => 'selected users',
        };
        return redirect('/admin/notifications')
            ->with('success', "Notification sent to {$createdCount} {$recipientText} successfully!");
    }

    public function sendToAll(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:admin_notification,system_update,bug_alert',
        ]);

        $users = User::query()->get();
        $createdCount = 0;

        foreach ($users as $user) {
            Notification::createAdminNotification(
                $user->id,
                $request->title,
                $request->message,
                $request->type
            );
            $createdCount++;
        }

        return redirect('/admin/notifications')
            ->with('success', "Notification sent to all {$createdCount} users successfully!");
    }

    public function show(Notification $notification)
    {
        $notification->load('user');
        return view('admin.notifications.show', compact('notification'));
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();

        return redirect('/admin/notifications')
            ->with('success', 'Notification deleted successfully!');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array|min:1',
            'notification_ids.*' => 'exists:notifications,id',
        ]);

        $deletedCount = Notification::whereIn('id', $request->notification_ids)->delete();

        return redirect('/admin/notifications')
            ->with('success', "{$deletedCount} notification(s) deleted successfully!");
    }

    public function markAllAsRead(Request $request)
    {
        // Header bell (AJAX): mark only the current user's notifications
        if ($request->expectsJson() || $request->ajax()) {
            $updatedCount = auth()->user()->unreadNotifications()->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read.',
                'updated' => $updatedCount,
            ]);
        }

        // Management page: requires communication permission (route also guards this for resource,
        // but mark-all-read is shared with the personal inbox route).
        if (! auth()->user()->hasAdminPermission('communication')) {
            abort(403, 'Access denied. You do not have permission to manage notifications.');
        }

        $updatedCount = Notification::unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect('/admin/notifications')
            ->with('success', "{$updatedCount} notification(s) marked as read!");
    }

    public function getStats()
    {
        $stats = [
            'total' => Notification::count(),
            'unread' => Notification::unread()->count(),
            'friend_requests' => Notification::byType('friend_request')->count(),
            'messages' => Notification::byType('message')->count(),
            'admin_notifications' => Notification::byType('admin_notification')->count(),
            'system_updates' => Notification::byType('system_update')->count(),
            'bug_alerts' => Notification::byType('bug_alert')->count(),
        ];

        return response()->json($stats);
    }

    public function getRecent()
    {
        $notifications = Notification::with('user')
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json(['notifications' => $notifications]);
    }

    public function getUnread(Request $request)
    {
        $unreadCount = Notification::unread()->count();

        // Management unread list (browser)
        $allUnreadNotifications = Notification::with('user')
            ->unread()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => Notification::count(),
            'unread' => $unreadCount,
            'friend_requests' => Notification::byType('friend_request')->unread()->count(),
            'messages' => Notification::byType('message')->unread()->count(),
            'admin_notifications' => Notification::byType('admin_notification')->unread()->count(),
            'system_updates' => Notification::byType('system_update')->unread()->count(),
            'bug_alerts' => Notification::byType('bug_alert')->unread()->count(),
        ];

        return view('admin.notifications.unread', compact('allUnreadNotifications', 'stats', 'unreadCount'));
    }

    /**
     * Personal inbox for the admin header notification bell.
     */
    public function getMyUnread()
    {
        $user = auth()->user();

        $notifications = $user->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn (Notification $notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'is_read' => (bool) $notification->is_read,
                'created_at' => optional($notification->created_at)->diffForHumans(),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function getUnreadCount()
    {
        $unreadCount = auth()->user()->unreadNotifications()->count();

        return response()->json(['unread_count' => $unreadCount]);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|exists:notifications,id',
        ]);

        $user = auth()->user();

        // Personal inbox: only allow marking the current user's own notifications
        if ($request->expectsJson() || $request->ajax()) {
            $notification = $user->notifications()->findOrFail($request->notification_id);
            $notification->update(['is_read' => true, 'read_at' => now()]);

            return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
        }

        if (! $user->hasAdminPermission('communication')) {
            abort(403, 'Access denied. You do not have permission to manage notifications.');
        }

        $notification = Notification::findOrFail($request->notification_id);
        $notification->update(['is_read' => true, 'read_at' => now()]);

        return redirect()->back()->with('success', 'Notification marked as read.');
    }
}
