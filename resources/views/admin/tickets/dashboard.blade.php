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

    @if(!empty($paymentDateErrors) && $paymentDateErrors->isNotEmpty())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($paymentDateErrors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Payments summary (paid tickets with recorded date) --}}
    <div class="bg-white rounded-xl shadow-sm border border-emerald-200 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-emerald-100 bg-emerald-50/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Payments</h2>
                <p class="text-sm text-gray-600 mt-0.5">Completed payments (status <span class="font-medium">Paid</span>) counted by the date payment was recorded.</p>
            </div>
            <form method="get" action="{{ url('admin/tickets') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="date_from" class="block text-xs font-medium text-gray-600 mb-1">From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ old('date_from', $dateFrom ?? request('date_from')) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label for="date_to" class="block text-xs font-medium text-gray-600 mb-1">To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ old('date_to', $dateTo ?? request('date_to')) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">Apply</button>
                @if(request()->has('date_from') || request()->has('date_to') || !empty($dateFrom) || !empty($dateTo) || (!empty($paymentDateErrors) && $paymentDateErrors->isNotEmpty()))
                    <a href="{{ url('admin/tickets') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">Clear</a>
                @endif
            </form>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Paid tickets (in range)</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($paymentsCount) }}</p>
                </div>
            </div>
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="ml-4 min-w-0">
                    <p class="text-sm font-medium text-gray-500">Total amount collected</p>
                    <p class="text-2xl font-semibold text-gray-900">&#8369;{{ number_format($paymentsTotal, 2) }}</p>
                </div>
            </div>
        </div>
        @if(!empty($paymentDateErrors) && $paymentDateErrors->isNotEmpty())
            <p class="px-6 pb-4 text-xs text-amber-800 bg-amber-50/80 mx-6 rounded-lg px-3 py-2 border border-amber-100">Totals above are <span class="font-medium">all time</span> until the date range is valid.</p>
        @elseif(!empty($dateFrom) || !empty($dateTo))
            <p class="px-6 pb-4 text-xs text-gray-500">Showing payments with recorded date
                @if(!empty($dateFrom)) from {{ \Carbon\Carbon::parse($dateFrom)->format('M j, Y') }} @endif
                @if(!empty($dateTo)) through {{ \Carbon\Carbon::parse($dateTo)->format('M j, Y') }} @endif
            </p>
        @else
            <p class="px-6 pb-4 text-xs text-gray-500">All time. Use the date fields above to filter.</p>
        @endif
    </div>

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
