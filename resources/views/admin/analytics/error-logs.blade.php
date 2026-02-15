@extends('layouts.admin')

@section('title', 'Error Logs')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="px-4 py-6 sm:px-6 sm:py-8 bg-white shadow-md rounded-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Error Logs</h1>
                <p class="mt-1 text-sm sm:text-base text-gray-600">
                    Recent application errors (404, 405, 500, and others) to help you monitor system stability.
                </p>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="px-4 sm:px-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4 sm:p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Errors</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
            </div>
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4 sm:p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Errors Today</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['today'] }}</div>
            </div>
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-4 sm:p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Top Status Codes</div>
                <dl class="space-y-1">
                    @forelse($stats['by_status'] as $row)
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <dt class="text-gray-600">HTTP {{ $row->status_code ?? 'N/A' }}</dt>
                            <dd class="font-semibold text-gray-900">{{ $row->count }}</dd>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400">No data yet.</p>
                    @endforelse
                </dl>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="px-4 sm:px-6">
        <div class="bg-white shadow-md rounded-xl overflow-hidden">
            <!-- Filters -->
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100">
                <form method="GET" class="grid grid-cols-1 sm:grid-cols-[120px_minmax(0,1fr)] gap-3 sm:gap-4 items-center">
                    <div>
                        <label for="status_code" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Status Code
                        </label>
                        <select id="status_code" name="status_code"
                                class="block w-full rounded-md border-gray-300 text-xs sm:text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All</option>
                            @foreach([400,401,403,404,405,419,429,500,502,503] as $code)
                                <option value="{{ $code }}" {{ request('status_code') == $code ? 'selected' : '' }}>
                                    {{ $code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                        <div class="flex-1">
                            <label for="search" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                Search (message, path, exception)
                            </label>
                            <input type="text" id="search" name="search" value="{{ request('search') }}"
                                   class="block w-full rounded-md border-gray-300 text-xs sm:text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="e.g. 404, /admin, MethodNotAllowed">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center justify-center px-3 py-2 rounded-md border border-transparent bg-indigo-600 text-xs sm:text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                Apply
                            </button>
                            <a href="{{ url('/admin/analytics/error-logs') }}"
                               class="inline-flex items-center justify-center px-3 py-2 rounded-md border border-gray-300 text-xs sm:text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-4 py-2 text-left text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide">Time</th>
                            <th class="px-3 sm:px-4 py-2 text-left text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-3 sm:px-4 py-2 text-left text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide">Request</th>
                            <th class="px-3 sm:px-4 py-2 text-left text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide">User</th>
                            <th class="px-3 sm:px-4 py-2 text-left text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide">Message</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($logs as $log)
                            @php
                                $status = $log->status_code ?? 500;
                                $badgeColor = $status >= 500 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 sm:px-4 py-2 whitespace-nowrap text-xs sm:text-sm text-gray-600">
                                    {{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('Y-m-d g:i:s A') : 'N/A' }}
                                </td>
                                <td class="px-3 sm:px-4 py-2 whitespace-nowrap text-xs sm:text-sm">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full border text-[11px] font-semibold {{ $badgeColor }}">
                                        HTTP {{ $status }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700">
                                    <div class="font-mono text-[11px] sm:text-xs text-gray-600">
                                        {{ $log->method ?? 'GET' }} /{{ $log->path }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-0.5 truncate max-w-xs sm:max-w-md">
                                        {{ $log->exception_class }}
                                    </div>
                                </td>
                                <td class="px-3 sm:px-4 py-2 whitespace-nowrap text-xs sm:text-sm text-gray-700">
                                    @if($log->user && $log->user->exists)
                                        <div class="truncate max-w-[120px] sm:max-w-[160px]">
                                            {{ $log->user->name ?? 'Unknown' }}
                                        </div>
                                        <div class="text-[10px] text-gray-400 truncate max-w-[140px]">
                                            {{ $log->user->email ?? '' }}
                                        </div>
                                    @else
                                        <span class="text-[11px] text-gray-400">Guest</span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-4 py-2 text-xs sm:text-sm text-gray-700">
                                    <div class="truncate max-w-xs sm:max-w-md">
                                        {{ $log->message ?? 'No message' }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 sm:px-4 py-6 text-center text-sm text-gray-500">
                                    No errors have been logged yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-4 sm:px-6 py-3 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection


