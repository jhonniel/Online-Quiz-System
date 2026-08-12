@extends('layouts.admin')

@section('title', 'Notification - ' . $notification->title)
@section('page-title', 'View Notification')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <a href="{{ url('/admin/notifications') }}" class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700">Notifications</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-700">View</span>
        </div>
    </li>
@endsection

@section('content')
<div class="mx-2 sm:mx-3 lg:mx-4 xl:mx-6 max-w-3xl">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <a href="{{ url('/admin/notifications') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Notifications
            </a>
            <form method="POST" action="{{ url('/admin/notifications/' . $notification->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this notification?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Delete
                </button>
            </form>
        </div>

        <div class="p-6 space-y-6">
            <!-- Recipient -->
            <div>
                <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Recipient</h3>
                <div class="flex items-center space-x-3">
                    @if($notification->user)
                        @if($notification->user->profile_picture ?? null)
                            <img class="h-10 w-10 rounded-full" src="{{ $notification->user->getProfilePictureUrl() }}" alt="{{ $notification->user->name }}">
                        @else
                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                <span class="text-sm font-medium text-indigo-700">{{ substr($notification->user->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div>
                            <p class="text-sm font-medium text-gray-900"><x-user-name :user="$notification->user" /></p>
                            <p class="text-sm text-gray-500">{{ $notification->user->email }}</p>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">User no longer available</p>
                    @endif
                </div>
            </div>

            <!-- Type & Status -->
            <div class="flex flex-wrap gap-4">
                <div>
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Type</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($notification->type === 'friend_request') bg-blue-100 text-blue-800
                        @elseif($notification->type === 'message') bg-green-100 text-green-800
                        @elseif($notification->type === 'admin_notification') bg-purple-100 text-purple-800
                        @elseif($notification->type === 'system_update') bg-yellow-100 text-yellow-800
                        @elseif($notification->type === 'bug_alert') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                    </span>
                </div>
                <div>
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Status</h3>
                    @if($notification->is_read)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Read</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Unread</span>
                    @endif
                </div>
            </div>

            <!-- Title -->
            <div>
                <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Title</h3>
                <p class="text-lg font-semibold text-gray-900">{{ $notification->title }}</p>
            </div>

            <!-- Message -->
            <div>
                <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Message</h3>
                <div class="mt-1 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $notification->message }}</p>
                </div>
            </div>

            <!-- Dates -->
            <div class="pt-4 border-t border-gray-200 flex flex-wrap gap-6 text-sm text-gray-500">
                <div>
                    <span class="font-medium text-gray-700">Sent:</span>
                    {{ $notification->created_at ? $notification->created_at->format('M j, Y g:i A') : '—' }}
                </div>
                @if($notification->is_read && $notification->read_at)
                    <div>
                        <span class="font-medium text-gray-700">Read:</span>
                        {{ $notification->read_at->format('M j, Y g:i A') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
