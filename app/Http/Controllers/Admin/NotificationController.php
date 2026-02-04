<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

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
        $users = User::where('role', 'user')->orderBy('name')->get();
        return view('admin.notifications.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:admin_notification,system_update,bug_alert,custom',
            'recipient_type' => 'required|in:all,specific',
            'user_ids' => 'required_if:recipient_type,specific|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $createdCount = 0;

        if ($request->recipient_type === 'all') {
            // Send to all users
            $users = User::where('role', 'user')->get();
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

        $recipientText = $request->recipient_type === 'all' ? 'all users' : 'selected users';
        return redirect()->route('admin.notifications.index')
            ->with('success', "Notification sent to {$createdCount} {$recipientText} successfully!");
    }

    public function sendToAll(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:admin_notification,system_update,bug_alert',
        ]);

        $users = User::where('role', 'user')->get();
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

        return redirect()->route('admin.notifications.index')
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

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification deleted successfully!');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array|min:1',
            'notification_ids.*' => 'exists:notifications,id',
        ]);

        $deletedCount = Notification::whereIn('id', $request->notification_ids)->delete();

        return redirect()->route('admin.notifications.index')
            ->with('success', "{$deletedCount} notification(s) deleted successfully!");
    }

    public function markAllAsRead()
    {
        $updatedCount = Notification::unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->route('admin.notifications.index')
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
        $notifications = Notification::with('user')
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $unreadCount = Notification::unread()->count();

        // If this is an AJAX/API request (like from fetch), return JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
        }

        // Otherwise, return a view for browser requests
        // Get all unread notifications for the view (not just limit 10)
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

    public function getUnreadCount()
    {
        $unreadCount = Notification::unread()->count();
        return response()->json(['unread_count' => $unreadCount]);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|exists:notifications,id',
        ]);

        $notification = Notification::findOrFail($request->notification_id);
        $notification->update(['is_read' => true, 'read_at' => now()]);

        // If this is an AJAX/API request, return JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
        }

        // Otherwise, redirect back with success message
        return redirect()->back()->with('success', 'Notification marked as read.');
    }
}
