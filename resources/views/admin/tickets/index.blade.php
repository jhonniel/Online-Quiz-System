@extends('layouts.admin')

@section('title', $filter === 'open' ? 'Open Tickets' : 'Closed Tickets')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    {{-- Page header --}}
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $filter === 'open' ? 'Open Tickets' : 'Closed Tickets' }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $tickets->total() }} ticket(s)</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ url('admin/tickets') }}" class="inline-flex items-center px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">Dashboard</a>
                @if($filter === 'open')
                    <a href="{{ url('admin/tickets/closed') }}" class="inline-flex items-center px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">Closed Tickets</a>
                @else
                    <a href="{{ url('admin/tickets/open') }}" class="inline-flex items-center px-4 py-2.5 border border-amber-200 rounded-lg text-sm font-medium text-amber-800 bg-amber-50 hover:bg-amber-100 transition-colors">Open Tickets</a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <ul class="divide-y divide-gray-200">
            @forelse($tickets as $ticket)
                <li class="hover:bg-gray-50/50 transition-colors">
                    <a href="{{ url('admin/tickets/' . $ticket->id) }}" class="block px-6 py-4">
                        <div class="flex justify-between items-start gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono text-sm font-semibold text-indigo-600">{{ $ticket->ticket_number }}</span>
                                    @php
                                        $status = $ticket->status;
                                        $statusLabel = strtoupper(str_replace('_', ' ', (string) $status));
                                        $badge = match($status) {
                                            'open' => 'bg-amber-100 text-amber-800',
                                            'processing' => 'bg-blue-100 text-blue-800',
                                            'needs_investigation' => 'bg-purple-100 text-purple-800',
                                            'resolved', 'closed' => 'bg-green-100 text-green-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                        {{ $statusLabel }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $ticket->type_label }}</span>
                                </div>
                                <p class="mt-2 text-sm text-gray-600 line-clamp-2">{{ Str::limit($ticket->description, 120) }}</p>
                                <p class="mt-2 text-xs text-gray-500">
                                    {{ $ticket->full_name }} · {{ $ticket->email }} · {{ $ticket->created_at->format('M j, Y g:i A') }}
                                    @if($ticket->assignedTo)
                                        · Assigned to {{ $ticket->assignedTo->name }}
                                    @endif
                                    @if($ticket->latestLog)
                                        · Last action: {{ str_replace('_', ' ', $ticket->latestLog->action) }}
                                        @if($ticket->latestLog->user)
                                            by {{ $ticket->latestLog->user->name }}
                                        @endif
                                        ({{ $ticket->latestLog->created_at->diffForHumans() }})
                                    @endif
                                </p>
                            </div>
                            <span class="flex-shrink-0 text-indigo-600 text-sm font-medium">View →</span>
                        </div>
                    </a>
                </li>
            @empty
                <li class="px-6 py-12 text-center text-gray-500">
                    <p>No {{ $filter }} tickets.</p>
                </li>
            @endforelse
        </ul>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
            {{ $tickets->links() }}
        </div>
    </div>
</div>
@endsection
