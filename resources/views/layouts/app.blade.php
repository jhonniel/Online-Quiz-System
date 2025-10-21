<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', $settings['system_name'])</title>

        <!-- Favicon -->
        @if(isset($settings['system_icon']) && $settings['system_icon'])
            <link rel="icon" type="image/x-icon" href="{{ Storage::url($settings['system_icon']) }}">
            <link rel="shortcut icon" type="image/x-icon" href="{{ Storage::url($settings['system_icon']) }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Quiz Animation Styles -->
        <style>
            .question-transition {
                transition: all 0.3s ease-in-out;
            }

            .question-enter {
                opacity: 0;
                transform: translateX(20px);
            }

            .question-enter-active {
                opacity: 1;
                transform: translateX(0);
            }

            .question-exit {
                opacity: 1;
                transform: translateX(0);
            }

            .question-exit-active {
                opacity: 0;
                transform: translateX(-20px);
            }

            /* Custom Scrollbar Styles */
            .overflow-x-auto::-webkit-scrollbar {
                height: 8px;
            }

            .overflow-x-auto::-webkit-scrollbar-track {
                background: #f1f5f9;
                border-radius: 4px;
            }

            .overflow-x-auto::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }

            .overflow-x-auto::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen">
            <!-- Navigation -->
            <nav class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                                    @if($settings['system_logo'])
                                        <img src="{{ Storage::url($settings['system_logo']) }}"
                                             alt="{{ $settings['system_name'] }}"
                                             class="h-8 w-auto object-contain">
                                    @endif
                                    <span class="text-xl font-bold text-gray-800">
                                        {{ $settings['system_name'] }}
                                    </span>
                                </a>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                @auth
                                    @if(!auth()->user()->isAdmin())
                                        <a href="{{ route('user.dashboard') }}" class="border-indigo-400 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                            Dashboard
                                        </a>
                                        <a href="{{ route('user.feedback.index') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                            Feedback
                                        </a>
                                    @endif
                                @endauth
                            </div>
                        </div>

                        <!-- Settings Dropdown -->
                        <div class="hidden sm:ml-6 sm:flex sm:items-center">
                            <div class="ml-3 relative">
                                <div class="flex items-center space-x-4">
                                    @auth
                                        @if(!auth()->user()->isAdmin())
                                            <!-- User Profile Dropdown -->
                                            <div class="relative" x-data="{ open: false }">
                                                <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none">
                                                    <!-- Profile Picture -->
                                                    @if(auth()->user()->profile_picture)
                                                        <img src="{{ auth()->user()->getProfilePictureUrl() }}"
                                                             alt="{{ auth()->user()->name }}"
                                                             class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 hover:border-indigo-500 transition-colors">
                                                    @else
                                                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center border-2 border-gray-200 hover:border-indigo-500 transition-colors">
                                                            <span class="text-indigo-600 font-semibold text-sm">
                                                                {{ auth()->user()->getInitials() }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    <div class="flex flex-col text-left">
                                                        <span class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</span>
                                                        <span class="text-xs text-gray-500">Click to edit</span>
                                                    </div>
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>

                                                <!-- Dropdown Menu -->
                                                <div x-show="open"
                                                     @click.away="open = false"
                                                     x-transition:enter="transition ease-out duration-100"
                                                     x-transition:enter-start="transform opacity-0 scale-95"
                                                     x-transition:enter-end="transform opacity-100 scale-100"
                                                     x-transition:leave="transition ease-in duration-75"
                                                     x-transition:leave-start="transform opacity-100 scale-100"
                                                     x-transition:leave-end="transform opacity-0 scale-95"
                                                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                                    <a href="{{ route('profile.edit') }}"
                                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                        Edit Profile
                                                    </a>
                    <a href="{{ route('user.dashboard') }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('friends.index') }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Friends
                        </div>
                        <span id="friend-request-count" class="hidden bg-red-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center">0</span>
                    </a>
                    <a href="{{ route('user-chat.index') }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            Chat
                        </div>
                        <span id="unread-message-count" class="hidden bg-red-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center">0</span>
                    </a>
                                                    <div class="border-t border-gray-100"></div>
                                                    <form method="POST" action="{{ route('logout') }}">
                                                        @csrf
                                                        <button type="submit"
                                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                            </svg>
                                                            Logout
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-700">
                                                Welcome, {{ auth()->user()->name }}
                                            </span>
                                        @endif

                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                                                Logout
                                            </button>
                                        </form>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>

    @yield('scripts')

    <!-- Live Chat for Logged-in Users -->
    @auth
        @if(!auth()->user()->isAdmin())
            <div id="live-chat-widget" class="fixed bottom-4 right-4 z-50">
                <!-- Chat Toggle Button -->
                <button id="chat-toggle" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-4 shadow-lg transition-all duration-300 hover:scale-110">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </button>

                <!-- Chat Window -->
                <div id="chat-window" class="hidden absolute bottom-16 right-0 w-80 bg-white rounded-lg shadow-xl border border-gray-200">
                    <!-- Chat Header -->
                    <div class="bg-indigo-600 text-white p-4 rounded-t-lg flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <div>
                                <h3 class="font-semibold">Live Support</h3>
                                <p class="text-sm text-indigo-100">We're here to help!</p>
                            </div>
                            <button id="inbox-btn" class="text-indigo-100 hover:text-white p-1" title="Inbox">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            </button>
                        </div>
                        <button id="chat-close" class="text-indigo-100 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Inbox Panel -->
                    <div id="inbox-panel" class="hidden h-64 overflow-y-auto p-4">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-semibold text-gray-900">Your Tickets</h4>
                            <button id="new-ticket-btn" class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">
                                New Ticket
                            </button>
                        </div>
                        <div id="tickets-list" class="space-y-2">
                            <!-- Tickets will be loaded here -->
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <div id="chat-messages" class="h-64 overflow-y-auto p-4 space-y-3">
                        <div class="flex items-start space-x-2">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="bg-gray-100 rounded-lg p-3 max-w-xs">
                                <p class="text-sm text-gray-800">Hello! How can I help you today?</p>
                                <p class="text-xs text-gray-500 mt-1">Support Team</p>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Input -->
                    <div class="p-4 border-t border-gray-200">
                        <div id="chat-input-section">
                            <div class="flex space-x-2">
                                <input type="text" id="chat-input" placeholder="Type your message..."
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <button id="chat-send" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    Send
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Live chat is available during business hours (9 AM - 5 PM)</p>
                        </div>

                        <!-- Closed Chat Message -->
                        <div id="chat-closed-section" class="hidden">
                            <div class="bg-gray-100 border border-gray-200 rounded-lg p-3 text-center">
                                <p class="text-sm text-gray-600 mb-2">This chat has been closed by support.</p>
                                <p class="text-xs text-gray-500 mb-3">If you need further assistance, you can request to reopen it.</p>
                                <button id="reopen-request-btn" class="bg-yellow-600 text-white px-3 py-1 rounded text-xs hover:bg-yellow-700">
                                    Request to Reopen
                                </button>
                            </div>
                        </div>

                        <!-- Reopen Request Pending -->
                        <div id="chat-reopen-requested-section" class="hidden">
                            <div class="bg-orange-100 border border-orange-200 rounded-lg p-3 text-center">
                                <p class="text-sm text-orange-800 mb-2">Your reopen request has been submitted.</p>
                                <p class="text-xs text-orange-700 mb-3">Please wait for admin approval.</p>
                                <div class="flex items-center justify-center">
                                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-orange-600"></div>
                                    <span class="ml-2 text-xs text-orange-600">Waiting for response...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Info -->
                        <div id="ticket-info" class="mt-2 text-xs text-gray-500 hidden">
                            <p><strong>Ticket:</strong> <span id="current-ticket-number"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const chatToggle = document.getElementById('chat-toggle');
                    const chatWindow = document.getElementById('chat-window');
                    const chatClose = document.getElementById('chat-close');
                    const chatInput = document.getElementById('chat-input');
                    const chatSend = document.getElementById('chat-send');
                    const chatMessages = document.getElementById('chat-messages');
                    const inboxBtn = document.getElementById('inbox-btn');
                    const inboxPanel = document.getElementById('inbox-panel');
                    const newTicketBtn = document.getElementById('new-ticket-btn');
                    const ticketsList = document.getElementById('tickets-list');

                    let messages = [];
                    let unreadCount = 0;
                    let currentTicketNumber = null;
                    let isChatClosed = false;
                    let tickets = [];
                    let isInboxOpen = false;

                    // Load messages on chat open
                    function loadMessages() {
                        if (!currentTicketNumber) {
                            return;
                        }

                        fetch(`{{ route("chat.ticket", ":ticketNumber") }}`.replace(':ticketNumber', currentTicketNumber))
                            .then(response => response.json())
                            .then(data => {
                                if (data.messages) {
                                    messages = data.messages;
                                    displayMessages();
                                    markMessagesAsRead();
                                    updateChatStatus(data);
                                }
                            })
                            .catch(error => {
                                console.error('Error loading messages:', error);
                            });
                    }

                    // Update chat status based on ticket status
                    function updateChatStatus(ticketData) {
                        if (!ticketData) {
                            return;
                        }

                        const ticketStatus = ticketData.status;
                        const inputSection = document.getElementById('chat-input-section');
                        const closedSection = document.getElementById('chat-closed-section');
                        const reopenRequestedSection = document.getElementById('chat-reopen-requested-section');
                        const ticketInfo = document.getElementById('ticket-info');
                        const ticketNumberSpan = document.getElementById('current-ticket-number');

                        // Update UI based on ticket status
                        if (ticketStatus === 'reopen_requested') {
                            inputSection.classList.add('hidden');
                            closedSection.classList.add('hidden');
                            reopenRequestedSection.classList.remove('hidden');
                        } else if (ticketStatus === 'closed') {
                            inputSection.classList.add('hidden');
                            closedSection.classList.remove('hidden');
                            reopenRequestedSection.classList.add('hidden');
                        } else {
                            // Open, reopened, or other active statuses
                            inputSection.classList.remove('hidden');
                            closedSection.classList.add('hidden');
                            reopenRequestedSection.classList.add('hidden');
                        }

                        if (currentTicketNumber) {
                            ticketInfo.classList.remove('hidden');
                            ticketNumberSpan.textContent = currentTicketNumber;
                        }
                    }

                    // Display messages
                    function displayMessages() {
                        chatMessages.innerHTML = '';
                        messages.forEach(message => {
                            addMessageToUI(message.message, message.sender_type, message.created_at);
                        });
                        scrollToBottom();
                    }

                    // Add message to UI
                    function addMessageToUI(text, senderType, timestamp) {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'flex items-start space-x-2';

                        const isUser = senderType === 'user';
                        const time = new Date(timestamp).toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        if (isUser) {
                            messageDiv.innerHTML = `
                                <div class="flex-1"></div>
                                <div class="bg-indigo-600 text-white rounded-lg p-3 max-w-xs">
                                    <p class="text-sm">${text}</p>
                                    <p class="text-xs text-indigo-100 mt-1">You - ${time}</p>
                                </div>
                            `;
                        } else {
                            messageDiv.innerHTML = `
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div class="bg-gray-100 rounded-lg p-3 max-w-xs">
                                    <p class="text-sm text-gray-800">${text}</p>
                                    <p class="text-xs text-gray-500 mt-1">Support Team - ${time}</p>
                                </div>
                            `;
                        }

                        chatMessages.appendChild(messageDiv);
                    }

                    // Scroll to bottom
                    function scrollToBottom() {
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }

                    // Mark messages as read
                    function markMessagesAsRead() {
                        const unreadMessageIds = messages
                            .filter(msg => msg.sender_type === 'admin' && !msg.is_read)
                            .map(msg => msg.id);

                        if (unreadMessageIds.length > 0) {
                            fetch('{{ route("chat.mark-read") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({ message_ids: unreadMessageIds })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    updateUnreadCount();
                                }
                            })
                            .catch(error => {
                                console.error('Error marking messages as read:', error);
                            });
                        }
                    }

                    // Update unread count
                    function updateUnreadCount() {
                        fetch('{{ route("chat.unread-count") }}')
                            .then(response => response.json())
                            .then(data => {
                                unreadCount = data.count;
                                updateChatButton();
                            })
                            .catch(error => {
                                console.error('Error fetching unread count:', error);
                            });
                    }

                    // Update chat button with unread count
                    function updateChatButton() {
                        const chatToggle = document.getElementById('chat-toggle');
                        if (unreadCount > 0) {
                            chatToggle.innerHTML = `
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">${unreadCount}</span>
                            `;
                            chatToggle.classList.add('relative');
                        } else {
                            chatToggle.innerHTML = `
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            `;
                        }
                    }

                    // Toggle chat window
                    chatToggle.addEventListener('click', function() {
                        chatWindow.classList.toggle('hidden');
                        if (!chatWindow.classList.contains('hidden')) {
                            loadMessages();
                            chatInput.focus();
                        }
                    });

                    // Close chat window
                    chatClose.addEventListener('click', function() {
                        chatWindow.classList.add('hidden');
                    });

                    // Send message
                    function sendMessage() {
                        const message = chatInput.value.trim();
                        if (!message || isChatClosed) return;

                        // Add user message to UI immediately
                        addMessageToUI(message, 'user', new Date().toISOString());
                        chatInput.value = '';
                        scrollToBottom();

                        // Send to server
                        const requestData = { message: message };
                        if (currentTicketNumber) {
                            requestData.ticket_number = currentTicketNumber;
                        }

                        fetch('{{ route("chat.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(requestData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                messages.push(data.message);
                                if (data.ticket_number) {
                                    currentTicketNumber = data.ticket_number;
                                    updateChatStatus();
                                }
                            } else {
                                console.error('Failed to send message:', data.message);
                                ToastNotification.error('Failed to send message: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error sending message:', error);
                        });
                    }

                    // Request to reopen chat
                    function requestReopen() {
                        if (!currentTicketNumber) return;

                        const reason = prompt('Please provide a reason for reopening this chat:');
                        if (reason && reason.trim()) {
                            fetch(`{{ route("chat.reopen", ":ticketNumber") }}`.replace(':ticketNumber', currentTicketNumber), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({ reason: reason.trim() })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    ToastNotification.success('Reopen request submitted successfully. Admin will be notified.');
                                    loadMessages(); // Reload to get updated status
                                } else {
                                    ToastNotification.error('Failed to submit reopen request: ' + (data.message || 'Unknown error'));
                                }
                            })
                            .catch(error => {
                                console.error('Error submitting reopen request:', error);
                                ToastNotification.error('Error submitting reopen request');
                            });
                        }
                    }

                    // Inbox functionality
                    function loadTickets() {
                        fetch('{{ route("chat.tickets") }}')
                            .then(response => response.json())
                            .then(data => {
                                tickets = data;
                                displayTickets();
                            })
                            .catch(error => {
                                console.error('Error loading tickets:', error);
                            });
                    }

                    function displayTickets() {
                        ticketsList.innerHTML = '';

                        if (tickets.length === 0) {
                            ticketsList.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">No tickets yet. Create your first ticket!</p>';
                            return;
                        }

                        tickets.forEach(ticket => {
                            const ticketElement = document.createElement('div');
                            ticketElement.className = `p-3 border rounded-lg cursor-pointer hover:bg-gray-50 ${ticket.ticket_number === currentTicketNumber ? 'bg-indigo-50 border-indigo-200' : 'border-gray-200'}`;
                            ticketElement.innerHTML = `
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium text-gray-900">${ticket.subject || 'Support Request'}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${getStatusBadgeClass(ticket.status)}">
                                                ${ticket.status.replace('_', ' ')}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">${ticket.ticket_number}</p>
                                        <p class="text-xs text-gray-400">${formatDate(ticket.updated_at)}</p>
                                    </div>
                                    ${ticket.unread_count > 0 ? `<span class="bg-red-500 text-white text-xs rounded-full px-2 py-1">${ticket.unread_count}</span>` : ''}
                                </div>
                            `;

                            ticketElement.addEventListener('click', () => {
                                switchToTicket(ticket.ticket_number);
                            });

                            ticketsList.appendChild(ticketElement);
                        });
                    }

                    function getStatusBadgeClass(status) {
                        switch(status) {
                            case 'open': return 'bg-green-100 text-green-800';
                            case 'closed': return 'bg-gray-100 text-gray-800';
                            case 'reopened': return 'bg-yellow-100 text-yellow-800';
                            case 'reopen_requested': return 'bg-orange-100 text-orange-800';
                            default: return 'bg-gray-100 text-gray-800';
                        }
                    }

                    function formatDate(dateString) {
                        const date = new Date(dateString);
                        const now = new Date();
                        const diffInHours = (now - date) / (1000 * 60 * 60);

                        if (diffInHours < 1) {
                            return 'Just now';
                        } else if (diffInHours < 24) {
                            return `${Math.floor(diffInHours)}h ago`;
                        } else {
                            return `${Math.floor(diffInHours / 24)}d ago`;
                        }
                    }

                    function switchToTicket(ticketNumber) {
                        currentTicketNumber = ticketNumber;
                        isInboxOpen = false;
                        inboxPanel.classList.add('hidden');
                        chatMessages.classList.remove('hidden');
                        loadMessages();
                        displayTickets(); // Refresh to show current ticket as selected
                    }

                    function createNewTicket() {
                        const subject = prompt('What is this ticket about? (optional)');
                        if (subject !== null) {
                            fetch('{{ route("chat.create") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({ subject: subject })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    currentTicketNumber = data.ticket_number;
                                    isInboxOpen = false;
                                    inboxPanel.classList.add('hidden');
                                    chatMessages.classList.remove('hidden');
                                    loadTickets();
                                    loadMessages();
                                } else {
                                    ToastNotification.error('Error creating ticket: ' + (data.message || 'Unknown error'));
                                }
                            })
                            .catch(error => {
                                console.error('Error creating ticket:', error);
                                ToastNotification.error('Error creating ticket');
                            });
                        }
                    }

                    // Toggle inbox
                    inboxBtn.addEventListener('click', function() {
                        isInboxOpen = !isInboxOpen;
                        if (isInboxOpen) {
                            inboxPanel.classList.remove('hidden');
                            chatMessages.classList.add('hidden');
                            loadTickets();
                        } else {
                            inboxPanel.classList.add('hidden');
                            chatMessages.classList.remove('hidden');
                        }
                    });

                    // New ticket button
                    newTicketBtn.addEventListener('click', createNewTicket);

                    // Load first available ticket
                    function loadFirstTicket() {
                        fetch('{{ route("chat.tickets") }}')
                            .then(response => response.json())
                            .then(data => {
                                if (data.length > 0) {
                                    currentTicketNumber = data[0].ticket_number;
                                    loadMessages();
                                }
                            })
                            .catch(error => {
                                console.error('Error loading first ticket:', error);
                            });
                    }

                    // Load notification counts
                    function loadNotificationCounts() {
                        // Load friend request count
                        fetch('{{ route("friends.index") }}')
                            .then(response => response.text())
                            .then(html => {
                                // Parse the HTML to extract pending requests count
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                const pendingRequests = doc.querySelectorAll('[data-friend-request]');
                                const count = pendingRequests.length;

                                const friendRequestCount = document.getElementById('friend-request-count');
                                if (count > 0) {
                                    friendRequestCount.textContent = count;
                                    friendRequestCount.classList.remove('hidden');
                                } else {
                                    friendRequestCount.classList.add('hidden');
                                }
                            })
                            .catch(error => {
                                console.error('Error loading friend request count:', error);
                            });

                        // Load unread message count
                        fetch('{{ route("user-chat.unread-count") }}')
                            .then(response => response.json())
                            .then(data => {
                                const unreadMessageCount = document.getElementById('unread-message-count');
                                if (data.count > 0) {
                                    unreadMessageCount.textContent = data.count;
                                    unreadMessageCount.classList.remove('hidden');
                                } else {
                                    unreadMessageCount.classList.add('hidden');
                                }
                            })
                            .catch(error => {
                                console.error('Error loading unread message count:', error);
                            });
                    }

                    // Event listeners
                    chatSend.addEventListener('click', sendMessage);
                    chatInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            sendMessage();
                        }
                    });

                    // Reopen request button
                    const reopenRequestBtn = document.getElementById('reopen-request-btn');
                    if (reopenRequestBtn) {
                        reopenRequestBtn.addEventListener('click', requestReopen);
                    }

                    // Initial load
                    updateUnreadCount();
                    loadFirstTicket();
                    loadNotificationCounts();

                    // Poll for new messages every 10 seconds
                    setInterval(function() {
                        if (!chatWindow.classList.contains('hidden')) {
                            loadMessages();
                        } else {
                            updateUnreadCount();
                        }
                    }, 10000);
                });

                // User Status Tracking
                let lastActivity = Date.now();
                let isIdle = false;
                let statusUpdateInterval;

                // Track user activity
                function trackActivity() {
                    lastActivity = Date.now();
                    if (isIdle) {
                        isIdle = false;
                        updateUserStatus('online');
                    }
                }

                // Check if user is idle (no activity for 5 minutes)
                function checkIdleStatus() {
                    const now = Date.now();
                    const timeSinceActivity = now - lastActivity;
                    const idleThreshold = 5 * 60 * 1000; // 5 minutes

                    if (timeSinceActivity > idleThreshold && !isIdle) {
                        isIdle = true;
                        updateUserStatus('idle');
                    }
                }

                // Update user status
                function updateUserStatus(status) {
                    fetch('{{ route("status.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ status: status })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Status updated to:', data.status);
                        }
                    })
                    .catch(error => {
                        console.error('Error updating status:', error);
                    });
                }

                // Set up activity tracking
                ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
                    document.addEventListener(event, trackActivity, true);
                });

                // Check idle status every minute
                statusUpdateInterval = setInterval(checkIdleStatus, 60000);

                // Set user as online when page loads
                updateUserStatus('online');

                // Set user as away when page is hidden
                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        updateUserStatus('away');
                    } else {
                        updateUserStatus('online');
                        lastActivity = Date.now();
                    }
                });

                // Set user as offline when page is about to unload
                window.addEventListener('beforeunload', function() {
                    updateUserStatus('offline');
                });
            </script>
        @endif
    @endauth

        <!-- Toast Notifications -->
        @include('components.toast')

        <!-- Seasonal Effects -->
        @include('components.seasonal-effects')
    </body>
</html>
