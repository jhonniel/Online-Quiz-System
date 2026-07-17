@extends('layouts.admin')

@section('title', 'Confession – Chat Rooms')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Confession – Chat Rooms</h1>
            <p class="mt-1 text-sm text-gray-500">Anonymous group chat rooms from Say-it. Soft-delete rooms or individual messages.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
            <a href="{{ url('/admin/confession') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Contents</a>
            <a href="{{ route('say-it.chat.index') }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Open Say-it Chat →</a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creator</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Messages</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last activity</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($rooms as $room)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.confession.chat-rooms.show', $room) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                {{ $room->name }}
                            </a>
                            <p class="text-xs text-gray-400 font-mono">{{ $room->slug }}</p>
                            <div class="mt-1 flex flex-wrap gap-1">
                                @if($room->trashed())
                                    <span class="inline-flex rounded-full bg-red-50 text-red-700 px-1.5 py-0.5 text-[10px] font-semibold">Deleted</span>
                                @endif
                                @if($room->is_frozen)
                                    <span class="inline-flex rounded-full bg-amber-50 text-amber-800 px-1.5 py-0.5 text-[10px] font-semibold">Frozen</span>
                                @endif
                                @if($room->isGibberishActive())
                                    <span class="inline-flex rounded-full bg-fuchsia-50 text-fuchsia-800 px-1.5 py-0.5 text-[10px] font-semibold">Gibberish</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 font-mono">{{ $room->creator_codename }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $room->messages_count }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $room->last_message_at?->format('M j, Y g:i A') ?? $room->created_at->format('M j, Y g:i A') }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm space-x-2">
                            <a href="{{ route('admin.confession.chat-rooms.show', $room) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                            <form action="{{ route('admin.confession.chat-rooms.destroy', $room) }}" method="POST" class="inline" onsubmit="return confirm('Delete this room and all its messages?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No chat rooms yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($rooms->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $rooms->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
