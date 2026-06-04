@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col min-h-0">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-4 flex-shrink-0">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center min-w-0">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-3 min-w-0">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white truncate">Leave Requests</h1>
                    <p class="text-indigo-100 text-sm">Manage your leave and other requests</p>
                </div>
            </div>
            <a href="{{ route('user.leave-requests.create') }}"
               class="inline-flex w-full sm:w-auto justify-center items-center px-4 py-2.5 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white hover:bg-white/30 transition duration-200 touch-manipulation shrink-0">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Request
            </a>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('info'))
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mx-4 mt-4 rounded-lg">
            <p class="text-sm text-blue-800">{{ session('info') }}</p>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mx-4 mt-4 rounded-lg">
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

    <!-- Statistics & balance cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-5 pt-5 sm:pt-6 px-4 pb-5 sm:pb-6">
        <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Requests</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Approved</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['approved'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Rejected</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['rejected'] }}</p>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'employee')
            <div class="bg-white rounded-lg shadow p-4 border border-indigo-200 sm:col-span-2 lg:col-span-2">
                <div class="flex items-center min-h-[4.5rem]">
                    <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 min-w-0">
                        <p class="text-sm font-medium text-gray-500">Leave Credits</p>
                        <p class="text-xl font-bold text-gray-900">
                            {{ $balances['leave']['remaining'] }} / {{ $balances['leave']['allowance'] }} days
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Used: {{ $balances['leave']['used'] }} {{ $balances['leave']['used'] == 1 ? 'day' : 'days' }} this year
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border border-emerald-200 sm:col-span-2 lg:col-span-2">
                <div class="flex items-center min-h-[4.5rem]">
                    <div class="flex-shrink-0 bg-emerald-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m3-4h-4a2 2 0 00-2 2v6a2 2 0 002 2h3l2 2 2-2h1a2 2 0 002-2v-3a8 8 0 10-4 0v1"></path>
                        </svg>
                    </div>
                    <div class="ml-4 min-w-0">
                        <p class="text-sm font-medium text-gray-500">Overtime ({{ $overtimeWindowLabel ?? 'This Year' }})</p>
                        <p class="text-xl">
                            <span class="font-bold {{ str_starts_with($overtimeFormatted, '-') ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $overtimeFormatted }}
                            </span>
                        </p>
                        @if(isset($approvedAbsentCount))
                            <p class="text-xs text-gray-500 mt-1">Approved Absent: {{ $approvedAbsentCount }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if(auth()->user()->role === 'student' && isset($studentTime))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 px-4 pb-4">
            <!-- Total Time from DTR -->
            <div class="bg-white rounded-lg shadow p-4 border border-indigo-200 sm:col-span-2 lg:col-span-2">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Time from DTR</p>
                        <p class="text-xl font-bold text-gray-900">
                            {{ $studentTime['total_dtr_hours_formatted'] }} hours
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Sum of all your recorded DTR hours
                        </p>
                    </div>
                </div>
            </div>

            <!-- Remaining Time Needed -->
            <div class="bg-white rounded-lg shadow p-4 border border-blue-200 sm:col-span-2 lg:col-span-2">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m9-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Remaining Time Needed</p>
                        @if($studentTime['required_hours'] > 0 && $studentTime['remaining_hours_formatted'])
                            @php $hasRemaining = $studentTime['remaining_hours'] > 0; @endphp
                            <p class="text-xl font-bold {{ $hasRemaining ? 'text-red-600' : 'text-green-600' }}">
                                {{ $studentTime['remaining_hours_formatted'] }} hours
                            </p>
                            <p class="text-xs mt-1 {{ $hasRemaining ? 'text-red-500' : 'text-green-500' }}">
                                {{ $hasRemaining ? 'You still need to complete this time.' : 'You have met or exceeded the required time.' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Time Needed to Acquire:
                                <span class="font-semibold">
                                    {{ $studentTime['required_hours_formatted'] ?? 'Not set' }}
                                </span>
                                @if(!empty($studentTime['required_hours_formatted']))
                                    hours
                                @endif
                            </p>
                        @else
                            <p class="text-xl font-bold text-gray-500">
                                Not set
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                Required hours not configured. Please contact admin.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    @endif

    <!-- Leave Requests Table -->
    <div class="flex-1 overflow-y-auto px-3 sm:px-4 pt-2 sm:pt-3 pb-3 sm:pb-4">
        @if($leaveRequests->count() > 0)
            <x-responsive-data-panel>
                <x-slot:mobile>
                    @foreach($leaveRequests as $request)
                        <div class="mobile-card">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="mobile-card-title">{{ $request->type_label }}</p>
                                    <p class="mobile-card-subtitle">
                                        {{ $request->start_date->format('M d, Y') }}
                                        @if($request->end_date && $request->end_date->ne($request->start_date))
                                            – {{ $request->end_date->format('M d, Y') }}
                                        @endif
                                    </p>
                                </div>
                                <span class="px-2 py-1 inline-flex shrink-0 text-xs leading-5 font-semibold rounded-full {{ $request->status_badge_class }}">
                                    {{ $request->display_status }}
                                </span>
                            </div>
                            <dl class="mobile-card-kv">
                                <dt>Duration</dt>
                                <dd>{{ $request->duration_display_label }}</dd>
                                <dt>Submitted</dt>
                                <dd>{{ $request->created_at->format('M d, Y') }}</dd>
                            </dl>
                            <div class="mobile-card-actions">
                                <a href="{{ route('user.leave-requests.show', $request) }}"
                                   class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 touch-manipulation py-1">
                                    View
                                </a>
                                @if($request->needsAttendanceOvertimeCompletion())
                                    <a href="{{ route('user.leave-requests.complete-attendance-overtime', $request) }}"
                                       class="text-sm font-semibold text-orange-700 hover:text-orange-900 touch-manipulation py-1">
                                        Complete details
                                    </a>
                                @endif
                                @if($request->isPending())
                                    <form action="{{ route('user.leave-requests.destroy', $request) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this leave request?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800 touch-manipulation py-1">
                                            Delete
                                        </button>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Range</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($leaveRequests as $request)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900">{{ $request->type_label }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $request->start_date->format('M d, Y') }}
                                            @if($request->end_date && $request->end_date->ne($request->start_date))
                                                <span class="text-gray-500">-</span>
                                                {{ $request->end_date->format('M d, Y') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">{{ $request->duration_display_label }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $request->status_badge_class }}">
                                            {{ $request->display_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center flex-wrap gap-3">
                                            <a href="{{ route('user.leave-requests.show', $request) }}"
                                               class="text-indigo-600 hover:text-indigo-900">
                                                View
                                            </a>
                                            @if($request->needsAttendanceOvertimeCompletion())
                                                <a href="{{ route('user.leave-requests.complete-attendance-overtime', $request) }}"
                                                   class="text-orange-700 hover:text-orange-900 font-semibold">
                                                    Complete details
                                                </a>
                                            @endif
                                            @if($request->isPending())
                                                <form action="{{ route('user.leave-requests.destroy', $request) }}" method="POST" class="inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this leave request?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
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
            <div class="bg-white rounded-lg border border-gray-200 mt-3 px-4 py-3">
                {{ $leaveRequests->links() }}
            </div>
        @else
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No leave requests</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new leave request.</p>
                    <div class="mt-6">
                        <a href="{{ route('user.leave-requests.create') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            New Request
                        </a>
                    </div>
                </div>
        </div>
        @endif
    </div>
</div>
@endsection

