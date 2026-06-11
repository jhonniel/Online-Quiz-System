@extends('layouts.user')

@section('content')
    <div class="h-full flex flex-col space-y-2 min-h-0" data-chat-realtime>
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
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Chat</h1>
                    <p class="text-indigo-100 text-sm">Message friends, groups, or chat anonymously</p>
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
    <div class="bg-white shadow-sm border-t border-b border-gray-200 overflow-hidden flex-1 flex flex-col min-w-0">
        <div class="flex flex-col lg:flex-row h-full min-w-0">
            <!-- Friends Sidebar -->
            <div class="w-full lg:w-1/3 border-r border-gray-200 flex flex-col">
                <!-- Sidebar Header -->
                <div class="p-3 sm:p-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900">Chats</h3>
                            <p class="text-xs sm:text-sm text-gray-500">Friends, groups, and anonymous</p>
                        </div>
                        <button type="button"
                                onclick="openStartAnonymousChatModal()"
                                class="text-xs font-medium text-purple-700 hover:text-purple-900 whitespace-nowrap">
                            + Anonymous
                        </button>
                    </div>
                </div>

                <div class="px-3 pt-3 border-b border-gray-200">
                    <x-story-bar :feed="$storyFeed" />
                </div>

                <!-- Chat Lists -->
                <div class="flex-1 overflow-y-auto">
                    @if($groupChats->count() > 0)
                        <div class="px-3 pt-3 pb-1">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Group Chats</h4>
                        </div>
                        <div id="group-chats-list" class="p-2 pt-0 border-b border-gray-200">
                            @foreach($groupChats as $entry)
                                @php($groupChat = $entry['group'])
                                <div class="group-chat-item p-2 sm:p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors mb-2"
                                     data-group-id="{{ $groupChat->id }}"
                                     data-group-name="{{ $groupChat->name }}"
                                     data-members-count="{{ $groupChat->members_count }}">
                                    <div class="flex items-center space-x-2 sm:space-x-3">
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-purple-100 rounded-full flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="chat-item-title font-medium text-gray-900 truncate text-sm sm:text-base {{ $entry['has_new'] ? 'font-semibold' : '' }}">{{ $groupChat->name }}</p>
                                            <p class="chat-item-subtitle text-xs truncate {{ $entry['has_new'] ? 'text-purple-700 font-medium' : 'text-gray-500' }}">
                                                @if($entry['has_new'])
                                                    @if($entry['preview'])
                                                        New message: {{ $entry['preview'] }}
                                                    @else
                                                        New message
                                                    @endif
                                                @else
                                                    {{ $groupChat->members_count }} members
                                                @endif
                                            </p>
                                        </div>
                                        <span id="unread-group-{{ $groupChat->id }}" class="{{ $entry['unread_count'] > 0 ? '' : 'hidden' }} bg-red-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center shrink-0">{{ $entry['unread_count'] > 99 ? '99+' : $entry['unread_count'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($anonymousRooms->count() > 0)
                        <div class="px-3 pt-3 pb-1">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Anonymous</h4>
                        </div>
                        <div id="anonymous-chats-list" class="p-2 pt-0 border-b border-gray-200">
                            @foreach($anonymousRooms as $entry)
                                <div class="anonymous-chat-item p-2 sm:p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors mb-2"
                                     data-anonymous-room-id="{{ $entry['room']->id }}"
                                     data-peer-alias="{{ $entry['peer_alias'] }}"
                                     data-peer-name="{{ $entry['is_creator'] ? ($entry['peer_name'] ?? '') : '' }}"
                                     data-is-creator="{{ $entry['is_creator'] ? '1' : '0' }}">
                                    <div class="flex items-center space-x-2 sm:space-x-3">
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-purple-100 rounded-full flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-1M9 12h6m-6-4h6m2-5H7a2 2 0 00-2 2v6a2 2 0 002 2h2v4l4-4h5a2 2 0 002-2V7a2 2 0 00-2-2z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="chat-item-title font-medium text-gray-900 truncate text-sm sm:text-base {{ $entry['has_new'] ? 'font-semibold' : '' }}">{{ $entry['peer_alias'] }}</p>
                                            <p class="chat-item-subtitle text-xs truncate {{ $entry['has_new'] ? 'text-purple-700 font-medium' : 'text-gray-500' }}">
                                                @if($entry['has_new'])
                                                    @if($entry['preview'])
                                                        New message: {{ $entry['preview'] }}
                                                    @else
                                                        New message
                                                    @endif
                                                @else
                                                    Anonymous chat
                                                @endif
                                            </p>
                                        </div>
                                        <span id="unread-anonymous-{{ $entry['room']->id }}" class="{{ $entry['unread_count'] > 0 ? '' : 'hidden' }} bg-red-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center shrink-0">{{ $entry['unread_count'] > 99 ? '99+' : $entry['unread_count'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="px-3 pt-3 pb-1">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Friends</h4>
                    </div>
                    <div id="friends-list" class="p-2 pt-0">
                        @foreach($friends as $entry)
                            @php($friend = $entry['friend'])
                            <div class="friend-item p-2 sm:p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors mb-2"
                                 data-friend-id="{{ $friend->id }}"
                                 data-friend-name="{{ $friend->name }}">
                                <div class="flex items-center space-x-2 sm:space-x-3">
                                    <div class="relative">
                                        @php($friendRing = $storyRingMap[$friend->id] ?? ['has_story' => false, 'has_unviewed' => false])
                                        <x-profile-avatar
                                            :user="$friend"
                                            size="sm"
                                            :has-story="$friendRing['has_story']"
                                            :has-unviewed="$friendRing['has_unviewed']"
                                            :clickable="$friendRing['has_story']"
                                            :story-user-id="$friend->id" />
                                        <!-- Online indicator -->
                                        <div class="online-indicator absolute bottom-0 right-0 w-2 h-2 sm:w-3 sm:h-3 bg-gray-300 border-2 border-white rounded-full"></div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="chat-item-title font-medium text-gray-900 truncate text-sm sm:text-base {{ $entry['has_new'] ? 'font-semibold' : '' }}">{{ $friend->name }}</p>
                                        <p class="chat-item-subtitle text-xs sm:text-sm truncate {{ $entry['has_new'] ? 'text-indigo-700 font-medium' : 'text-gray-500' }}">
                                            @if($entry['has_new'])
                                                @if($entry['preview'])
                                                    New message: {{ $entry['preview'] }}
                                                @else
                                                    New message
                                                @endif
                                            @else
                                                Click to chat
                                            @endif
                                        </p>
                                    </div>
                                    <span id="unread-{{ $friend->id }}" class="{{ $entry['unread_count'] > 0 ? '' : 'hidden' }} bg-red-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center shrink-0">{{ $entry['unread_count'] > 99 ? '99+' : $entry['unread_count'] }}</span>
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
            <div class="flex-1 flex flex-col min-w-0">
                <!-- No Chat Selected -->
                <div id="no-chat-selected" class="flex-1 flex items-center justify-center text-gray-500">
                    <div class="text-center p-4">
                        <svg class="mx-auto h-16 w-16 sm:h-20 sm:w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <h3 class="mt-4 text-base sm:text-lg font-medium text-gray-900">Select a chat to start messaging</h3>
                        <p class="mt-2 text-sm text-gray-500">Choose a friend, group, or anonymous chat from the list.</p>
                    </div>
                </div>

                <!-- Chat Interface -->
                <div id="chat-area" class="hidden flex-1 flex flex-col min-w-0">
                    <!-- Chat Header -->
                    <div class="flex items-center space-x-2 sm:space-x-3 p-3 sm:p-4 border-b border-gray-200 bg-gray-50">
                        <div class="relative">
                            <div id="chat-friend-avatar" class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                <span class="text-indigo-600 font-semibold text-xs sm:text-sm">U</span>
                            </div>
                            <div class="absolute bottom-0 right-0 w-2 h-2 sm:w-3 sm:h-3 bg-green-400 border-2 border-white rounded-full"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 id="chat-friend-name" class="font-medium text-gray-900 text-sm sm:text-base truncate">Chat Name</h3>
                            <p id="chat-subtitle" class="text-xs sm:text-sm text-gray-500">Online</p>
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
                    <div id="chat-messages" class="flex-1 overflow-y-auto overflow-x-hidden p-3 sm:p-4 space-y-3 sm:space-y-4 bg-gray-50 min-w-0">
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
                                <input type="file"
                                       id="chat-image-input"
                                       class="hidden"
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <button type="button"
                                        id="image-upload-button"
                                        class="absolute left-2 sm:left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-indigo-600"
                                        title="Send image (max 2MB)">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                                <input type="text"
                                       id="message-input"
                                       placeholder="Type your message..."
                                       class="w-full pl-9 sm:pl-10 pr-10 sm:pr-12 py-2 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base">
                                <button type="button" id="emoji-button"
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
                        <p class="text-xs text-gray-500 mt-2">Press Enter to send · Images up to 2MB (auto-delete in 24h)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="start-anonymous-chat-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-600/50" onclick="closeStartAnonymousChatModal()"></div>
        <div class="relative w-full max-w-lg rounded-lg bg-white shadow-xl">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Start Anonymous Chat</h3>
                <p class="text-sm text-gray-500 mt-1">Pick someone to message. They will only see your anonymous alias.</p>
            </div>
            <div class="px-6 py-5 max-h-80 overflow-y-auto" id="anonymous-targets-list">
                <p class="text-sm text-gray-500">Loading available chats...</p>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closeStartAnonymousChatModal()" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="chat-image-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-600/50" onclick="closeChatImageModal()"></div>
        <div class="relative w-full max-w-md rounded-lg bg-white shadow-xl">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Send image</h3>
                <p class="text-sm text-gray-500 mt-1">Max 2MB · Deleted automatically after 24 hours</p>
            </div>
            <div class="px-6 py-5 space-y-4">
                <img id="chat-image-preview" src="" alt="Preview" class="hidden max-h-48 mx-auto rounded-lg border border-gray-200 object-contain">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Who can see it?</label>
                    <div class="space-y-2">
                        <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="chat_image_mode" value="stay_24h" class="mt-1" checked>
                            <span>
                                <span class="block text-sm font-medium text-gray-900">Stay in chat (24h)</span>
                                <span class="block text-xs text-gray-500">Visible in the thread until the timer ends.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="chat_image_mode" value="view_once" class="mt-1">
                            <span>
                                <span class="block text-sm font-medium text-gray-900">View once</span>
                                <span class="block text-xs text-gray-500">Recipients open it one time, then it is hidden.</span>
                            </span>
                        </label>
                    </div>
                </div>
                <input type="text"
                       id="chat-image-caption"
                       maxlength="1000"
                       placeholder="Optional caption..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
                <button type="button" onclick="closeChatImageModal()" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                <button type="button" id="chat-image-send-button" onclick="sendSelectedChatImage()" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Send image</button>
            </div>
        </div>
    </div>
</div>

    <!-- Loading overlay for chat messages -->
    <div id="loading-overlay" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <p class="text-sm text-gray-500 text-center py-8">Loading messages...</p>
        </div>
    </div>

    <x-story-ui :feed="$storyFeed" />
</div>
@endsection

@section('scripts')
<script>
        const currentUserId = {{ auth()->id() }};
        const currentUserName = @json(auth()->user()->name);
        let onlineUserIds = new Set();
        let currentFriendId = null;
        let currentFriendName = null;
        let currentGroupId = null;
        let currentGroupName = null;
        let currentAnonymousRoomId = null;
        let currentAnonymousPeerAlias = null;
        let currentAnonymousPeerName = null;
        let currentMyAnonymousAlias = null;
        let currentChatType = null;
        let messages = [];
        let typingTimeout = null;
        let pendingChatImageFile = null;
        let mediaTimerInterval = null;
        const chatMediaBaseUrl = @json(url('/chat-media'));
        const knownPeerNameFromUrl = new URLSearchParams(window.location.search).get('peer_name');

        function chatJsonHeaders() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            };
        }

        async function chatFetchJson(url, options = {}) {
            const response = await fetch(url, {
                credentials: 'same-origin',
                ...options,
                headers: {
                    ...chatJsonHeaders(),
                    ...(options.headers || {}),
                },
            });

            const contentType = response.headers.get('content-type') || '';
            let data = null;

            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                throw new Error(text || `Request failed (${response.status})`);
            }

            if (!response.ok) {
                const message = data.error
                    || data.message
                    || (data.errors ? Object.values(data.errors).flat().join(' ') : null)
                    || `Request failed (${response.status})`;
                throw new Error(message);
            }

            return data;
        }

        async function chatUploadForm(url, formData) {
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData,
            });

            const contentType = response.headers.get('content-type') || '';
            let data = null;

            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                throw new Error(text || `Request failed (${response.status})`);
            }

            if (!response.ok) {
                const message = data.error
                    || data.message
                    || (data.errors ? Object.values(data.errors).flat().join(' ') : null)
                    || `Request failed (${response.status})`;
                throw new Error(message);
            }

            return data;
        }

        function formatMediaRemaining(expiresAt) {
            const remainingMs = new Date(expiresAt).getTime() - Date.now();
            if (remainingMs <= 0) {
                return 'Expired';
            }

            const totalSeconds = Math.floor(remainingMs / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        function enrichMediaPayload(media, isOwn) {
            if (!media) {
                return null;
            }

            const expiresAt = media.expires_at;
            if (expiresAt && new Date(expiresAt).getTime() <= Date.now()) {
                return null;
            }

            const mode = media.mode || 'stay_24h';
            const viewed = !!media.viewed;
            const revealed = !!media._revealed;
            let canView = isOwn || mode === 'stay_24h';

            if (typeof media.can_view === 'boolean') {
                canView = media.can_view;
            }

            if (mode === 'view_once' && !isOwn) {
                canView = revealed || (canView && !viewed);
            }

            return {
                ...media,
                mode,
                is_sender: isOwn,
                viewed,
                can_view: canView,
                url: canView ? `${chatMediaBaseUrl}/${media.id}` : null,
            };
        }

        function buildMediaHtml(media, isOwn) {
            const payload = enrichMediaPayload(media, isOwn);
            if (!payload) {
                return '';
            }

            const timerText = formatMediaRemaining(payload.expires_at);
            const modeLabel = payload.mode === 'view_once' ? '1 view' : '24h';

            if (payload.mode === 'view_once' && !payload.is_sender && !payload.can_view && !payload._revealed) {
                if (payload.viewed) {
                    return `<div class="mt-1 rounded-md border border-dashed px-3 py-5 text-center text-xs opacity-80">Photo opened</div>`;
                }

                return `<button type="button" onclick="revealChatMedia(${payload.id})" class="mt-1 w-full rounded-md border border-dashed px-3 py-6 text-center text-xs hover:opacity-90">
                    <span class="block font-semibold mb-1">Tap to view once</span>
                    <span class="block opacity-80">${modeLabel} · deletes in ${timerText}</span>
                </button>`;
            }

            if (payload.url) {
                return `<div class="relative mt-1 max-w-full">
                    <img src="${escapeHtml(payload.url)}" alt="Chat image" class="max-w-full rounded-md max-h-64 object-contain bg-black/5" loading="lazy">
                    <div class="chat-media-timer absolute bottom-2 right-2 bg-black/75 text-white text-[10px] px-2 py-1 rounded" data-expires-at="${escapeHtml(payload.expires_at)}">${modeLabel} · ${timerText}</div>
                </div>`;
            }

            return '';
        }

        function refreshMediaTimers() {
            document.querySelectorAll('.chat-media-timer[data-expires-at]').forEach((element) => {
                const expiresAt = element.dataset.expiresAt;
                const modeLabel = element.textContent.startsWith('1 view') ? '1 view' : '24h';
                element.textContent = `${modeLabel} · ${formatMediaRemaining(expiresAt)}`;
            });
        }

        function startMediaTimerUpdates() {
            if (mediaTimerInterval) {
                clearInterval(mediaTimerInterval);
            }

            refreshMediaTimers();
            mediaTimerInterval = setInterval(refreshMediaTimers, 1000);
        }

        function revealChatMedia(mediaId) {
            const index = messages.findIndex((entry) => Number(entry.media?.id) === Number(mediaId));
            if (index === -1) {
                return;
            }

            messages[index].media._revealed = true;
            messages[index].media.viewed = true;
            displayMessages();
        }

        function closeChatImageModal() {
            document.getElementById('chat-image-modal').classList.add('hidden');
            pendingChatImageFile = null;
            document.getElementById('chat-image-input').value = '';
            document.getElementById('chat-image-caption').value = '';
            const preview = document.getElementById('chat-image-preview');
            preview.classList.add('hidden');
            preview.removeAttribute('src');
        }

        function openChatImageModal(file) {
            if (file.size > 2097152) {
                showNotification('Images must be 2MB or smaller.', 'error');
                return;
            }

            pendingChatImageFile = file;
            const preview = document.getElementById('chat-image-preview');
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
            document.getElementById('chat-image-modal').classList.remove('hidden');
        }

        function getSelectedImageMode() {
            const selected = document.querySelector('input[name="chat_image_mode"]:checked');
            return selected ? selected.value : 'stay_24h';
        }

        function getChatSendUrl() {
            if (currentChatType === 'anonymous') {
                return `{{ url('anonymous-chat') }}/${currentAnonymousRoomId}/messages`;
            }

            if (currentChatType === 'group') {
                return `{{ url('group-chats') }}/${currentGroupId}/messages`;
            }

            return '{{ url("/user-chat/send") }}';
        }

        async function sendSelectedChatImage() {
            if (!pendingChatImageFile || !currentChatType) {
                showNotification('Select a conversation first.', 'error');
                return;
            }

            const sendButton = document.getElementById('chat-image-send-button');
            sendButton.disabled = true;

            const formData = new FormData();
            formData.append('image', pendingChatImageFile);
            formData.append('image_mode', getSelectedImageMode());

            const caption = document.getElementById('chat-image-caption').value.trim();
            if (caption) {
                formData.append('message', caption);
            }

            if (currentChatType === 'friend') {
                formData.append('receiver_id', currentFriendId);
            }

            try {
                const data = await chatUploadForm(getChatSendUrl(), formData);
                closeChatImageModal();

                if (data.error) {
                    showNotification(data.error, 'error');
                } else if (data.message) {
                    appendSentMessage(data.message);
                    loadUnreadCounts();
                }
            } catch (error) {
                showNotification(error.message || 'Error sending image', 'error');
            } finally {
                sendButton.disabled = false;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.friend-item').forEach(item => {
                item.addEventListener('click', function() {
                    selectFriend(this.dataset.friendId, this.dataset.friendName);
                });
            });

            document.querySelectorAll('.group-chat-item').forEach(item => {
                item.addEventListener('click', function() {
                    selectGroup(this.dataset.groupId, this.dataset.groupName);
                });
            });

            document.querySelectorAll('.anonymous-chat-item').forEach(item => {
                item.addEventListener('click', function() {
                    selectAnonymous(
                        this.dataset.anonymousRoomId,
                        this.dataset.peerAlias,
                        this.dataset.peerName || '',
                        this.dataset.isCreator === '1'
                    );
                });
            });

            // Send message
            const sendButton = document.getElementById('send-button');
            const messageInput = document.getElementById('message-input');

            sendButton.addEventListener('click', sendMessage);
            document.getElementById('image-upload-button').addEventListener('click', () => {
                document.getElementById('chat-image-input').click();
            });
            document.getElementById('chat-image-input').addEventListener('change', function() {
                const file = this.files && this.files[0];
                if (file) {
                    openChatImageModal(file);
                }
                this.value = '';
            });
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // Enable/disable send button based on input
            messageInput.addEventListener('input', function() {
                sendButton.disabled = this.value.trim() === '';
                if (window.ChatRealtime && currentChatType) {
                    if (currentChatType === 'anonymous') {
                        window.ChatRealtime.sendTypingSignal({
                            name: currentMyAnonymousAlias || 'Anonymous',
                            anonymous: true,
                        });
                    } else {
                        window.ChatRealtime.sendTypingSignal({
                            name: currentUserName,
                            userId: currentUserId,
                        });
                    }
                }
            });

            function bootChatPage() {
                initChatRealtime();
                loadUnreadCounts();
            }

            if (window.ChatRealtime) {
                bootChatPage();
            } else {
                window.addEventListener('chat-realtime:ready', bootChatPage, { once: true });
            }

            // Poll for new messages every 3 seconds
            // setInterval(loadUnreadCounts, 3000);
        });

        function clearChatSelection() {
            document.querySelectorAll('.friend-item, .group-chat-item, .anonymous-chat-item').forEach(item => {
                item.classList.remove('bg-indigo-50', 'border-indigo-300', 'bg-purple-50', 'border-purple-300');
            });
        }

        function selectFriend(friendId, friendName) {
            currentChatType = 'friend';
            currentFriendId = friendId;
            currentFriendName = friendName;
            currentGroupId = null;
            currentGroupName = null;
            currentAnonymousRoomId = null;
            currentAnonymousPeerAlias = null;
            currentAnonymousPeerName = null;
            currentMyAnonymousAlias = null;

            document.getElementById('no-chat-selected').classList.add('hidden');
            document.getElementById('chat-area').classList.remove('hidden');
            document.getElementById('chat-friend-name').textContent = friendName;
            document.getElementById('chat-subtitle').classList.remove('hidden');
            document.getElementById('chat-subtitle').textContent = 'Direct message';

            clearChatSelection();
            document.querySelector(`[data-friend-id="${friendId}"]`)?.classList.add('bg-indigo-50', 'border-indigo-300');

            clearSidebarUnread('friend', friendId);
            loadMessages();
            markAsRead();
            if (window.ChatRealtime) {
                window.ChatRealtime.switchChannel('friend', Number(friendId));
            }
        }

        function selectGroup(groupId, groupName) {
            currentChatType = 'group';
            currentGroupId = groupId;
            currentGroupName = groupName;
            currentFriendId = null;
            currentFriendName = null;
            currentAnonymousRoomId = null;
            currentAnonymousPeerAlias = null;
            currentAnonymousPeerName = null;
            currentMyAnonymousAlias = null;

            document.getElementById('no-chat-selected').classList.add('hidden');
            document.getElementById('chat-area').classList.remove('hidden');
            document.getElementById('chat-friend-name').textContent = groupName;
            document.getElementById('chat-subtitle').classList.remove('hidden');
            document.getElementById('chat-subtitle').textContent = 'Group chat';

            clearChatSelection();
            document.querySelector(`[data-group-id="${groupId}"]`)?.classList.add('bg-purple-50', 'border-purple-300');

            clearSidebarUnread('group', groupId);
            loadMessages();
            if (window.ChatRealtime) {
                window.ChatRealtime.switchChannel('group', Number(groupId));
            }
        }

        function selectAnonymous(roomId, peerAlias, peerName, isCreator = false) {
            currentChatType = 'anonymous';
            currentAnonymousRoomId = roomId;
            currentAnonymousPeerAlias = peerAlias || 'Anonymous';
            currentAnonymousPeerName = isCreator ? (peerName || knownPeerNameFromUrl || '') : '';
            currentFriendId = null;
            currentFriendName = null;
            currentGroupId = null;
            currentGroupName = null;

            document.getElementById('no-chat-selected').classList.add('hidden');
            document.getElementById('chat-area').classList.remove('hidden');
            document.getElementById('chat-friend-name').textContent = currentAnonymousPeerAlias;
            document.getElementById('chat-subtitle').classList.add('hidden');
            document.getElementById('chat-subtitle').textContent = '';

            clearChatSelection();
            document.querySelector(`[data-anonymous-room-id="${roomId}"]`)?.classList.add('bg-purple-50', 'border-purple-300');

            clearSidebarUnread('anonymous', roomId);
            loadMessages();
            if (window.ChatRealtime) {
                window.ChatRealtime.switchChannel('anonymous', Number(roomId));
            }
        }

        function initChatRealtime() {
            if (!window.ChatRealtime) {
                return;
            }

            window.ChatRealtime.init({
                userId: currentUserId,
                onMessage: handleRealtimeMessage,
                onTyping: handleRealtimeTyping,
                onTypingStop: hideTypingIndicator,
                onMessagesRead: handleRealtimeReadReceipt,
                onPresenceSync: syncOnlineUsers,
                onPresenceJoin: (user) => setFriendOnlineStatus(user.id, true),
                onPresenceLeave: (user) => setFriendOnlineStatus(user.id, false),
            });
        }

        function loadMessages() {
            if (currentChatType === 'anonymous') {
                loadAnonymousMessages();
                return;
            }

            if (currentChatType === 'group') {
                loadGroupMessages();
                return;
            }

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

        function isActiveConversation(payload) {
            if (payload.chat_type === 'friend' && currentChatType === 'friend') {
                return (Number(payload.sender_id) === Number(currentFriendId) && Number(payload.receiver_id) === currentUserId)
                    || (Number(payload.sender_id) === currentUserId && Number(payload.receiver_id) === Number(currentFriendId));
            }

            if (payload.chat_type === 'group' && currentChatType === 'group') {
                return Number(payload.group_chat_id) === Number(currentGroupId);
            }

            if (payload.chat_type === 'anonymous' && currentChatType === 'anonymous') {
                return Number(payload.room_id) === Number(currentAnonymousRoomId);
            }

            return false;
        }

        function handleRealtimeMessage(payload) {
            if (!payload) {
                return;
            }

            if (payload.chat_type !== 'anonymous' && Number(payload.sender_id) === currentUserId) {
                return;
            }

            if (!isActiveConversation(payload)) {
                loadUnreadCounts();
                return;
            }

            if (payload.chat_type === 'anonymous') {
                payload.is_own = false;
                payload.sender_alias = payload.sender_alias || 'Anonymous';
            }

            if (payload.media) {
                payload.media = enrichMediaPayload(payload.media, false);
            }

            if (messages.some((entry) => Number(entry.id) === Number(payload.id))) {
                return;
            }

            messages.push(payload);
            displayMessages();
        }

        function handleRealtimeTyping(payload) {
            const typingIndicator = document.getElementById('typing-indicator');
            const typingText = document.getElementById('typing-text');
            if (!typingIndicator || !typingText) {
                return;
            }

            if (!payload.anonymous && Number(payload.user_id) === currentUserId) {
                return;
            }

            typingText.textContent = `${payload.name || 'Someone'} is typing...`;
            typingIndicator.classList.remove('hidden');
        }

        function hideTypingIndicator() {
            document.getElementById('typing-indicator')?.classList.add('hidden');
        }

        function handleRealtimeReadReceipt(payload) {
            if (currentChatType !== 'friend' || !currentFriendId) {
                return;
            }

            if (Number(payload.reader_id) === Number(currentFriendId) && Number(payload.sender_id) === currentUserId) {
                const subtitle = document.getElementById('chat-subtitle');
                subtitle.classList.remove('hidden');
                subtitle.textContent = 'Seen';
            }
        }

        function syncOnlineUsers(users) {
            onlineUserIds = new Set((users || []).map((user) => Number(user.id)));
            document.querySelectorAll('.friend-item[data-friend-id]').forEach((item) => {
                setFriendOnlineStatus(item.dataset.friendId, onlineUserIds.has(Number(item.dataset.friendId)));
            });
        }

        function setFriendOnlineStatus(userId, isOnline) {
            const item = document.querySelector(`.friend-item[data-friend-id="${userId}"]`);
            if (!item) {
                return;
            }

            const dot = item.querySelector('.online-indicator');
            if (!dot) {
                return;
            }

            dot.classList.toggle('bg-green-400', isOnline);
            dot.classList.toggle('bg-gray-300', !isOnline);
        }

        function loadGroupMessages() {
            if (!currentGroupId) return;

            showLoading();

            fetch(`{{ url('group-chats') }}/${currentGroupId}/messages`)
                .then(response => response.json())
                .then(data => {
                    hideLoading();

                    if (data.error) {
                        showNotification(data.error, 'error');
                        return;
                    }

                    messages = data.messages;
                    if (data.group_chat?.members?.length) {
                        document.getElementById('chat-subtitle').classList.remove('hidden');
                        document.getElementById('chat-subtitle').textContent = `${data.group_chat.members.length} members`;
                    }
                    displayMessages();
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error loading group messages:', error);
                    showNotification('Error loading group messages', 'error');
                });
        }

        function loadAnonymousMessages(silent = false) {
            if (!currentAnonymousRoomId) return;

            if (!silent) {
                showLoading();
            }

            fetch(`{{ url('anonymous-chat') }}/${currentAnonymousRoomId}/messages`)
                .then(response => response.json())
                .then(data => {
                    if (!silent) {
                        hideLoading();
                    }

                    if (data.error) {
                        showNotification(data.error, 'error');
                        return;
                    }

                    currentMyAnonymousAlias = data.room?.my_alias || 'Anonymous';
                    document.getElementById('chat-subtitle').classList.add('hidden');
                    document.getElementById('chat-subtitle').textContent = '';

                    messages = data.messages || [];
                    displayMessages();
                })
                .catch(error => {
                    if (!silent) {
                        hideLoading();
                    }
                    console.error('Error loading anonymous messages:', error);
                    showNotification('Error loading anonymous messages', 'error');
                });
        }

        function appendSentMessage(message) {
            if (!message) {
                return;
            }

            if (messages.some((entry) => Number(entry.id) === Number(message.id))) {
                return;
            }

            messages.push(message);
            displayMessages();
        }

        function buildMessageBubbleHtml(message, isOwn) {
            const time = new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            let senderName = '';

            if (currentChatType === 'group' && !isOwn && message.sender?.name) {
                senderName = `<p class="text-xs font-semibold mb-1 text-gray-600 break-words">${escapeHtml(message.sender.name)}</p>`;
            } else if (currentChatType === 'anonymous' && !isOwn && message.sender_alias) {
                senderName = `<p class="text-xs font-semibold mb-1 text-gray-600 break-words">${escapeHtml(message.sender_alias)}</p>`;
            }

            const captionHtml = message.message && String(message.message).trim()
                ? `<p class="text-sm break-words whitespace-pre-wrap ${message.media ? 'mt-1' : ''}">${escapeHtml(message.message)}</p>`
                : '';
            const mediaHtml = buildMediaHtml(message.media, isOwn);
            const bubbleClasses = getMessageBubbleClasses(isOwn);
            const timeClasses = getMessageTimeClasses(isOwn);

            return `
                <div class="max-w-[85%] sm:max-w-sm lg:max-w-md min-w-0 w-fit ${isOwn ? 'ml-auto' : 'mr-auto'}">
                    <div class="px-3 sm:px-4 py-2 rounded-lg ${bubbleClasses}">
                        ${senderName}
                        ${mediaHtml}
                        ${captionHtml}
                        <p class="text-xs mt-1 ${timeClasses}">${time}</p>
                    </div>
                </div>
            `;
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
                const isOwn = currentChatType === 'anonymous'
                    ? !!message.is_own
                    : message.sender_id == {{ auth()->id() }};
                const messageDiv = document.createElement('div');
                messageDiv.className = `flex w-full min-w-0 ${isOwn ? 'justify-end' : 'justify-start'}`;
                messageDiv.innerHTML = buildMessageBubbleHtml(message, isOwn);
                messagesDiv.appendChild(messageDiv);
            });

            messagesDiv.scrollTop = messagesDiv.scrollHeight;
            startMediaTimerUpdates();
        }

        function sendMessage() {
            const input = document.getElementById('message-input');
            const message = input.value.trim();

            if (!message) return;
            if (!currentChatType) {
                showNotification('Select a conversation first.', 'error');
                return;
            }
            if (currentChatType === 'anonymous' && !currentAnonymousRoomId) return;
            if (currentChatType === 'group' && !currentGroupId) return;
            if (currentChatType === 'friend' && !currentFriendId) return;

            // Disable send button temporarily
            const sendButton = document.getElementById('send-button');
            sendButton.disabled = true;

            const optimisticId = `temp-${Date.now()}`;
            messages.push({
                id: optimisticId,
                message,
                created_at: new Date().toISOString(),
                is_own: true,
                sender_id: currentUserId,
            });
            displayMessages();

            input.value = '';
            sendButton.disabled = true;

            if (currentChatType === 'anonymous') {
                chatFetchJson(`{{ url('anonymous-chat') }}/${currentAnonymousRoomId}/messages`, {
                    method: 'POST',
                    body: JSON.stringify({ message }),
                })
                .then(data => {
                    if (data.error) {
                        messages = messages.filter((entry) => entry.id !== optimisticId);
                        displayMessages();
                        showNotification(data.error, 'error');
                    } else if (data.message) {
                        messages = messages.filter((entry) => entry.id !== optimisticId);
                        appendSentMessage(data.message);
                    }
                    sendButton.disabled = input.value.trim() === '';
                })
                .catch(error => {
                    messages = messages.filter((entry) => entry.id !== optimisticId);
                    displayMessages();
                    console.error('Error sending anonymous message:', error);
                    showNotification(error.message || 'Error sending message', 'error');
                    sendButton.disabled = input.value.trim() === '';
                });
                return;
            }

            const sendUrl = currentChatType === 'group'
                ? `{{ url('group-chats') }}/${currentGroupId}/messages`
                : '{{ url("/user-chat/send") }}';
            const payload = currentChatType === 'group'
                ? { message }
                : { receiver_id: currentFriendId, message };

            chatFetchJson(sendUrl, {
                method: 'POST',
                body: JSON.stringify(payload),
            })
            .then(data => {
                if (data.error) {
                    messages = messages.filter((entry) => entry.id !== optimisticId);
                    displayMessages();
                    showNotification(data.error, 'error');
                } else if (data.message) {
                    messages = messages.filter((entry) => entry.id !== optimisticId);
                    appendSentMessage(data.message);
                }
                sendButton.disabled = input.value.trim() === '';
            })
            .catch(error => {
                messages = messages.filter((entry) => entry.id !== optimisticId);
                displayMessages();
                console.error('Error sending message:', error);
                showNotification(error.message || 'Error sending message', 'error');
                sendButton.disabled = input.value.trim() === '';
            });
        }

        function formatUnreadCount(count) {
            return count > 99 ? '99+' : String(count);
        }

        function updateSidebarItem(item, { unreadCount, preview, hasNew, defaultSubtitle }) {
            if (!item) {
                return;
            }

            const title = item.querySelector('.chat-item-title');
            const subtitle = item.querySelector('.chat-item-subtitle');

            if (title) {
                title.classList.toggle('font-semibold', hasNew);
            }

            if (subtitle) {
                subtitle.classList.toggle('text-gray-500', !hasNew);
                subtitle.classList.toggle('text-indigo-700', hasNew && item.classList.contains('friend-item'));
                subtitle.classList.toggle('font-medium', hasNew);
                subtitle.classList.toggle('text-purple-700', hasNew && !item.classList.contains('friend-item'));

                if (hasNew) {
                    subtitle.textContent = preview ? `New message: ${preview}` : 'New message';
                } else if (defaultSubtitle) {
                    subtitle.textContent = defaultSubtitle;
                }
            }
        }

        function applySidebarUnread(data) {
            (data.friends || []).forEach((entry) => {
                const item = document.querySelector(`.friend-item[data-friend-id="${entry.id}"]`);
                const badge = document.getElementById(`unread-${entry.id}`);

                if (badge) {
                    if (entry.unread_count > 0) {
                        badge.textContent = formatUnreadCount(entry.unread_count);
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }

                updateSidebarItem(item, {
                    unreadCount: entry.unread_count,
                    preview: entry.preview,
                    hasNew: entry.has_new,
                    defaultSubtitle: 'Click to chat',
                });
            });

            (data.groups || []).forEach((entry) => {
                const item = document.querySelector(`.group-chat-item[data-group-id="${entry.id}"]`);
                const badge = document.getElementById(`unread-group-${entry.id}`);
                const membersCount = item?.dataset.membersCount || '';

                if (badge) {
                    if (entry.unread_count > 0) {
                        badge.textContent = formatUnreadCount(entry.unread_count);
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }

                updateSidebarItem(item, {
                    unreadCount: entry.unread_count,
                    preview: entry.preview,
                    hasNew: entry.has_new,
                    defaultSubtitle: membersCount ? `${membersCount} members` : 'Group chat',
                });
            });

            (data.anonymous || []).forEach((entry) => {
                const item = document.querySelector(`.anonymous-chat-item[data-anonymous-room-id="${entry.room_id}"]`);
                const badge = document.getElementById(`unread-anonymous-${entry.room_id}`);

                if (badge) {
                    if (entry.unread_count > 0) {
                        badge.textContent = formatUnreadCount(entry.unread_count);
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }

                updateSidebarItem(item, {
                    unreadCount: entry.unread_count,
                    preview: entry.preview,
                    hasNew: entry.has_new,
                    defaultSubtitle: 'Anonymous chat',
                });
            });
        }

        function loadUnreadCounts() {
            fetch('{{ url("/user-chat/sidebar-unread") }}')
                .then(response => response.json())
                .then(data => {
                    applySidebarUnread(data);
                })
                .catch(error => {
                    console.error('Error loading unread counts:', error);
                });
        }

        function clearSidebarUnread(type, id) {
            if (type === 'friend') {
                const badge = document.getElementById(`unread-${id}`);
                const item = document.querySelector(`.friend-item[data-friend-id="${id}"]`);
                if (badge) badge.classList.add('hidden');
                updateSidebarItem(item, { hasNew: false, defaultSubtitle: 'Click to chat' });
            } else if (type === 'group') {
                const badge = document.getElementById(`unread-group-${id}`);
                const item = document.querySelector(`.group-chat-item[data-group-id="${id}"]`);
                const membersCount = item?.dataset.membersCount || '';
                if (badge) badge.classList.add('hidden');
                updateSidebarItem(item, { hasNew: false, defaultSubtitle: membersCount ? `${membersCount} members` : 'Group chat' });
            } else if (type === 'anonymous') {
                const badge = document.getElementById(`unread-anonymous-${id}`);
                const item = document.querySelector(`.anonymous-chat-item[data-anonymous-room-id="${id}"]`);
                if (badge) badge.classList.add('hidden');
                updateSidebarItem(item, { hasNew: false, defaultSubtitle: 'Anonymous chat' });
            }
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

        const urlParams = new URLSearchParams(window.location.search);
        const friendId = urlParams.get('friend');
        const groupId = urlParams.get('group');
        const anonymousRoomId = urlParams.get('anonymous_room');
        const isAnonymousStarter = urlParams.get('starter') === '1';

        if (anonymousRoomId) {
            const roomElement = document.querySelector(`[data-anonymous-room-id="${anonymousRoomId}"]`);
            if (roomElement) {
                selectAnonymous(
                    anonymousRoomId,
                    roomElement.dataset.peerAlias,
                    roomElement.dataset.peerName || knownPeerNameFromUrl || '',
                    roomElement.dataset.isCreator === '1' || isAnonymousStarter
                );
            } else {
                fetch(`{{ url('anonymous-chat') }}/${anonymousRoomId}/messages`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.room) {
                            selectAnonymous(
                                anonymousRoomId,
                                data.room.peer_alias,
                                data.room.is_creator ? (data.room.peer_name || knownPeerNameFromUrl || '') : '',
                                Boolean(data.room.is_creator || isAnonymousStarter)
                            );
                        }
                    });
            }
        } else if (groupId) {
            const groupElement = document.querySelector(`[data-group-id="${groupId}"]`);
            if (groupElement) {
                selectGroup(groupId, groupElement.dataset.groupName);
            }
        } else if (friendId) {
            const friendElement = document.querySelector(`[data-friend-id="${friendId}"]`);
            if (friendElement) {
                selectFriend(friendId, friendElement.dataset.friendName);
            }
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getMessageBubbleClasses(isOwn) {
            if (currentChatType === 'anonymous') {
                return isOwn
                    ? 'bg-black text-white'
                    : 'bg-white text-gray-900 border border-gray-300';
            }

            return isOwn
                ? 'bg-indigo-600 text-white'
                : 'bg-white text-gray-900 border border-gray-200';
        }

        function getMessageTimeClasses(isOwn) {
            if (currentChatType === 'anonymous') {
                return isOwn ? 'text-gray-300' : 'text-gray-500';
            }

            return isOwn ? 'text-indigo-100' : 'text-gray-500';
        }

        function openStartAnonymousChatModal() {
            document.getElementById('start-anonymous-chat-modal').classList.remove('hidden');
            loadAnonymousTargets();
        }

        function closeStartAnonymousChatModal() {
            document.getElementById('start-anonymous-chat-modal').classList.add('hidden');
        }

        function loadAnonymousTargets() {
            const list = document.getElementById('anonymous-targets-list');
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
                                    onclick="startAnonymousChat(decodeURIComponent(this.dataset.token))"
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

        function startAnonymousChat(token) {
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
                        showNotification(data.error || 'Unable to start anonymous chat.', 'error');
                        return;
                    }

                    closeStartAnonymousChatModal();
                    window.location.href = data.redirect_url;
                })
                .catch(function () {
                    showNotification('Unable to start anonymous chat.', 'error');
                });
        }
    </script>
@endsection
