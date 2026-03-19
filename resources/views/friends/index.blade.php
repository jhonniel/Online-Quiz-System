@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col space-y-3 min-h-0">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Friends & Connections</h1>
                    <p class="text-indigo-100 text-sm">Connect with friends and build your network</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ url('/user-chat') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <span class="hidden sm:inline">Start Chat</span>
                </a>
            </div>
        </div>
    </div>
    <!-- Info Section -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-2">
                <h3 class="text-sm font-medium text-gray-900">Friends Management</h3>
                <p class="text-xs text-gray-500">View and manage your friends list. To send friend requests, visit your profile page.</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-hidden mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 h-full">
            <!-- Pending Requests -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 h-full flex flex-col">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-orange-50">
                        <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Pending Requests
                            @if($pendingRequests->count() > 0)
                                <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-0.5 rounded-full">
                                    {{ $pendingRequests->count() }}
                                </span>
                            @endif
                        </h3>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4">
                        @if($pendingRequests->count() > 0)
                            <div class="space-y-3">
                            @foreach($pendingRequests as $request)
                                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-3 hover:shadow-md transition-all duration-200" data-friend-request>
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            @if($request->user->profile_picture)
                                                <img src="{{ $request->user->getProfilePictureUrl() }}"
                                                     alt="{{ $request->user->name }}"
                                                     class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
                                            @else
                                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                                                    <span class="text-white font-semibold text-sm">
                                                        {{ $request->user->getInitials() }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $request->user->name }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ $request->user->email }}</p>
                                        </div>
                                        <div class="flex flex-col space-y-1">
                                            <button onclick="acceptRequest({{ $request->id }})"
                                                    class="bg-green-500 text-white px-3 py-1 rounded-md text-xs font-medium hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors duration-200">
                                                Accept
                                            </button>
                                            <button onclick="rejectRequest({{ $request->id }})"
                                                    class="bg-red-500 text-white px-3 py-1 rounded-md text-xs font-medium hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200">
                                                Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-16 h-16 mx-auto bg-gradient-to-br from-yellow-100 to-orange-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 mb-1">No pending requests</h3>
                                <p class="text-xs text-gray-500">All caught up! No friend requests waiting.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Friends List -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 h-full flex flex-col">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                        <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            My Friends
                            <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2 py-0.5 rounded-full">
                                {{ $allFriends->count() }}
                            </span>
                        </h3>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4">
                        @if($allFriends->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($allFriends as $friend)
                                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-3 hover:shadow-md transition-all duration-200 group">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                @if($friend->profile_picture)
                                                    <img src="{{ $friend->getProfilePictureUrl() }}"
                                                         alt="{{ $friend->name }}"
                                                         class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
                                                @else
                                                    <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                                                        <span class="text-white font-semibold text-sm">
                                                            {{ $friend->getInitials() }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $friend->name }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ $friend->email }}</p>
                                                <div class="flex items-center mt-1">
                                                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                                                    <span class="text-xs text-gray-500">Online</span>
                                                </div>
                                            </div>
                                            <div class="flex flex-col space-y-1">
                                                <a href="{{ url('/user-chat') }}?friend={{ $friend->id }}"
                                                   class="bg-indigo-500 text-white px-3 py-1 rounded-md text-xs font-medium hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors duration-200">
                                                    Chat
                                                </a>
                                                <button onclick="removeFriend({{ $friend->id }})"
                                                        class="bg-red-500 text-white px-3 py-1 rounded-md text-xs font-medium hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-20 h-20 mx-auto bg-gradient-to-br from-green-100 to-emerald-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 mb-1">No friends yet</h3>
                                <p class="text-xs text-gray-500 mb-4">Start building your network by visiting your profile page to send friend requests.</p>
                                <a href="{{ url('/profile') }}"
                                   class="bg-indigo-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors duration-200">
                                    Go to Profile
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
                    <svg class="animate-spin h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-2">Processing...</h3>
                <p class="text-sm text-gray-500 mt-1">Please wait while we process your request.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>

        function acceptRequest(friendshipId) {
            showLoading();

            fetch(`{{ url('friends') }}/${friendshipId}/accept`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showNotification('Friend request accepted!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.error || 'Error accepting friend request', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showNotification('Error accepting friend request', 'error');
            });
        }

    function rejectRequest(friendshipId) {
        if (confirm('Are you sure you want to reject this friend request?')) {
                    showLoading();

                    fetch(`{{ url('friends') }}/${friendshipId}/reject`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideLoading();
                        if (data.success) {
                            showNotification('Friend request rejected', 'info');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showNotification(data.error || 'Error rejecting friend request', 'error');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        console.error('Error:', error);
                    showNotification('Error rejecting friend request', 'error');
                });
            }
    }

        function removeFriend(friendshipId) {
            if (confirm('Are you sure you want to remove this friend?')) {
                    showLoading();

                    fetch(`{{ url('friends') }}/${friendshipId}/remove`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideLoading();
                        if (data.success) {
                            showNotification('Friend removed successfully', 'info');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showNotification(data.error || 'Error removing friend', 'error');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        console.error('Error:', error);
                    showNotification('Error removing friend', 'error');
                });
            }
    }

        function showLoading() {
            document.getElementById('loading-overlay').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loading-overlay').classList.add('hidden');
        }

        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${getNotificationClass(type)}`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        ${getNotificationIcon(type)}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">${message}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        function getNotificationClass(type) {
            switch(type) {
                case 'success': return 'bg-green-500 text-white';
                case 'error': return 'bg-red-500 text-white';
                case 'warning': return 'bg-yellow-500 text-white';
                default: return 'bg-blue-500 text-white';
            }
        }

        function getNotificationIcon(type) {
            switch(type) {
                case 'success': return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                case 'error': return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                case 'warning': return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>';
                default: return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            }
        }

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            const searchResults = document.getElementById('search-results');
            const searchInput = document.getElementById('friend-search');
            if (!searchResults.contains(e.target) && !searchInput.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
    </script>
@endsection
