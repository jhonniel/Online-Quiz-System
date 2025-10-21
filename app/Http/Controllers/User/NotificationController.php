<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('user.notifications.index', compact('notifications'));
    }

    public function getUnreadCount()
    {
        $user = auth()->user();
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'unread_count' => $unreadCount
        ]);
    }

    public function getRecent()
    {
        $user = auth()->user();

        $notifications = $user->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'created_at_full' => $notification->created_at->format('M j, Y g:i A'),
                ];
            })
        ]);
    }

    public function markAsRead(Request $request)
    {
        $user = auth()->user();

        if ($request->has('notification_id')) {
            $notification = $user->notifications()->findOrFail($request->notification_id);
            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        }

        if ($request->has('mark_all')) {
            $user->unreadNotifications()->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid request'
        ], 400);
    }

    public function markAsUnread(Request $request)
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($request->notification_id);
        $notification->markAsUnread();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread'
        ]);
    }

    public function delete(Request $request)
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($request->notification_id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    public function clearAll()
    {
        $user = auth()->user();
        $user->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared'
        ]);
    }
}
