@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col space-y-2 min-h-0">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Chat with Friends</h1>
                    <p class="text-indigo-100 text-sm">Connect and chat with your friends</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ url('/friends') }}"
                   class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="hidden sm:inline">Manage Friends</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Chat Interface -->
    <div class="bg-white shadow-sm border-t border-b border-gray-200 overflow-hidden flex-1 flex flex-col">
        <div class="flex flex-col lg:flex-row h-full">
            <!-- Friends Sidebar -->
            <div class="w-full lg:w-1/3 border-r border-gray-200 flex flex-col">
                <!-- Friends Header -->
                <div class="p-3 sm:p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Your Friends</h3>
                    <p class="text-xs sm:text-sm text-gray-500">Select a friend to start chatting</p>
                </div>

                <!-- Friends List -->
                <div class="flex-1 overflow-y-auto">
                    <div id="friends-list" class="p-2">
                        @foreach($friends as $friend)
                            <div class="friend-item p-2 sm:p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors mb-2"
                                 data-friend-id="{{ $friend->id }}"
                                 data-friend-name="{{ $friend->name }}">
                                <div class="flex items-center space-x-2 sm:space-x-3">
                                    <div class="relative">
                                        @if($friend->profile_picture)
                                            <img src="{{ $friend->getProfilePictureUrl() }}"
                                                 alt="{{ $friend->name }}"
                                                 class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 rounded-full object-cover">
                                        @else
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                                                <span class="text-indigo-600 font-semibold text-xs sm:text-sm">
                                                    {{ $friend->getInitials() }}
                                                </span>
                                            </div>
                                        @endif
                                        <!-- Online indicator -->
                                        <div class="absolute bottom-0 right-0 w-2 h-2 sm:w-3 sm:h-3 bg-green-400 border-2 border-white rounded-full"></div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 truncate text-sm sm:text-base">{{ $friend->name }}</p>
                                        <p class="text-xs sm:text-sm text-gray-500 truncate hidden sm:block">Click to chat</p>
                                    </div>
                                    <span id="unread-{{ $friend->id }}" class="hidden bg-red-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center">0</span>
                                </div>
                            </div>
                        @endforeach

                                @if($friends->count() === 0)
                                    <div class="text-center py-12 px-4">
                                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <h3 class="mt-4 text-lg font-medium text-gray-900">No friends yet</h3>
                                        <p class="mt-2 text-sm text-gray-500">Add friends to start chatting with them.</p>
                                        <div class="mt-6">
                                            <a href="{{ url('/friends') }}"
                                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                Add Friends
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

            <!-- Chat Area -->
            <div class="flex-1 flex flex-col">
                <!-- No Chat Selected -->
                <div id="no-chat-selected" class="flex-1 flex items-center justify-center text-gray-500">
                    <div class="text-center p-4">
                        <svg class="mx-auto h-16 w-16 sm:h-20 sm:w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <h3 class="mt-4 text-base sm:text-lg font-medium text-gray-900">Select a friend to start chatting</h3>
                        <p class="mt-2 text-sm text-gray-500">Choose a friend from the list to begin your conversation.</p>
                    </div>
                </div>

                <!-- Chat Interface -->
                <div id="chat-area" class="hidden flex-1 flex flex-col">
                    <!-- Chat Header -->
                    <div class="flex items-center space-x-2 sm:space-x-3 p-3 sm:p-4 border-b border-gray-200 bg-gray-50">
                        <div class="relative">
                            <div id="chat-friend-avatar" class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                <span class="text-indigo-600 font-semibold text-xs sm:text-sm">U</span>
                            </div>
                            <div class="absolute bottom-0 right-0 w-2 h-2 sm:w-3 sm:h-3 bg-green-400 border-2 border-white rounded-full"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 id="chat-friend-name" class="font-medium text-gray-900 text-sm sm:text-base truncate">Friend Name</h3>
                            <p class="text-xs sm:text-sm text-gray-500">Online</p>
                        </div>
                        <div class="flex space-x-1 sm:space-x-2">
                            <button onclick="clearChat()"
                                    class="text-gray-400 hover:text-gray-600 p-1 sm:p-2 rounded-lg hover:bg-gray-100"
                                    title="Clear chat">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div id="chat-messages" class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-3 sm:space-y-4 bg-gray-50">
                        <!-- Messages will be loaded here -->
                    </div>

                    <!-- Typing Indicator -->
                    <div id="typing-indicator" class="hidden px-3 sm:px-4 py-2 bg-gray-50 border-t border-gray-200">
                        <div class="flex items-center space-x-2 text-xs sm:text-sm text-gray-500">
                            <div class="flex space-x-1">
                                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                            </div>
                            <span id="typing-text">Someone is typing...</span>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="p-3 sm:p-4 border-t border-gray-200 bg-white">
                        <div class="flex space-x-2 sm:space-x-3">
                            <div class="flex-1 relative">
                                <input type="text"
                                       id="message-input"
                                       placeholder="Type your message..."
                                       class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 pr-10 sm:pr-12 text-sm sm:text-base">
                                <button id="emoji-button"
                                        class="absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                        title="Add emoji">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                            </div>
                            <button id="send-button"
                                    class="bg-indigo-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled>
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Press Enter to send message</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Fallback overlay when AppSkeleton is unavailable -->
    <div id="loading-overlay" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <p class="text-sm text-gray-500 text-center py-8">Loading messages...</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
        let currentFriendId = null;
        let currentFriendName = null;
        let messages = [];
        let typingTimeout = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Friend selection
            document.querySelectorAll('.friend-item').forEach(item => {
                item.addEventListener('click', function() {
                    const friendId = this.dataset.friendId;
                    const friendName = this.dataset.friendName;
                    selectFriend(friendId, friendName);
                });
            });

            // Send message
            const sendButton = document.getElementById('send-button');
            const messageInput = document.getElementById('message-input');

            sendButton.addEventListener('click', sendMessage);
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // Enable/disable send button based on input
            messageInput.addEventListener('input', function() {
                sendButton.disabled = this.value.trim() === '';
            });

            // Load unread counts
            loadUnreadCounts();

            // Polling disabled to reduce server load
            // Poll for new messages every 3 seconds
            // setInterval(loadUnreadCounts, 3000);
        });

        function selectFriend(friendId, friendName) {
            currentFriendId = friendId;
            currentFriendName = friendName;

            // Update UI
            document.getElementById('no-chat-selected').classList.add('hidden');
            document.getElementById('chat-area').classList.remove('hidden');
            document.getElementById('chat-friend-name').textContent = friendName;

            // Update friend list selection
            document.querySelectorAll('.friend-item').forEach(item => {
                item.classList.remove('bg-indigo-50', 'border-indigo-300');
            });
            document.querySelector(`[data-friend-id="${friendId}"]`).classList.add('bg-indigo-50', 'border-indigo-300');

            // Load messages
            loadMessages();

            // Mark messages as read
            markAsRead();
        }

        function loadMessages() {
            if (!currentFriendId) return;

            showLoading();

            fetch(`{{ url('user-chat') }}/${currentFriendId}/messages`)
                .then(response => response.json())
                .then(data => {
                    hideLoading();

                    if (data.error) {
                        showNotification(data.error, 'error');
                        return;
                    }

                    messages = data.messages;
                    displayMessages();
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error loading messages:', error);
                    showNotification('Error loading messages', 'error');
                });
        }

        function displayMessages() {
            const messagesDiv = document.getElementById('chat-messages');
            messagesDiv.innerHTML = '';

            if (messages.length === 0) {
                messagesDiv.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <p class="mt-2">No messages yet. Start the conversation!</p>
                    </div>
                `;
                return;
            }

            messages.forEach(message => {
                const isOwn = message.sender_id == {{ auth()->id() }};
                const messageDiv = document.createElement('div');
                messageDiv.className = `flex ${isOwn ? 'justify-end' : 'justify-start'}`;

                const time = new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                messageDiv.innerHTML = `
                    <div class="max-w-xs sm:max-w-sm lg:max-w-md">
                        <div class="px-3 sm:px-4 py-2 rounded-lg ${isOwn ? 'bg-indigo-600 text-white' : 'bg-white text-gray-900 border border-gray-200'}">
                            <p class="text-sm">${message.message}</p>
                            <p class="text-xs mt-1 ${isOwn ? 'text-indigo-100' : 'text-gray-500'}">${time}</p>
                        </div>
                    </div>
                `;

                messagesDiv.appendChild(messageDiv);
            });

            // Scroll to bottom
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        function sendMessage() {
            const input = document.getElementById('message-input');
            const message = input.value.trim();

            if (!message || !currentFriendId) return;

            // Disable send button temporarily
            const sendButton = document.getElementById('send-button');
            sendButton.disabled = true;

            // Add message to UI immediately
            const messageDiv = document.createElement('div');
            messageDiv.className = 'flex justify-end';
            const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            messageDiv.innerHTML = `
                <div class="max-w-xs sm:max-w-sm lg:max-w-md">
                    <div class="px-3 sm:px-4 py-2 rounded-lg bg-indigo-600 text-white">
                        <p class="text-sm">${message}</p>
                        <p class="text-xs mt-1 text-indigo-100">${time}</p>
                    </div>
                </div>
            `;
            document.getElementById('chat-messages').appendChild(messageDiv);
            document.getElementById('chat-messages').scrollTop = document.getElementById('chat-messages').scrollHeight;

            input.value = '';
            sendButton.disabled = true;

            // Send to server
            fetch('{{ url("/user-chat/send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    receiver_id: currentFriendId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showNotification(data.error, 'error');
                }
                // Re-enable send button
                sendButton.disabled = input.value.trim() === '';
            })
            .catch(error => {
                console.error('Error sending message:', error);
                showNotification('Error sending message', 'error');
                // Re-enable send button
                sendButton.disabled = input.value.trim() === '';
            });
        }

        function loadUnreadCounts() {
            fetch('{{ url("/user-chat/recent") }}')
                .then(response => response.json())
                .then(friends => {
                    friends.forEach(friend => {
                        const unreadElement = document.getElementById(`unread-${friend.id}`);
                        if (friend.unread_count > 0) {
                            unreadElement.textContent = friend.unread_count;
                            unreadElement.classList.remove('hidden');
                        } else {
                            unreadElement.classList.add('hidden');
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading unread counts:', error);
                });
        }

        function markAsRead() {
            if (!currentFriendId) return;

            fetch('{{ url("/user-chat/mark-read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    sender_id: currentFriendId
                })
            })
            .then(response => response.json())
            .then(data => {
                // Update unread count
                const unreadElement = document.getElementById(`unread-${currentFriendId}`);
                unreadElement.classList.add('hidden');
            })
            .catch(error => {
                console.error('Error marking as read:', error);
            });
        }

    function clearChat() {
        if (confirm('Are you sure you want to clear this chat? This action cannot be undone.')) {
            document.getElementById('chat-messages').innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <p class="mt-2">Chat cleared</p>
                </div>
            `;
        }
    }

        function showLoading() {
            const chatMessages = document.getElementById('chat-messages');
            if (window.AppSkeleton && chatMessages) {
                AppSkeleton.render(chatMessages, 'list');
            } else {
                document.getElementById('loading-overlay').classList.remove('hidden');
            }
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

        // Auto-select friend from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const friendId = urlParams.get('friend');
        if (friendId) {
            const friendElement = document.querySelector(`[data-friend-id="${friendId}"]`);
            if (friendElement) {
                const friendName = friendElement.dataset.friendName;
                selectFriend(friendId, friendName);
            }
        }
    </script>
@endsection
