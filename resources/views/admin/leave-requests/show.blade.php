@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl px-4 py-6 sm:px-6 sm:py-8 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-3 sm:p-4">
                    <svg class="h-8 w-8 sm:h-10 sm:w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold">Leave Request Details</h1>
                    <p class="text-sm sm:text-base text-indigo-100 mt-1">Review and manage this leave request</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <a href="{{ route('admin.leave-requests.show-pdf', $leaveRequest) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 text-xs sm:text-sm bg-white text-indigo-700 rounded-lg hover:bg-indigo-50 transition duration-200 font-medium">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    View PDF
                </a>
                <a href="{{ $backLink['url'] }}"
                   class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 text-xs sm:text-sm bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white hover:bg-white/20 transition duration-200">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span class="hidden sm:inline">{{ $backLink['label'] }}</span>
                    <span class="sm:hidden">Back</span>
                </a>
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

    @if(session('info'))
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
            <p class="text-sm text-blue-700">{{ session('info') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
            <!-- Request Information -->
            <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4">Request Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">
                            {{ ($teacherExcusedBatchmates ?? collect())->count() > 1 ? 'Student (this record)' : ($leaveRequest->user->role === 'student' ? 'Student' : 'Employee') }}
                        </label>
                        <p class="text-sm font-semibold text-gray-900"><x-user-name :user="$leaveRequest->user" :size="16" /></p>
                        <p class="text-xs text-gray-500">{{ $leaveRequest->user->email }}</p>
                    </div>

                    @if(($teacherExcusedBatchmates ?? collect())->count() > 1)
                        <div class="md:col-span-2 rounded-lg border border-indigo-100 bg-indigo-50/50 px-4 py-3">
                            <label class="block text-sm font-medium text-indigo-900 mb-2">Students included in this request</label>
                            <p class="text-sm text-gray-900 leading-relaxed">
                                @foreach($teacherExcusedBatchmates as $batchMate)
                                    @if($batchMate->user)
                                        <x-user-name :user="$batchMate->user" :size="14" />@if(!$loop->last), @endif
                                    @endif
                                @endforeach
                            </p>
                            <p class="mt-2 text-xs text-indigo-800/80">
                                This is one shared teacher filing. Approve or reject on this page applies only to
                                <strong><x-user-name :user="$leaveRequest->user" :size="14" /></strong>. Use the links below to open each student’s record.
                            </p>
                            <ul class="mt-3 flex flex-wrap gap-2">
                                @foreach($teacherExcusedBatchmates as $mate)
                                    @if($mate->id === $leaveRequest->id)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-600 text-white">
                                            <x-user-name :user="$mate->user" :size="14" /> (viewing)
                                        </span>
                                    @else
                                        <a href="{{ route('admin.leave-requests.show', ['leaveRequest' => $mate->id, 'from' => request('from'), 'return' => request('return')]) }}"
                                           class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-white border border-indigo-200 text-indigo-700 hover:bg-indigo-100">
                                            <x-user-name :user="$mate->user" :size="14" />
                                        </a>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Request Type</label>
                        @php
                            $isStudentLeaveRequest = ($leaveRequest->user?->role === 'student');
                            $isTeacherFiledExcused = $isTeacherFiledExcused ?? $leaveRequest->wasFiledByTeacher();
                            $canEditApprovedStudentAbsentExcused = $isStudentLeaveRequest
                                && ! $isTeacherFiledExcused
                                && $leaveRequest->isApproved()
                                && in_array($leaveRequest->type, ['absent', 'excused'], true);
                            $canEditLeaveRequestType = ($canEditLeaveRequestDetails ?? false)
                                && ! $isTeacherFiledExcused
                                && (! $leaveRequest->isApproved() || $canEditApprovedStudentAbsentExcused);
                        @endphp
                        @if($canEditLeaveRequestType)
                            @php
                                $editableLeaveTypeOptions = $leaveTypeOptions ?? [];
                                if ($canEditApprovedStudentAbsentExcused) {
                                    $editableLeaveTypeOptions = array_values(array_filter(
                                        $editableLeaveTypeOptions,
                                        fn ($option) => in_array($option['value'] ?? null, ['absent', 'excused'], true)
                                    ));
                                }
                            @endphp
                            <form action="{{ url('/admin/leave-requests/' . $leaveRequest->id . '/type') }}" method="POST"
                                  class="flex flex-col sm:flex-row sm:items-end gap-3"
                                  onsubmit="return confirmLeaveTypeChange(this);">
                                @csrf
                                @include('admin.leave-requests.partials.show-nav-fields')
                                @method('PATCH')
                                <div class="flex-1 min-w-0">
                                    <select name="type" id="leave-request-type"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                        @foreach($editableLeaveTypeOptions as $option)
                                            <option value="{{ $option['value'] }}"
                                                {{ $leaveRequest->type === $option['value'] ? 'selected' : '' }}>
                                                {{ $option['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @if($isStudentLeaveRequest)
                                        <p class="mt-1 text-xs text-gray-500">
                                            Excused is admin-only and does not count toward absence merits. You can change Absent ↔ Excused even after approval.
                                        </p>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <label for="type-change-notes" class="sr-only">Note (optional)</label>
                                    <input type="text" name="admin_notes" id="type-change-notes"
                                           value="{{ old('admin_notes') }}"
                                           placeholder="Optional note for activity log"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <button type="submit"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 whitespace-nowrap">
                                    Update Type
                                </button>
                            </form>
                        @else
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $isTeacherFiledExcused ? 'Official Excused' : $leaveRequest->type_label }}
                            </p>
                            @if($isTeacherFiledExcused)
                                <p class="mt-1 text-xs text-gray-500">
                                    Filed by a teacher. Request type cannot be changed.
                                </p>
                            @endif
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Date range</label>
                        @if($leaveRequest->isApproved())
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $leaveRequest->start_date->format('M d, Y') }}
                                @if($leaveRequest->end_date && !$leaveRequest->start_date->isSameDay($leaveRequest->end_date))
                                    – {{ $leaveRequest->end_date->format('M d, Y') }}
                                @endif
                            </p>
                        @elseif($canEditLeaveRequestDetails ?? false)
                            <form action="{{ url('/admin/leave-requests/' . $leaveRequest->id . '/dates') }}" method="POST"
                                  class="flex flex-col sm:flex-row sm:flex-wrap sm:items-end gap-3"
                                  onsubmit="return confirmLeaveDateChange(this);">
                                @csrf
                                @include('admin.leave-requests.partials.show-nav-fields')
                                @method('PATCH')
                                <div class="flex-1 min-w-[10rem]">
                                    <label for="leave-request-start-date" class="sr-only">Start date</label>
                                    <input type="date" name="start_date" id="leave-request-start-date"
                                           value="{{ old('start_date', $leaveRequest->start_date->format('Y-m-d')) }}"
                                           required
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                    @error('start_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex-1 min-w-[10rem]">
                                    <label for="leave-request-end-date" class="sr-only">End date</label>
                                    <input type="date" name="end_date" id="leave-request-end-date"
                                           value="{{ old('end_date', ($leaveRequest->end_date ?? $leaveRequest->start_date)->format('Y-m-d')) }}"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                    @error('end_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex-1 min-w-0 sm:min-w-[12rem]">
                                    <label for="date-change-notes" class="sr-only">Note (optional)</label>
                                    <input type="text" name="admin_notes" id="date-change-notes"
                                           value="{{ old('admin_notes') }}"
                                           placeholder="Optional note for activity log"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <button type="submit"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 whitespace-nowrap">
                                    Update Dates
                                </button>
                            </form>
                            <p class="mt-2 text-xs text-gray-500">Admins may set any date, including past dates. Use the same start and end date for a single day.</p>
                        @else
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $leaveRequest->start_date->format('M d, Y') }}
                                @if($leaveRequest->end_date && !$leaveRequest->start_date->isSameDay($leaveRequest->end_date))
                                    – {{ $leaveRequest->end_date->format('M d, Y') }}
                                @endif
                            </p>
                        @endif
                        @if($dayTotalFiled = $leaveRequest->attendanceDayTotalFiledDisplay())
                            <p class="mt-1.5 text-xs text-gray-500">
                                Day total filed: <span class="font-mono font-medium text-gray-700">{{ $dayTotalFiled }}</span>
                            </p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Duration</label>
                        @if(($canEditLeaveRequestDetails ?? false) && $leaveRequest->type === 'overtime' && in_array($leaveRequest->status, ['pending', 'approved'], true))
                            @php
                                $editableOvertimeHours = old(
                                    'overtime_hours',
                                    $leaveRequest->overtimeHoursFormattedFromReason() ?? '00:00'
                                );
                            @endphp
                            <form action="{{ url('/admin/leave-requests/' . $leaveRequest->id . '/overtime-hours') }}" method="POST"
                                  class="flex flex-col sm:flex-row sm:items-end gap-3"
                                  onsubmit="return confirm('Update overtime hours for this request? Employee overtime balance will reflect the new total.');">
                                @csrf
                                @include('admin.leave-requests.partials.show-nav-fields')
                                @method('PATCH')
                                <div class="flex-1 min-w-[8rem]">
                                    <label for="leave-request-overtime-hours" class="sr-only">Overtime hours</label>
                                    <input type="text" name="overtime_hours" id="leave-request-overtime-hours"
                                           value="{{ $editableOvertimeHours }}"
                                           placeholder="00:00"
                                           required
                                           pattern="^\d{1,4}:\d{2}$"
                                           class="w-full px-3 py-2 text-sm font-mono border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                    @error('overtime_hours')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex-1 min-w-0 sm:min-w-[12rem]">
                                    <label for="overtime-hours-change-notes" class="sr-only">Note (optional)</label>
                                    <input type="text" name="admin_notes" id="overtime-hours-change-notes"
                                           value="{{ old('admin_notes') }}"
                                           placeholder="Optional note for activity log"
                                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <button type="submit"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 whitespace-nowrap">
                                    Update Overtime
                                </button>
                            </form>
                            <p class="mt-2 text-xs text-gray-500">Adjust total overtime hours (HH:MM). This updates the employee overtime balance when approved.</p>
                        @else
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $leaveRequest->duration_display_label }}
                            </p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $leaveRequest->status_badge_class }}">
                                {{ $leaveRequest->display_status }}
                            </span>
                            @if($leaveRequest->admin_officially_excused)
                                <span class="px-2.5 py-1 inline-flex items-center gap-1 text-xs font-semibold rounded-full bg-teal-100 text-teal-800">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                    Officially Excused
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Submitted On</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $leaveRequest->created_at->format('F d, Y g:i A') }}</p>
                    </div>

                    @if($leaveRequest->reviewed_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Reviewed On</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $leaveRequest->reviewed_at->format('F d, Y g:i A') }}</p>
                        </div>

                        @if($leaveRequest->reviewer)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Reviewed By</label>
                                <p class="text-sm font-semibold text-gray-900"><x-user-name :user="$leaveRequest->reviewer" :size="16" /></p>
                            </div>
                        @endif
                    @endif
                </div>

                @if($leaveRequest->type === 'additional_time')
                    @php
                        $raw = $leaveRequest->reason ?? '';
                        $additionalInputMode = 'Fixed Date (1 day = 8 hours)';
                        $additionalHours = '-';

                        if (preg_match('/Additional Time Input Mode:\s*(.+)/', $raw, $m)) {
                            $additionalInputMode = trim($m[1]);
                        }
                        if (preg_match('/Additional Time Hours:\s*(.+)/', $raw, $m)) {
                            $additionalHours = trim($m[1]);
                        }
                    @endphp

                    <div class="mt-4 sm:mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Additional Time Input Mode</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $additionalInputMode }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Additional Time Hours</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $additionalHours }}</p>
                        </div>
                    </div>
                @endif

                {{-- Structured details for special types so admin can see the same form info as the employee --}}
                @if($leaveRequest->type === 'overtime')
                    @php
                        $raw = $leaveRequest->reason ?? '';
                        $otHours = '';
                        $otDates = $leaveRequest->overtimeDisplayDatesForLetter();
                        $otTasks = '';
                        $otReason = '';
                        $otWorkType = $leaveRequest->overtimeWorkTypeLabelFromReason();
                        $otTravelLocation = $leaveRequest->travelTimeLocationSummaryFromReason();

                        if (preg_match('/Total Overtime Hours:\s*(.+)/', $raw, $m)) {
                            $otHours = trim($m[1]);
                        }
                        if (preg_match('/Tasks \/ ClickUp Links:\s*(.+?)(?:\n+Additional Explanation:|\z)/s', $raw, $m)) {
                            $otTasks = trim($m[1]);
                        }
                        if (preg_match('/Additional Explanation:\s*(.+)\z/s', $raw, $m)) {
                            $otReason = trim($m[1]);
                        }

                        $otTasksEscaped = e($otTasks);
                        $otTasksWithLinks = preg_replace(
                            '~(https?://[^\s]+)~',
                            '<a href="$1" target="_blank" rel="noopener" class="text-indigo-600 underline break-words">$1</a>',
                            $otTasksEscaped
                        );
                    @endphp

                    <div class="mt-4 sm:mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        @if($otWorkType)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Overtime Type</label>
                                <p class="text-sm font-semibold text-gray-900">{{ $otWorkType }}</p>
                            </div>
                        @endif
                        @if($otTravelLocation)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Travel Location</label>
                                <p class="text-sm font-semibold text-gray-900">{{ $otTravelLocation }}</p>
                            </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Total Overtime Hours</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $otHours }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Overtime Dates</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $otDates }}</p>
                        </div>
                        @if($otWorkType !== 'Travel Time')
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tasks / ClickUp Links</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200 whitespace-pre-line">
                                {!! nl2br($otTasksWithLinks) !!}
                            </p>
                        </div>
                        @endif
                        @if($otReason)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Additional Explanation</label>
                                <p class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200 whitespace-pre-line">
                                    {{ $otReason }}
                                </p>
                            </div>
                        @endif
                    </div>

                @elseif($leaveRequest->type === 'work_from_home')
                    @php
                        $raw = $leaveRequest->reason ?? '';
                        $mode = '';
                        $remoteAddress = '';
                        $workDates = '';
                        $tasks = '';
                        $reasonAbsence = '';

                        if (preg_match('/Mode:\s*(.+)/', $raw, $m)) {
                            $mode = trim($m[1]);
                        }
                        if (preg_match('/Remote Address:\s*(.+)/', $raw, $m)) {
                            $remoteAddress = trim($m[1]);
                        }
                        if (preg_match('/Work Dates:\s*(.+)/', $raw, $m)) {
                            $workDates = trim($m[1]);
                        }
                        if (preg_match('/Tasks \/ ClickUp Links:\s*(.+?)(?:\n+Additional Explanation:|\z)/s', $raw, $m)) {
                            $tasks = trim($m[1]);
                        }
                        if (preg_match('/Additional Explanation:\s*(.+)\z/s', $raw, $m)) {
                            $reasonAbsence = trim($m[1]);
                        }

                        $tasksEscaped = e($tasks);
                        $tasksWithLinks = preg_replace(
                            '~(https?://[^\s]+)~',
                            '<a href="$1" target="_blank" rel="noopener" class="text-indigo-600 underline break-words">$1</a>',
                            $tasksEscaped
                        );
                    @endphp

                    <div class="mt-4 sm:mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Work Mode</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $mode }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Remote Address</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $remoteAddress }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Work Dates</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $workDates }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tasks / ClickUp Links</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200 whitespace-pre-line">
                                {!! nl2br($tasksWithLinks) !!}
                            </p>
                        </div>
                        @if($reasonAbsence)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Reasons for Absence</label>
                                <p class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200 whitespace-pre-line">
                                    {{ $reasonAbsence }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endif

                @if(!empty($leaveRequest->all_supporting_document_paths))
                    <div class="mt-4 sm:mt-6 p-4 bg-indigo-50 border border-indigo-100 rounded-lg">
                        <div class="flex items-start space-x-3">
                            <svg class="h-5 w-5 text-indigo-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Supporting Document(s)</p>
                                <div class="mt-1 space-y-1">
                                    @foreach($leaveRequest->all_supporting_document_paths as $index => $docPath)
                                        @php
                                            $docUrl = null;
                                            try {
                                                $docUrl = \Illuminate\Support\Facades\Storage::disk('digitalocean')
                                                    ->temporaryUrl(
                                                        $docPath,
                                                        now()->addMinutes(30),
                                                        ['ResponseContentDisposition' => 'inline']
                                                    );
                                            } catch (\Throwable $e) {
                                                try {
                                                    $docUrl = \Illuminate\Support\Facades\Storage::url($docPath);
                                                } catch (\Throwable $e) {
                                                    $docUrl = null;
                                                }
                                            }
                                        @endphp
                                        @if($docUrl)
                                            <a href="{{ $docUrl }}" target="_blank" rel="noopener"
                                               class="block text-sm text-indigo-700 underline break-words">View / Download file {{ $index + 1 }}</a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($leaveRequest->reason && !in_array($leaveRequest->type, ['vacation_leave', 'sick_leave', 'work_from_home', 'overtime']))
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Reason (Raw)</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200 whitespace-pre-line">
                            {{ $leaveRequest->reason }}
                        </p>
                    </div>
                @endif

                <!-- Vacation / Sick Leave / Offset Letter-style View (matches employee template) -->
                @if(in_array($leaveRequest->type, ['vacation_leave', 'sick_leave', 'offset']))
                    @php
                        $effectiveDate = $leaveRequest->created_at->format('F d, Y');
                        $startDate = $leaveRequest->start_date->format('F d, Y');
                        $endDate = ($leaveRequest->end_date ?? $leaveRequest->start_date)->format('F d, Y');
                        $lengthText = $leaveRequest->duration_display_label;
                        $reasonText = $leaveRequest->reason ?: '_______________________________________________';
                        $employee = $leaveRequest->user;
                    @endphp

                    <div class="mt-4 sm:mt-6 border border-gray-300 rounded-lg p-4 sm:p-6 space-y-3 sm:space-y-4 bg-white">
                        <!-- Effective Date -->
                        <p class="text-xs font-semibold tracking-wide text-gray-700 uppercase">
                            {{ $effectiveDate }}
                        </p>

                        <!-- Greeting -->
                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $letterAddressee ?? 'Dear HR Admin,' }}</p>
                        </div>

                        <!-- Body -->
                        <div class="space-y-3 text-sm text-gray-800">
                            <p>
                                Please accept this letter as formal request for a leave of absence. My leave is due to
                                <span class="underline decoration-gray-400 decoration-1">{{ $reasonText }}</span>
                                . I am requesting a leave of
                                <span class="underline decoration-gray-400 decoration-1">{{ $lengthText }}</span>.
                                The leave will last from
                                <span class="underline decoration-gray-400 decoration-1">{{ $startDate }}</span>
                                until
                                <span class="underline decoration-gray-400 decoration-1">{{ $endDate }}</span>.
                            </p>

                            <p>
                                If my leave of absence is approved, I’ll try my best to assist with any questions by phone call
                                or chat provided that I have the means or I can connect with the internet.
                            </p>

                            <p>
                                Please let me know if you have any questions and an appropriate time for us to speak to
                                discuss the terms of my leave of absence.
                            </p>
                        </div>

                        <!-- Closing and Signature Block -->
                        <div class="space-y-1 text-sm text-gray-800 pt-4">
                            <p>Thank you for understanding.</p>
                            <p class="mt-4">Best regards,</p>
                            <p>Truly yours,</p>
                        </div>

                        <div class="mt-4 space-y-2 text-sm text-gray-900">
                            <div class="space-y-0.5">
                                <p class="font-semibold">{{ $employee->name ?? '[YOUR NAME]' }}</p>
                                <p class="text-gray-700 uppercase text-xs tracking-wide">{{ strtoupper($employee->role ?? 'Employee') }}</p>
                            </div>

                            @php
                                $remarksText = strtoupper($leaveRequest->status === 'approved'
                                    ? 'Approved'
                                    : ($leaveRequest->status === 'rejected' ? 'Disapproved' : 'Pending'));
                            @endphp
                            @php
                                $remarksText = strtoupper($leaveRequest->status === 'approved'
                                    ? 'Approved'
                                    : ($leaveRequest->status === 'rejected' ? 'Disapproved' : 'Pending'));
                            @endphp

                            <div class="mt-6 space-y-1 text-sm text-gray-900">
                                <p>Noted:</p>
                                <div class="mt-2">
                                    <p class="font-semibold underline">{{ $signatories['immediate_supervisor'] ?? '—' }}</p>
                                    <p class="text-gray-700 text-xs tracking-wide">IMMEDIATE SUPERVISOR</p>
                                </div>
                                <div class="mt-3">
                                    <p class="font-semibold underline">{{ $signatories['hr_admin'] ?? '—' }}</p>
                                    <p class="text-gray-700 text-xs tracking-wide">HR ADMIN</p>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-gray-700">Approved:</p>
                                        <p class="font-semibold underline">{{ $signatories['cto'] ?? '—' }}</p>
                                        <p class="text-gray-700 text-xs tracking-wide">CHIEF TECHNOLOGY OFFICER</p>
                                    </div>
                                    <div class="text-xs font-semibold tracking-wide text-gray-900">
                                        REMARKS: {{ $remarksText }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($leaveRequest->type === 'work_from_home')
                    @php
                        $effectiveDate = $leaveRequest->created_at->format('F d, Y');
                        $employee = $leaveRequest->user;
                        $raw = $leaveRequest->reason ?? '';
                        $mode = '';
                        $remoteAddress = '';
                        $workDates = '';
                        $reasonAbsence = '';
                        $tasks = '';

                        if (preg_match('/Mode:\s*(.+)/', $raw, $m)) {
                            $mode = trim($m[1]);
                        }
                        if (preg_match('/Remote Address:\s*(.+)/', $raw, $m)) {
                            $remoteAddress = trim($m[1]);
                        }
                        if (preg_match('/Work Dates:\s*(.+)/', $raw, $m)) {
                            $workDates = trim($m[1]);
                        }
                        if (preg_match('/Additional Explanation:\s*(.+)\z/s', $raw, $m)) {
                            $reasonAbsence = trim($m[1]);
                        }
                        if (preg_match('/Tasks \/ ClickUp Links:\s*(.+?)(?:\n+Additional Explanation:|\z)/s', $raw, $m)) {
                            $tasks = trim($m[1]);
                        }

                        $tasksEscaped = e($tasks);
                        $tasksWithLinks = preg_replace(
                            '~(https?://[^\s]+)~',
                            '<a href="$1" target="_blank" rel="noopener" class="text-indigo-600 underline break-words">$1</a>',
                            $tasksEscaped
                        );
                    @endphp

                    <div class="mt-4 sm:mt-6 border border-gray-300 rounded-lg p-4 sm:p-6 space-y-3 sm:space-y-4 bg-white">
                        <!-- Effective Date -->
                        <p class="text-xs font-semibold tracking-wide text-gray-700 uppercase">
                            {{ $effectiveDate }}
                        </p>

                        <!-- Greeting -->
                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $letterAddressee ?? 'Dear HR Admin,' }}</p>
                        </div>

                        <!-- Body -->
                        <div class="space-y-3 text-sm text-gray-800">
                            <p>
                                Please accept this letter as official notice that I will be
                                <span class="font-semibold">
                                    [{{ $mode ?: 'working remotely or request to be excused' }}]
                                </span>
                                at
                                <span class="font-semibold">
                                    [{{ $remoteAddress ?: 'remote address' }}]
                                </span>
                                and was or will be unable to report to work on or from
                                <span class="font-semibold">
                                    [{{ $leaveRequest->start_date->format('F d, Y') }}]
                                </span>
                                to
                                <span class="font-semibold">
                                    [{{ ($leaveRequest->end_date ?? $leaveRequest->start_date)->format('F d, Y') }}]
                                </span>
                                due to
                                <span class="font-semibold">
                                    [{{ $reasonAbsence ?: 'reasons for absence' }}]
                                </span>.
                            </p>

                            <p class="font-semibold">
                                [Strictly List down Task Listed in ClickUp for Devs via link]
                            </p>
                            <p class="min-h-[3rem] border-t border-gray-300 pt-2 text-gray-800 whitespace-pre-line">
                                {!! nl2br($tasksWithLinks) !!}
                            </p>

                            <p>Thank you for understanding.</p>
                            <p class="mt-4">Best regards,</p>
                            <p>Truly yours,</p>
                        </div>

                        <!-- Signature and Noted/Approved Block -->
                        <div class="mt-4 space-y-2 text-sm text-gray-900">
                            <div class="space-y-0.5">
                                <p class="font-semibold">{{ $employee->name ?? '[YOUR NAME]' }}</p>
                                <p class="text-gray-700 uppercase text-xs tracking-wide">{{ strtoupper($employee->role ?? 'Employee') }}</p>
                            </div>

                            @php
                                $remarksText = strtoupper($leaveRequest->status === 'approved'
                                    ? 'Approved'
                                    : ($leaveRequest->status === 'rejected' ? 'Disapproved' : 'Pending'));
                            @endphp
                            <div class="mt-6 space-y-1 text-sm text-gray-900">
                                <p>Noted:</p>
                                <div class="mt-2">
                                    <p class="font-semibold underline">{{ $signatories['immediate_supervisor'] ?? '—' }}</p>
                                    <p class="text-gray-700 text-xs tracking-wide">IMMEDIATE SUPERVISOR</p>
                                </div>
                                <div class="mt-3">
                                    <p class="font-semibold underline">{{ $signatories['hr_admin'] ?? '—' }}</p>
                                    <p class="text-gray-700 text-xs tracking-wide">HR ADMIN</p>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-gray-700">Approved:</p>
                                        <p class="font-semibold underline">{{ $signatories['cto'] ?? '—' }}</p>
                                        <p class="text-gray-700 text-xs tracking-wide">CHIEF TECHNOLOGY OFFICER</p>
                                    </div>
                                    <div class="text-xs font-semibold tracking-wide text-gray-900">
                                        REMARKS: {{ $remarksText }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($leaveRequest->type === 'overtime')
                    @php
                        $effectiveDate = $leaveRequest->created_at->format('F d, Y');
                        $employee = $leaveRequest->user;
                        $raw = $leaveRequest->reason ?? '';
                        $otHours = '';
                        $otDates = $leaveRequest->overtimeDisplayDatesForLetter();
                        $otReason = '';
                        $otTasks = '';
                        $otWorkType = $leaveRequest->overtimeWorkTypeLabelFromReason();
                        $otTravelLocation = $leaveRequest->travelTimeLocationSummaryFromReason();

                        if (preg_match('/Total Overtime Hours:\s*(.+)/', $raw, $m)) {
                            $otHours = trim($m[1]);
                        }
                        if (preg_match('/Tasks \/ ClickUp Links:\s*(.+?)(?:\n+Additional Explanation:|\z)/s', $raw, $m)) {
                            $otTasks = trim($m[1]);
                        }
                        if (preg_match('/Additional Explanation:\s*(.+)\z/s', $raw, $m)) {
                            $otReason = trim($m[1]);
                        }
                    @endphp

                    <div class="mt-4 sm:mt-6 border border-gray-300 rounded-lg p-4 sm:p-6 space-y-3 sm:space-y-4 bg-white">
                        <!-- Effective Date -->
                        <p class="text-xs font-semibold tracking-wide text-gray-700 uppercase">
                            {{ $effectiveDate }}
                        </p>

                        <!-- Greeting -->
                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $letterAddressee ?? 'Dear HR Admin,' }}</p>
                        </div>

                        <!-- Body -->
                        <div class="space-y-3 text-sm text-gray-800">
                            @if($otWorkType)
                                <p>
                                    Overtime type:
                                    <span class="font-semibold underline decoration-gray-400 decoration-1">{{ $otWorkType }}</span>
                                </p>
                            @endif
                            @if($otTravelLocation)
                                <p>
                                    Travel location:
                                    <span class="font-semibold underline decoration-gray-400 decoration-1">{{ $otTravelLocation }}</span>
                                </p>
                            @endif
                            <p>
                                I respectfully request your approval for an additional
                                <span class="font-semibold underline decoration-gray-400 decoration-1">
                                    {{ $otHours ?: '[hours, HH:MM]' }}
                                </span>
                                of overtime worked on
                                <span class="font-semibold underline decoration-gray-400 decoration-1">
                                    {{ $otDates ?: '[date(s)]' }}
                                </span>@if(!empty($otReason)),
                                due to
                                <span class="font-semibold underline decoration-gray-400 decoration-1">
                                    {{ $otReason }}
                                </span>@else.@endif
                            </p>

                            @if(empty($otReason) && $otWorkType !== 'Travel Time')
                            <p class="text-xs text-gray-600">
                                Examples of valid reasons: urgent project deadline, increased workload, critical system maintenance.
                            </p>
                            @endif

                            @if($otWorkType !== 'Travel Time')
                            <p class="font-semibold">
                                Tasks completed (ClickUp links)
                            </p>
                            @php
                                // Make any URLs inside the task list clickable while preserving line breaks
                                $otTasksEscaped = e($otTasks);
                                $otTasksWithLinks = preg_replace(
                                    '~(https?://[^\s]+)~',
                                    '<a href="$1" target="_blank" rel="noopener" class="text-indigo-600 underline break-words">$1</a>',
                                    $otTasksEscaped
                                );
                            @endphp
                            <p class="min-h-[3rem] border-t border-gray-300 pt-2 text-gray-800 whitespace-pre-line">
                                {!! nl2br($otTasksWithLinks) !!}
                            </p>
                            @endif

                            <p>Thank you for understanding.</p>
                            <p class="mt-4">Best regards,</p>
                            <p>Truly yours,</p>
                        </div>

                        <!-- Signature and Noted/Approved Block -->
                        <div class="mt-4 space-y-2 text-sm text-gray-900">
                            <div class="space-y-0.5">
                                <p class="font-semibold">{{ $employee->name ?? '[YOUR NAME]' }}</p>
                                <p class="text-gray-700 uppercase text-xs tracking-wide">{{ strtoupper($employee->role ?? 'Employee') }}</p>
                            </div>

                            @php
                                $remarksText = strtoupper($leaveRequest->status === 'approved'
                                    ? 'Approved'
                                    : ($leaveRequest->status === 'rejected' ? 'Disapproved' : 'Pending'));
                            @endphp
                            <div class="mt-6 space-y-1 text-sm text-gray-900">
                                <p>Noted:</p>
                                <div class="mt-2">
                                    <p class="font-semibold underline">{{ $signatories['immediate_supervisor'] ?? '—' }}</p>
                                    <p class="text-gray-700 text-xs tracking-wide">IMMEDIATE SUPERVISOR</p>
                                </div>
                                <div class="mt-3">
                                    <p class="font-semibold underline">{{ $signatories['hr_admin'] ?? '—' }}</p>
                                    <p class="text-gray-700 text-xs tracking-wide">HR ADMIN</p>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-gray-700">Approved:</p>
                                        <p class="font-semibold underline">{{ $signatories['cto'] ?? '—' }}</p>
                                        <p class="text-gray-700 text-xs tracking-wide">CHIEF TECHNOLOGY OFFICER</p>
                                    </div>
                                    <div class="text-xs font-semibold tracking-wide text-gray-900">
                                        REMARKS: {{ $remarksText }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Admin Notes -->
            @if($leaveRequest->admin_notes)
                <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4">Admin Notes</h2>
                    <p class="text-xs sm:text-sm text-gray-900 bg-blue-50 p-3 sm:p-4 rounded-lg border border-blue-200 whitespace-pre-line">
                        {{ $leaveRequest->admin_notes }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Side Panel: Balances / Student Time + Actions -->
        <div class="space-y-4 sm:space-y-6">
            @if(isset($studentTime) && $leaveRequest->user->role === 'student')
                <!-- Student DTR Time Summary -->
                @php
                    $meritSummary = $studentTime['merits'] ?? null;
                    $meritStatusKey = $meritSummary['status']['key'] ?? 'clear';
                    $hasMeritViolation = is_array($meritSummary) && (
                        (int) ($meritSummary['total'] ?? 0) > 0
                        || ! empty($meritSummary['notices']['rules_warning'])
                        || ! empty($meritSummary['notices']['final_notice'])
                        || ! empty($meritSummary['notices']['student_terminated'])
                    );
                    $meritPanelClass = $hasMeritViolation
                        ? 'border-red-500 bg-red-50 merit-violation-glow'
                        : 'border-gray-100';
                    $meritTotalClass = $hasMeritViolation ? 'text-red-700' : 'text-gray-900';
                @endphp
                <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6 space-y-3 sm:space-y-4">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-2">Student Time Summary</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="border border-gray-100 rounded-lg px-3 py-2">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Time from DTR</p>
                            <p class="text-sm text-gray-900">
                                <span class="font-bold">{{ $studentTime['total_dtr_hours_formatted'] }}</span> hours
                            </p>
                            <p class="text-xs text-gray-500">Sum of all recorded DTR hours</p>
                        </div>
                        <div class="border border-gray-100 rounded-lg px-3 py-2">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Remaining Time Needed</p>
                            @if($studentTime['required_hours'] > 0 && $studentTime['remaining_hours_formatted'])
                                @php $hasRemaining = $studentTime['remaining_hours'] > 0; @endphp
                                <p class="text-sm">
                                    <span class="font-bold {{ $hasRemaining ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $studentTime['remaining_hours_formatted'] }}
                                    </span> hours
                                </p>
                                <p class="text-xs mt-0.5 {{ $hasRemaining ? 'text-red-500' : 'text-green-500' }}">
                                    {{ $hasRemaining ? 'Student still needs to complete this time.' : 'Student has met or exceeded the required time.' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Time Needed: <span class="font-semibold">{{ $studentTime['required_hours_formatted'] }}</span> hours
                                </p>
                            @else
                                <p class="text-sm text-gray-500">
                                    Not set
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Required time has not been configured yet for this student.
                                </p>
                            @endif
                        </div>
                    </div>

                    @if(is_array($meritSummary))
                        @if($hasMeritViolation)
                            <style>
                                @keyframes merit-violation-pulse {
                                    0%, 100% {
                                        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.55), 0 0 18px 2px rgba(239, 68, 68, 0.35);
                                    }
                                    50% {
                                        box-shadow: 0 0 0 8px rgba(239, 68, 68, 0), 0 0 28px 6px rgba(220, 38, 38, 0.55);
                                    }
                                }
                                .merit-violation-glow {
                                    animation: merit-violation-pulse 1.6s ease-in-out infinite;
                                }
                            </style>
                        @endif
                        <div class="border-2 rounded-lg px-3 py-3 space-y-3 {{ $meritPanelClass }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold {{ $hasMeritViolation ? 'text-red-700' : 'text-gray-500' }} uppercase tracking-wide">Merits / Violations</p>
                                    <p class="text-sm font-semibold {{ $meritTotalClass }} mt-0.5">
                                        <span class="tabular-nums text-lg">{{ number_format((int) ($meritSummary['total'] ?? 0)) }}</span>
                                        total merit{{ (int) ($meritSummary['total'] ?? 0) === 1 ? '' : 's' }}
                                    </p>
                                    <p class="text-xs {{ $hasMeritViolation ? 'text-red-800/80' : 'text-gray-600' }} mt-1">
                                        Under-time {{ (int) ($meritSummary['breakdown']['undertime'] ?? 0) }}
                                        + excess absence {{ (int) ($meritSummary['breakdown']['excess_absence'] ?? 0) }}
                                        + manual {{ (int) ($meritSummary['breakdown']['manual'] ?? 0) }}
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold
                                        {{ $hasMeritViolation ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $meritSummary['status']['label'] ?? 'No merits' }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="rounded-md bg-white/70 border border-gray-100 px-2.5 py-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Absence balance</p>
                                    <p class="text-sm text-gray-900 mt-0.5">
                                        <span class="font-bold tabular-nums">{{ number_format((float) ($meritSummary['absence']['approved_days'] ?? 0), 0) }}</span>
                                        / {{ number_format((float) ($meritSummary['absence']['allowable'] ?? 0), 0) }}
                                        approved absent day(s)
                                    </p>
                                    <p class="text-xs mt-0.5 {{ ((float) ($meritSummary['absence']['remaining_balance'] ?? 0)) <= 0 ? 'text-red-600' : 'text-gray-500' }}">
                                        Remaining allowable:
                                        <span class="font-semibold tabular-nums">{{ number_format((float) ($meritSummary['absence']['remaining_balance'] ?? 0), 0) }}</span>
                                        @if((int) ($meritSummary['absence']['excess_merits'] ?? 0) > 0)
                                            · <span class="text-red-700 font-semibold">{{ (int) $meritSummary['absence']['excess_merits'] }} excess merit(s)</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="rounded-md bg-white/70 border border-gray-100 px-2.5 py-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Under-time filings</p>
                                    <p class="text-sm text-gray-900 mt-0.5">
                                        <span class="font-bold tabular-nums">{{ (int) ($meritSummary['undertime']['filing_count'] ?? 0) }}</span>
                                        filing(s) below 08:00
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ (int) ($meritSummary['undertime']['merits'] ?? 0) }} undertime merit(s)
                                        · {{ (int) ($meritSummary['undertime']['filings_until_next_merit'] ?? 0) }} more until next
                                        (every {{ (int) ($meritSummary['undertime']['filings_per_merit'] ?? 5) }})
                                    </p>
                                </div>
                            </div>

                            <p class="text-xs text-gray-600">
                                {{ $meritSummary['status']['hint'] ?? '' }}
                                Thresholds: warning at {{ (int) ($meritSummary['thresholds']['warning'] ?? 1) }},
                                final at {{ (int) ($meritSummary['thresholds']['final'] ?? 3) }}.
                            </p>

                            @if(!empty($meritSummary['notices']['rules_warning']) || !empty($meritSummary['notices']['final_notice']) || !empty($meritSummary['notices']['student_terminated']))
                                <div class="flex flex-wrap gap-1.5">
                                    @if(!empty($meritSummary['notices']['rules_warning']))
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800">Rules warning active</span>
                                    @endif
                                    @if(!empty($meritSummary['notices']['final_notice']))
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold bg-red-100 text-red-800">Final notice active</span>
                                    @endif
                                    @if(!empty($meritSummary['notices']['student_terminated']))
                                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-900 text-white">Terminated</span>
                                    @endif
                                </div>
                            @endif

                            @if(!empty($meritSummary['projection']))
                                @php $proj = $meritSummary['projection']; @endphp
                                <div class="rounded-md border border-indigo-200 bg-indigo-50/70 px-2.5 py-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-700">If this Absent request is approved</p>
                                    <p class="text-xs text-indigo-950 mt-1">
                                        Adds <span class="font-semibold tabular-nums">{{ (int) $proj['request_days'] }}</span> absent day(s)
                                        → <span class="font-semibold tabular-nums">{{ (int) $proj['approved_days_after'] }}</span> total
                                        (remaining balance <span class="font-semibold tabular-nums">{{ number_format((float) $proj['remaining_balance_after'], 0) }}</span>).
                                    </p>
                                    @if((int) $proj['added_excess_merits'] > 0)
                                        <p class="text-xs text-red-700 mt-1 font-medium">
                                            Would add {{ (int) $proj['added_excess_merits'] }} excess absence merit(s)
                                            → {{ (int) $proj['total_merits_after'] }} total merit(s).
                                            @if(!empty($proj['hits_final']))
                                                Hits final notice threshold.
                                            @elseif(!empty($proj['hits_warning']))
                                                Hits rules violation warning threshold.
                                            @endif
                                        </p>
                                    @elseif(!empty($proj['uses_remaining_balance']))
                                        <p class="text-xs text-emerald-700 mt-1 font-medium">
                                            Still within allowable absence balance — no new excess absence merit from this request.
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-600 mt-1">
                                            No additional excess absence merit projected from this request.
                                        </p>
                                    @endif
                                    <p class="text-[11px] text-indigo-700/80 mt-1">
                                        Use <strong>Accept as Officially Excused</strong> if a school letter should exclude this from demerits.
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @elseif(isset($balances))
                <!-- Employee Balances & Overtime -->
                @php $approveImpact = $balances['approve_impact'] ?? null; @endphp
                <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6 space-y-3 sm:space-y-4">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-2">Employee Balances ({{ now()->year }})</h3>
                    <div class="grid grid-cols-1 gap-3">
                        <div class="border border-gray-100 rounded-lg px-3 py-2 {{ (($approveImpact['kind'] ?? null) === 'leave_negative') ? 'border-red-300 bg-red-50/50' : '' }}">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Leave Credits</p>
                            <p class="text-sm text-gray-900">
                                Remaining:
                                <span class="font-bold">{{ $balances['leave']['remaining'] ?? 0 }}</span>
                                / {{ $balances['leave']['allowance'] ?? 0 }} days
                            </p>
                            <p class="text-xs text-gray-500">Used: {{ $balances['leave']['used'] ?? 0 }} days</p>
                        </div>
                        <div class="border border-gray-100 rounded-lg px-3 py-2 {{ str_starts_with($overtimeFormatted ?? '00:00', '-') ? 'bg-red-50/40 border-red-200' : '' }}">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Overtime (This Year)</p>
                            <p class="text-sm text-gray-900">
                                <span class="font-bold {{ str_starts_with($overtimeFormatted ?? '00:00', '-') ? 'text-red-600' : 'text-gray-900' }}">{{ $overtimeFormatted ?? '00:00' }}</span> hours
                                @if(str_starts_with($overtimeFormatted ?? '00:00', '-'))
                                    <span class="text-xs text-red-500 ml-2">(Negative Balance)</span>
                                @endif
                            </p>
                        </div>
                        @if(isset($balances['work_from_home']))
                        <div class="border border-gray-100 rounded-lg px-3 py-2 {{ in_array(($approveImpact['kind'] ?? null), ['wfh_negative', 'wfh_carryover', 'wfh_carryover_create'], true) || ($balances['work_from_home']['remaining'] ?? 0) <= 0 || ($balances['work_from_home']['carryover_debt'] ?? 0) > 0 ? 'bg-amber-50/50 border-amber-200' : '' }}">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                Work From Home ({{ $balances['work_from_home']['month_label'] ?? 'This month' }})
                            </p>
                            <p class="text-sm text-gray-900">
                                Remaining:
                                <span class="font-bold">{{ number_format($balances['work_from_home']['remaining'] ?? 0, 0) }}</span>
                                / {{ $balances['work_from_home']['allowance'] ?? 2 }} days
                            </p>
                            @if(($balances['work_from_home']['carryover_debt'] ?? 0) > 0)
                                <p class="text-xs text-amber-700 mt-1">
                                    Includes {{ number_format($balances['work_from_home']['carryover_debt'], 0) }} day(s) deducted from admin-filed WFH in the previous month.
                                </p>
                            @endif
                        </div>
                        @endif
                    </div>

                    @if(is_array($approveImpact))
                        <div class="rounded-md border border-red-300 bg-red-50 px-3 py-2.5">
                            <p class="text-xs font-semibold uppercase tracking-wide text-red-700">
                                @if(($approveImpact['kind'] ?? '') === 'leave_negative')
                                    Negative leave balance warning
                                @elseif(str_starts_with((string) ($approveImpact['kind'] ?? ''), 'wfh_carryover'))
                                    WFH carryover warning
                                @else
                                    WFH balance warning
                                @endif
                            </p>
                            <p class="text-xs text-red-900 mt-1">
                                @if(($approveImpact['kind'] ?? '') === 'leave_negative')
                                    Approving this request needs
                                    <span class="font-semibold tabular-nums">{{ number_format((float) $approveImpact['request_days'], 2) }}</span>
                                    day(s) but remaining {{ $approveImpact['label'] }} is only
                                    <span class="font-semibold tabular-nums">{{ number_format((float) $approveImpact['remaining_before'], 2) }}</span>.
                                    Balance after approval:
                                    <span class="font-semibold tabular-nums text-red-700">{{ number_format((float) $approveImpact['remaining_after'], 2) }}</span>
                                    (short by {{ number_format((float) $approveImpact['shortfall'], 2) }}).
                                @elseif(($approveImpact['kind'] ?? '') === 'wfh_carryover_create')
                                    Approving this admin-filed WFH will exceed
                                    {{ $approveImpact['month_label'] ?? 'this month' }} and create about
                                    <span class="font-semibold tabular-nums">{{ number_format((float) ($approveImpact['carryover_next'] ?? 0), 0) }}</span>
                                    day(s) of carryover debt for next month.
                                @elseif(($approveImpact['kind'] ?? '') === 'wfh_carryover')
                                    This month already has
                                    <span class="font-semibold tabular-nums">{{ number_format((float) $approveImpact['carryover_debt'], 0) }}</span>
                                    day(s) of carryover debt reducing available WFH balance.
                                @else
                                    Approving this WFH needs
                                    <span class="font-semibold tabular-nums">{{ number_format((float) $approveImpact['request_days'], 0) }}</span>
                                    day(s) but only
                                    <span class="font-semibold tabular-nums">{{ number_format((float) $approveImpact['remaining_before'], 0) }}</span>
                                    remain for {{ $approveImpact['month_label'] ?? 'this month' }}.
                                    @if(!empty($approveImpact['blocked']))
                                        Employee-filed WFH over quota cannot be approved; reject or refile as admin if carryover is intended.
                                    @endif
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            @if($leaveRequest->needsAttendanceOvertimeCompletion())
                <div class="rounded-lg border border-orange-300 bg-orange-50 px-4 py-4 text-sm text-orange-950 mb-4">
                    <p class="font-semibold">Student must complete overtime details first</p>
                    <p class="mt-1">This request was created from Record Attendance. The student still needs to submit a reason before you can approve.</p>
                </div>
            @endif

            <!-- Action Panel -->
            @if($leaveRequest->isPending() || $leaveRequest->status === 'for_more_verification')
                <!-- Approve Form -->
                @if($leaveRequest->type === 'offset' && isset($hasNegativeBalance) && $hasNegativeBalance)
                    <!-- Force Approve Form (for offset when duration exceeds overtime balance) -->
                    <div class="bg-white rounded-lg shadow border border-orange-200 p-4 sm:p-6">
                        <div class="mb-3 sm:mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-orange-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <div class="text-xs sm:text-sm text-orange-800">
                                    <strong>Warning:</strong> This offset ({{ $leaveRequest->duration_display_label }}; {{ number_format($leaveRequest->offset_hours_needed, 2) }} h charged against overtime) exceeds the employee's current overtime balance. Approving this request will result in a negative overtime balance.
                                </div>
                            </div>
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Force Approve Request</h3>
                        <form action="{{ url('/admin/leave-requests/' . $leaveRequest->id . '/force-accept') }}" method="POST" class="space-y-3 sm:space-y-4">
                            @csrf
                            @include('admin.leave-requests.partials.show-nav-fields')
                            <div>
                                <label for="force_accept_notes" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                <textarea name="admin_notes" id="force_accept_notes" rows="3"
                                          placeholder="Add any notes about this force approval..."
                                          class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"></textarea>
                            </div>
                            <button type="submit" id="force-approve-btn"
                                    class="w-full px-4 py-2 text-sm sm:text-base bg-orange-600 text-white rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="force-approve-content flex items-center justify-center">
                                    <svg class="h-4 w-4 sm:h-5 sm:w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Force Approve Request
                                </span>
                                <span class="force-approve-loading hidden flex items-center justify-center">
                                    <svg class="animate-spin h-4 w-4 sm:h-5 sm:w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                            </button>
                        </form>
                    </div>
                @elseif($leaveRequest->needsAttendanceOvertimeCompletion())
                    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6 opacity-75">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-2">Approve Request</h3>
                        <p class="text-sm text-gray-600">Approval is disabled until the student completes the overtime form from Record Attendance.</p>
                    </div>
                @else
                    @php
                        $canOfficiallyExcuse = auth()->user()?->canAcceptOfficiallyExcusedLeave()
                            && ($leaveRequest->user?->role === 'student')
                            && in_array($leaveRequest->type, ['absent', 'excused', 'other'], true)
                            && ! $leaveRequest->admin_officially_excused;
                    @endphp
                    <!-- Normal Approve Form -->
                    @php
                        $approveMeritProjection = $studentTime['merits']['projection'] ?? null;
                        $approveWillAddMerits = is_array($approveMeritProjection)
                            && (int) ($approveMeritProjection['added_excess_merits'] ?? 0) > 0;
                        $approveMeritWarning = null;
                        if ($approveWillAddMerits) {
                            $added = (int) $approveMeritProjection['added_excess_merits'];
                            $after = (int) $approveMeritProjection['total_merits_after'];
                            $approveMeritWarning = "This student will get {$added} merit"
                                .($added === 1 ? '' : 's')
                                ." from this absence violation if you approve (total would become {$after})."
                                .(
                                    ! empty($approveMeritProjection['hits_final'])
                                        ? ' This also hits the final notice threshold.'
                                        : (
                                            ! empty($approveMeritProjection['hits_warning'])
                                                ? ' This also hits the rules violation warning threshold.'
                                                : ''
                                        )
                                )
                                .' Cancel and use “Accept as Officially Excused” if they have a school letter. Continue with normal approval?';
                        }

                        $employeeApproveImpact = $balances['approve_impact'] ?? null;
                        $approveBalanceWarning = is_array($employeeApproveImpact)
                            ? (string) ($employeeApproveImpact['warning'] ?? '')
                            : null;
                        $approveHasBalanceRisk = filled($approveBalanceWarning);
                        $approveWfhBlocked = ! empty($employeeApproveImpact['blocked']);
                        $approveHasRisk = $approveWillAddMerits || $approveHasBalanceRisk;
                        $approveConfirmWarning = $approveMeritWarning ?: $approveBalanceWarning;
                    @endphp
                    <div class="bg-white rounded-lg shadow border {{ $approveHasRisk ? 'border-red-300' : 'border-gray-200' }} p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Approve Request</h3>
                        @if($approveWillAddMerits)
                            <div class="mb-3 sm:mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-xs sm:text-sm text-red-800 font-medium">
                                    Approving this request will give this student
                                    <span class="font-bold tabular-nums">{{ (int) $approveMeritProjection['added_excess_merits'] }}</span>
                                    excess absence merit{{ (int) $approveMeritProjection['added_excess_merits'] === 1 ? '' : 's' }}
                                    (total would become {{ (int) $approveMeritProjection['total_merits_after'] }}).
                                    @if(!empty($approveMeritProjection['hits_final']))
                                        This hits the final notice threshold.
                                    @elseif(!empty($approveMeritProjection['hits_warning']))
                                        This hits the rules violation warning threshold.
                                    @endif
                                </p>
                                <p class="text-[11px] sm:text-xs text-red-700 mt-1">
                                    Use <strong>Accept as Officially Excused</strong> instead if this is covered by a school letter and should not count as a demerit.
                                </p>
                            </div>
                        @endif
                        @if($approveHasBalanceRisk)
                            <div class="mb-3 sm:mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-xs sm:text-sm text-red-800 font-medium">
                                    @if(($employeeApproveImpact['kind'] ?? '') === 'leave_negative')
                                        This approval will put the employee on a <strong>negative leave balance</strong>
                                        ({{ number_format((float) $employeeApproveImpact['remaining_before'], 2) }} → {{ number_format((float) $employeeApproveImpact['remaining_after'], 2) }} days).
                                    @elseif(($employeeApproveImpact['kind'] ?? '') === 'wfh_carryover_create')
                                        This admin-filed WFH approval will create
                                        <strong>{{ number_format((float) ($employeeApproveImpact['carryover_next'] ?? 0), 0) }} day(s) of carryover</strong>
                                        against next month’s WFH balance.
                                    @elseif(($employeeApproveImpact['kind'] ?? '') === 'wfh_carryover')
                                        This month already has
                                        <strong>{{ number_format((float) $employeeApproveImpact['carryover_debt'], 0) }} day(s) of WFH carryover debt</strong>
                                        reducing available balance.
                                    @else
                                        This WFH approval exceeds the remaining balance for
                                        {{ $employeeApproveImpact['month_label'] ?? 'this month' }}
                                        (needs {{ number_format((float) $employeeApproveImpact['request_days'], 0) }},
                                        remaining {{ number_format((float) $employeeApproveImpact['remaining_before'], 0) }}).
                                    @endif
                                </p>
                                @if($approveWfhBlocked)
                                    <p class="text-[11px] sm:text-xs text-red-700 mt-1">
                                        Employee-filed WFH over the monthly quota cannot be approved. Reject the request, or file WFH as admin if carryover to next month is intended.
                                    </p>
                                @endif
                            </div>
                        @endif
                        <form action="{{ url('/admin/leave-requests/' . $leaveRequest->id . '/approve') }}" method="POST"
                              class="space-y-3 sm:space-y-4"
                              @if($approveConfirmWarning) data-approve-warning="{{ $approveConfirmWarning }}" @endif>
                            @csrf
                            @include('admin.leave-requests.partials.show-nav-fields')
                            <div>
                                <label for="approve_notes" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                <textarea name="admin_notes" id="approve_notes" rows="3"
                                          placeholder="Add any notes about this approval..."
                                          class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                            </div>
                            <button type="submit" id="approve-btn"
                                    @if($approveWfhBlocked) disabled @endif
                                    class="w-full px-4 py-2 text-sm sm:text-base {{ $approveHasRisk ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500' }} text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="approve-content flex items-center justify-center">
                                    <svg class="h-4 w-4 sm:h-5 sm:w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    @if($approveWfhBlocked)
                                        Cannot Approve (WFH Over Quota)
                                    @elseif($approveWillAddMerits)
                                        Approve Anyway (Adds Merit)
                                    @elseif($approveHasBalanceRisk)
                                        Approve Anyway (Negative / Carryover)
                                    @else
                                        Approve Request
                                    @endif
                                </span>
                                <span class="approve-loading hidden flex items-center justify-center">
                                    <svg class="animate-spin h-4 w-4 sm:h-5 sm:w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                            </button>
                        </form>
                    </div>

                    @if($canOfficiallyExcuse)
                        <!-- Accept as Officially Excused (admin with full / student-management access) -->
                        <div class="bg-white rounded-lg shadow border border-teal-200 p-4 sm:p-6">
                            <div class="mb-3 sm:mb-4 p-3 bg-teal-50 border border-teal-200 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-teal-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    <div class="text-xs sm:text-sm text-teal-800">
                                        <strong>Official Excuse:</strong> Approves this request and excludes it from the student's demerit count. Use only when the student has a valid official excuse letter from their school.
                                    </div>
                                </div>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Accept as Officially Excused</h3>
                            <form action="{{ url('/admin/leave-requests/' . $leaveRequest->id . '/accept-officially-excused') }}" method="POST" class="space-y-3 sm:space-y-4">
                                @csrf
                                @include('admin.leave-requests.partials.show-nav-fields')
                                <div>
                                    <label for="officially_excused_notes" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                    <textarea name="admin_notes" id="officially_excused_notes" rows="3"
                                              placeholder="e.g. School excuse letter verified, signed by registrar…"
                                              class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                                </div>
                                <button type="submit" id="officially-excused-btn"
                                        class="w-full px-4 py-2 text-sm sm:text-base bg-teal-600 text-white rounded-lg hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span class="officially-excused-content flex items-center justify-center">
                                        <svg class="h-4 w-4 sm:h-5 sm:w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        Accept as Officially Excused
                                    </span>
                                    <span class="officially-excused-loading hidden flex items-center justify-center">
                                        <svg class="animate-spin h-4 w-4 sm:h-5 sm:w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Processing...
                                    </span>
                                </button>
                            </form>
                        </div>
                    @endif
                @endif

                <!-- For More Verification Form -->
                <div class="bg-white rounded-lg shadow border border-blue-200 p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Mark as For More Verification</h3>
                    <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4">Use this when you need additional verification checks before final approval or rejection.</p>
                    <form action="{{ url('/admin/leave-requests/' . $leaveRequest->id . '/verify') }}" method="POST" class="space-y-3 sm:space-y-4">
                        @csrf
                        @include('admin.leave-requests.partials.show-nav-fields')
                        <div>
                            <label for="verify_notes" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Verification Notes (Required)</label>
                            <textarea name="admin_notes" id="verify_notes" rows="3"
                                      placeholder="Explain what needs further verification..."
                                      required
                                      class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        <button type="submit" id="verify-btn"
                                class="w-full px-4 py-2 text-sm sm:text-base bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="verify-content flex items-center justify-center">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m3 8h9"></path>
                                </svg>
                                Set For More Verification
                            </span>
                            <span class="verify-loading hidden flex items-center justify-center">
                                <svg class="animate-spin h-4 w-4 sm:h-5 sm:w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </form>
                </div>

                <!-- Reject Form -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Reject Request</h3>
                    <form action="{{ url('/admin/leave-requests/' . $leaveRequest->id . '/reject') }}" method="POST" class="space-y-3 sm:space-y-4">
                        @csrf
                        @include('admin.leave-requests.partials.show-nav-fields')
                        <div>
                            <label for="reject_notes" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Reason for Rejection</label>
                            <textarea name="admin_notes" id="reject_notes" rows="3"
                                      placeholder="Please provide a reason for rejection..."
                                      class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                        </div>
                        <button type="submit" id="reject-btn"
                                class="w-full px-4 py-2 text-sm sm:text-base bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="reject-content flex items-center justify-center">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Reject Request
                            </span>
                            <span class="reject-loading hidden flex items-center justify-center">
                                <svg class="animate-spin h-4 w-4 sm:h-5 sm:w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </form>
                </div>

                <!-- Resubmit Form -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Request Resubmission</h3>
                    <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4">If there are errors in the request, you can ask the employee to resubmit it.</p>
                    <form action="{{ url('/admin/leave-requests/' . $leaveRequest->id . '/resubmit') }}" method="POST" class="space-y-3 sm:space-y-4">
                        @csrf
                        @include('admin.leave-requests.partials.show-nav-fields')
                        <div>
                            <label for="resubmit_notes" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">What needs to be corrected?</label>
                            <textarea name="admin_notes" id="resubmit_notes" rows="3"
                                      placeholder="Describe what errors need to be fixed..."
                                      class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                        </div>
                        <button type="submit" id="resubmit-btn"
                                class="w-full px-4 py-2 text-sm sm:text-base bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="resubmit-content flex items-center justify-center">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Request Resubmission
                            </span>
                            <span class="resubmit-loading hidden flex items-center justify-center">
                                <svg class="animate-spin h-4 w-4 sm:h-5 sm:w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </form>
                </div>
            @else
                <!-- Status Info -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Request Status</h3>
                    <div class="text-center py-3 sm:py-4">
                        <span class="px-3 sm:px-4 py-2 inline-flex text-base sm:text-lg leading-5 font-semibold rounded-full {{ $leaveRequest->status_badge_class }}">
                            {{ $leaveRequest->display_status }}
                        </span>
                        <p class="text-xs sm:text-sm text-gray-500 mt-3 sm:mt-4">
                            This request is currently marked as {{ $leaveRequest->display_status }}.
                        </p>
                        @if($leaveRequest->isRejected() || $leaveRequest->isApproved() || $leaveRequest->status === 'for_more_verification')
                            <form action="{{ url('/admin/leave-requests/' . $leaveRequest->id . '/resubmit') }}" method="POST" class="mt-3 sm:mt-4">
                                @csrf
                                @include('admin.leave-requests.partials.show-nav-fields')
                                <div class="mb-3 sm:mb-4">
                                    <label for="resubmit_notes_existing" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Notes for Resubmission</label>
                                    <textarea name="admin_notes" id="resubmit_notes_existing" rows="3"
                                              placeholder="Add notes about what needs to be corrected..."
                                              class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                                </div>
                                <button type="submit" id="resubmit-existing-btn"
                                        class="w-full px-4 py-2 text-sm sm:text-base bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span class="resubmit-existing-content flex items-center justify-center">
                                        <svg class="h-4 w-4 sm:h-5 sm:w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Request Resubmission
                                    </span>
                                    <span class="resubmit-existing-loading hidden flex items-center justify-center">
                                        <svg class="animate-spin h-4 w-4 sm:h-5 sm:w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Processing...
                                    </span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function confirmLeaveTypeChange(form) {
    const select = form.querySelector('#leave-request-type');
    if (!select) {
        return true;
    }
    const selected = select.options[select.selectedIndex];
    const current = select.getAttribute('data-current-type');
    if (current && selected.value === current) {
        return false;
    }
    let message = 'Change request type to "' + selected.text + '"?';
    @if($leaveRequest->isApproved() && ($leaveRequest->user?->role === 'student') && in_array($leaveRequest->type, ['absent', 'excused'], true))
        message += '\n\nThis will update absence merit counting (Excused does not count toward absences).';
    @elseif($leaveRequest->isApproved())
        message += '\n\nThis request is approved. DTR credits will be recalculated for the new type.';
    @endif
    return window.confirm(message);
}

function confirmLeaveDateChange(form) {
    const startInput = form.querySelector('#leave-request-start-date');
    const endInput = form.querySelector('#leave-request-end-date');
    if (!startInput) {
        return true;
    }
    const currentStart = startInput.getAttribute('data-current-start');
    const currentEnd = endInput ? endInput.getAttribute('data-current-end') : null;
    const newEnd = endInput && endInput.value ? endInput.value : startInput.value;
    if (currentStart && startInput.value === currentStart && currentEnd && newEnd === currentEnd) {
        return false;
    }
    let message = 'Change request dates to ' + startInput.value + ' – ' + newEnd + '?';
    @if($leaveRequest->isApproved())
        message += '\n\nThis request is approved. DTR credits will be recalculated for the new date range.';
    @endif
    return window.confirm(message);
}

document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('leave-request-type');
    if (typeSelect) {
        typeSelect.setAttribute('data-current-type', typeSelect.value);
    }
    const startDateInput = document.getElementById('leave-request-start-date');
    const endDateInput = document.getElementById('leave-request-end-date');
    if (startDateInput) {
        startDateInput.setAttribute('data-current-start', startDateInput.value);
    }
    if (endDateInput) {
        endDateInput.setAttribute('data-current-end', endDateInput.value);
    }
    // Handle Force Approve form
    const forceApproveForm = document.querySelector('form[action*="force-accept"]');
    if (forceApproveForm) {
        forceApproveForm.addEventListener('submit', function(e) {
            const btn = document.getElementById('force-approve-btn');
            if (btn) {
                btn.disabled = true;
                const content = btn.querySelector('.force-approve-content');
                const loading = btn.querySelector('.force-approve-loading');
                if (content) content.classList.add('hidden');
                if (loading) loading.classList.remove('hidden');
            }
        });
    }

    // Handle Approve form
    const approveForm = document.querySelector('form[action*="/approve"]:not([action*="force-accept"])');
    if (approveForm) {
        approveForm.addEventListener('submit', function(e) {
            const approveWarning = approveForm.getAttribute('data-approve-warning')
                || approveForm.getAttribute('data-merit-warning');
            if (approveWarning && !window.confirm(approveWarning)) {
                e.preventDefault();
                return;
            }

            const btn = document.getElementById('approve-btn');
            if (btn) {
                btn.disabled = true;
                const content = btn.querySelector('.approve-content');
                const loading = btn.querySelector('.approve-loading');
                if (content) content.classList.add('hidden');
                if (loading) loading.classList.remove('hidden');
            }
        });
    }

    // Handle Verify form
    const verifyForm = document.querySelector('form[action*="/verify"]');
    if (verifyForm) {
        verifyForm.addEventListener('submit', function(e) {
            const btn = document.getElementById('verify-btn');
            if (btn) {
                btn.disabled = true;
                const content = btn.querySelector('.verify-content');
                const loading = btn.querySelector('.verify-loading');
                if (content) content.classList.add('hidden');
                if (loading) loading.classList.remove('hidden');
            }
        });
    }

    // Handle Officially Excused form
    const officiallyExcusedForm = document.querySelector('form[action*="accept-officially-excused"]');
    if (officiallyExcusedForm) {
        officiallyExcusedForm.addEventListener('submit', function(e) {
            const btn = document.getElementById('officially-excused-btn');
            if (btn) {
                btn.disabled = true;
                const content = btn.querySelector('.officially-excused-content');
                const loading = btn.querySelector('.officially-excused-loading');
                if (content) content.classList.add('hidden');
                if (loading) loading.classList.remove('hidden');
            }
        });
    }

    // Handle Reject form
    const rejectForm = document.querySelector('form[action*="reject"]');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            const btn = document.getElementById('reject-btn');
            if (btn) {
                btn.disabled = true;
                const content = btn.querySelector('.reject-content');
                const loading = btn.querySelector('.reject-loading');
                if (content) content.classList.add('hidden');
                if (loading) loading.classList.remove('hidden');
            }
        });
    }

    // Handle Resubmit forms (both pending and existing)
    const resubmitForms = document.querySelectorAll('form[action*="resubmit"]');
    resubmitForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                const content = btn.querySelector('.resubmit-content, .resubmit-existing-content');
                const loading = btn.querySelector('.resubmit-loading, .resubmit-existing-loading');
                if (content) content.classList.add('hidden');
                if (loading) loading.classList.remove('hidden');
            }
        });
    });
});
</script>

<!-- Activity Log Section -->
<div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6 mt-6">
    <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4">Activity Log</h2>
    <p class="text-sm text-gray-500 mb-4">Track all updates and actions performed on this leave request.</p>

    @if($leaveRequest->logs && $leaveRequest->logs->count() > 0)
        <div class="space-y-4">
            @foreach($leaveRequest->logs as $log)
                <div class="border-l-4 {{ $log->action === 'admin_officially_excused' ? 'border-teal-500' : ($log->action === 'approved' ? 'border-green-500' : ($log->action === 'rejected' ? 'border-red-500' : ($log->action === 'resubmission_requested' ? 'border-yellow-500' : ($log->action === 'for_more_verification' ? 'border-blue-500' : (in_array($log->action, ['type_changed', 'dates_changed', 'overtime_hours_adjusted'], true) ? 'border-purple-500' : ($log->action === 'requester_resubmitted' ? 'border-indigo-500' : 'border-gray-400')))))) }} pl-4 py-2">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $log->action === 'admin_officially_excused' ? 'bg-teal-100 text-teal-800' : ($log->action === 'approved' ? 'bg-green-100 text-green-800' : ($log->action === 'rejected' ? 'bg-red-100 text-red-800' : ($log->action === 'resubmission_requested' ? 'bg-yellow-100 text-yellow-800' : ($log->action === 'for_more_verification' ? 'bg-blue-100 text-blue-800' : (in_array($log->action, ['type_changed', 'dates_changed', 'overtime_hours_adjusted'], true) ? 'bg-purple-100 text-purple-800' : ($log->action === 'requester_resubmitted' ? 'bg-indigo-100 text-indigo-900' : 'bg-gray-100 text-gray-800')))))) }}">
                                    {{ $log->action_label }}
                                </span>
                                @if($log->status_before && $log->status_after)
                                    <span class="text-xs text-gray-500">
                                        {{ ucfirst($log->status_before) }} → {{ ucfirst($log->status_after) }}
                                    </span>
                                @endif
                            </div>
                            <div class="mt-2 text-sm text-gray-700">
                                @if($log->performer)
                                    @if($log->action === 'filed_by_admin' && $leaveRequest->user_id !== $log->performed_by)
                                        <span class="font-medium"><x-user-name :user="$log->performer" :size="14" /></span>
                                        <span class="text-gray-500">filed this leave request on behalf of the employee</span>
                                    @else
                                        <span class="font-medium"><x-user-name :user="$log->performer" :size="14" /></span>
                                        <span class="text-gray-500">performed this action</span>
                                    @endif
                                @endif
                            </div>
                            @if($log->notes)
                                <div class="mt-2 text-sm text-gray-600 bg-gray-50 rounded p-2">
                                    {{ $log->notes }}
                                </div>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 ml-4">
                            {{ $log->created_at->format('M d, Y h:i A') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="mt-2 text-sm">No activity log entries yet.</p>
        </div>
    @endif
</div>
@endsection

