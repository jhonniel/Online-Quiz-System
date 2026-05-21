@extends('layouts.admin')

@section('page-title', 'Live Chat Management')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Live Chat</span>
        </div>
    </li>
@endsection

@section('content')
<div class="h-full flex flex-col space-y-3">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Live Chat Management</h1>
                    <p class="text-indigo-100 text-sm">Manage live chat conversations with users</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <div class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm font-medium min-h-[1.75rem] flex items-center" id="unread-count" data-skeleton-badge="1">
                    <span class="inline-block h-5 w-28 animate-pulse bg-indigo-200/80 rounded-full" aria-hidden="true"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 flex-shrink-0">
        <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-3">
                    <p class="text-xs sm:text-sm font-medium text-gray-500">Total</p>
                    <p class="text-sm sm:text-lg font-semibold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-3">
                    <p class="text-xs sm:text-sm font-medium text-gray-500">Open</p>
                    <p class="text-sm sm:text-lg font-semibold text-green-600">{{ $stats['open'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-3">
                    <p class="text-xs sm:text-sm font-medium text-gray-500">Closed</p>
                    <p class="text-sm sm:text-lg font-semibold text-gray-600">{{ $stats['closed'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-2 sm:ml-3">
                    <p class="text-xs sm:text-sm font-medium text-gray-500">Reopened</p>
                    <p class="text-sm sm:text-lg font-semibold text-yellow-600">{{ $stats['reopened'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- User Presence Section -->
    <div class="flex-shrink-0">
        <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">User Presence</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 sm:w-3 sm:h-3 bg-green-500 rounded-full"></div>
                        <span class="text-xs sm:text-sm font-medium text-gray-900">Online</span>
                    </div>
                    <span class="text-sm sm:text-lg font-semibold text-green-600" id="online-count">0</span>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 sm:w-3 sm:h-3 bg-yellow-500 rounded-full"></div>
                        <span class="text-xs sm:text-sm font-medium text-gray-900">Away</span>
                    </div>
                    <span class="text-sm sm:text-lg font-semibold text-yellow-600" id="away-count">0</span>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 sm:w-3 sm:h-3 bg-orange-500 rounded-full"></div>
                        <span class="text-xs sm:text-sm font-medium text-gray-900">Idle</span>
                    </div>
                    <span class="text-sm sm:text-lg font-semibold text-orange-600" id="idle-count">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Grid -->
    <div class="flex-1 overflow-y-auto">
        @if($tickets->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
                @foreach($tickets as $ticket)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 hover:shadow-md transition-shadow cursor-pointer"
                         onclick="window.location.href='{{ url('/admin/live-chat/' . $ticket->ticket_number) }}'">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                                <div class="relative flex-shrink-0">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-indigo-600 font-semibold text-xs sm:text-sm">
                                            {{ strtoupper(substr($ticket->user->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <!-- Status indicator -->
                                    <div class="absolute -bottom-1 -right-1 w-3 h-3 sm:w-4 sm:h-4 rounded-full border-2 border-white {{ $ticket->user->getStatusBadgeClass() }} flex items-center justify-center">
                                        <span class="text-xs">{{ $ticket->user->getStatusIcon() }}</span>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center space-x-1 sm:space-x-2">
                                        <h3 class="text-sm sm:text-base lg:text-lg font-medium text-gray-900 truncate">{{ $ticket->user->name }}</h3>
                                        <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-full text-xs font-medium {{ $ticket->user->getStatusBadgeClass() }} flex-shrink-0">
                                            {{ ucfirst($ticket->user->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-gray-500 truncate">{{ $ticket->ticket_number }}</p>
                                    <p class="text-xs text-gray-400 hidden sm:block">Last seen: {{ $ticket->user->getLastActivityText() }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-1 flex-shrink-0">
                                @if($ticket->unread_count > 0)
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-0.5 rounded-full">
                                        {{ $ticket->unread_count }} new
                                    </span>
                                @endif
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $ticket->getStatusBadgeClass() }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </div>
                        </div>

                        <div class="mb-3 sm:mb-4">
                            <p class="text-sm font-medium text-gray-900 mb-1 truncate">{{ $ticket->subject }}</p>
                            @if($ticket->messages->count() > 0)
                                <p class="text-xs sm:text-sm text-gray-600 truncate">
                                    {{ $ticket->messages->first()->message }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $ticket->messages->first()->created_at ? \Carbon\Carbon::parse($ticket->messages->first()->created_at)->diffForHumans() : 'N/A' }}
                                </p>
                            @else
                                <p class="text-xs sm:text-sm text-gray-500 truncate">{{ $ticket->description }}</p>
                            @endif
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">
                                {{ $ticket->messages->count() }} messages
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $ticket->getPriorityBadgeClass() }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No chat tickets</h3>
                <p class="mt-1 text-sm text-gray-500">No users have created support tickets yet.</p>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update unread count
    function updateUnreadCount() {
        const countElement = document.getElementById('unread-count');
        fetch('{{ url("/admin/live-chat/unread-count") }}')
            .then(response => response.json())
            .then(data => {
                if (data.count > 0) {
                    countElement.textContent = `${data.count} unread messages`;
                    countElement.className = 'bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium';
                } else {
                    countElement.textContent = 'No unread messages';
                    countElement.className = 'bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium';
                }
            })
            .catch(error => {
                console.error('Error fetching unread count:', error);
            });
    }

    // Update user presence counts
    function updatePresenceCounts() {
        ['online-count', 'away-count', 'idle-count'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el && window.AppSkeleton) {
                el.innerHTML = AppSkeleton.html('badge');
            }
        });
        // Fetch online users
        fetch('{{ url("/admin/status/online-users") }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('online-count').textContent = data.length;
            })
            .catch(error => console.error('Error fetching online users:', error));

        // Fetch away users
        fetch('{{ url("/admin/status/away-users") }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('away-count').textContent = data.length;
            })
            .catch(error => console.error('Error fetching away users:', error));

        // Fetch idle users
        fetch('{{ url("/admin/status/idle-users") }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('idle-count').textContent = data.length;
            })
            .catch(error => console.error('Error fetching idle users:', error));
    }

    // Update counts on page load
    updateUnreadCount();
    updatePresenceCounts();

    // Polling disabled to reduce server load
    // Update counts every 30 seconds
    // setInterval(() => {
    //     updateUnreadCount();
    //     updatePresenceCounts();
    // }, 30000);
});
</script>
@endsection
