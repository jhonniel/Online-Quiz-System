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
            <a href="{{ route('admin.leave-requests.index') }}"
               class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 text-xs sm:text-sm bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white hover:bg-white/20 transition duration-200">
                <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="hidden sm:inline">Back to List</span>
                <span class="sm:hidden">Back</span>
            </a>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
            <!-- Request Information -->
            <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4">Request Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Employee</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $leaveRequest->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $leaveRequest->user->email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Request Type</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $leaveRequest->type_label }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Start Date</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $leaveRequest->start_date->format('F d, Y') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">End Date</label>
                        <p class="text-sm font-semibold text-gray-900">
                            @if($leaveRequest->end_date)
                                {{ $leaveRequest->end_date->format('F d, Y') }}
                            @else
                                <span class="text-gray-400">Same day</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Duration</label>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $leaveRequest->days }} {{ $leaveRequest->days == 1 ? 'day' : 'days' }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $leaveRequest->status_badge_class }}">
                            {{ $leaveRequest->display_status }}
                        </span>
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
                                <p class="text-sm font-semibold text-gray-900">{{ $leaveRequest->reviewer->name }}</p>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Structured details for special types so admin can see the same form info as the employee --}}
                @if($leaveRequest->type === 'overtime')
                    @php
                        $raw = $leaveRequest->reason ?? '';
                        $otHours = '';
                        $otDates = '';
                        $otTasks = '';
                        $otReason = '';

                        if (preg_match('/Total Overtime Hours:\s*(.+)/', $raw, $m)) {
                            $otHours = trim($m[1]);
                        }
                        if (preg_match('/Overtime Dates:\s*(.+)/', $raw, $m)) {
                            $otDates = trim($m[1]);
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
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Total Overtime Hours</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $otHours }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Overtime Dates</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $otDates }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tasks / ClickUp Links</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200 whitespace-pre-line">
                                {!! nl2br($otTasksWithLinks) !!}
                            </p>
                        </div>
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
                        $lengthText = $leaveRequest->days . ' ' . ($leaveRequest->days == 1 ? 'day' : 'days');
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
                            <p class="text-sm font-semibold text-gray-900">Dear Ms. May Grace Acosta,</p>
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

                            <p class="font-semibold">Additional info:</p>
                            <p class="min-h-[3rem] border-t border-gray-300 pt-2 text-gray-800 whitespace-pre-line">
                                {{ $leaveRequest->reason }}
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
                                    <p class="font-semibold underline">{{ $signatories['immediate_supervisor'] ?? 'CHARMAINE JOY ROSATACE' }}</p>
                                    <p class="text-gray-700 text-xs tracking-wide">IMMEDIATE SUPERVISOR</p>
                                </div>
                                <div class="mt-3">
                                    <p class="font-semibold underline">{{ $signatories['hr_admin'] ?? 'MAY GRACE ACOSTA' }}</p>
                                    <p class="text-gray-700 text-xs tracking-wide">HR ADMIN</p>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-gray-700">Approved:</p>
                                        <p class="font-semibold underline">{{ $signatories['cto'] ?? 'NITISH KHEMANI' }}</p>
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
                            <p class="text-sm font-semibold text-gray-900">Dear Ms. May Grace Acosta,</p>
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
                                    <p class="font-semibold underline">{{ $signatories['immediate_supervisor'] ?? 'CHARMAINE JOY ROSATACE' }}</p>
                                    <p class="text-gray-700 text-xs tracking-wide">IMMEDIATE SUPERVISOR</p>
                                </div>
                                <div class="mt-3">
                                    <p class="font-semibold underline">{{ $signatories['hr_admin'] ?? 'MAY GRACE ACOSTA' }}</p>
                                    <p class="text-gray-700 text-xs tracking-wide">HR ADMIN</p>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-gray-700">Approved:</p>
                                        <p class="font-semibold underline">{{ $signatories['cto'] ?? 'NITISH KHEMANI' }}</p>
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
                        $otDates = '';
                        $otReason = '';
                        $otTasks = '';

                        if (preg_match('/Total Overtime Hours:\s*(.+)/', $raw, $m)) {
                            $otHours = trim($m[1]);
                        }
                        if (preg_match('/Overtime Dates:\s*(.+)/', $raw, $m)) {
                            $otDates = trim($m[1]);
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
                            <p class="text-sm font-semibold text-gray-900">Dear Ms. May Grace Acosta,</p>
                        </div>

                        <!-- Body -->
                        <div class="space-y-3 text-sm text-gray-800">
                            <p>
                                Please accept this letter to formally request for an approval for additional
                                <span class="font-semibold underline decoration-gray-400 decoration-1">
                                    {{ $otHours ?: '[Number of hours]' }}
                                </span>
                                working hours and in days
                                <span class="font-semibold underline decoration-gray-400 decoration-1">
                                    {{ $otDates ?: '[state the dates]' }}
                                </span>
                                @if(!empty($otReason))
                                due to
                                <span class="font-semibold underline decoration-gray-400 decoration-1">
                                    {{ $otReason }}
                                </span>
                                @endif
                                Examples: urgent project deadline, increased workload due to staffing shortage, critical system maintenance, etc.
                            </p>

                            <p class="font-semibold">
                                [Strictly List down Task Listed in ClickUp for Devs via link]
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
                                    <p class="font-semibold underline">{{ $signatories['immediate_supervisor'] ?? 'CHARMAINE JOY ROSATACE' }}</p>
                                    <p class="text-gray-700 text-xs tracking-wide">IMMEDIATE SUPERVISOR</p>
                                </div>
                                <div class="mt-3">
                                    <p class="font-semibold underline">{{ $signatories['hr_admin'] ?? 'MAY GRACE ACOSTA' }}</p>
                                    <p class="text-gray-700 text-xs tracking-wide">HR ADMIN</p>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-gray-700">Approved:</p>
                                        <p class="font-semibold underline">{{ $signatories['cto'] ?? 'NITISH KHEMANI' }}</p>
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
                </div>
            @elseif(isset($balances))
                <!-- Employee Balances & Overtime -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6 space-y-3 sm:space-y-4">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-2">Employee Balances ({{ now()->year }})</h3>
                    <div class="grid grid-cols-1 gap-3">
                        <div class="border border-gray-100 rounded-lg px-3 py-2">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Vacation Leave</p>
                            <p class="text-sm text-gray-900">
                                Remaining:
                                <span class="font-bold">{{ $balances['vacation']['remaining'] }}</span>
                                / {{ $balances['vacation']['allowance'] }} days
                            </p>
                            <p class="text-xs text-gray-500">Used: {{ $balances['vacation']['used'] }} days</p>
                        </div>
                        <div class="border border-gray-100 rounded-lg px-3 py-2">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Sick Leave</p>
                            <p class="text-sm text-gray-900">
                                Remaining:
                                <span class="font-bold">{{ $balances['sick']['remaining'] }}</span>
                                / {{ $balances['sick']['allowance'] }} days
                            </p>
                            <p class="text-xs text-gray-500">Used: {{ $balances['sick']['used'] }} days</p>
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
                    </div>
                </div>
            @endif

            <!-- Action Panel -->
            @if($leaveRequest->isPending())
                <!-- Approve Form -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Approve Request</h3>
                    <form action="{{ route('admin.leave-requests.approve', $leaveRequest) }}" method="POST" class="space-y-3 sm:space-y-4">
                        @csrf
                        <div>
                            <label for="approve_notes" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea name="admin_notes" id="approve_notes" rows="3"
                                      placeholder="Add any notes about this approval..."
                                      class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>
                        <button type="submit"
                                class="w-full px-4 py-2 text-sm sm:text-base bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Approve Request
                        </button>
                    </form>
                </div>

                <!-- Reject Form -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Reject Request</h3>
                    <form action="{{ route('admin.leave-requests.reject', $leaveRequest) }}" method="POST" class="space-y-3 sm:space-y-4">
                        @csrf
                        <div>
                            <label for="reject_notes" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Reason for Rejection</label>
                            <textarea name="admin_notes" id="reject_notes" rows="3"
                                      placeholder="Please provide a reason for rejection..."
                                      class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                        </div>
                        <button type="submit"
                                class="w-full px-4 py-2 text-sm sm:text-base bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Reject Request
                        </button>
                    </form>
                </div>

                <!-- Resubmit Form -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-3 sm:mb-4">Request Resubmission</h3>
                    <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4">If there are errors in the request, you can ask the employee to resubmit it.</p>
                    <form action="{{ route('admin.leave-requests.resubmit', $leaveRequest) }}" method="POST" class="space-y-3 sm:space-y-4">
                        @csrf
                        <div>
                            <label for="resubmit_notes" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">What needs to be corrected?</label>
                            <textarea name="admin_notes" id="resubmit_notes" rows="3"
                                      placeholder="Describe what errors need to be fixed..."
                                      class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                        </div>
                        <button type="submit"
                                class="w-full px-4 py-2 text-sm sm:text-base bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Request Resubmission
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
                            This request has already been {{ $leaveRequest->status }}.
                        </p>
                        @if($leaveRequest->isRejected() || $leaveRequest->isApproved())
                            <form action="{{ route('admin.leave-requests.resubmit', $leaveRequest) }}" method="POST" class="mt-3 sm:mt-4">
                                @csrf
                                <div class="mb-3 sm:mb-4">
                                    <label for="resubmit_notes_existing" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Notes for Resubmission</label>
                                    <textarea name="admin_notes" id="resubmit_notes_existing" rows="3"
                                              placeholder="Add notes about what needs to be corrected..."
                                              class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                                </div>
                                <button type="submit"
                                        class="w-full px-4 py-2 text-sm sm:text-base bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    <svg class="h-4 w-4 sm:h-5 sm:w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Request Resubmission
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

