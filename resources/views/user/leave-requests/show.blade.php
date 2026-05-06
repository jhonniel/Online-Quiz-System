@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col min-h-0">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-4 flex-shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Leave Request Details</h1>
                    <p class="text-indigo-100 text-sm">View your leave request information</p>
                </div>
            </div>
            <a href="{{ url('/leave-requests') }}"
               class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white hover:bg-white/30 transition duration-200">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </a>
        </div>
    </div>

    <!-- Details -->
    <div class="flex-1 overflow-y-auto p-4">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6 space-y-6">
                <!-- Status Badge -->
                <div class="flex items-center justify-between">
                    <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full {{ $leaveRequest->status_badge_class }}">
                        {{ $leaveRequest->display_status }}
                    </span>
                    @if($leaveRequest->isPending())
                        <div class="flex items-center space-x-2">
                            @if($leaveRequest->reviewed_at)
                                <a href="{{ url('/leave-requests/' . $leaveRequest->id . '/edit') }}"
                                   class="inline-flex items-center px-4 py-2 border border-indigo-300 text-sm font-medium rounded-lg text-indigo-700 bg-white hover:bg-indigo-50">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit Request
                                </a>
                            @endif
                            <form action="{{ url('/leave-requests/' . $leaveRequest->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this leave request?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete Request
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <!-- Request Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Request Type</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $leaveRequest->type_label }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Duration</label>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $leaveRequest->duration_display_label }}
                        </p>
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

                    <div class="mt-1 grid grid-cols-1 md:grid-cols-2 gap-6">
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


                <!-- Vacation / Sick Leave / Offset Letter-style View (matches provided template) -->
                @if(in_array($leaveRequest->type, ['leave', 'vacation_leave', 'sick_leave', 'offset']))
                    @php
                        $effectiveDate = $leaveRequest->created_at->format('F d, Y');
                        $startDate = $leaveRequest->start_date->format('F d, Y');
                        $endDate = ($leaveRequest->end_date ?? $leaveRequest->start_date)->format('F d, Y');
                        $lengthText = $leaveRequest->duration_display_label;
                        $reasonText = $leaveRequest->reason ?: '_______________________________________________';
                        $user = auth()->user();
                    @endphp

                    <div class="border border-gray-300 rounded-lg p-6 space-y-4 bg-white">
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

                        <!-- Closing -->
                        <div class="space-y-1 text-sm text-gray-800 pt-4">
                            <p>Thank you for understanding.</p>
                            <p class="mt-4">Best regards,</p>
                            <p>Truly yours,</p>
                        </div>

                        <!-- Signature Block -->
                        <div class="mt-4 space-y-2 text-sm text-gray-900">
                            <div class="space-y-0.5">
                                <p class="font-semibold">{{ $user->name ?? '[YOUR NAME]' }}</p>
                                <p class="text-gray-700 uppercase text-xs tracking-wide">{{ strtoupper($user->role ?? 'Employee') }}</p>
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

                @if($leaveRequest->supporting_document_path)
                    @php
                        $docUrl = null;
                        try {
                            // Prefer a signed URL with inline content-disposition so the browser opens it
                            $docUrl = \Illuminate\Support\Facades\Storage::disk('digitalocean')
                                ->temporaryUrl(
                                    $leaveRequest->supporting_document_path,
                                    now()->addMinutes(30),
                                    ['ResponseContentDisposition' => 'inline']
                                );
                        } catch (\Throwable $e) {
                            try {
                                $docUrl = \Illuminate\Support\Facades\Storage::url($leaveRequest->supporting_document_path);
                            } catch (\Throwable $e) {
                                $docUrl = null;
                            }
                        }
                    @endphp
                    @if($docUrl)
                        <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Supporting Document</p>
                                    <a href="{{ $docUrl }}" target="_blank" rel="noopener"
                                       class="text-sm text-indigo-700 underline break-words">View / Download</a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
                            </div>
                        </div>
                    </div>
                @elseif($leaveRequest->type === 'work_from_home')
                    @php
                        $effectiveDate = $leaveRequest->created_at->format('F d, Y');
                        $user = auth()->user();
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

                        // clickable links in task list
                        $tasksEscaped = e($tasks);
                        $tasksWithLinks = preg_replace(
                            '~(https?://[^\s]+)~',
                            '<a href="$1" target="_blank" rel="noopener" class="text-indigo-600 underline break-words">$1</a>',
                            $tasksEscaped
                        );
                    @endphp

                    <div class="border border-gray-300 rounded-lg p-6 space-y-4 bg-white">
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

                        <!-- Signature Block -->
                        <div class="mt-4 space-y-2 text-sm text-gray-900">
                            <div class="space-y-0.5">
                                <p class="font-semibold">{{ $user->name ?? '[YOUR NAME]' }}</p>
                                <p class="text-gray-700 uppercase text-xs tracking-wide">{{ strtoupper($user->role ?? 'Employee') }}</p>
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
                        $user = auth()->user();
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

                    <div class="border border-gray-300 rounded-lg p-6 space-y-4 bg-white">
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

                        <!-- Signature Block -->
                        <div class="mt-4 space-y-2 text-sm text-gray-900">
                            <div class="space-y-0.5">
                                <p class="font-semibold">{{ $user->name ?? '[YOUR NAME]' }}</p>
                                <p class="text-gray-700 uppercase text-xs tracking-wide">{{ strtoupper($user->role ?? 'Employee') }}</p>
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

                <!-- Reason (shown only for simple types, not letter-style layouts like WFH/OT/Vacation/Sick) -->
                @if($leaveRequest->reason && !in_array($leaveRequest->type, ['leave', 'vacation_leave', 'sick_leave', 'work_from_home', 'overtime']))
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Reason</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            {{ $leaveRequest->reason }}
                        </p>
                    </div>
                @endif

                @include('user.leave-requests.partials.admin-feedback-for-requester', [
                    'leaveRequest' => $leaveRequest,
                    'leaveRequestActivityLogs' => $leaveRequestActivityLogs,
                ])
            </div>
        </div>
    </div>
</div>
@endsection

