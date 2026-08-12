@extends('layouts.admin')

@section('title', 'Employee Dashboard')

@section('content')
<div class="px-3 sm:px-4 lg:px-6 xl:px-8 space-y-6">
    <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-lg p-6 text-white">
        <h1 class="text-2xl font-bold">Employee Dashboard</h1>
        <p class="text-indigo-100 mt-1">Overview of employee leave requests and key workforce metrics.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Employees</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['total_employees']) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Active Employees</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($stats['active_employees']) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pending Leaves</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($stats['pending_leave_requests']) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Approved This Month</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($stats['approved_this_month']) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Rejected This Month</p>
            <p class="mt-2 text-2xl font-bold text-rose-600">{{ number_format($stats['rejected_this_month']) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">On Leave Today</p>
            <p class="mt-2 text-2xl font-bold text-indigo-600">{{ number_format($stats['employees_on_leave_today']) }}</p>
        </div>
    </div>

    @include('admin.partials.scoped-dashboard-analytics', [
        'chartProfile' => 'employee',
        'chartFormAction' => route('admin.employee-dashboard.index'),
        'chartPeriod' => $chartPeriod ?? 'week',
        'chartFrom' => $chartFrom ?? now()->subDays(6)->format('Y-m-d'),
        'chartTo' => $chartTo ?? now()->format('Y-m-d'),
        'scopedChartPayload' => $scopedChartPayload ?? [],
        'preserveQuery' => request()->except(['chart_period', 'chart_from', 'chart_to']),
    ])

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70">
                <h2 class="text-base font-semibold text-gray-900">Leave Requests by Type</h2>
                <p class="text-sm text-gray-500">All employee leave requests grouped by leave type.</p>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($typeCounts as $row)
                        <div class="rounded-xl border border-gray-200 bg-white p-4">
                            <p class="text-sm font-semibold text-gray-900">{{ $typeLabels[$row->type] ?? ucfirst(str_replace('_', ' ', $row->type)) }}</p>
                            <p class="mt-2 text-2xl font-bold text-indigo-600">{{ number_format((int) $row->total) }}</p>
                            <div class="mt-2 flex items-center gap-2 text-xs">
                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800">P: {{ (int) $row->pending }}</span>
                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">A: {{ (int) $row->approved }}</span>
                                <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800">R: {{ (int) $row->rejected }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="sm:col-span-2 lg:col-span-3 text-center py-10 text-gray-500">
                            No leave request data available.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70">
                <h2 class="text-base font-semibold text-gray-900">Recent Leave Requests</h2>
                <p class="text-sm text-gray-500">Latest submissions from employees.</p>
            </div>
            <div class="divide-y divide-gray-100 max-h-[560px] overflow-y-auto">
                @forelse($recentLeaveRequests as $leave)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900"><x-user-name :user="$leave->user" /></p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $typeLabels[$leave->type] ?? ucfirst(str_replace('_', ' ', $leave->type)) }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ optional($leave->start_date)->format('M d, Y') }} - {{ optional($leave->end_date)->format('M d, Y') }}
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $leave->status_badge_class }}">
                                {{ $leave->display_status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 text-sm">
                        No recent leave requests.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden"
         x-data="{
             employeeViewMode: 'summary',
             employeesOpen: JSON.parse(localStorage.getItem('admin.employeeDashboard.employeesOpen') ?? 'false'),
             toggleEmployeesOpen() {
                 this.employeesOpen = !this.employeesOpen;
                 localStorage.setItem('admin.employeeDashboard.employeesOpen', JSON.stringify(this.employeesOpen));
             }
         }">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70 relative z-30">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">All Employees Data</h2>
                    <p class="text-sm text-gray-500">Complete employee list with status and leave request totals.</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                    <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 w-full sm:w-auto">
                        <button type="button"
                                @click="employeeViewMode = 'summary'"
                                :class="employeeViewMode === 'summary' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors w-full sm:w-auto">
                            Summary
                        </button>
                        <button type="button"
                                @click="employeeViewMode = 'detailed'"
                                :class="employeeViewMode === 'detailed' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-1.5 text-xs font-semibold rounded-md transition-colors w-full sm:w-auto">
                            Detailed per-type
                        </button>
                    </div>
                    <button type="button"
                            @click="toggleEmployeesOpen()"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-gray-700 text-xs font-semibold hover:bg-gray-50">
                        <span x-text="employeesOpen ? 'Collapse' : 'Expand'"></span>
                        <svg class="h-4 w-4 transition-transform" :class="employeesOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div x-show="employeesOpen" x-cloak class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Employee</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Department</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">University</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Total Leaves</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Pending</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Approved</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Rejected</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Leave Balance</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Overtime Balance</th>
                        <th x-show="employeeViewMode === 'detailed'" x-cloak class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Per Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($employees as $employee)
                        @php
                            $s = $employeeLeaveStats[$employee->id] ?? null;
                            $total = (int) ($s->total ?? 0);
                            $pending = (int) ($s->pending ?? 0);
                            $approved = (int) ($s->approved ?? 0);
                            $rejected = (int) ($s->rejected ?? 0);
                            $b = $employeeBalances[$employee->id] ?? null;
                            $leaveRemaining = $b['leave_remaining'] ?? 0;
                            $overtimeFormatted = $b['overtime_formatted'] ?? '00:00';
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-gray-900"><x-user-name :user="$employee" /></div>
                                <div class="text-xs text-gray-500">#{{ $employee->id }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $employee->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $employee->department->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $employee->university->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $employee->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold text-indigo-700">{{ $total }}</td>
                            <td class="px-4 py-3 text-center text-sm font-medium text-amber-700">{{ $pending }}</td>
                            <td class="px-4 py-3 text-center text-sm font-medium text-emerald-700">{{ $approved }}</td>
                            <td class="px-4 py-3 text-center text-sm font-medium text-rose-700">{{ $rejected }}</td>
                            <td class="px-4 py-3 text-center text-sm font-semibold text-gray-900">{{ rtrim(rtrim(number_format((float) $leaveRemaining, 2, '.', ''), '0'), '.') }}</td>
                            <td class="px-4 py-3 text-center text-sm font-semibold {{ str_starts_with($overtimeFormatted, '-') ? 'text-rose-700' : 'text-emerald-700' }}">{{ $overtimeFormatted }}</td>
                            <td x-show="employeeViewMode === 'detailed'" x-cloak class="px-4 py-3 text-xs text-gray-700 min-w-[360px]">
                                <div class="grid grid-cols-2 xl:grid-cols-3 gap-1.5">
                                    @foreach($typeLabels as $typeKey => $typeLabel)
                                        <span class="inline-flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 px-2 py-1">
                                            <span class="truncate mr-2">{{ $typeLabel }}</span>
                                            <span class="font-semibold text-indigo-700">{{ (int) ($employeeTypeCounts[$employee->id][$typeKey] ?? 0) }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ optional($employee->created_at)->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td :colspan="employeeViewMode === 'detailed' ? 13 : 12" class="px-4 py-10 text-center text-sm text-gray-500">No employees found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-visible"
         x-data="{
             leaveDaysOpen: JSON.parse(localStorage.getItem('admin.employeeDashboard.leaveDaysOpen') ?? 'false'),
             toggleLeaveDaysOpen() {
                 this.leaveDaysOpen = !this.leaveDaysOpen;
                 localStorage.setItem('admin.employeeDashboard.leaveDaysOpen', JSON.stringify(this.leaveDaysOpen));
             }
         }">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Leave Request Days by Employee</h2>
                    <p class="text-sm text-gray-500">Total leave-request days per employee, grouped by leave type.</p>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 w-full lg:w-auto">
                    <form method="GET" action="{{ url('/admin/employee-dashboard') }}" class="w-full lg:w-auto grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-1.5 items-end">
                        <input type="hidden" name="leave_days_filter" value="1">
                        <div class="min-w-0">
                            <label for="leave_days_start_date" class="block text-[10px] text-gray-500 mb-0.5">From</label>
                            <input id="leave_days_start_date" name="leave_days_start_date" type="date" value="{{ $leaveDaysStartDate ?? '' }}" class="h-8 w-full rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="min-w-0">
                            <label for="leave_days_end_date" class="block text-[10px] text-gray-500 mb-0.5">To</label>
                            <input id="leave_days_end_date" name="leave_days_end_date" type="date" value="{{ $leaveDaysEndDate ?? '' }}" class="h-8 w-full rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="min-w-0">
                            <label for="leave_days_sort" class="block text-[10px] text-gray-500 mb-0.5">Sort</label>
                            <select id="leave_days_sort" name="leave_days_sort" class="h-8 w-full rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="total_desc" {{ ($leaveDaysSort ?? 'total_desc') === 'total_desc' ? 'selected' : '' }}>Highest total first</option>
                                <option value="total_asc" {{ ($leaveDaysSort ?? 'total_desc') === 'total_asc' ? 'selected' : '' }}>Lowest total first</option>
                                <option value="name_asc" {{ ($leaveDaysSort ?? 'total_desc') === 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                                <option value="name_desc" {{ ($leaveDaysSort ?? 'total_desc') === 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                            </select>
                        </div>
                        <div class="min-w-0 relative"
                             x-data="{ leaveTypeOpen: false }">
                            <label class="block text-[10px] text-gray-500 mb-0.5">Leave Type</label>
                            <button type="button"
                                    @click="leaveTypeOpen = !leaveTypeOpen"
                                    class="h-8 w-full rounded-md border border-gray-300 bg-white px-2 text-xs text-gray-700 flex items-center justify-between">
                                @php $selectedLeaveTypesCount = count((array) ($leaveDaysTypes ?? [])); @endphp
                                <span>{{ $selectedLeaveTypesCount > 0 ? ($selectedLeaveTypesCount . ' selected') : 'All types' }}</span>
                                <svg class="h-4 w-4 text-gray-500 transition-transform" :class="leaveTypeOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="leaveTypeOpen"
                                 x-cloak
                                 @click.outside="leaveTypeOpen = false"
                                 class="absolute top-full left-0 mt-1 w-full z-[120] rounded-md border border-gray-200 bg-white shadow-lg p-2 space-y-1 max-h-52 overflow-y-auto">
                                @foreach($typeLabels as $typeKey => $typeLabel)
                                    <label class="flex items-center gap-2 text-xs text-gray-700">
                                        <input type="checkbox"
                                               name="leave_days_type[]"
                                               value="{{ $typeKey }}"
                                               {{ in_array($typeKey, (array) ($leaveDaysTypes ?? []), true) ? 'checked' : '' }}
                                               class="h-3.5 w-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span>{{ $typeLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <button type="submit" class="h-8 inline-flex items-center justify-center px-2.5 rounded-md bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
                            Apply
                        </button>
                        <a href="{{ url('/admin/employee-dashboard') }}" class="h-8 inline-flex items-center justify-center px-2.5 rounded-md border border-gray-200 bg-white text-gray-700 text-xs font-semibold hover:bg-gray-50">
                            Clear
                        </a>
                    </form>
                    <button type="button"
                            @click="toggleLeaveDaysOpen()"
                            class="h-8 inline-flex items-center justify-center gap-1 px-2.5 rounded-md border border-gray-200 bg-white text-gray-700 text-xs font-semibold hover:bg-gray-50 shrink-0 self-end">
                        <span x-text="leaveDaysOpen ? 'Collapse' : 'Expand'"></span>
                        <svg class="h-4 w-4 transition-transform" :class="leaveDaysOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div x-show="leaveDaysOpen" x-cloak class="overflow-x-auto relative z-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Employee</th>
                            @foreach($typeLabels as $typeLabel)
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 whitespace-nowrap">{{ $typeLabel }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-700 whitespace-nowrap">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse(($leaveDaysEmployees ?? collect()) as $row)
                        @php
                            $employee = $row['employee'];
                            $daysByType = $row['days_by_type'] ?? [];
                            $totalLeaveDays = (int) ($row['total_days'] ?? 0);
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium text-gray-900"><x-user-name :user="$employee" /></div>
                                <div class="text-xs text-gray-500">{{ $employee->email }}</div>
                            </td>
                            @foreach($typeLabels as $typeKey => $typeLabel)
                                <td class="px-4 py-3 text-sm text-right font-semibold text-rose-700 whitespace-nowrap">
                                    {{ (int) ($daysByType[$typeKey] ?? 0) }}
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-sm text-right font-extrabold text-indigo-700 whitespace-nowrap">
                                {{ (int) $totalLeaveDays }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 2 + count($typeLabels) }}" class="px-4 py-10 text-center text-sm text-gray-500">
                                No approved leave day data available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

