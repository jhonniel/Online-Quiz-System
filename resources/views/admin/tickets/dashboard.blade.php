@extends('layouts.admin')

@section('title', 'Tickets – Dashboard')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    {{-- Page header --}}
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Ticket Reports</h1>
                <p class="mt-1 text-sm text-gray-500">Problem reports submitted via Report a Problem. Review and resolve tickets.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ url('admin/tickets/open') }}" class="inline-flex items-center px-4 py-2.5 border border-amber-200 rounded-lg text-sm font-medium text-amber-800 bg-amber-50 hover:bg-amber-100 transition-colors">
                    <svg class="w-4 h-4 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Open Tickets
                </a>
                <a href="{{ url('admin/tickets/closed') }}" class="inline-flex items-center px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Closed Tickets
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total tickets</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $total }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-amber-700">Open</p>
                        <p class="text-2xl font-semibold text-amber-700">{{ $open }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-green-200 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-700">Closed</p>
                        <p class="text-2xl font-semibold text-green-700">{{ $closed }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent tickets --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
            <h2 class="text-base font-semibold text-gray-900">Recent tickets</h2>
            <p class="text-sm text-gray-500 mt-0.5">Latest submissions across all statuses</p>
        </div>
        <ul class="divide-y divide-gray-200">
            @forelse($recent as $ticket)
                <li class="hover:bg-gray-50/50 transition-colors">
                    <a href="{{ url('admin/tickets/' . $ticket->id) }}" class="block px-6 py-4">
                        <div class="flex justify-between items-start gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono text-sm font-semibold text-indigo-600">{{ $ticket->ticket_number }}</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->isOpen() ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $ticket->status }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $ticket->type_label }}</span>
                                </div>
                                <p class="mt-2 text-sm text-gray-600 line-clamp-2">{{ Str::limit($ticket->description, 120) }}</p>
                                <p class="mt-2 text-xs text-gray-500">{{ $ticket->full_name }} · {{ $ticket->created_at->format('M j, Y g:i A') }}</p>
                            </div>
                            <span class="flex-shrink-0 text-indigo-600 text-sm font-medium">View →</span>
                        </div>
                    </a>
                </li>
            @empty
                <li class="px-6 py-12 text-center text-gray-500">
                    <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <p class="mt-2">No tickets yet.</p>
                </li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
