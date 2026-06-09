@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl px-4 py-6 sm:px-6 sm:py-8 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-3 sm:p-4">
                    <svg class="h-8 w-8 sm:h-10 sm:w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold">Employee Leave Requests</h1>
                    <p class="text-sm sm:text-base text-indigo-100 mt-1">Review and manage all employee leave requests</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-2 sm:p-3">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Requests</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-2 sm:p-3">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Pending</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-lg p-2 sm:p-3">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Approved</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['approved'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-100 rounded-lg p-2 sm:p-3">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Rejected</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['rejected'] }}</p>
                </div>
            </div>
        </div>
    </div>

    @php
        $leaveExportQuery = array_filter([
            'department_id' => request('department_id'),
            'type' => request('type'),
            'employee' => request('employee'),
            'date_from' => request('date_from'),
            'date_to' => request('date_to'),
            'search' => request('search', $search ?? ''),
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-4 border-b border-gray-100">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Export approved requests</h2>
                <p class="text-xs text-gray-500 mt-0.5">Exports <strong>Sick Leave</strong> and <strong>Vacation Leave</strong> only, grouped per employee with approved request counts. Status is always <strong>Approved</strong>.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.leave-requests.export-csv', $leaveExportQuery) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-green-600 text-sm font-medium text-white hover:bg-green-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export CSV
                </a>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                    <input type="checkbox" id="leave-export-pdf-with-qr" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <span>Include verification QR</span>
                </label>
                <a href="{{ route('admin.leave-requests.export-pdf', $leaveExportQuery) }}"
                   id="leave-export-pdf-link"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-red-600 text-sm font-medium text-white hover:bg-red-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Export PDF
                </a>
            </div>
        </div>

        <form method="GET" action="{{ url('/admin/leave-requests') }}" class="space-y-4">
            <div class="flex flex-nowrap items-end gap-2 w-full">
                <div class="flex-1 min-w-0 basis-0">
                    <label for="department_id" class="block text-xs font-medium text-gray-700 mb-1">Department</label>
                    <select name="department_id" id="department_id" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Departments</option>
                        @foreach($departments ?? [] as $department)
                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-0 basis-0">
                    <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="for_more_verification" {{ request('status') == 'for_more_verification' ? 'selected' : '' }}>For More Verification</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="flex-1 min-w-0 basis-0">
                    <label for="type" class="block text-xs font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" id="type" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Types</option>
                        <option value="vacation_leave" {{ request('type') == 'vacation_leave' ? 'selected' : '' }}>Vacation Leave</option>
                        <option value="sick_leave" {{ request('type') == 'sick_leave' ? 'selected' : '' }}>Sick Leave</option>
                        <option value="work_from_home" {{ request('type') == 'work_from_home' ? 'selected' : '' }}>Work From Home</option>
                        <option value="absent" {{ request('type') == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="overtime" {{ request('type') == 'overtime' ? 'selected' : '' }}>Overtime</option>
                        <option value="offset" {{ request('type') == 'offset' ? 'selected' : '' }}>Offset</option>
                        <option value="travel" {{ request('type') == 'travel' ? 'selected' : '' }}>Travel</option>
                        <option value="additional_time" {{ request('type') == 'additional_time' ? 'selected' : '' }}>Additional Time</option>
                        <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="flex-1 min-w-0 basis-0">
                    <label for="employee" class="block text-xs font-medium text-gray-700 mb-1">Employee</label>
                    <select name="employee" id="employee" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-0 basis-0">
                    <label for="date_from" class="block text-xs font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date"
                           name="date_from"
                           id="date_from"
                           value="{{ request('date_from') }}"
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="flex-1 min-w-0 basis-0">
                    <label for="date_to" class="block text-xs font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date"
                           name="date_to"
                           id="date_to"
                           value="{{ request('date_to') }}"
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="flex-[2.5] min-w-0 basis-0">
                    <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                    <input type="text"
                           name="search"
                           id="search"
                           value="{{ request('search', $search ?? '') }}"
                           placeholder="Search employee, email, type, status, reason, ID..."
                           autocomplete="off"
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="shrink-0 w-24">
                    <label for="employee-leave-filter-submit" class="block text-xs font-medium text-gray-700 mb-1 invisible" aria-hidden="true">Filter</label>
                    <button type="submit" id="employee-leave-filter-submit" class="w-full px-3 py-1.5 text-sm whitespace-nowrap bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Filter
                    </button>
                </div>
            </div>

            @if(request('department_id') || request('status') || request('type') || request('employee') || request('date_from') || request('date_to') || request('search'))
                <div class="flex justify-end">
                    <a href="{{ url('/admin/leave-requests') }}"
                       class="text-sm text-gray-600 hover:text-gray-900 underline">
                        Clear filters
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Leave Requests Table -->
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        @if($leaveRequests->count() > 0)
            <x-responsive-data-panel class="border-0 shadow-none rounded-none">
                <x-slot:mobile>
                    @foreach($leaveRequests as $request)
                        <div class="mobile-card">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="mobile-card-title">{{ $request->user->name }}</p>
                                    <p class="mobile-card-subtitle">{{ $request->user->email }}</p>
                                    <p class="text-sm font-medium text-gray-800 mt-1">{{ $request->type_label }}</p>
                                </div>
                                <span class="px-2 py-1 inline-flex shrink-0 text-xs leading-5 font-semibold rounded-full {{ $request->status_badge_class }}">
                                    {{ $request->display_status }}
                                </span>
                            </div>
                            <dl class="mobile-card-kv">
                                <dt>Dates</dt>
                                <dd>
                                    {{ $request->start_date->format('M d') }}
                                    @if($request->end_date && $request->end_date->ne($request->start_date))
                                        – {{ $request->end_date->format('M d, Y') }}
                                    @endif
                                </dd>
                                <dt>Duration</dt>
                                <dd>{{ $request->duration_display_label }}</dd>
                                <dt>Submitted</dt>
                                <dd>{{ $request->created_at->format('M d, Y') }}</dd>
                            </dl>
                            <div class="mobile-card-actions">
                                <a href="{{ url('/admin/leave-requests/' . $request->id) }}"
                                   class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 touch-manipulation py-1">
                                    View Details
                                </a>
                                @php
                                    $filedByAdminLog = $request->logs->firstWhere('action', 'filed_by_admin');
                                    $canDelete = $filedByAdminLog && $filedByAdminLog->performed_by === auth()->id();
                                @endphp
                                @if($canDelete)
                                    <form action="{{ url('/admin/leave-requests/' . $request->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this leave request?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800 touch-manipulation py-1">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </x-slot:mobile>

                <x-slot:desktop>
            <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Date Range</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Reviewed By</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Submitted</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($leaveRequests as $request)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 sm:px-6 py-4">
                                    <div class="flex items-center min-w-0">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-medium text-gray-900 truncate">{{ $request->user->name }}</div>
                                            <div class="text-xs sm:text-sm text-gray-500 truncate">{{ $request->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 sm:px-6 py-4">
                                    <span class="text-xs sm:text-sm text-gray-900">{{ $request->type_label }}</span>
                                </td>
                                <td class="px-3 sm:px-6 py-4 hidden sm:table-cell">
                                    <div class="text-xs sm:text-sm text-gray-900">
                                        {{ $request->start_date->format('M d, Y') }}
                                        @if($request->end_date && $request->end_date->ne($request->start_date))
                                            <span class="text-gray-500">-</span>
                                            {{ $request->end_date->format('M d, Y') }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 sm:px-6 py-4">
                                    <span class="text-xs sm:text-sm text-gray-900">{{ $request->duration_display_label }}</span>
                                    <div class="text-xs text-gray-500 sm:hidden mt-1">
                                        {{ $request->start_date->format('M d') }}
                                        @if($request->end_date && $request->end_date->ne($request->start_date))
                                            - {{ $request->end_date->format('M d') }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 sm:px-6 py-4">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $request->status_badge_class }}">
                                        {{ $request->display_status }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-6 py-4 hidden lg:table-cell text-xs sm:text-sm text-gray-500">
                                    @if($request->status === 'approved' && $request->approvedBy && $request->approvedBy->performer)
                                        <div class="text-xs">
                                            <div class="font-medium text-gray-900">{{ $request->approvedBy->performer->name }}</div>
                                            <div class="text-gray-500">{{ $request->approvedBy->created_at->format('M d, Y') }}</div>
                                        </div>
                                    @elseif($request->status === 'rejected' && $request->rejectedBy && $request->rejectedBy->performer)
                                        <div class="text-xs">
                                            <div class="font-medium text-gray-900">{{ $request->rejectedBy->performer->name }}</div>
                                            <div class="text-gray-500">{{ $request->rejectedBy->created_at->format('M d, Y') }}</div>
                                        </div>
                                    @elseif($request->status === 'pending' && $request->reviewed_at && $request->resubmissionRequestedBy && $request->resubmissionRequestedBy->performer)
                                        <div class="text-xs">
                                            <div class="font-medium text-gray-900">{{ $request->resubmissionRequestedBy->performer->name }}</div>
                                            <div class="text-gray-500">{{ $request->resubmissionRequestedBy->created_at->format('M d, Y') }}</div>
                                        </div>
                                    @elseif($request->status === 'for_more_verification' && $request->reviewer)
                                        <div class="text-xs">
                                            <div class="font-medium text-gray-900">{{ $request->reviewer->name }}</div>
                                            <div class="text-gray-500">{{ optional($request->reviewed_at)->format('M d, Y') }}</div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-6 py-4 hidden lg:table-cell text-xs sm:text-sm text-gray-500">
                                    {{ $request->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                    <a href="{{ url('/admin/leave-requests/' . $request->id) }}"
                                       class="text-indigo-600 hover:text-indigo-900 whitespace-nowrap">
                                        View Details
                                    </a>
                                        @php
                                            $filedByAdminLog = $request->logs->firstWhere('action', 'filed_by_admin');
                                            $canDelete = $filedByAdminLog && $filedByAdminLog->performed_by === auth()->id();
                                        @endphp
                                        @if($canDelete)
                                            <form action="{{ url('/admin/leave-requests/' . $request->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this leave request?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 whitespace-nowrap">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </x-slot:desktop>
            </x-responsive-data-panel>

            <!-- Pagination -->
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                {{ $leaveRequests->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No leave requests</h3>
                <p class="mt-1 text-sm text-gray-500">No leave requests match your filters.</p>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action="{{ url('/admin/leave-requests') }}"]');
        const searchInput = document.getElementById('search');

        let t = null;
        if (form && searchInput) {
            searchInput.addEventListener('input', function () {
                if (t) clearTimeout(t);
                t = setTimeout(() => form.submit(), 350);
            });
        }

        const pdfLink = document.getElementById('leave-export-pdf-link');
        const pdfWithQr = document.getElementById('leave-export-pdf-with-qr');
        const syncPdfExportUrl = () => {
            if (!pdfLink) {
                return;
            }
            const url = new URL(pdfLink.href, window.location.origin);
            if (pdfWithQr?.checked) {
                url.searchParams.set('with_qr', '1');
            } else {
                url.searchParams.delete('with_qr');
            }
            pdfLink.href = url.pathname + url.search;
        };
        pdfWithQr?.addEventListener('change', syncPdfExportUrl);
        pdfLink?.addEventListener('click', syncPdfExportUrl);
    });
</script>
@endsection

