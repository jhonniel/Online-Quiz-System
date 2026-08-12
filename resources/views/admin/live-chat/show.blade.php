@extends('layouts.admin')

@section('title', 'Chat - ' . $ticket->ticket_number)
@section('page-title', 'Live Chat - ' . $ticket->user->name)

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <a href="{{ url('/admin/live-chat') }}" class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700">Live Chat</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-700">{{ $ticket->ticket_number }}</span>
        </div>
    </li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto py-2">
    <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex flex-wrap justify-between items-start sm:items-center gap-4 mb-6">
                <div class="flex items-center space-x-4">
                    <a href="{{ url('/admin/live-chat') }}"
                       class="flex-shrink-0 p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                       title="Back to Live Chat">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="relative">
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                <span class="text-indigo-600 font-semibold text-sm">
                                    {{ strtoupper(substr($ticket->user->name, 0, 2)) }}
                                </span>
                            </div>
                            <!-- Status indicator -->
                            <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white {{ $ticket->user->getStatusBadgeClass() }} flex items-center justify-center">
                                <span class="text-xs">{{ $ticket->user->getStatusIcon() }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h2 class="text-xl font-bold text-gray-900"><x-user-name :user="$ticket->user" :size="20" /></h2>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $ticket->user->getStatusBadgeClass() }}">
                                    {{ ucfirst($ticket->user->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500">{{ $ticket->ticket_number }}</p>
                            <p class="text-xs text-gray-400">Last seen: {{ $ticket->user->getLastActivityText() }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->getStatusBadgeClass() }}">
                        {{ ucfirst($ticket->status) }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->getPriorityBadgeClass() }}">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </div>
            </div>

            <!-- Chat Messages -->
            <div id="chat-messages" class="h-96 overflow-y-auto border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50">
                @foreach($messages as $message)
                    <div class="flex {{ $message->isFromAdmin() ? 'justify-end' : 'justify-start' }} mb-4">
                        <div class="max-w-xs lg:max-w-md">
                            <div class="flex items-end space-x-2 {{ $message->isFromAdmin() ? 'flex-row-reverse space-x-reverse' : '' }}">
                                <div class="w-8 h-8 {{ $message->isFromAdmin() ? 'bg-indigo-600' : 'bg-gray-300' }} rounded-full flex items-center justify-center">
                                    @if($message->isFromAdmin())
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    @else
                                        <span class="text-gray-600 font-semibold text-xs">
                                            {{ strtoupper(substr($message->user->name, 0, 2)) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex flex-col {{ $message->isFromAdmin() ? 'items-end' : 'items-start' }}">
                                    <div class="px-4 py-2 rounded-lg {{ $message->isFromAdmin() ? 'bg-indigo-600 text-white' : 'bg-white text-gray-900' }} shadow-sm">
                                        <p class="text-sm">{{ $message->message }}</p>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $message->created_at ? \Carbon\Carbon::parse($message->created_at)->format('M d, g:i A') : 'N/A' }}
                                        @if($message->isFromAdmin())
                                            - {{ $message->admin->name ?? 'Admin' }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Typing Indicators -->
            <div id="typing-indicators" class="mb-2 hidden">
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                    <span id="typing-text">Someone is typing...</span>
                </div>
            </div>

            <!-- Pre-loaded Messages -->
            @if($preloadedMessages->count() > 0)
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Quick Responses:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($preloadedMessages as $category => $messages)
                            <div class="border border-gray-200 rounded-lg p-2">
                                <h5 class="text-xs font-semibold text-gray-600 mb-1">{{ ucfirst($category) }}</h5>
                                <div class="space-y-1">
                                    @foreach($messages as $message)
                                        <button class="preloaded-message-btn w-full text-left text-xs p-2 hover:bg-gray-100 rounded border border-transparent hover:border-gray-300 transition-colors"
                                                data-message="{{ $message->message }}">
                                            {{ $message->title }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif


            <!-- Chat Input -->
            @if($ticket->isOpen() || $ticket->isReopened())
                <div class="flex space-x-2">
                    <input type="text" id="message-input" placeholder="Type your message..."
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <button id="send-button" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Send
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Press Enter to send message</p>
            @elseif($ticket->isReopenRequested())
                <div class="bg-orange-100 border border-orange-200 rounded-lg p-4 text-center">
                    <p class="text-orange-800 mb-2">User has requested to reopen this ticket.</p>
                    <p class="text-sm text-orange-700 mb-3">Reason: {{ $ticket->reopen_request_reason }}</p>
                    <div class="flex space-x-2 justify-center">
                        <button id="approve-reopen-button" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            Approve Reopen
                        </button>
                        <button id="deny-reopen-button" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Deny Request
                        </button>
                    </div>
                </div>
            @else
                <div class="bg-gray-100 border border-gray-200 rounded-lg p-4 text-center">
                    <p class="text-gray-600 mb-2">This ticket is closed.</p>
                    <button id="reopen-button" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        Reopen Ticket
                    </button>
                </div>
            @endif

            <!-- Ticket Actions -->
            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    <p><strong>Subject:</strong> {{ $ticket->subject }}</p>
                    @if($ticket->description)
                        <p><strong>Description:</strong> {{ $ticket->description }}</p>
                    @endif
                </div>
                <div class="flex space-x-2">
                    @if($ticket->isOpen() || $ticket->isReopened())
                        <button id="close-button" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Close Ticket
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


<script>

// Simple notification function
function showNotification(message, type = 'info') {
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
        if (notification.parentNode) {
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

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Admin Live Chat');

    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const chatMessages = document.getElementById('chat-messages');
    const closeButton = document.getElementById('close-button');
    const reopenButton = document.getElementById('reopen-button');

    // Debug: Check if buttons exist
    console.log('Message input:', messageInput);
    console.log('Send button:', sendButton);
    console.log('Close button:', closeButton);
    console.log('Reopen button:', reopenButton);
    const ticketNumber = '{{ $ticket->ticket_number }}';

    // Auto-scroll to bottom
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Send message
    function sendMessage() {
        const message = messageInput.value.trim();
        if (!message) return;

        // Add message to UI immediately
        addMessageToUI(message, 'admin', '{{ auth()->user()->name }}');
        messageInput.value = '';

        // Send to server
        fetch(`{{ url('/admin/live-chat/' . $ticket->ticket_number . '/message') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to send message');
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
        });
    }

    // Add message to UI
    function addMessageToUI(message, senderType, senderName) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${senderType === 'admin' ? 'justify-end' : 'justify-start'} mb-4`;

        const isAdmin = senderType === 'admin';
        const currentTime = new Date().toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        messageDiv.innerHTML = `
            <div class="max-w-xs lg:max-w-md">
                <div class="flex items-end space-x-2 ${isAdmin ? 'flex-row-reverse space-x-reverse' : ''}">
                    <div class="w-8 h-8 ${isAdmin ? 'bg-indigo-600' : 'bg-gray-300'} rounded-full flex items-center justify-center">
                        ${isAdmin ?
                            '<svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>' :
                            `<span class="text-gray-600 font-semibold text-xs">${senderName.substring(0, 2).toUpperCase()}</span>`
                        }
                    </div>
                    <div class="flex flex-col ${isAdmin ? 'items-end' : 'items-start'}">
                        <div class="px-4 py-2 rounded-lg ${isAdmin ? 'bg-indigo-600 text-white' : 'bg-white text-gray-900'} shadow-sm">
                            <p class="text-sm">${message}</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            ${currentTime}
                            ${isAdmin ? `- ${senderName}` : ''}
                        </p>
                    </div>
                </div>
            </div>
        `;

        chatMessages.appendChild(messageDiv);
        scrollToBottom();
    }

    // Event listeners
    sendButton.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // Initial scroll to bottom
    scrollToBottom();

    // Close ticket
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            const reason = prompt('Reason for closing (optional):');
            if (reason !== null) {
                fetch(`{{ url('/admin/live-chat/' . $ticket->ticket_number . '/close') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ reason: reason })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        ToastNotification.error('Error closing ticket: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error closing ticket:', error);
                    ToastNotification.error('Error closing ticket');
                });
            }
        });
    }

    // Reopen ticket
    if (reopenButton) {
        reopenButton.onclick = function(e) {
            e.preventDefault();

            const reason = prompt('Reason for reopening (optional):');
            if (reason !== null) {
                // Simple form submission approach
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url("/admin/live-chat/" . $ticket->ticket_number . "/reopen") }}';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'reason';
                reasonInput.value = reason;

                form.appendChild(csrfInput);
                form.appendChild(reasonInput);
                document.body.appendChild(form);
                form.submit();
            }
        };
    }


    // Debug: Check for approve/deny buttons
    const approveReopenButton = document.getElementById('approve-reopen-button');
    const denyReopenButton = document.getElementById('deny-reopen-button');
    console.log('Approve reopen button:', approveReopenButton);
    console.log('Deny reopen button:', denyReopenButton);

    // Approve reopen request
    if (approveReopenButton) {
        console.log('Approve reopen button found, adding event listener');
        approveReopenButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Approve reopen button clicked');

            if (confirm('Are you sure you want to approve this reopen request?')) {
                const reason = prompt('Optional reason for approving reopen:');
                console.log('Sending approve request with reason:', reason);

                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url("/admin/live-chat/" . $ticket->ticket_number . "/reopen") }}';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'reason';
                reasonInput.value = reason || '';

                form.appendChild(csrfInput);
                form.appendChild(reasonInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    } else {
        console.log('Approve reopen button not found');
    }

    // Deny reopen request
    if (denyReopenButton) {
        console.log('Deny reopen button found, adding event listener');
        denyReopenButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Deny reopen button clicked');

            if (confirm('Are you sure you want to deny this reopen request?')) {
                const reason = prompt('Reason for denying reopen request:');
                if (reason && reason.trim()) {
                    console.log('Sending deny request with reason:', reason);

                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ url("/admin/live-chat/" . $ticket->ticket_number . "/deny-reopen") }}';

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const reasonInput = document.createElement('input');
                    reasonInput.type = 'hidden';
                    reasonInput.name = 'reason';
                    reasonInput.value = reason;

                    form.appendChild(csrfInput);
                    form.appendChild(reasonInput);
                    document.body.appendChild(form);
                    form.submit();
                } else {
                    alert('Please provide a reason for denying the reopen request.');
                }
            }
        });
    } else {
        console.log('Deny reopen button not found');
    }



    // Polling disabled to reduce server load
    // Poll for new messages every 5 seconds
    // setInterval(function() {
    //     fetch(`{{ url('/admin/live-chat/' . $ticket->ticket_number . '/messages') }}`)
    //         .then(response => response.json())
    //         .then(messages => {
    //             // This is a simple implementation - in a real app you'd want to track the last message ID
    //             // and only add new messages to avoid duplicates
    //         })
    //         .catch(error => {
    //             console.error('Error fetching messages:', error);
    //         });
    // }, 5000);
});
</script>
@endsection
