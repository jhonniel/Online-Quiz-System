@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl px-4 py-6 sm:px-6 sm:py-8 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-3 sm:p-4">
                    <svg class="h-8 w-8 sm:h-10 sm:w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold">Student Time Requests</h1>
                    <p class="text-sm sm:text-base text-indigo-100 mt-1">Review and approve student attendance time requests</p>
                </div>
            </div>
            @isset($pendingCount)
                <div class="mt-2 md:mt-0 flex space-x-3 md:ml-auto">
                    <div class="bg-white/10 rounded-xl px-3 py-2 text-center">
                        <p class="text-xs text-indigo-100 uppercase tracking-wide">Pending</p>
                        <p class="mt-1 text-lg font-semibold">{{ $pendingCount }}</p>
                    </div>
                    <div class="bg-white/10 rounded-xl px-3 py-2 text-center">
                        <p class="text-xs text-indigo-100 uppercase tracking-wide">Rejected</p>
                        <p class="mt-1 text-lg font-semibold">{{ $rejectedCount }}</p>
                    </div>
                </div>
            @endisset
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(!empty($undertimeRulesResult['enabled']) || !empty($undertimeRulesResult['disabled']))
        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-lg">
            <p class="text-sm text-amber-900">
                @if(!empty($undertimeRulesResult['enabled']))
                    <strong>{{ $undertimeRulesResult['enabled'] }}</strong> student(s) had
                    <strong>Rules violation warning</strong> enabled automatically (5+ consecutive under-time days).
                @endif
                @if(!empty($undertimeRulesResult['enabled']) && !empty($undertimeRulesResult['disabled']))
                    <span class="mx-1">·</span>
                @endif
                @if(!empty($undertimeRulesResult['disabled']))
                    <strong>{{ $undertimeRulesResult['disabled'] }}</strong> student(s) had the warning
                    <strong>disabled automatically</strong> (10+ consecutive 08:00 days).
                @endif
                Admin-manually enabled warnings are not changed by automation.
            </p>
        </div>
    @endif

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

    @if(session('error') || $errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        {{ session('error') }}
                        @foreach($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="{{ url('/admin/time-requests') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               id="search"
                               value="{{ request('search') }}"
                               placeholder="Student name, email, or school..."
                               autocomplete="off"
                               class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-500 min-h-[1rem]">Name, email, or school</p>
                </div>

                <div>
                    <label for="university_id" class="block text-sm font-medium text-gray-700 mb-2">School / University</label>
                    <select name="university_id" id="university_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Schools</option>
                        @foreach(($universities ?? collect()) as $university)
                            <option value="{{ $university->id }}" {{ (string) request('university_id') === (string) $university->id ? 'selected' : '' }}>
                                {{ $university->full_name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 min-h-[1rem] invisible" aria-hidden="true">&nbsp;</p>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 min-h-[1rem] invisible" aria-hidden="true">&nbsp;</p>
                </div>

                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                    <select name="student_id" id="student_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Students</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 min-h-[1rem] invisible" aria-hidden="true">&nbsp;</p>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-xs text-gray-500 min-h-[1rem]">
                        {{ request('date_from') ? "\u{00a0}" : 'Leave empty for all dates' }}
                    </p>
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-xs text-gray-500 min-h-[1rem]">
                        {{ request('date_to') ? "\u{00a0}" : 'Leave empty for all dates' }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <x-admin-filter-button id="time-requests-filter-submit" />
                @if(request()->hasAny(['search', 'university_id', 'status', 'student_id', 'date_from', 'date_to']))
                    <a href="{{ url('/admin/time-requests') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Time Requests Table -->
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        @php($canBulkApprove = auth()->user()->isAdmin())
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Requests</h2>
                @if($canBulkApprove)
                    <p class="text-sm text-gray-600 mt-0.5">Select pending requests to approve in bulk</p>
                @else
                    <p class="text-sm text-gray-600 mt-0.5">Review and process student attendance time requests</p>
                @endif
            </div>
            @if($canBulkApprove)
                <div id="bulk-actions" class="flex flex-wrap items-center gap-3" style="display: none;">
                    <span class="text-sm text-gray-700" id="selected-count">0 selected</span>
                    <button type="button"
                            onclick="openBulkApproveModal()"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-green-600 hover:bg-green-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Approve Selected
                    </button>
                </div>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @if($canBulkApprove)
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                                <input type="checkbox"
                                       id="select-all-pending"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                       title="Select all pending on this page"
                                       onchange="toggleSelectAllPending(this)">
                            </th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours (HH:MM)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($timeRequests as $request)
                        <tr class="hover:bg-gray-50">
                            @if($canBulkApprove)
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    @if($request->status === 'pending')
                                        <input type="checkbox"
                                               class="pending-request-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                               value="{{ $request->id }}"
                                               onchange="updateBulkSelection()">
                                    @endif
                                </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><x-user-name :user="$request->user" :size="16" /></div>
                                <div class="text-sm text-gray-500">{{ $request->user->email }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ optional($request->user->university)->name ?? 'No school on file' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>{{ $request->date->format('M d, Y') }}</div>
                                @if($request->requested_total_hours && (float) $request->requested_total_hours > \App\Support\DtrTimeRequestHours::STANDARD_DAY_HOURS)
                                    <div class="text-xs text-amber-700 mt-0.5">
                                        Day total filed: <span class="font-mono font-semibold">{{ \App\Support\DtrTimeRequestHours::decimalToTimeString((float) $request->requested_total_hours) }}</span>
                                    </div>
                                @elseif($request->requested_total_hours)
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        Day total filed: <span class="font-mono font-medium text-gray-700">{{ \App\Support\DtrTimeRequestHours::decimalToTimeString((float) $request->requested_total_hours) }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $request->isOvertime() ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $request->request_type_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                {{ $request->formatted_time }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $request->remarks ?: '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $request->getStatusBadgeClass() }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $request->created_at->format('M d, Y g:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if(in_array($request->status, ['pending', 'rejected'], true))
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <button type="button"
                                                onclick="openEditModal({{ $request->id }}, @js($request->user->name), @js($request->date->format('Y-m-d')), @js($request->formatted_time), @js($request->remarks ?? ''), @js($request->request_type_label), @js($request->isOvertime()))"
                                                class="text-indigo-600 hover:text-indigo-900">
                                            Edit
                                        </button>
                                        @if($request->status === 'pending')
                                            <span class="text-gray-300">|</span>
                                            <button type="button" onclick="openApproveModal({{ $request->id }})"
                                                    class="text-green-600 hover:text-green-900">
                                                Approve
                                            </button>
                                            <span class="text-gray-300">|</span>
                                            <button type="button" onclick="openRejectModal({{ $request->id }})"
                                                    class="text-red-600 hover:text-red-900">
                                                Reject
                                            </button>
                                        @else
                                            <span class="text-gray-300">|</span>
                                            <span class="text-xs text-gray-500">Edit saves as pending</span>
                                            @if(auth()->user()->isSuperAdmin())
                                                <span class="text-gray-300">|</span>
                                                <button type="button"
                                                        onclick="openDeleteModal({{ $request->id }}, @js($request->user->name), @js($request->date->format('M d, Y')))"
                                                        class="text-red-600 hover:text-red-900"
                                                        title="Delete rejected request">
                                                    Delete
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                @else
                                    <div class="flex items-center space-x-3">
                                        <div class="text-gray-400">
                                            @if($request->reviewer)
                                                Reviewed by <x-user-name :user="$request->reviewer" />
                                                @if($request->reviewed_at)
                                                    <br><span class="text-xs">{{ $request->reviewed_at->format('M d, Y g:i A') }}</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canBulkApprove ? 9 : 8 }}" class="px-6 py-8 text-center text-sm text-gray-500">
                                No time requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($timeRequests->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ $timeRequests->firstItem() }} to {{ $timeRequests->lastItem() }} of {{ $timeRequests->total() }} results
                    </div>
                    <div class="flex items-center space-x-2">
                        {{ $timeRequests->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="text-sm text-gray-700">
                    Showing {{ $timeRequests->count() }} result(s)
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Edit Time Request</h3>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <p class="text-sm text-gray-600 mb-1">
            Student: <span id="edit-student-name" class="font-medium text-gray-900"></span>
        </p>
        <p class="text-sm text-gray-600 mb-4">
            Request type: <span id="edit-request-type" class="font-medium text-gray-900"></span>
        </p>

        <form id="edit-form" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="edit_date" class="block text-sm font-medium text-gray-700 mb-2">Date <span class="text-red-600">*</span></label>
                    <input type="date" name="date" id="edit_date" required max="{{ now()->format('Y-m-d') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="edit_time" class="block text-sm font-medium text-gray-700 mb-2"><span id="edit-time-label">Worked hours (HH:MM)</span> <span class="text-red-600">*</span></label>
                    <input type="text" name="time" id="edit_time" required
                           placeholder="08:00"
                           autocomplete="off"
                           class="edit-time-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono text-center">
                    <p class="mt-1 text-xs text-gray-500">
                        Duration in <strong>HH:MM</strong> format (e.g. 08:00 = 8 hours, 07:30 = 7.5 hours). <strong>No AM/PM</strong> — not clock time.
                    </p>
                </div>
                <div>
                    <label for="edit_remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                    <textarea name="remarks" id="edit_remarks" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                        onclick="closeEditModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Save changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Approve Modal -->
<div id="approve-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Approve Time Request</h3>
            <button onclick="closeApproveModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="approve-form" method="POST" action="">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="approve_admin_notes" class="block text-sm font-medium text-gray-700 mb-2">Admin Notes (Optional)</label>
                    <textarea name="admin_notes" 
                              id="approve_admin_notes" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" 
                        onclick="closeApproveModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Approve
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Approve Modal (admin only) -->
@if(auth()->user()->isAdmin())
<div id="bulk-approve-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Approve Selected Time Requests</h3>
            <button type="button" onclick="closeBulkApproveModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <p class="text-sm text-gray-600 mb-4">
            You are about to approve <span id="bulk-approve-count" class="font-semibold text-gray-900">0</span> pending request(s). Hours will be applied to each student's DTR.
        </p>

        <form id="bulk-approve-form" method="POST" action="{{ route('admin.time-requests.bulk-approve') }}">
            @csrf
            <div id="bulk-approve-ids"></div>
            <div class="space-y-4">
                <div>
                    <label for="bulk_approve_admin_notes" class="block text-sm font-medium text-gray-700 mb-2">Admin Notes (Optional)</label>
                    <textarea name="admin_notes"
                              id="bulk_approve_admin_notes"
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Applied to all selected requests"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                        onclick="closeBulkApproveModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Approve Selected
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Reject Modal -->
<div id="reject-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Reject Time Request</h3>
            <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="reject-form" method="POST" action="">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="reject_admin_notes" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason <span class="text-red-600">*</span></label>
                    <textarea name="admin_notes" 
                              id="reject_admin_notes" 
                              rows="3"
                              required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Please provide a reason for rejection</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" 
                        onclick="closeRejectModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Reject
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div id="delete-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Delete Time Request</h3>
            <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="space-y-4">
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            Are you sure you want to delete this rejected time request? This action cannot be undone.
                        </p>
                        <div class="mt-2 text-sm text-red-600">
                            <p><strong>Student:</strong> <span id="delete-student-name"></span></p>
                            <p><strong>Date:</strong> <span id="delete-request-date"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form id="delete-form" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" 
                        onclick="closeDeleteModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Delete
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function formatEditTimeInput(input) {
    let digits = input.value.replace(/\D/g, '').slice(0, 4);
    if (digits.length <= 2) {
        input.value = digits;
        return;
    }
    const h = digits.slice(0, 2);
    const m = digits.slice(2);
    input.value = m ? h + ':' + m : h;
}

function formatEditTimeOnBlur(input) {
    let value = input.value.trim();
    if (value === '') {
        return;
    }

    let digits = value.replace(/\D/g, '');
    if (digits.length === 0) {
        input.value = '';
        return;
    }

    let hours = '';
    let minutes = '';

    if (value.includes(':')) {
        const parts = value.split(':');
        hours = parts[0].replace(/\D/g, '').slice(-2);
        minutes = parts[1].replace(/\D/g, '').slice(0, 2);
    } else if (digits.length >= 2) {
        hours = digits.slice(0, digits.length - 2).slice(-2);
        minutes = digits.slice(-2);
    } else {
        hours = digits.padStart(2, '0');
        minutes = '00';
    }

    hours = (hours || '0').padStart(2, '0').slice(-2);
    minutes = (minutes || '0').padStart(2, '0').slice(0, 2);
    input.value = hours + ':' + minutes;
}

document.addEventListener('DOMContentLoaded', function () {
    const editTimeInput = document.getElementById('edit_time');
    if (editTimeInput) {
        editTimeInput.addEventListener('input', function () {
            formatEditTimeInput(this);
        });
        editTimeInput.addEventListener('blur', function () {
            formatEditTimeOnBlur(this);
        });
    }
});

function openEditModal(requestId, studentName, date, time, remarks, requestTypeLabel, isOvertime) {
    const modal = document.getElementById('edit-modal');
    const form = document.getElementById('edit-form');
    form.action = `/admin/time-requests/${requestId}`;
    document.getElementById('edit-student-name').textContent = studentName;
    document.getElementById('edit-request-type').textContent = requestTypeLabel;
    document.getElementById('edit-time-label').textContent = isOvertime
        ? 'Overtime hours (HH:MM)'
        : 'Regular worked hours (HH:MM, max 08:00)';
    document.getElementById('edit_date').value = date;
    document.getElementById('edit_time').value = time;
    document.getElementById('edit_remarks').value = remarks || '';
    modal.classList.remove('hidden');
}

function closeEditModal() {
    const modal = document.getElementById('edit-modal');
    modal.classList.add('hidden');
    document.getElementById('edit-form').reset();
}

function openApproveModal(requestId) {
    const modal = document.getElementById('approve-modal');
    const form = document.getElementById('approve-form');
    form.action = `/admin/time-requests/${requestId}/approve`;
    modal.classList.remove('hidden');
}

function closeApproveModal() {
    const modal = document.getElementById('approve-modal');
    modal.classList.add('hidden');
    document.getElementById('approve-form').reset();
}

function getSelectedPendingCheckboxes() {
    return Array.from(document.querySelectorAll('.pending-request-checkbox:checked'));
}

function updateBulkSelection() {
    const selected = getSelectedPendingCheckboxes();
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    const selectAll = document.getElementById('select-all-pending');
    const allPending = document.querySelectorAll('.pending-request-checkbox');

    if (!bulkActions) {
        return;
    }

    if (selectedCount) {
        selectedCount.textContent = selected.length + ' selected';
    }

    bulkActions.style.display = selected.length > 0 ? 'flex' : 'none';

    if (selectAll && allPending.length > 0) {
        selectAll.checked = selected.length === allPending.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < allPending.length;
    }
}

function toggleSelectAllPending(selectAllCheckbox) {
    document.querySelectorAll('.pending-request-checkbox').forEach(function (checkbox) {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateBulkSelection();
}

function openBulkApproveModal() {
    const selected = getSelectedPendingCheckboxes();
    if (selected.length === 0) {
        alert('Select at least one pending time request.');
        return;
    }

    const idsContainer = document.getElementById('bulk-approve-ids');
    if (!idsContainer) {
        return;
    }

    idsContainer.innerHTML = '';
    selected.forEach(function (checkbox) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'request_ids[]';
        input.value = checkbox.value;
        idsContainer.appendChild(input);
    });

    document.getElementById('bulk-approve-count').textContent = String(selected.length);
    document.getElementById('bulk-approve-modal').classList.remove('hidden');
}

function closeBulkApproveModal() {
    const modal = document.getElementById('bulk-approve-modal');
    if (!modal) {
        return;
    }
    modal.classList.add('hidden');
    document.getElementById('bulk-approve-form').reset();
    document.getElementById('bulk-approve-ids').innerHTML = '';
}

function openRejectModal(requestId) {
    const modal = document.getElementById('reject-modal');
    const form = document.getElementById('reject-form');
    form.action = `/admin/time-requests/${requestId}/reject`;
    modal.classList.remove('hidden');
}

function closeRejectModal() {
    const modal = document.getElementById('reject-modal');
    modal.classList.add('hidden');
    document.getElementById('reject-form').reset();
}

// Delete Modal Functions
function openDeleteModal(requestId, studentName, requestDate) {
    const modal = document.getElementById('delete-modal');
    const form = document.getElementById('delete-form');
    form.action = `/admin/time-requests/${requestId}`;
    document.getElementById('delete-student-name').textContent = studentName;
    document.getElementById('delete-request-date').textContent = requestDate;
    modal.classList.remove('hidden');
}

function closeDeleteModal() {
    const modal = document.getElementById('delete-modal');
    modal.classList.add('hidden');
    document.getElementById('delete-form').reset();
}

// Close modals when clicking outside
document.getElementById('edit-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});

document.getElementById('approve-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeApproveModal();
    }
});

document.getElementById('bulk-approve-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeBulkApproveModal();
    }
});

document.getElementById('reject-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    updateBulkSelection();
});

document.getElementById('delete-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endsection
