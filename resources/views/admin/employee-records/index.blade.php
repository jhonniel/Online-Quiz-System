@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl px-4 py-6 sm:px-6 sm:py-8 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4 min-w-0">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-3 sm:p-4">
                    <svg class="h-8 w-8 sm:h-10 sm:w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold">Employee Records</h1>
                    <p class="text-sm sm:text-base text-indigo-100 mt-1">Track leave credits, overtime, and offset balances with request history</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="{{ route('admin.employee-records.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1.5">Year</label>
                    <select name="year" id="year" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" {{ (int) $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
                    <select name="department_id" id="department_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1.5">Search</label>
                    <input type="text" name="search" id="search" value="{{ $search }}"
                           placeholder="Employee name or email..."
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="flex justify-end">
                <x-admin-filter-button>Apply filters</x-admin-filter-button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50/80">
            <h2 class="text-sm font-semibold text-gray-900">Employees</h2>
            <p class="text-xs text-gray-500 mt-0.5">Leave credits for {{ $year }} · Overtime balance is cumulative from approved requests</p>
        </div>

        @if($employees->count() > 0)
            <x-responsive-data-panel class="border-0 shadow-none rounded-none">
                <x-slot:mobile>
                    @foreach($employees as $employee)
                        @php
                            $summary = $summaries[$employee->id] ?? null;
                        @endphp
                        <div class="mobile-card">
                            <div class="min-w-0">
                                <p class="mobile-card-title break-words"><x-user-name :user="$employee" :size="16" /></p>
                                <p class="mobile-card-subtitle break-all">{{ $employee->email }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $employee->department?->name ?? $employee->departmentPosition?->department?->name ?? '— No department —' }}
                                </p>
                            </div>
                            <dl class="mobile-card-kv mt-3">
                                <dt>Leave remaining</dt>
                                <dd>{{ number_format($summary['leave']['remaining'] ?? 0, 2) }} / {{ number_format($summary['leave']['allowance'] ?? 0, 2) }} days</dd>
                                <dt>Overtime balance</dt>
                                <dd class="tabular-nums">{{ $summary['overtime']['formatted'] ?? '00:00' }}</dd>
                            </dl>
                            <div class="mobile-card-actions">
                                <a href="{{ route('admin.employee-records.show', ['employee' => $employee->id, 'year' => $year]) }}"
                                   class="inline-flex items-center min-h-[44px] text-sm font-semibold text-indigo-600 hover:text-indigo-800 touch-manipulation px-2">
                                    View ledger
                                </a>
                            </div>
                        </div>
                    @endforeach
                </x-slot:mobile>

                <x-slot:desktop>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave credits ({{ $year }})</th>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime balance</th>
                                <th class="px-4 lg:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($employees as $employee)
                                @php $summary = $summaries[$employee->id] ?? null; @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 lg:px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><x-user-name :user="$employee" :size="16" /></div>
                                        <div class="text-xs text-gray-500">{{ $employee->email }}</div>
                                    </td>
                                    <td class="px-4 lg:px-6 py-4 text-sm text-gray-700">
                                        {{ $employee->department?->name ?? $employee->departmentPosition?->department?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 lg:px-6 py-4 text-sm text-gray-900 tabular-nums">
                                        <span class="font-semibold">{{ number_format($summary['leave']['remaining'] ?? 0, 2) }}</span>
                                        <span class="text-gray-500">/ {{ number_format($summary['leave']['allowance'] ?? 0, 2) }} days</span>
                                        <div class="text-xs text-gray-500 mt-0.5">Used: {{ number_format($summary['leave']['used'] ?? 0, 2) }}</div>
                                    </td>
                                    <td class="px-4 lg:px-6 py-4 text-sm font-semibold text-gray-900 tabular-nums">
                                        {{ $summary['overtime']['formatted'] ?? '00:00' }}
                                    </td>
                                    <td class="px-4 lg:px-6 py-4 text-right text-sm font-medium">
                                        <a href="{{ route('admin.employee-records.show', ['employee' => $employee->id, 'year' => $year]) }}"
                                           class="text-indigo-600 hover:text-indigo-900">View ledger</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-slot:desktop>
            </x-responsive-data-panel>

            <div class="bg-gray-50 px-3 sm:px-4 py-3 border-t border-gray-200 overflow-x-auto">
                {{ $employees->links() }}
            </div>
        @else
            <div class="text-center py-12 px-4">
                <p class="text-sm text-gray-500">No employees match your filters.</p>
            </div>
        @endif
    </div>
</div>
@endsection
