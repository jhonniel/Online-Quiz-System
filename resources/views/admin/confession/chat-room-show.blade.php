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
                @if($room->trashed())
                    · <span class="text-red-600 font-semibold">Deleted</span>
                @endif
                @if($room->is_frozen)
                    · <span class="text-amber-700 font-semibold">Frozen</span>
                @endif
                @if($room->isGibberishActive())
                    · <span class="text-fuchsia-700 font-semibold">Gibberish until {{ $room->gibberish_until->format('M j, g:i A') }}</span>
                @endif
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
            @unless($room->trashed())
                <a href="{{ route('say-it.chat.show', $room) }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Open public room</a>
                <form action="{{ route('admin.confession.chat-rooms.destroy', $room) }}" method="POST" onsubmit="return confirm('Delete this room and all its messages?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">Delete room</button>
                </form>
            @endunless
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-900">Moderation codes (admin record)</h2>
            <p class="text-xs text-gray-500 mt-0.5">Shown to the creator once at room creation. Usage is logged here.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-white">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Effect</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Used by</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Used at / IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($room->moderationCodeRecords() as $record)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $record['label'] }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-800">{{ $record['code'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($record['used_at'])
                                    <span class="inline-flex rounded-full bg-red-50 text-red-700 px-2 py-0.5 text-xs font-semibold">Used</span>
                                @else
                                    <span class="inline-flex rounded-full bg-emerald-50 text-emerald-700 px-2 py-0.5 text-xs font-semibold">Unused</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-700">{{ $record['used_by'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if($record['used_at'])
                                    {{ $record['used_at'] }}
                                    <span class="block text-xs">{{ $record['used_ip'] ?? '—' }}</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
                            @if($message->image_path)
                                <p class="mt-1 text-xs text-gray-500">
                                    Image attached
                                    @if($message->image_expires_at)
                                        · expires {{ $message->image_expires_at->format('M j, g:i A') }}
                                    @endif
                                </p>
                            @endif
                        </div>
                        @unless($room->trashed())
                            <form action="{{ route('admin.confession.chat-messages.destroy', [$room, $message]) }}" method="POST" onsubmit="return confirm('Delete this message?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        @endunless
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
