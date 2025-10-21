@extends('layouts.user')

@section('page-title', 'Notifications')

@section('content')
<div class="mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                    <p class="text-sm text-gray-600 mt-1">Stay updated with your latest activities</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="markAllAsRead()"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Mark All Read
                    </button>
                    <button onclick="clearAllNotifications()"
                            class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Clear All
                    </button>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="divide-y divide-gray-200">
            @forelse($notifications as $notification)
                <div class="p-6 hover:bg-gray-50 transition-colors duration-200 {{ !$notification->is_read ? 'bg-blue-50' : '' }}"
                     id="notification-{{ $notification->id }}">
                    <div class="flex items-start space-x-4">
                        <!-- Notification Icon -->
                        <div class="flex-shrink-0">
                            @if($notification->type === 'friend_request')
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                            @elseif($notification->type === 'message')
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                            @elseif($notification->type === 'admin_notification')
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @elseif($notification->type === 'system_update')
                                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </div>
                            @elseif($notification->type === 'bug_alert')
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 002.828 0L12 7H4.828zM4 12h16M4 6h16M4 18h16"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Notification Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-900">{{ $notification->title }}</h3>
                                <div class="flex items-center space-x-2">
                                    @if(!$notification->is_read)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            New
                                        </span>
                                    @endif
                                    <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">{{ $notification->message }}</p>

                            <!-- Action Buttons -->
                            <div class="flex items-center space-x-3 mt-3">
                                @if(!$notification->is_read)
                                    <button onclick="markAsRead({{ $notification->id }})"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                        Mark as read
                                    </button>
                                @else
                                    <button onclick="markAsUnread({{ $notification->id }})"
                                            class="text-xs text-gray-500 hover:text-gray-700 font-medium">
                                        Mark as unread
                                    </button>
                                @endif
                                <button onclick="deleteNotification({{ $notification->id }})"
                                        class="text-xs text-red-600 hover:text-red-800 font-medium">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 002.828 0L12 7H4.828zM4 12h16M4 6h16M4 18h16"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No notifications</h3>
                    <p class="mt-1 text-sm text-gray-500">You're all caught up! New notifications will appear here.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($notifications->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

<script>
// Notification management functions
async function markAsRead(notificationId) {
    try {
        const response = await fetch('{{ route("notifications.mark-read") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ notification_id: notificationId })
        });

        if (response.ok) {
            const notificationElement = document.getElementById('notification-' + notificationId);
            if (notificationElement) {
                notificationElement.classList.remove('bg-blue-50');
                const newBadge = notificationElement.querySelector('.bg-blue-100');
                if (newBadge) {
                    newBadge.remove();
                }
                const markReadBtn = notificationElement.querySelector('button[onclick*="markAsRead"]');
                if (markReadBtn) {
                    markReadBtn.outerHTML = '<button onclick="markAsUnread(' + notificationId + ')" class="text-xs text-gray-500 hover:text-gray-700 font-medium">Mark as unread</button>';
                }
            }
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
}

async function markAsUnread(notificationId) {
    try {
        const response = await fetch('{{ route("notifications.mark-unread") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ notification_id: notificationId })
        });

        if (response.ok) {
            const notificationElement = document.getElementById('notification-' + notificationId);
            if (notificationElement) {
                notificationElement.classList.add('bg-blue-50');
                const markUnreadBtn = notificationElement.querySelector('button[onclick*="markAsUnread"]');
                if (markUnreadBtn) {
                    markUnreadBtn.outerHTML = '<button onclick="markAsRead(' + notificationId + ')" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Mark as read</button>';
                }
            }
        }
    } catch (error) {
        console.error('Error marking notification as unread:', error);
    }
}

async function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        try {
            const response = await fetch('{{ route("notifications.delete") }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ notification_id: notificationId })
            });

            if (response.ok) {
                const notificationElement = document.getElementById('notification-' + notificationId);
                if (notificationElement) {
                    notificationElement.remove();
                }
            }
        } catch (error) {
            console.error('Error deleting notification:', error);
        }
    }
}

async function markAllAsRead() {
    if (confirm('Are you sure you want to mark all notifications as read?')) {
        try {
            const response = await fetch('{{ route("notifications.mark-read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ mark_all: true })
            });

            if (response.ok) {
                location.reload();
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }
}

async function clearAllNotifications() {
    if (confirm('Are you sure you want to delete all notifications? This action cannot be undone.')) {
        try {
            const response = await fetch('{{ route("notifications.clear-all") }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                location.reload();
            }
        } catch (error) {
            console.error('Error clearing all notifications:', error);
        }
    }
}
</script>
@endsection
