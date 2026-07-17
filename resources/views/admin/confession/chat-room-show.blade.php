@extends('layouts.admin')

@section('title', 'Confession – Chat Room')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <p class="text-sm text-gray-500">
                <a href="{{ route('admin.confession.chat-rooms') }}" class="text-indigo-600 hover:text-indigo-800">← Chat rooms</a>
            </p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $room->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                Created by <span class="font-mono">{{ $room->creator_codename }}</span>
                · <span class="font-mono">{{ $room->slug }}</span>
                · {{ $room->created_at->format('M j, Y g:i A') }}
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
            <a href="{{ route('say-it.chat.show', $room) }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Open public room</a>
            <form action="{{ route('admin.confession.chat-rooms.destroy', $room) }}" method="POST" onsubmit="return confirm('Delete this room and all its messages?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">Delete room</button>
            </form>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <ul class="divide-y divide-gray-200">
            @forelse($messages as $message)
                <li class="p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="text-xs text-gray-500 mb-1">
                                <span class="font-mono font-semibold text-gray-700">{{ $message->codename }}</span>
                                · {{ $message->created_at->format('M j, Y g:i A') }}
                                · IP: {{ $message->ip_address ?? '—' }}
                            </div>
                            <p class="text-sm text-gray-900 whitespace-pre-wrap break-words">{{ $message->display_body }}</p>
                        </div>
                        <form action="{{ route('admin.confession.chat-messages.destroy', [$room, $message]) }}" method="POST" onsubmit="return confirm('Delete this message?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">Delete</button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="px-4 py-10 text-center text-sm text-gray-500">No messages in this room.</li>
            @endforelse
        </ul>
        @if($messages->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
