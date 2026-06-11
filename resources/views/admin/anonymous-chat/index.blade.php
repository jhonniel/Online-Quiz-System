@extends('layouts.admin')

@section('title', 'Anonymous Chats')
@section('page-title', 'Anonymous Chats')

@section('breadcrumb')
<li class="flex items-center">
    <span class="text-gray-900 font-medium text-sm">Anonymous Chats</span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Anonymous Chat Monitor</h1>
            <p class="mt-1 text-sm text-gray-500">Users chat with anonymous aliases. Admins can see the real identities here and in User Activity logs.</p>
        </div>
        <a href="{{ route('admin.user-activity.index', ['activity_type' => 'anonymous_chat']) }}"
           class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100">
            View Activity Logs
        </a>
    </div>

    <form method="GET" action="{{ route('admin.anonymous-chats.index') }}" class="bg-white rounded-lg border border-gray-200 p-4 flex flex-col sm:flex-row gap-3">
        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Search by user name or email..."
               class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <button type="submit" class="px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Search</button>
    </form>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User A</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alias A</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User B</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alias B</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Messages</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Activity</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($rooms as $room)
                    @php
                        $participantA = $room->participants->firstWhere('user_id', $room->user_one_id);
                        $participantB = $room->participants->firstWhere('user_id', $room->user_two_id);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div class="font-medium">{{ $room->userOne->name }}</div>
                            <div class="text-xs text-gray-500">{{ $room->userOne->email }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $participantA?->display_alias ?: '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div class="font-medium">{{ $room->userTwo->name }}</div>
                            <div class="text-xs text-gray-500">{{ $room->userTwo->email }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $participantB?->display_alias ?: '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ number_format($room->messages_count) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $room->updated_at?->format('M j, Y g:i A') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.anonymous-chats.show', $room) }}"
                               class="text-sm font-medium text-indigo-600 hover:text-indigo-800">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">No anonymous chats yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $rooms->links() }}
</div>
@endsection
