@extends('layouts.admin')

@section('title', 'Anonymous Chat Details')
@section('page-title', 'Anonymous Chat Details')

@section('breadcrumb')
<li class="flex items-center">
    <a href="{{ route('admin.anonymous-chats.index') }}" class="text-gray-500 text-sm hover:text-gray-700">Anonymous Chats</a>
</li>
<li class="flex items-center">
    <span class="text-gray-900 font-medium text-sm">Room #{{ $room->id }}</span>
</li>
@endsection

@section('content')
@php
    $participantOne = $room->participants->firstWhere('user_id', $room->user_one_id);
    $participantTwo = $room->participants->firstWhere('user_id', $room->user_two_id);
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Participant A</h2>
            <p class="font-medium text-gray-900">{{ $room->userOne->name }}</p>
            <p class="text-sm text-gray-500">{{ $room->userOne->email }}</p>
            <p class="text-sm text-purple-700 mt-3">Shown to other user as: <strong>{{ $participantOne?->display_alias ?: '—' }}</strong></p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Participant B</h2>
            <p class="font-medium text-gray-900">{{ $room->userTwo->name }}</p>
            <p class="text-sm text-gray-500">{{ $room->userTwo->email }}</p>
            <p class="text-sm text-purple-700 mt-3">Shown to other user as: <strong>{{ $participantTwo?->display_alias ?: '—' }}</strong></p>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Messages</h2>
                <p class="text-xs text-gray-500">Real sender names are visible to admins only.</p>
            </div>
            <a href="{{ route('admin.user-activity.index', ['activity_type' => 'anonymous_chat']) }}"
               class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Open activity logs</a>
        </div>

        <div class="divide-y divide-gray-200">
            @forelse($room->messages as $message)
                <div class="px-5 py-4">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $message->sender->name }}</p>
                            <p class="text-xs text-gray-500">{{ $message->sender->email }}</p>
                            <p class="text-xs text-purple-700 mt-1">Alias in chat: {{ $room->senderAlias($message->sender_id) }}</p>
                        </div>
                        <p class="text-xs text-gray-500 shrink-0">{{ $message->created_at?->format('M j, Y g:i A') }}</p>
                    </div>
                    @if(filled($message->message))
                        <p class="mt-3 text-sm text-gray-800 whitespace-pre-wrap">{{ $message->message }}</p>
                    @endif
                    @include('partials.admin-chat-attachment', ['media' => $message->media ?? null])
                </div>
            @empty
                <div class="px-5 py-10 text-center text-sm text-gray-500">No messages in this room yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
