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
                                <p class="text-sm font-semibold text-gray-900">{{ $leave->user->name ?? 'Unknown Employee' }}</p>
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

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70">
            <h2 class="text-base font-semibold text-gray-900">All Employees Data</h2>
            <p class="text-sm text-gray-500">Complete employee list with status and leave request totals.</p>
        </div>
        <div class="overflow-x-auto">
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
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-gray-900">{{ $employee->name }}</div>
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
                            <td class="px-4 py-3 text-sm text-gray-600">{{ optional($employee->created_at)->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-10 text-center text-sm text-gray-500">No employees found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

