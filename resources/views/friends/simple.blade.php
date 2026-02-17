<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends - Simple Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-6">Friends - Simple Test</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Search for Friends</h2>

            <!-- Search Input -->
            <div class="mb-4">
                <input type="text"
                       id="friend-search"
                       placeholder="Search for users by name or email..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Search Results -->
            <div id="search-results" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                <h3 class="font-medium mb-2">Search Results:</h3>
                <div id="results-list"></div>
            </div>

            <!-- Friends List -->
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-4">Your Friends ({{ $friends->count() }})</h3>
                @if($friends->count() > 0)
                    <div class="space-y-2">
                        @foreach($friends as $friend)
                            <div class="p-3 border border-gray-200 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    @if($friend->profile_picture)
                                        <img src="{{ $friend->getProfilePictureUrl() }}"
                                             alt="{{ $friend->name }}"
                                             class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <span class="text-indigo-600 font-semibold text-sm">
                                                {{ $friend->getInitials() }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">{{ $friend->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $friend->email }}</p>
                                    </div>
                                    <button onclick="startChat({{ $friend->id }})"
                                            class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">
                                        Chat
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <p>No friends yet. Search for users above to add them as friends.</p>
                    </div>
                @endif
            </div>

            <!-- Pending Requests -->
            @if($pendingRequests->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-medium mb-4">Pending Friend Requests</h3>
                    <div class="space-y-2">
                        @foreach($pendingRequests as $request)
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        @if($request->user->profile_picture)
                                            <img src="{{ $request->user->getProfilePictureUrl() }}"
                                                 alt="{{ $request->user->name }}"
                                                 class="w-10 h-10 rounded-full object-cover">
                                        @else
                                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                                <span class="text-indigo-600 font-semibold text-sm">
                                                    {{ $request->user->getInitials() }}
                                                </span>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $request->user->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $request->user->email }}</p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button onclick="acceptRequest({{ $request->id }})"
                                                class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                            Accept
                                        </button>
                                        <button onclick="rejectRequest({{ $request->id }})"
                                                class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                            Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Debug Info -->
            <div id="debug-info" class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
                <h3 class="font-medium text-yellow-800">Debug Info:</h3>
                <div id="debug-content">
                    <p>User: {{ auth()->user() ? auth()->user()->name : 'Not logged in' }}</p>
                    <p>Friends count: {{ $friends->count() }}</p>
                    <p>Pending requests: {{ $pendingRequests->count() }}</p>
                    <p>Sent requests: {{ $sentRequests->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let searchTimeout;

        document.getElementById('friend-search').addEventListener('input', function(e) {
            const query = e.target.value;
            const resultsDiv = document.getElementById('search-results');
            const resultsList = document.getElementById('results-list');
            const debugDiv = document.getElementById('debug-content');

            clearTimeout(searchTimeout);

            if (query.length < 2) {
                resultsDiv.classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(() => {
                debugDiv.innerHTML += '<br>Searching for: ' + query;

                fetch(`/friends/search?q=${encodeURIComponent(query)}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    debugDiv.innerHTML += '<br>Response status: ' + response.status;
                    return response.json();
                })
                .then(data => {
                    debugDiv.innerHTML += '<br>Found ' + data.length + ' users';

                    if (data.length > 0) {
                        resultsList.innerHTML = data.map(user => `
                            <div class="p-2 border-b border-gray-200 hover:bg-gray-100 cursor-pointer" onclick="sendFriendRequest(${user.id}, '${user.name}')">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <strong>${user.name}</strong><br>
                                        <span class="text-sm text-gray-500">${user.email}</span>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded-full ${getStatusClass(user.friendship_status)}">${getStatusText(user.friendship_status)}</span>
                                </div>
                            </div>
                        `).join('');
                        resultsDiv.classList.remove('hidden');
                    } else {
                        resultsList.innerHTML = '<div class="text-gray-500">No users found</div>';
                        resultsDiv.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    debugDiv.innerHTML += '<br>Error: ' + error.message;
                    console.error('Search error:', error);
                });
            }, 300);
        });

        function getStatusClass(status) {
            switch(status) {
                case 'accepted': return 'bg-green-100 text-green-800';
                case 'pending': return 'bg-yellow-100 text-yellow-800';
                case 'blocked': return 'bg-red-100 text-red-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        function getStatusText(status) {
            switch(status) {
                case 'accepted': return 'Friends';
                case 'pending': return 'Pending';
                case 'blocked': return 'Blocked';
                default: return 'Add Friend';
            }
        }

        function sendFriendRequest(userId, userName) {
            const debugDiv = document.getElementById('debug-content');
            debugDiv.innerHTML += '<br>Sending friend request to: ' + userName;

            fetch('/friends/send-request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ friend_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    ToastNotification.success('Friend request sent to ' + userName + '!');
                    location.reload();
                } else {
                    ToastNotification.error('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                ToastNotification.error('Error sending friend request');
            });
        }

        function acceptRequest(friendshipId) {
            fetch(`/friends/${friendshipId}/accept`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    ToastNotification.success('Friend request accepted!');
                    location.reload();
                } else {
                    ToastNotification.error('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                ToastNotification.error('Error accepting friend request');
            });
        }

        function rejectRequest(friendshipId) {
            fetch(`/friends/${friendshipId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    ToastNotification.success('Friend request rejected');
                    location.reload();
                } else {
                    ToastNotification.error('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                ToastNotification.error('Error rejecting friend request');
            });
        }

        function startChat(friendId) {
            window.open(`/user-chat?friend=${friendId}`, '_blank');
        }
    </script>
</body>
</html>
