@extends('layouts.user')

@section('content')
<div class="min-h-full flex flex-col">
    <!-- Search and Filter Bar -->
    @if(($canViewAssignedQuizzes ?? auth()->user()->canViewAssignedQuizzes()))
    <div class="bg-white shadow-sm border-b border-gray-200 p-4 flex-shrink-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <!-- Search -->
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text"
                           id="search-input"
                           placeholder="Search quizzes..."
                           class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-2">
                <button class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <span class="hidden sm:inline">Filter</span>
                </button>
                <button onclick="openQuizCodeModal()" class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    <span class="hidden sm:inline">Enter Quiz Code</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-4 flex-shrink-0">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">My Dashboard</h1>
                <p class="text-indigo-100 text-sm">Welcome back, {{ auth()->user()->name }}! Your rank: {{ auth()->user()->getRankText() }} ({{ auth()->user()->getTotalScore() }} pts)</p>
            </div>
        </div>
    </div>

    @if(auth()->user()->role === 'student' && !empty($studentLeaveBalanceSummary))
    <div class="bg-sky-50 border-b border-sky-200 px-4 py-3 flex-shrink-0" role="region" aria-label="Absent request allowance">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex items-start gap-2 min-w-0">
                <svg class="h-5 w-5 text-sky-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-sky-900">Absent-request allowance (set by administrator)</p>
                    <p class="text-sm text-sky-800 mt-0.5">
                        <span class="font-bold tabular-nums">{{ number_format((float) ($studentLeaveBalanceSummary['remaining_absence_balance'] ?? 0), 2) }}</span>
                        day(s) still available
                        <span class="text-sky-700">·</span>
                        <span class="tabular-nums">{{ number_format((float) ($studentLeaveBalanceSummary['approved_absent_days'] ?? 0), 2) }}</span>
                        day(s) used toward approved absences
                        <span class="text-sky-700">·</span>
                        allowance cap:
                        <span class="tabular-nums">{{ number_format((float) ($studentLeaveBalanceSummary['allowable_absences'] ?? 0), 2) }}</span>
                        day(s)
                    </p>
                </div>
            </div>
            <a href="{{ route('user.leave-requests.create') }}"
               class="inline-flex items-center justify-center px-3 py-1.5 rounded-md text-sm font-medium text-sky-900 bg-white border border-sky-300 hover:bg-sky-100 shrink-0">
                File leave / absence request
            </a>
        </div>
    </div>
    @endif

    <!-- Ongoing Quiz Alert -->
    @if(($canViewAssignedQuizzes ?? auth()->user()->canViewAssignedQuizzes()) && $ongoingQuiz && $ongoingQuiz->isOngoing())
    <div class="bg-blue-50 border-b border-blue-200 p-4 flex-shrink-0">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-blue-800">
                    Quiz in Progress
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>You have an ongoing quiz: <strong>{{ $ongoingQuiz->quiz->title }}</strong></p>
                    @if($ongoingQuiz->quiz->time_limit)
                        <p class="mt-1">
                            Time remaining:
                            <span class="font-mono font-bold">
                                {{ floor($ongoingQuiz->remaining_time / 60) }}:{{ str_pad($ongoingQuiz->remaining_time % 60, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </p>
                    @endif
                </div>
                <div class="mt-3">
                    <a href="{{ url('/quizzes/' . $ongoingQuiz->quiz->id . '/take') }}"
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Continue Quiz
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(auth()->user()->role === 'student' && ($evaluationAvailable ?? false))
    <div class="bg-emerald-50 border-b border-emerald-200 p-4 flex-shrink-0">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-emerald-800">Evaluation Form Available</h3>
                <p class="mt-1 text-sm text-emerald-700">
                    You completed your required training hours. Please submit
                    <span class="font-semibold">{{ $evaluationFormTitle ?? 'the evaluation form' }}</span>.
                </p>
                <div class="mt-3">
                    <a href="{{ url('/evaluation') }}"
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-emerald-700 bg-emerald-100 hover:bg-emerald-200">
                        Open Evaluation Form
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(auth()->user()->role === 'student' && !empty($studentOjtAccessCountdown))
    @php
        $ojtDaysLeft = (int) ($studentOjtAccessCountdown['days_remaining'] ?? 0);
        $ojtAccessEnd = $studentOjtAccessCountdown['access_end_date'] ?? null;
    @endphp
    <div class="bg-amber-50 border-b border-amber-200 p-4 flex-shrink-0" role="status">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
                <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-semibold text-amber-900">Post–OJT account access</h3>
                @if($ojtDaysLeft === 0)
                    <p class="mt-1 text-sm text-amber-800">
                        Your scheduled access period has ended or ends today. Your student account may be disabled automatically. If you still need access, contact the administration right away.
                    </p>
                @else
                    <p class="mt-1 text-sm text-amber-800">
                        <span class="text-2xl font-bold text-amber-950 tabular-nums">{{ $ojtDaysLeft }}</span>
                        <span class="font-medium">{{ \Illuminate\Support\Str::plural('day', $ojtDaysLeft) }}</span>
                        remaining until your student account may be automatically disabled after your OJT completion grace period.
                    </p>
                @endif
                @if($ojtAccessEnd instanceof \Carbon\Carbon)
                    <p class="mt-2 text-xs text-amber-700/90">
                        Scheduled end of this access window:
                        <time datetime="{{ $ojtAccessEnd->toDateString() }}">{{ $ojtAccessEnd->timezone(config('app.timezone'))->format('F j, Y') }}</time>
                        ({{ config('app.timezone') }}).
                    </p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if(auth()->user()->role === 'student' && !empty($studentTrainingStats))
    @php
        $estimatedEndDate = $studentTrainingStats['estimated_end_date'] ?? null;
        $ojtTargetEndDate = $studentTrainingStats['ojt_target_end_date'] ?? null;
        $possibleExitConferenceDate = $studentTrainingStats['possible_exit_conference_date'] ?? null;
        $possibleExitWeekdays = (int) ($studentTrainingStats['possible_exit_conference_weekdays'] ?? 0);
        $remainingTrainingHrs = (float) ($studentTrainingStats['remaining_hours'] ?? 0);

        if (is_string($estimatedEndDate) && $estimatedEndDate !== '') {
            $estimatedEndDate = \Carbon\Carbon::parse($estimatedEndDate);
        }
        if (is_string($ojtTargetEndDate) && $ojtTargetEndDate !== '') {
            $ojtTargetEndDate = \Carbon\Carbon::parse($ojtTargetEndDate);
        }
        if (is_string($possibleExitConferenceDate) && $possibleExitConferenceDate !== '') {
            $possibleExitConferenceDate = \Carbon\Carbon::parse($possibleExitConferenceDate);
        }
    @endphp
    <div class="p-4 pt-0 flex-shrink-0 space-y-3">
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-semibold text-indigo-900">Training Progress</h3>
                    <p class="text-xs text-indigo-700 mt-1">
                        Progress: {{ number_format((float) ($studentTrainingStats['progress_percent'] ?? 0), 1) }}%
                        ({{ number_format((float) ($studentTrainingStats['logged_hours'] ?? 0), 2) }}h logged /
                        {{ number_format((float) ($studentTrainingStats['required_hours'] ?? 0), 2) }}h required)
                    </p>
                    @if($ojtTargetEndDate instanceof \Carbon\CarbonInterface && $estimatedEndDate instanceof \Carbon\CarbonInterface && $remainingTrainingHrs > 0 && $estimatedEndDate->copy()->startOfDay()->gt($ojtTargetEndDate->copy()->startOfDay()))
                        <p class="mt-2 text-xs font-medium text-amber-900 bg-amber-100/80 border border-amber-200 rounded-md px-2 py-1.5">
                            At your recent logging pace you may finish <strong>after</strong> the administrator’s OJT target date
                            (<time datetime="{{ $ojtTargetEndDate->toDateString() }}">{{ $ojtTargetEndDate->timezone(config('app.timezone'))->format('F j, Y') }}</time>). Consider pacing up or confirming with your coordinator.
                        </p>
                    @endif
                </div>
                <div class="flex flex-col gap-3 sm:text-right shrink-0 sm:min-w-[11rem]">
                    <div>
                        <p class="text-xs font-medium text-indigo-700 uppercase tracking-wide">exit conference</p>
                        <p class="text-[11px] text-indigo-600 mt-0.5">This is your target date to be finished on your Internship or OJT.</p>
                        @if($ojtTargetEndDate instanceof \Carbon\CarbonInterface)
                            <p class="text-sm font-semibold text-indigo-950 tabular-nums">
                                {{ $ojtTargetEndDate->timezone(config('app.timezone'))->format('F j, Y') }}
                            </p>
                        @else
                            <p class="text-sm font-semibold text-indigo-950 tabular-nums">Not set</p>
                            @if($possibleExitConferenceDate instanceof \Carbon\CarbonInterface && $possibleExitWeekdays > 0)
                                <p class="text-[11px] text-indigo-600 mt-0.5">
                                    Estimated: {{ $possibleExitConferenceDate->timezone(config('app.timezone'))->format('F j, Y') }}
                                </p>
                            @endif
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-medium text-indigo-700 uppercase tracking-wide">Projected completion (recent pace)</p>
                        <p class="text-sm font-semibold text-indigo-950 tabular-nums">
                            @if($estimatedEndDate instanceof \Carbon\CarbonInterface)
                                {{ $estimatedEndDate->timezone(config('app.timezone'))->format('F j, Y') }}
                            @else
                                —
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($studentLeaveBalanceSummary))
        @php
            $remainingAbsenceBalance = (float) ($studentLeaveBalanceSummary['remaining_absence_balance'] ?? 0);
            $absenceBalanceExhausted = $remainingAbsenceBalance <= 0;
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Accumulated Approved Absent Days</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">
                    {{ number_format((float) ($studentLeaveBalanceSummary['approved_absent_days'] ?? 0), 2) }}
                </p>
            </div>
            <div class="rounded-lg p-4 shadow-sm border {{ $absenceBalanceExhausted ? 'bg-red-50 border-red-200' : 'bg-white border-gray-200' }}">
                <p class="text-xs font-medium uppercase tracking-wide {{ $absenceBalanceExhausted ? 'text-red-700' : 'text-gray-500' }}">Available absence balance</p>
                <p class="mt-0.5 text-[11px] leading-snug {{ $absenceBalanceExhausted ? 'text-red-700' : 'text-gray-500' }}">Based on the absence allowance your administrator set (approved “Absent” requests consume this balance).</p>
                <p class="mt-2 text-2xl font-bold tabular-nums {{ $absenceBalanceExhausted ? 'text-red-800' : 'text-gray-900' }}">
                    {{ number_format($remainingAbsenceBalance, 2) }}
                    <span class="text-base font-semibold {{ $absenceBalanceExhausted ? 'text-red-700' : 'text-gray-600' }}">days left</span>
                </p>
                <p class="text-xs mt-1 {{ $absenceBalanceExhausted ? 'text-red-700' : 'text-gray-500' }}">
                    Used:
                    {{ number_format((float) ($studentLeaveBalanceSummary['approved_absent_days'] ?? 0), 2) }}
                    · Allowance:
                    {{ number_format((float) ($studentLeaveBalanceSummary['allowable_absences'] ?? 0), 2) }}
                    day(s)
                </p>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Required vs Logged vs Remaining</h3>
                <div class="h-60">
                    <canvas id="studentTrainingProgressChart"></canvas>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Logged Hours (Last 6 Months)</h3>
                <div class="h-60">
                    <canvas id="studentMonthlyHoursChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(auth()->user()->role === 'student' && isset($studentResubmissionRequests) && $studentResubmissionRequests->isNotEmpty())
    <div class="p-4 pt-0 flex-shrink-0">
        <div class="bg-rose-50 border border-rose-200 rounded-lg p-4" role="status">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-rose-900">Leave Requests For Resubmission</h3>
                    <p class="mt-1 text-xs text-rose-700">
                        You have {{ $studentResubmissionRequests->count() }} request{{ $studentResubmissionRequests->count() > 1 ? 's' : '' }} needing correction and resubmission.
                    </p>
                </div>
                <a href="{{ url('/leave-requests') }}"
                   class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium border border-rose-300 text-rose-700 bg-white hover:bg-rose-100">
                    Open Leave Requests
                </a>
            </div>
            <div class="mt-3 space-y-2">
                @foreach($studentResubmissionRequests as $resubReq)
                    <div class="flex items-center justify-between bg-white/80 border border-rose-100 rounded-md px-3 py-2">
                        <div>
                            <p class="text-xs font-semibold text-gray-800">{{ $resubReq->type_label ?? ucfirst(str_replace('_', ' ', (string) $resubReq->type)) }}</p>
                            <p class="text-[11px] text-gray-600">
                                Updated {{ optional($resubReq->updated_at)->format('M j, Y g:i A') ?? '—' }}
                            </p>
                        </div>
                        <a href="{{ route('user.leave-requests.edit', $resubReq) }}"
                           class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-medium text-white bg-rose-600 hover:bg-rose-700">
                            Resubmit
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if(auth()->user()->role === 'employee' && !empty($employeeLeaveSummary))
    <div class="p-4 pt-2 flex-shrink-0 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Leave Requests</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format((int) ($employeeLeaveSummary['total_requests'] ?? 0)) }}</p>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-amber-700">Pending</p>
                <p class="mt-1 text-2xl font-bold text-amber-900">{{ number_format((int) ($employeeLeaveSummary['pending_requests'] ?? 0)) }}</p>
            </div>
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Approved</p>
                <p class="mt-1 text-2xl font-bold text-emerald-900">{{ number_format((int) ($employeeLeaveSummary['approved_requests'] ?? 0)) }}</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-red-700">Rejected</p>
                <p class="mt-1 text-2xl font-bold text-red-900">{{ number_format((int) ($employeeLeaveSummary['rejected_requests'] ?? 0)) }}</p>
            </div>
            <div class="bg-rose-50 border border-rose-200 rounded-lg p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-rose-700">For Resubmission</p>
                <p class="mt-1 text-2xl font-bold text-rose-900">{{ number_format((int) ($employeeLeaveSummary['resubmission_requests'] ?? 0)) }}</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-blue-700">Leave Credits Remaining</p>
                <p class="mt-1 text-2xl font-bold text-blue-900">{{ number_format((float) ($employeeLeaveSummary['leave_credits_remaining'] ?? 0), 2) }}</p>
                <p class="text-[11px] mt-1 text-blue-700">
                    {{ (int) ($employeeLeaveSummary['leave_credits_year'] ?? now()->year) }} pool:
                    <span class="tabular-nums">{{ number_format((float) ($employeeLeaveSummary['leave_credits_used'] ?? 0), 2) }}</span>
                    day(s) used
                    <span class="text-blue-600">·</span>
                    <span class="tabular-nums">{{ number_format((float) ($employeeLeaveSummary['leave_credits_allowance'] ?? 0), 2) }}</span>
                    day(s) allowance
                </p>
                <p class="text-[11px] mt-0.5 text-blue-700">Usable for Vacation Leave / Sick Leave (approved requests this year)</p>
            </div>
            <div class="bg-violet-50 border border-violet-200 rounded-lg p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-violet-700">Overtime Balance</p>
                <p class="mt-1 text-2xl font-bold text-violet-900 tabular-nums">{{ $employeeLeaveSummary['overtime_balance_formatted'] ?? '00:00' }}</p>
                <p class="text-[11px] mt-1 text-violet-700">Available hours that can be used for Offset</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Leave Request Status Breakdown</h3>
                <div class="h-60">
                    <canvas id="employeeLeaveStatusChart"></canvas>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Leave Requests Filed (Last 6 Months)</h3>
                <div class="h-60">
                    <canvas id="employeeMonthlyLeaveChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(auth()->user()->role === 'employee' && isset($employeeResubmissionRequests) && $employeeResubmissionRequests->isNotEmpty())
    <div class="p-4 pt-0 flex-shrink-0">
        <div class="bg-rose-50 border border-rose-200 rounded-lg p-4" role="status">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-rose-900">Leave Requests For Resubmission</h3>
                    <p class="mt-1 text-xs text-rose-700">
                        You have {{ $employeeResubmissionRequests->count() }} request{{ $employeeResubmissionRequests->count() > 1 ? 's' : '' }} that need updates and resubmission.
                    </p>
                </div>
                <a href="{{ url('/leave-requests') }}"
                   class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium border border-rose-300 text-rose-700 bg-white hover:bg-rose-100">
                    Open Leave Requests
                </a>
            </div>
            <div class="mt-3 space-y-2">
                @foreach($employeeResubmissionRequests as $resubReq)
                    <div class="flex items-center justify-between bg-white/80 border border-rose-100 rounded-md px-3 py-2">
                        <div>
                            <p class="text-xs font-semibold text-gray-800">{{ $resubReq->type_label ?? ucfirst(str_replace('_', ' ', (string) $resubReq->type)) }}</p>
                            <p class="text-[11px] text-gray-600">
                                Updated {{ optional($resubReq->updated_at)->format('M j, Y g:i A') ?? '—' }}
                            </p>
                        </div>
                        <a href="{{ route('user.leave-requests.edit', $resubReq) }}"
                           class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-medium text-white bg-rose-600 hover:bg-rose-700">
                            Resubmit
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    @if(($canViewAssignedQuizzes ?? auth()->user()->canViewAssignedQuizzes()))
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3 lg:gap-4 flex-shrink-0 p-4">
        <!-- Total Quizzes -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-3 sm:p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-4 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Quizzes</dt>
                            <dd class="text-lg sm:text-xl font-semibold text-gray-900">{{ $totalQuizzes }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Quizzes -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-3 sm:p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-4 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                            <dd class="text-lg sm:text-xl font-semibold text-gray-900">{{ $completedQuizzes }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Quizzes -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 sm:col-span-2 lg:col-span-1">
            <div class="p-3 sm:p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-4 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                            <dd class="text-lg sm:text-xl font-semibold text-gray-900">{{ $pendingQuizzes }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Available Quizzes -->
    @if(($canViewAssignedQuizzes ?? auth()->user()->canViewAssignedQuizzes()))
    <div class="bg-white shadow-sm border-t border-gray-200 overflow-visible flex flex-col">

        @if($allQuizzes->count() > 0)
            <div class="overflow-x-auto overflow-y-visible">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden sm:inline">Quiz ID</span>
                                <span class="sm:hidden">ID</span>
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[200px]">
                                Quiz
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden md:inline">Topic</span>
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden lg:inline">Questions</span>
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden md:inline">Status</span>
                            </th>
                            <th scope="col" class="relative px-3 sm:px-4 lg:px-6 py-2 w-12">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($allQuizzes as $quiz)
                            @php
                                $assignment = $quiz->assignments->first();
                                $isAssigned = $assignment !== null;
                                $isCompleted = $assignment ? $assignment->is_completed : false;
                                $status = $assignment ? $assignment->status : 'available';
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ str_pad($quiz->id, 4, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-3 sm:px-4 lg:px-6 py-3">
                                    <div class="text-sm font-medium text-gray-900">{{ Str::limit($quiz->title, 40) }}</div>
                                    @if($quiz->description)
                                        <div class="text-sm text-gray-500 truncate max-w-xs">{{ Str::limit($quiz->description, 60) }}</div>
                                    @endif
                                    <!-- Mobile: Show topic and status below title -->
                                    <div class="sm:hidden mt-2 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $quiz->topic }}
                                        </span>
                                        @if($isAssigned)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $assignment->getStatusBadgeClass() }}">
                                                {{ $assignment->getStatusText() }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Available
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap hidden md:table-cell">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $quiz->topic }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
                                    {{ $quiz->total_questions }}
                                </td>
                                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap hidden md:table-cell">
                                    @if($isAssigned)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $assignment->getStatusBadgeClass() }}">
                                            {{ $assignment->getStatusText() }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Available
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-4 lg:px-6 py-3 whitespace-nowrap text-right text-sm font-medium w-12">
                                    <div class="relative inline-block text-left" x-data="{ open: false }">
                                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 rounded-full p-1">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                            </svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20">
                                            <div class="py-1">
                                                @if($isAssigned && $isCompleted)
                                                    <a href="{{ url('/quizzes/' . $quiz->id . '/result') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        View Result
                                                    </a>
                                                @elseif($isAssigned && $assignment->status === 'in_progress')
                                                    <a href="{{ url('/quizzes/' . $quiz->id . '/take') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        Continue Quiz
                                                    </a>
                                                @elseif($isAssigned && $assignment->status === 'cancelled')
                                                    <a href="{{ url('/quizzes/' . $quiz->id . '/take') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                        </svg>
                                                        Restart Quiz
                                                    </a>
                                                @elseif($isAssigned)
                                                    <a href="{{ url('/quizzes/' . $quiz->id . '/take') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z"></path>
                                                        </svg>
                                                        Take Quiz
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 px-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No quizzes assigned</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if(auth()->user()->isApplicant())
                            You do not have any quizzes assigned yet. Your administrator will assign internship quizzes to you; they will appear here when ready.
                        @else
                            You do not have any active quizzes assigned yet. Use Enter Quiz Code if you received a code from your administrator.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="application/json" id="student-training-charts-data">
{!! json_encode($studentTrainingCharts ?? [
    'progress' => ['labels' => [], 'values' => []],
    'monthly' => ['labels' => [], 'values' => []],
]) !!}
</script>
<script type="application/json" id="employee-leave-charts-data">
{!! json_encode($employeeLeaveCharts ?? [
    'status' => ['labels' => [], 'values' => []],
    'monthly' => ['labels' => [], 'values' => []],
]) !!}
</script>

<!-- Quiz Code Modal -->
<div id="quizCodeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 sm:top-20 mx-auto p-4 sm:p-5 border w-11/12 sm:w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base sm:text-lg font-medium text-gray-900">Enter Quiz Code</h3>
                <button onclick="closeQuizCodeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

                <form id="quizCodeForm" method="POST" action="{{ url('/quizzes/validate-code') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="quiz_code" class="block text-sm font-medium text-gray-700 mb-2">
                            Quiz Code
                        </label>
                        <input type="text"
                               id="quiz_code"
                               name="quiz_code"
                               required
                               maxlength="20"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
                               placeholder="Enter the quiz code here">
                        <div id="quiz_code_error" class="mt-1 text-xs sm:text-sm text-red-600 hidden"></div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                        <button type="button"
                                onclick="closeQuizCodeModal()"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </button>
                        <button type="submit"
                                id="submitQuizCode"
                                class="w-full sm:w-auto px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="submitText">Start Quiz</span>
                            <span id="loadingText" class="hidden">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Validating...
                            </span>
                        </button>
                    </div>
                </form>
        </div>
    </div>
</div>

<script>
function openQuizCodeModal() {
    document.getElementById('quizCodeModal').classList.remove('hidden');
    const quizCodeInput = document.getElementById('quiz_code');
    quizCodeInput.value = '';
    quizCodeInput.focus();
}

function closeQuizCodeModal() {
    document.getElementById('quizCodeModal').classList.add('hidden');
    document.getElementById('quiz_code').value = '';
}

// Close modal when clicking outside
document.getElementById('quizCodeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeQuizCodeModal();
    }
});

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQuizCodeModal();
        }
    });

    // Handle quiz code form submission with AJAX
    document.getElementById('quizCodeForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const submitButton = document.getElementById('submitQuizCode');
        const submitText = document.getElementById('submitText');
        const loadingText = document.getElementById('loadingText');
        const errorDiv = document.getElementById('quiz_code_error');
        const quizCodeInput = document.getElementById('quiz_code');

        // Clear previous errors
        errorDiv.classList.add('hidden');
        quizCodeInput.classList.remove('border-red-500');

        // Show loading state
        submitButton.disabled = true;
        submitText.classList.add('hidden');
        loadingText.classList.remove('hidden');

        // Prepare form data
        const formData = new FormData(form);

        // Add AJAX header
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success toast
                ToastNotification.success(data.message);

                // Close modal
                closeQuizCodeModal();

                // Redirect to quiz
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 1000);
            } else {
                // Show error toast
                ToastNotification.error(data.message);

                // Show field error
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('hidden');
                quizCodeInput.classList.add('border-red-500');
                quizCodeInput.focus();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ToastNotification.error('An error occurred. Please try again.');
        })
        .finally(() => {
            // Reset button state
            submitButton.disabled = false;
            submitText.classList.remove('hidden');
            loadingText.classList.add('hidden');
        });
    });

</script>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartNode = document.getElementById('student-training-charts-data');
    const studentChartData = chartNode ? JSON.parse(chartNode.textContent) : {
        progress: { labels: [], values: [] },
        monthly: { labels: [], values: [] },
    };

    const commonBarOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#e5e7eb' },
            },
            x: {
                grid: { display: false },
            },
        },
    };

    const progressCanvas = document.getElementById('studentTrainingProgressChart');
    if (progressCanvas && studentChartData.progress?.labels?.length) {
        new Chart(progressCanvas, {
            type: 'bar',
            data: {
                labels: studentChartData.progress.labels,
                datasets: [{
                    data: studentChartData.progress.values,
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b'],
                    borderRadius: 6,
                    maxBarThickness: 54,
                }],
            },
            options: commonBarOptions,
        });
    }

    const monthlyCanvas = document.getElementById('studentMonthlyHoursChart');
    if (monthlyCanvas && studentChartData.monthly?.labels?.length) {
        new Chart(monthlyCanvas, {
            type: 'bar',
            data: {
                labels: studentChartData.monthly.labels,
                datasets: [{
                    data: studentChartData.monthly.values,
                    backgroundColor: '#3b82f6',
                    borderRadius: 6,
                    maxBarThickness: 36,
                }],
            },
            options: commonBarOptions,
        });
    }

    const employeeChartNode = document.getElementById('employee-leave-charts-data');
    const employeeChartData = employeeChartNode ? JSON.parse(employeeChartNode.textContent) : {
        status: { labels: [], values: [] },
        monthly: { labels: [], values: [] },
    };

    const employeeStatusCanvas = document.getElementById('employeeLeaveStatusChart');
    if (employeeStatusCanvas && employeeChartData.status?.labels?.length) {
        new Chart(employeeStatusCanvas, {
            type: 'doughnut',
            data: {
                labels: employeeChartData.status.labels,
                datasets: [{
                    data: employeeChartData.status.values,
                    backgroundColor: ['#f59e0b', '#10b981', '#ef4444', '#f43f5e'],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });
    }

    const employeeMonthlyCanvas = document.getElementById('employeeMonthlyLeaveChart');
    if (employeeMonthlyCanvas && employeeChartData.monthly?.labels?.length) {
        new Chart(employeeMonthlyCanvas, {
            type: 'bar',
            data: {
                labels: employeeChartData.monthly.labels,
                datasets: [{
                    data: employeeChartData.monthly.values,
                    backgroundColor: '#6366f1',
                    borderRadius: 6,
                    maxBarThickness: 36,
                }],
            },
            options: commonBarOptions,
        });
    }

    const searchInput = document.getElementById('search-input');
    const tableRows = document.querySelectorAll('tbody tr');

    if (!searchInput || !tableRows.length) {
        return;
    }

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            const quizTitle = row.querySelector('td:nth-child(2) .text-sm.font-medium').textContent.toLowerCase();
            const quizDescription = row.querySelector('td:nth-child(2) .text-sm.text-gray-500')?.textContent.toLowerCase() || '';

            // Get topic from either the dedicated column or mobile view
            const topicElement = row.querySelector('td:nth-child(3) span') || row.querySelector('td:nth-child(2) .sm\\:hidden span');
            const topic = topicElement?.textContent.toLowerCase() || '';

            // Get creator (only visible on large screens)
            const creatorElement = row.querySelector('td:nth-child(4)');
            const creator = creatorElement?.textContent.toLowerCase() || '';

            // Get status from either the dedicated column or mobile view
            const statusElement = row.querySelector('td:nth-child(6) span') || row.querySelector('td:nth-child(2) .sm\\:hidden span:last-child');
            const status = statusElement?.textContent.toLowerCase() || '';

            if (quizTitle.includes(searchTerm) || quizDescription.includes(searchTerm) ||
                topic.includes(searchTerm) || creator.includes(searchTerm) || status.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>
