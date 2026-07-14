@extends('layouts.admin')

@section('content')
@php
    $tabs = [
        'leave' => 'Leave credits',
        'overtime' => 'Overtime logs',
        'offset' => 'Offset logs',
        'activity' => 'Request logs',
    ];
    $allYears = $allYears ?? false;
    $yearParam = $allYears ? 'all' : $year;
    $yearLabel = $allYears ? 'All years' : $year;
@endphp
<div class="space-y-6">
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl px-4 py-6 sm:px-6 sm:py-8 text-white">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-start space-x-3 sm:space-x-4 min-w-0">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-3 sm:p-4">
                    <svg class="h-8 w-8 sm:h-10 sm:w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold break-words">{{ $employee->name }}</h1>
                    <p class="text-sm sm:text-base text-indigo-100 mt-1 break-all">{{ $employee->email }}</p>
                    <p class="text-xs text-indigo-200 mt-1">
                        {{ $employee->department?->name ?? $employee->departmentPosition?->department?->name ?? 'No department' }}
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.employee-records.index', ['year' => $yearParam]) }}"
               class="inline-flex w-full sm:w-auto items-center justify-center px-4 py-2.5 text-sm font-medium bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white hover:bg-white/20 min-h-[44px] touch-manipulation">
                Back to Employee Records
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Leave allowance ({{ $summaryYear }})</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($leaveSummary['allowance'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">days credited</p>
        </div>
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Leave used ({{ $summaryYear }})</p>
            <p class="mt-1 text-2xl font-bold text-amber-700 tabular-nums">−{{ number_format($leaveSummary['used'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">approved vacation / sick days</p>
        </div>
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Leave remaining</p>
            <p class="mt-1 text-2xl font-bold text-green-700 tabular-nums">{{ number_format($leaveSummary['remaining'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">days available</p>
        </div>
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Overtime balance</p>
            <p class="mt-1 text-2xl font-bold text-indigo-700 tabular-nums">{{ $overtimeSummary['formatted'] }}</p>
            <p class="text-xs text-gray-500 mt-1">
                +{{ \App\Support\EmployeeRecordsLedger::formatMinutes($overtimeSummary['earned_minutes']) }} earned,
                −{{ \App\Support\EmployeeRecordsLedger::formatMinutes($overtimeSummary['offset_minutes']) }} offset
            </p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="{{ route('admin.employee-records.show', $employee) }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="min-w-[10rem]">
                <label for="year" class="block text-sm font-medium text-gray-700 mb-1.5">Year</label>
                <select name="year" id="year" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="all" {{ $allYears ? 'selected' : '' }}>All years (full history)</option>
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ (! $allYears && (int) $year === $y) ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <x-admin-filter-button class="w-full sm:w-auto">Update</x-admin-filter-button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 overflow-x-auto">
            <nav class="flex min-w-max px-2 sm:px-4" aria-label="Ledger tabs">
                @foreach($tabs as $key => $label)
                    <a href="{{ route('admin.employee-records.show', ['employee' => $employee->id, 'year' => $yearParam, 'tab' => $key]) }}"
                       class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap {{ $tab === $key ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>

        @if($tab === 'leave')
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <p class="text-sm text-gray-600">
                    Credits added at year allocation; debits from approved vacation and sick leave requests.
                    @if($allYears)<span class="font-medium text-gray-700">Showing full history across all years.</span>@endif
                </p>
            </div>
            @if(count($leaveLedger) > 0)
                <div class="md:hidden mobile-card-list">
                    @foreach($leaveLedger as $entry)
                        <div class="mobile-card">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $entry['description'] }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $entry['date']->format('M j, Y') }}</p>
                                </div>
                                <span class="shrink-0 px-2 py-1 rounded-full text-xs font-semibold {{ $entry['direction'] === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $entry['direction'] === 'credit' ? '+' : '−' }}{{ $entry['amount_label'] }}
                                </span>
                            </div>
                            @if($entry['reference'])
                                <p class="text-xs text-gray-500 mt-2">{{ $entry['reference'] }}</p>
                            @endif
                            @if($entry['leave_request_id'])
                                <a href="{{ url('/admin/leave-requests/' . $entry['leave_request_id']) }}" class="inline-block mt-2 text-xs font-medium text-indigo-600">View request #{{ $entry['leave_request_id'] }}</a>
                            @endif
                            <p class="text-xs text-gray-400 mt-2">Balance after: {{ number_format($entry['balance_after'] ?? 0, 2) }} days</p>
                        </div>
                    @endforeach
                </div>
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                @if($allYears)<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>@endif
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Change</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Request</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($leaveLedger as $entry)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $entry['date']->format('M j, Y') }}</td>
                                    @if($allYears)<td class="px-4 py-3 text-sm text-gray-500 tabular-nums">{{ $entry['year'] ?? '—' }}</td>@endif
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $entry['description'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $entry['reference'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold tabular-nums {{ $entry['direction'] === 'credit' ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $entry['direction'] === 'credit' ? '+' : '−' }}{{ $entry['amount_label'] }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-700 tabular-nums">
                                        {{ number_format($entry['balance_after'] ?? 0, 2) }} days
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        @if($entry['leave_request_id'])
                                            <a href="{{ url('/admin/leave-requests/' . $entry['leave_request_id']) }}" class="text-indigo-600 hover:text-indigo-900">#{{ $entry['leave_request_id'] }}</a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 text-sm text-gray-500">No leave credit entries for {{ $yearLabel }}.</div>
            @endif
        @elseif($tab === 'overtime')
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <p class="text-sm text-gray-600">Approved overtime requests add time to the balance once their start date has passed (today or earlier).</p>
            </div>
            @if(count($overtimeLedger) > 0)
                <div class="md:hidden mobile-card-list">
                    @foreach($overtimeLedger as $entry)
                        <div class="mobile-card">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $entry['description'] }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $entry['date']->format('M j, Y') }}</p>
                                </div>
                                <span class="shrink-0 px-2 py-1 rounded-full text-xs font-semibold {{ $entry['direction'] === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $entry['direction'] === 'credit' ? '+' : '−' }}{{ $entry['amount_label'] }}
                                </span>
                            </div>
                            @if($entry['reference'])
                                <p class="text-xs text-gray-500 mt-2">{{ $entry['reference'] }}</p>
                            @endif
                            @if($entry['leave_request_id'])
                                <a href="{{ url('/admin/leave-requests/' . $entry['leave_request_id']) }}" class="inline-block mt-2 text-xs font-medium text-indigo-600">View request #{{ $entry['leave_request_id'] }}</a>
                            @endif
                            <p class="text-xs text-gray-400 mt-2">Balance after: {{ $entry['balance_after'] ?? '00:00' }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Change</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Request</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($overtimeLedger as $entry)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $entry['date']->format('M j, Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $entry['description'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $entry['reference'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold tabular-nums {{ $entry['direction'] === 'credit' ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $entry['direction'] === 'credit' ? '+' : '−' }}{{ $entry['amount_label'] }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-700 tabular-nums">{{ $entry['balance_after'] ?? '00:00' }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <a href="{{ url('/admin/leave-requests/' . $entry['leave_request_id']) }}" class="text-indigo-600 hover:text-indigo-900">#{{ $entry['leave_request_id'] }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 text-sm text-gray-500">No overtime entries for {{ $yearLabel }}.</div>
            @endif
        @elseif($tab === 'offset')
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <p class="text-sm text-gray-600">
                    Approved offset requests deduct time from the overtime balance.
                    @if($allYears)<span class="font-medium text-gray-700">Showing full history across all years.</span>@endif
                </p>
            </div>
            @if(count($offsetLedger) > 0)
                <div class="md:hidden mobile-card-list">
                    @foreach($offsetLedger as $entry)
                        <div class="mobile-card">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $entry['description'] }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $entry['date']->format('M j, Y') }}</p>
                                </div>
                                <span class="shrink-0 px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                    −{{ $entry['amount_label'] }}
                                </span>
                            </div>
                            @if($entry['reference'])
                                <p class="text-xs text-gray-500 mt-2">{{ $entry['reference'] }}</p>
                            @endif
                            @if($entry['leave_request_id'])
                                <a href="{{ url('/admin/leave-requests/' . $entry['leave_request_id']) }}" class="inline-block mt-2 text-xs font-medium text-indigo-600">View request #{{ $entry['leave_request_id'] }}</a>
                            @endif
                            <p class="text-xs text-gray-400 mt-2">Balance after: {{ $entry['balance_after'] ?? '00:00' }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                @if($allYears)<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>@endif
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Deducted</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Request</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($offsetLedger as $entry)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $entry['date']->format('M j, Y') }}</td>
                                    @if($allYears)<td class="px-4 py-3 text-sm text-gray-500 tabular-nums">{{ $entry['date']->year }}</td>@endif
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $entry['description'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $entry['reference'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold tabular-nums text-red-700">−{{ $entry['amount_label'] }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-700 tabular-nums">{{ $entry['balance_after'] ?? '00:00' }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <a href="{{ url('/admin/leave-requests/' . $entry['leave_request_id']) }}" class="text-indigo-600 hover:text-indigo-900">#{{ $entry['leave_request_id'] }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 text-sm text-gray-500">No offset entries for {{ $yearLabel }}.</div>
            @endif

            <div class="border-t border-gray-200">
                <div class="px-4 sm:px-6 py-4 bg-gray-50/50 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Offset request activity</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Filing, approvals, rejections, and updates for offset requests in {{ $yearLabel }}.</p>
                </div>
                @if($offsetActivityLogs->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($offsetActivityLogs as $log)
                            <div class="px-4 sm:px-6 py-4 hover:bg-gray-50">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">{{ $log->action_label }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Offset
                                            @if($log->leaveRequest)
                                                · {{ $log->leaveRequest->start_date?->format('M j, Y') }}
                                                @if($log->leaveRequest->end_date && $log->leaveRequest->end_date->ne($log->leaveRequest->start_date))
                                                    – {{ $log->leaveRequest->end_date->format('M j, Y') }}
                                                @endif
                                            @endif
                                        </p>
                                        @if($log->notes)
                                            <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $log->notes }}</p>
                                        @endif
                                        <p class="text-xs text-gray-500 mt-2">
                                            By {{ $log->performer?->name ?? 'System' }}
                                            · {{ $log->created_at->format('M j, Y g:i A') }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if($log->status_after)
                                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst(str_replace('_', ' ', $log->status_after)) }}</span>
                                        @endif
                                        @if($log->leave_request_id)
                                            <a href="{{ url('/admin/leave-requests/' . $log->leave_request_id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">View</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 text-sm text-gray-500">No offset request activity for {{ $yearLabel }}.</div>
                @endif
            </div>
        @else
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <p class="text-sm text-gray-600">Activity log for leave requests in {{ $yearLabel }} (approvals, rejections, updates, and adjustments).</p>
            </div>
            @if($activityLogs->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($activityLogs as $log)
                        <div class="px-4 sm:px-6 py-4 hover:bg-gray-50">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900">{{ $log->action_label }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $log->leaveRequest?->type_label ?? 'Leave request' }}
                                        @if($log->leaveRequest)
                                            · {{ $log->leaveRequest->start_date?->format('M j, Y') }}
                                        @endif
                                    </p>
                                    @if($log->notes)
                                        <p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $log->notes }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500 mt-2">
                                        By {{ $log->performer?->name ?? 'System' }}
                                        · {{ $log->created_at->format('M j, Y g:i A') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    @if($log->status_after)
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst(str_replace('_', ' ', $log->status_after)) }}</span>
                                    @endif
                                    @if($log->leave_request_id)
                                        <a href="{{ url('/admin/leave-requests/' . $log->leave_request_id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">View</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 text-sm text-gray-500">No request activity logs for {{ $yearLabel }}.</div>
            @endif
        @endif
    </div>
</div>
@endsection
