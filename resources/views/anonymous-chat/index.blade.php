@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col space-y-2 min-h-0">
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Anonymous Chat</h1>
                <p class="text-purple-100 text-sm">Chat without revealing names. Admins can monitor conversations for safety.</p>
            </div>
            <button type="button"
                    id="start-anonymous-chat-btn"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium text-purple-700 bg-white hover:bg-purple-50">
                Start New Chat
            </button>
        </div>
    </div>

    <div class="bg-white shadow-sm border border-gray-200 overflow-hidden flex-1 flex flex-col mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex flex-col lg:flex-row h-full min-h-[420px]">
            <div class="w-full lg:w-1/3 border-r border-gray-200 flex flex-col">
                <div class="p-3 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900">Your Anonymous Chats</h3>
                </div>
                <div class="flex-1 overflow-y-auto p-2" id="rooms-list">
                    @forelse($rooms as $entry)
                        <div class="room-item p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 mb-2"
                             data-room-id="{{ $entry['room']->id }}"
                             data-peer-alias="{{ $entry['peer_alias'] }}">
                            <p class="font-medium text-gray-900 truncate">{{ $entry['peer_alias'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">Anonymous conversation</p>
                        </div>
                    @empty
                        <div class="p-6 text-center text-sm text-gray-500">
                            No anonymous chats yet. Start one to message another user without showing your name.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex-1 flex flex-col">
                <div id="no-room-selected" class="flex-1 flex items-center justify-center text-gray-500 p-6 text-center">
                    <div>
                        <p class="text-base font-medium text-gray-900">Select or start an anonymous chat</p>
                        <p class="text-sm text-gray-500 mt-2">You will only see anonymous aliases, not real names.</p>
                    </div>
                </div>

                <div id="chat-area" class="hidden flex-1 flex flex-col">
                    <div class="p-3 sm:p-4 border-b border-gray-200 bg-gray-50">
                        <h3 id="chat-peer-alias" class="font-medium text-gray-900"></h3>
                        <p class="text-xs text-gray-500">You appear as <span id="chat-my-alias" class="font-medium"></span></p>
                    </div>
                    <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50"></div>
                    <div class="p-3 sm:p-4 border-t border-gray-200 bg-white">
                        <div class="flex gap-2">
                            <input type="text"
                                   id="message-input"
                                   maxlength="1000"
                                   placeholder="Type an anonymous message..."
                                   class="flex-1 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="button"
                                    id="send-button"
                                    disabled
                                    class="px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
                                Send
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="start-chat-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-600/50" onclick="closeStartChatModal()"></div>
        <div class="relative w-full max-w-lg rounded-lg bg-white shadow-xl">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Start Anonymous Chat</h3>
                <p class="text-sm text-gray-500 mt-1">Pick an anonymous alias. Real names stay hidden from both users.</p>
            </div>
            <div class="px-6 py-5 max-h-80 overflow-y-auto" id="targets-list">
                <p class="text-sm text-gray-500">Loading available chats...</p>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closeStartChatModal()" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentRoomId = null;

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.room-item').forEach(function (item) {
            item.addEventListener('click', function () {
                selectRoom(this.dataset.roomId, this.dataset.peerAlias);
            });
        });

        document.getElementById('start-anonymous-chat-btn').addEventListener('click', openStartChatModal);
        document.getElementById('send-button').addEventListener('click', sendMessage);
        document.getElementById('message-input').addEventListener('input', function () {
            document.getElementById('send-button').disabled = this.value.trim() === '';
        });
        document.getElementById('message-input').addEventListener('keypress', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendMessage();
            }
        });

        const roomFromUrl = new URLSearchParams(window.location.search).get('room');
        if (roomFromUrl) {
            const roomEl = document.querySelector(`[data-room-id="${roomFromUrl}"]`);
            if (roomEl) {
                selectRoom(roomFromUrl, roomEl.dataset.peerAlias);
            } else {
                fetch(`{{ url('anonymous-chat') }}/${roomFromUrl}/messages`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.room) {
                            selectRoom(roomFromUrl, data.room.peer_alias, data.room.my_alias);
                        }
                    });
            }
        }
    });

    function openStartChatModal() {
        document.getElementById('start-chat-modal').classList.remove('hidden');
        loadTargets();
    }

    function closeStartChatModal() {
        document.getElementById('start-chat-modal').classList.add('hidden');
    }

    function loadTargets() {
        const list = document.getElementById('targets-list');
        list.innerHTML = '<p class="text-sm text-gray-500">Loading available chats...</p>';

        fetch('{{ route('anonymous-chat.targets') }}')
            .then(response => response.json())
            .then(data => {
                if (!data.targets || data.targets.length === 0) {
                    list.innerHTML = '<p class="text-sm text-gray-500">No new users available for anonymous chat right now.</p>';
                    return;
                }

                list.innerHTML = data.targets.map(function (target) {
                    return `
                        <button type="button"
                                data-token="${encodeURIComponent(target.token)}"
                                onclick="startChat(decodeURIComponent(this.dataset.token))"
                                class="w-full text-left p-3 mb-2 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-200">
                            <span class="font-medium text-gray-900">${escapeHtml(target.alias)}</span>
                            <span class="block text-xs text-gray-500 mt-1">Tap to start anonymous chat</span>
                        </button>
                    `;
                }).join('');
            })
            .catch(function () {
                list.innerHTML = '<p class="text-sm text-red-600">Unable to load anonymous chat options.</p>';
            });
    }

    function startChat(token) {
        fetch('{{ route('anonymous-chat.start') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ token }),
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.error || 'Unable to start anonymous chat.');
                    return;
                }

                window.location.href = data.redirect_url;
            })
            .catch(function () {
                alert('Unable to start anonymous chat.');
            });
    }

    function selectRoom(roomId, peerAlias, myAlias) {
        currentRoomId = roomId;

        document.getElementById('no-room-selected').classList.add('hidden');
        document.getElementById('chat-area').classList.remove('hidden');
        document.getElementById('chat-peer-alias').textContent = peerAlias || 'Anonymous';
        if (myAlias) {
            document.getElementById('chat-my-alias').textContent = myAlias;
        }

        document.querySelectorAll('.room-item').forEach(function (item) {
            item.classList.remove('bg-purple-50', 'border-purple-300');
        });
        document.querySelector(`[data-room-id="${roomId}"]`)?.classList.add('bg-purple-50', 'border-purple-300');

        fetch(`{{ url('anonymous-chat') }}/${roomId}/messages`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                document.getElementById('chat-my-alias').textContent = data.room.my_alias || 'Anonymous';
                renderMessages(data.messages || []);
            });
    }

    function renderMessages(messages) {
        const container = document.getElementById('chat-messages');
        container.innerHTML = '';

        if (messages.length === 0) {
            container.innerHTML = '<p class="text-center text-sm text-gray-500 py-8">No messages yet. Say hello anonymously.</p>';
            return;
        }

        messages.forEach(function (message) {
            const wrapper = document.createElement('div');
            wrapper.className = 'flex ' + (message.is_own ? 'justify-end' : 'justify-start');

            const time = new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const senderLine = message.is_own ? '' : `<p class="text-xs font-semibold text-gray-600 mb-1">${message.sender_alias}</p>`;

            wrapper.innerHTML = `
                <div class="max-w-md">
                    <div class="px-3 py-2 rounded-lg ${message.is_own ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-900'}">
                        ${senderLine}
                        <p class="text-sm">${escapeHtml(message.message)}</p>
                        <p class="text-xs mt-1 ${message.is_own ? 'text-indigo-100' : 'text-gray-500'}">${time}</p>
                    </div>
                </div>
            `;

            container.appendChild(wrapper);
        });

        container.scrollTop = container.scrollHeight;
    }

    function sendMessage() {
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        if (!message || !currentRoomId) {
            return;
        }

        fetch(`{{ url('anonymous-chat') }}/${currentRoomId}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ message }),
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.error || 'Unable to send message.');
                    return;
                }

                input.value = '';
                document.getElementById('send-button').disabled = true;

                fetch(`{{ url('anonymous-chat') }}/${currentRoomId}/messages`)
                    .then(response => response.json())
                    .then(payload => renderMessages(payload.messages || []));
            });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
@endsection
