@extends('layouts.user')

@section('page-title', 'Daily Time Record')

@section('content')
<x-user-page-shell fluid>
    <x-user-page-header title="Daily Time Record (DTR)" subtitle="View attendance, filter by date, and manage time requests.">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </x-slot:icon>
        <x-slot:actions>
            <x-user-header-btn-secondary href="{{ route('user.dtr.export-pdf', request()->only(['date_from', 'date_to', 'status'])) }}">
                <svg class="h-4 w-4 shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export PDF
            </x-user-header-btn-secondary>
        </x-slot:actions>
    </x-user-page-header>

    @if(auth()->user()->role === 'student' && empty($canStudentRecordAttendance))
        @php
            $ndaTone = ($studentNda && $studentNda->isRejected()) ? 'danger' : (($studentNda && $studentNda->isPendingApproval()) ? 'warning' : 'info');
        @endphp
        <x-user-notice :tone="$ndaTone" title="NDA required before recording attendance">
            <x-slot:icon>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </x-slot:icon>
            @if(! $studentNda || (! $studentNda->hasSignedUpload() && ! $studentNda->isRejected()))
                <p>Upload your signed Non-Disclosure Agreement (NDA) and wait for administrator approval before you can use Record Attendance.</p>
            @elseif($studentNda->isPendingApproval())
                <p>Your signed NDA was uploaded on {{ $studentNda->signed_uploaded_at?->format('M d, Y g:i A') }} and is waiting for administrator review.</p>
            @elseif($studentNda->isRejected())
                <p>Your NDA was rejected and the signed file was removed. Please upload a corrected signed PDF and wait for approval.</p>
                @if($studentNda->review_notes)
                    <p class="mt-2 font-medium">Admin note: {{ $studentNda->review_notes }}</p>
                @endif
            @endif
            <x-slot:actions>
                <a href="{{ route('user.nda.index') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    Go to NDA
                </a>
            </x-slot:actions>
        </x-user-notice>
    @endif

    <x-user-dashboard-section title="Filter records" description="Choose a date range and status to refine your DTR." eyebrow="Refine">
        <x-user-filter-bar :action="route('user.dtr.index')" class="-mt-1">
            <x-user-filter-field label="Date from" for="date_from">
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}">
            </x-user-filter-field>
            <x-user-filter-field label="Date to" for="date_to">
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}">
            </x-user-filter-field>
            <x-user-filter-field label="Status" for="status">
                <select name="status" id="status">
                    <option value="">All statuses</option>
                    <option value="present" @selected(request('status') == 'present')>Present</option>
                    <option value="absent" @selected(request('status') == 'absent')>Absent</option>
                    <option value="holiday" @selected(request('status') == 'holiday')>Holiday</option>
                    <option value="late" @selected(request('status') == 'late')>Late</option>
                    <option value="half_day" @selected(request('status') == 'half_day')>Half day</option>
                    <option value="on_leave" @selected(request('status') == 'on_leave')>On leave</option>
                    <option value="travel" @selected(request('status') == 'travel')>Travel</option>
                </select>
            </x-user-filter-field>
            <x-user-filter-field label="Filter actions" for="dtr-filter-apply" col="actions" class="[&_.user-filter-bar__label]:sr-only">
                <x-user-btn-primary tag="button" type="submit" id="dtr-filter-apply" class="w-full sm:w-auto">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Apply filters
                </x-user-btn-primary>
                <x-user-btn-secondary href="{{ route('user.dtr.index') }}" class="w-full sm:w-auto">Reset</x-user-btn-secondary>
            </x-user-filter-field>
        </x-user-filter-bar>
    </x-user-dashboard-section>

    @if(auth()->user()->role === 'student' && !empty($incompleteOvertimeReminderItems))
    <x-user-notice tone="warning" title="Incomplete Additional Time details">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </x-slot:icon>
        <p>
            <strong>{{ count($incompleteOvertimeReminderItems) }}</strong> Additional Time request(s) from Record Attendance need a reason and Hubstaff screenshot(s).
            Complete them in <a href="{{ route('user.leave-requests.index') }}" class="font-semibold underline">Leave Requests</a>
            within {{ \App\Services\LeaveRequestIncompleteAttendanceOvertimeService::RESPONSE_WINDOW_DAYS }} days or they will be automatically rejected.
        </p>
    </x-user-notice>
    @endif

    @if(auth()->user()->role === 'student' && isset($pendingTimeRequests) && $pendingTimeRequests->count() > 0)
    <x-user-dashboard-section title="Pending time requests" :description="'Regular hours awaiting approval ('.$pendingTimeRequests->count().'). Additional Time is under Leave Requests.'">
        <x-user-panel title="Awaiting approval" :padding="false" embedded class="overflow-hidden border-0 shadow-none ring-0">
        <x-responsive-data-panel class="rounded-lg border border-slate-200/90 shadow-none">
            <x-slot:mobile>
                @foreach($pendingTimeRequests as $request)
                    <div class="mobile-card">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="mobile-card-title">{{ $request->date->format('M d, Y') }}</p>
                                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $request->isOvertime() ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $request->request_type_label }}
                                </span>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $request->getStatusBadgeClass() }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </div>
                        <dl class="mobile-card-kv">
                            <dt>Hours</dt>
                            <dd class="font-mono">{{ $request->formatted_time }}</dd>
                            <dt>Submitted</dt>
                            <dd>{{ $request->created_at->format('M d, Y') }}</dd>
                        </dl>
                        @if($request->remarks)
                            <p class="mt-2 text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($request->remarks, 80) }}</p>
                        @endif
                        @if($request->status === 'pending')
                            <div class="mobile-card-actions">
                                <form method="POST" action="{{ route('user.dtr-time-requests.destroy', $request) }}"
                                      onsubmit="return confirm('Discard this pending time request? The linked Additional Time leave request for the same day will also be removed.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800 touch-manipulation py-1">
                                        Discard
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </x-slot:mobile>
            <x-slot:desktop>
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50/95">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Hours</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Remarks</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Submitted</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($pendingTimeRequests as $request)
                    <tr class="transition-colors hover:bg-slate-50/80">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $request->date->format('M d, Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $request->isOvertime() ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $request->request_type_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-mono">
                            {{ $request->formatted_time }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $request->getStatusBadgeClass() }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $request->remarks ? \Illuminate\Support\Str::limit($request->remarks, 50) : '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $request->created_at->format('M d, Y g:i A') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            @if($request->status === 'pending')
                                <form method="POST" action="{{ route('user.dtr-time-requests.destroy', $request) }}"
                                      onsubmit="return confirm('Discard this pending time request? The linked Additional Time leave request for the same day will also be removed.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-rose-600 hover:text-rose-800">
                                        Discard
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </x-slot:desktop>
        </x-responsive-data-panel>
        </x-user-panel>
    </x-user-dashboard-section>
    @endif

    <x-user-dashboard-section title="Time records" description="Grouped by year, month, and week. Expand a section to view daily entries.">
        @if(auth()->user()->role === 'student')
            <x-slot:actions>
                @if(! empty($canStudentRecordAttendance))
                    <button type="button" onclick="openRecordAttendanceModal()"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Record attendance
                    </button>
                @else
                    <button type="button" disabled title="Upload and get your NDA approved first"
                            class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-500">
                        Record attendance
                    </button>
                @endif
            </x-slot:actions>
        @endif

        <x-user-kpi-grid columns="grid-cols-1 divide-y sm:grid-cols-1 sm:divide-y-0">
            <x-user-kpi-cell label="Records in current view" :value="number_format($totalRecords)" />
        </x-user-kpi-grid>

        <div class="overflow-hidden rounded-lg border border-slate-200/90 bg-white" id="dtr-groups-root">
            @forelse($groupedDtrsByYear as $yearKey => $year)
                <div class="border-b border-slate-200 last:border-b-0" data-year-group>
                    <button type="button"
                            class="flex w-full items-center justify-between bg-slate-50 px-5 py-3.5 text-left transition hover:bg-slate-100/80 sm:px-6"
                            data-toggle="year">
                        <h2 class="text-base font-semibold text-slate-900">{{ $year['label'] }}</h2>
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-sm font-medium text-slate-600" data-year-chevron aria-hidden="true">+</span>
                    </button>

                    <div class="hidden border-t border-slate-200 bg-white" data-year-content style="display: none;">
                        @foreach($year['months'] as $monthKey => $month)
                <div class="border-b border-slate-100" data-month-group>
                    <button type="button"
                            class="flex w-full items-center justify-between bg-white py-3 pl-8 pr-5 text-left transition hover:bg-slate-50 sm:pl-10 sm:pr-6"
                            data-toggle="month">
                        <h3 class="text-sm font-semibold text-slate-800">{{ $month['label'] }}</h3>
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded border border-slate-200 bg-slate-50 text-xs font-medium text-slate-600" data-month-chevron aria-hidden="true">+</span>
                    </button>

                    <div class="hidden border-t border-slate-100" data-month-content style="display: none;">
                        @foreach($month['weeks'] as $weekKey => $week)
                            <div class="border-b border-slate-100 last:border-b-0" data-week-group>
                                <button type="button"
                                        class="flex w-full items-center justify-between bg-slate-50/60 py-2.5 pl-10 pr-5 text-left transition hover:bg-slate-100/60 sm:pl-12 sm:pr-6"
                                        data-toggle="week">
                                    <h4 class="text-sm font-medium text-slate-700">{{ $week['label'] }}</h4>
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded border border-slate-200 bg-white text-xs font-medium text-slate-600" data-week-chevron aria-hidden="true">+</span>
                                </button>

                                <div class="hidden" data-week-content style="display: none;">
                                    <div class="md:hidden mobile-card-list px-2 py-1 bg-white">
                                        @foreach($week['records'] as $record)
                                            @include('user.dtr.partials.record-card', ['record' => $record])
                                        @endforeach
                                    </div>
                                    <div class="hidden md:block overflow-x-auto mobile-table-scroll">
                                    <table class="min-w-full divide-y divide-slate-100">
                                        <thead class="bg-slate-50/95">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Date</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Hubstaff</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Leave/offset</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Added time</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Total</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Status</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            @foreach($week['records'] as $dtr)
                                                @php
                                                    // Determine status first
                                                    if ($dtr->status === 'absent') {
                                                        $statusLabel = 'Absent';
                                                        $statusClass = 'bg-red-100 text-red-800';
                                                        $isCompleted = false;
                                                    } elseif ($dtr->status === 'holiday') {
                                                        $statusLabel = 'HOLIDAY';
                                                        $statusClass = 'bg-sky-100 text-sky-800';
                                                        $isCompleted = false;
                                                    } elseif ($dtr->status === 'on_leave') {
                                                        $statusLabel = 'Leave';
                                                        $statusClass = 'bg-purple-100 text-purple-800';
                                                        $isCompleted = true; // Show checkmark for leave entries
                                                    } elseif ($dtr->status === 'travel') {
                                                        $statusLabel = 'TRAVEL';
                                                        $statusClass = 'bg-blue-100 text-blue-800';
                                                        $isCompleted = true; // Show checkmark for travel entries
                                                    } else {
                                                        $totalMinutesForStatus = (int) round(($dtr->total_hours ?? 0) * 60);
                                                        if ($totalMinutesForStatus < 480) {
                                                            $statusLabel = 'Under Time';
                                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                                            $isCompleted = false;
                                                        } else {
                                                            $statusLabel = 'Completed';
                                                            $statusClass = 'bg-green-100 text-green-800';
                                                            $isCompleted = true;
                                                        }
                                                    }
                                                    
                                                    // Calculate time values
                                                    $addedHours = \App\Support\DtrAddedTimeFromRemarks::effectiveAddedHours($dtr);
                                                    $leaveHours = \App\Support\DtrEmployeeAttendance::displayedLeaveHours($dtr);
                                                    $workedHours = \App\Support\DtrEmployeeAttendance::displayedHubstaffHours($dtr);
                                                    $workedMinutes = (int) round($workedHours * 60);
                                                    $leaveMinutes = (int) round($leaveHours * 60);
                                                    $leaveH = intdiv($leaveMinutes, 60);
                                                    $leaveM = $leaveMinutes % 60;
                                                    $leaveFormatted = sprintf('%02d:%02d', $leaveH, $leaveM);
                                                    $workedH = intdiv($workedMinutes, 60);
                                                    $workedM = $workedMinutes % 60;
                                                    $workedFormatted = sprintf('%02d:%02d', $workedH, $workedM);
                                                    $workedOver8Hours = $workedMinutes > 480; // 8 hours = 480 minutes
                                                    
                                                    $extraMinutes = (int) round($addedHours * 60);
                                                    
                                                    $totalFormatted = \App\Support\DtrEmployeeAttendance::formatTotalHoursColumn($dtr);
                                                    $totalMinutes = (int) round(\App\Support\DtrEmployeeAttendance::displayedTotalHoursForColumn($dtr) * 60);
                                                    $totalOver8Hours = $totalMinutes > 480; // 8 hours = 480 minutes
                                                @endphp
                                                <tr class="transition-colors hover:bg-slate-50/80">
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">{{ $dtr->date->format('M d, Y') }}</div>
                                                        <div class="text-xs text-gray-500">{{ $dtr->date->format('l') }}</div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $workedMinutes > 0 ? $workedFormatted : '00:00' }}
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            $leaveTypeLabel = \App\Support\DtrEmployeeAttendance::displayedLeaveTypeLabel($dtr);
                                                        @endphp
                                                        <div class="text-sm font-medium text-teal-700">
                                                            {{ $leaveMinutes > 0 ? $leaveFormatted : '00:00' }}
                                                        </div>
                                                        @if($leaveTypeLabel && $leaveMinutes > 0)
                                                            <div class="text-xs text-teal-600">{{ $leaveTypeLabel }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            @if($isCompleted && $dtr->status !== 'on_leave' && $dtr->status !== 'travel')
                                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                                </svg>
                                                            @elseif($dtr->status === 'absent' || $dtr->status === 'holiday')
                                                                00:00
                                                            @elseif($extraMinutes > 0)
                                                                @php
                                                                    $extraH = intdiv($extraMinutes, 60);
                                                                    $extraM = $extraMinutes % 60;
                                                                @endphp
                                                                {{ sprintf('%02d:%02d', $extraH, $extraM) }}
                                                            @else
                                                                <span class="text-gray-400">-</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        @if(auth()->user()?->role === 'employee')
                                                            @php
                                                                $dailyDeficitFormatted = \App\Support\DtrEmployeeAttendance::formatDailyDeficitForColumn($dtr);
                                                            @endphp
                                                            <div class="leading-tight">
                                                                <div class="text-sm font-medium text-gray-900 tabular-nums">
                                                                    {{ $totalFormatted }}
                                                                </div>
                                                                @if($dailyDeficitFormatted)
                                                                    @php $dailyDeficitMinutes = \App\Support\DtrEmployeeAttendance::dailyColumnDeficitMinutes($dtr); @endphp
                                                                    <div class="text-[8px] font-normal text-red-500 tabular-nums mt-0.5" title="{{ $dailyDeficitMinutes }} minutes below 08:00">Deficit {{ $dailyDeficitFormatted }}</div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="text-sm font-medium text-gray-900">
                                                                @if($isCompleted)
                                                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                                    </svg>
                                                                @else
                                                                    {{ $totalMinutes > 0 ? $totalFormatted : '00:00' }}
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                            {{ $statusLabel }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4">
                                                        <div class="text-sm text-gray-500 max-w-xs truncate" title="{{ $dtr->remarks }}">
                                                            {{ $dtr->remarks ?: '-' }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="border-t-2 border-slate-200 bg-slate-50">
                                            @php
                                                $weeklyTotalHours = 0;
                                                foreach ($week['records'] as $dtr) {
                                                    $weeklyTotalHours += ($dtr->total_hours ?? 0);
                                                }
                                                $weeklyTotalMinutes = (int) round($weeklyTotalHours * 60);
                                                $weeklyTotalH = intdiv($weeklyTotalMinutes, 60);
                                                $weeklyTotalM = $weeklyTotalMinutes % 60;
                                                $weeklyTotalFormatted = sprintf('%02d:%02d', $weeklyTotalH, $weeklyTotalM);

                                                $weeklyBaseMinutes = 40 * 60;
                                                $deficitMinutes = max(0, $weeklyBaseMinutes - $weeklyTotalMinutes);
                                                $deficitH = intdiv($deficitMinutes, 60);
                                                $deficitM = $deficitMinutes % 60;
                                                $deficitFormatted = sprintf('%02d:%02d', $deficitH, $deficitM);
                                                $isWeekComplete = $deficitMinutes == 0;
                                            @endphp
                                            <tr>
                                                <td colspan="3" class="px-3 sm:px-6 py-3 text-right text-sm font-bold text-gray-900">Weekly Total:</td>
                                                <td class="px-3 sm:px-6 py-3 text-sm font-bold text-gray-900">
                                                    @if($isWeekComplete)
                                                        <svg class="w-5 h-5 text-green-600 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @else
                                                        {{ $weeklyTotalFormatted }}
                                                    @endif
                                                </td>
                                                <td colspan="2" class="px-3 sm:px-6 py-3 text-sm text-gray-600">
                                                    @if($deficitMinutes > 0)
                                                        Deficit: {{ $deficitFormatted }}
                                                    @else
                                                        <span class="text-green-600 font-semibold">Complete</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    </div>
                                    @php
                                        $weeklyTotalHours = 0;
                                        foreach ($week['records'] as $dtr) {
                                            $weeklyTotalHours += ($dtr->total_hours ?? 0);
                                        }
                                        $weeklyTotalMinutes = (int) round($weeklyTotalHours * 60);
                                        $weeklyTotalH = intdiv($weeklyTotalMinutes, 60);
                                        $weeklyTotalM = $weeklyTotalMinutes % 60;
                                        $weeklyTotalFormatted = sprintf('%02d:%02d', $weeklyTotalH, $weeklyTotalM);
                                        $weeklyBaseMinutes = 40 * 60;
                                        $deficitMinutes = max(0, $weeklyBaseMinutes - $weeklyTotalMinutes);
                                        $deficitH = intdiv($deficitMinutes, 60);
                                        $deficitM = $deficitMinutes % 60;
                                        $deficitFormatted = sprintf('%02d:%02d', $deficitH, $deficitM);
                                    @endphp
                                    <div class="mx-3 mb-3 mt-1 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm md:hidden">
                                        <div class="flex justify-between font-semibold text-gray-900">
                                            <span>Weekly total</span>
                                            <span>{{ $deficitMinutes === 0 ? 'Complete' : $weeklyTotalFormatted }}</span>
                                        </div>
                                        @if($deficitMinutes > 0)
                                            <p class="text-xs text-gray-600 mt-1">Deficit: {{ $deficitFormatted }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-14 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-slate-200">
                        <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-slate-900">No DTR records found</h3>
                    <p class="mt-2 text-sm text-slate-600">Adjust your filters or wait for records to be posted.</p>
                </div>
            @endforelse
        </div>
    </x-user-dashboard-section>
</x-user-page-shell>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-toggle="year"]').forEach(button => {
        button.addEventListener('click', function() {
            const yearGroup = this.closest('[data-year-group]');
            const content = yearGroup.querySelector('[data-year-content]');
            const chevron = yearGroup.querySelector('[data-year-chevron]');

            if (!content) return;

            const isHidden = content.classList.contains('hidden');
            if (isHidden) {
                content.classList.remove('hidden');
                content.style.display = '';
                if (chevron) chevron.textContent = '-';
            } else {
                content.classList.add('hidden');
                content.style.display = 'none';
                if (chevron) chevron.textContent = '+';
            }
        });
    });

    // Month toggle functionality
    document.querySelectorAll('[data-toggle="month"]').forEach(button => {
        button.addEventListener('click', function() {
            const monthGroup = this.closest('[data-month-group]');
            const content = monthGroup.querySelector('[data-month-content]');
            const chevron = monthGroup.querySelector('[data-month-chevron]');

            if (!content) return;

            const isHidden = content.classList.contains('hidden');
            if (isHidden) {
                content.classList.remove('hidden');
                content.style.display = '';
                chevron.textContent = '-';
            } else {
                content.classList.add('hidden');
                content.style.display = 'none';
                chevron.textContent = '+';
            }
        });
    });

    // Week toggle functionality
    document.querySelectorAll('[data-toggle="week"]').forEach(button => {
        button.addEventListener('click', function() {
            const weekGroup = this.closest('[data-week-group]');
            const content = weekGroup.querySelector('[data-week-content]');
            const chevron = weekGroup.querySelector('[data-week-chevron]');

            if (!content) return;

            const isHidden = content.classList.contains('hidden');
            if (isHidden) {
                content.classList.remove('hidden');
                content.style.display = '';
                chevron.textContent = '-';
            } else {
                content.classList.add('hidden');
                content.style.display = 'none';
                chevron.textContent = '+';
            }
        });
    });
});
</script>

@if(auth()->user()->role === 'student')
<!-- Record Attendance Modal -->
<div id="record-attendance-modal" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-slate-900/50 backdrop-blur-[2px]">
    <div class="relative top-10 mx-auto max-h-[90vh] w-11/12 overflow-y-auto rounded-xl border border-slate-200 bg-white p-5 shadow-xl md:w-3/4 lg:w-2/3 sm:p-6">
        <div class="flex items-start gap-3 mb-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0 flex items-start justify-between gap-2">
                <h3 class="text-lg font-bold text-gray-900 pt-1.5">Record Attendance</h3>
            <button onclick="closeRecordAttendanceModal()" class="text-gray-400 hover:text-gray-600 flex-shrink-0" aria-label="Close">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            </div>
        </div>

        <form action="{{ url('/dtr-time-requests') }}" method="POST" id="record-attendance-form">
            @csrf
            <input type="hidden" name="filter_date_from" value="{{ request('date_from') }}">
            <input type="hidden" name="filter_date_to" value="{{ request('date_to') }}">

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="attendance_date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" 
                               name="date_from" 
                               id="attendance_date_from" 
                               required
                               max="{{ date('Y-m-d') }}"
                               min="{{ request('date_from') ?: '' }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Cannot select future dates</p>
                        @error('date_from')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="attendance_date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" 
                               name="date_to" 
                               id="attendance_date_to" 
                               required
                               max="{{ date('Y-m-d') }}"
                               min="{{ request('date_from') ?: '' }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Date range must be within the selected filter range and cannot be in the future</p>
                        @error('date_to')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hours per Day</label>
                    <div id="days-container" class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3">
                        <p class="text-sm text-gray-500 text-center">Select a date range to see days</p>
                    </div>
                    @error('days')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @if(is_array($message))
                            <ul class="mt-1 list-disc list-inside text-sm text-red-600">
                                @foreach($message as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @enderror
                </div>

                <div>
                    <label for="attendance_remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks (Optional)</label>
                    <textarea name="remarks" 
                              id="attendance_remarks" 
                              rows="2"
                              maxlength="1000"
                              placeholder="Optional notes about this attendance filing"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    <p id="attendance_remarks_help" class="mt-1 text-xs text-gray-500">If you file Additional Time (over 08:00), you will complete the full Additional Time form under Leave Requests next.</p>
                    @error('remarks')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" 
                        onclick="closeRecordAttendanceModal()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel
                </button>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

@if(!empty($incompleteOvertimeReminderItems))
@php
    $incompleteOvertimeWindowDays = \App\Services\LeaveRequestIncompleteAttendanceOvertimeService::RESPONSE_WINDOW_DAYS;
@endphp
<!-- Incomplete Additional Time reminder (shown when opening DTR) -->
<div id="incomplete-overtime-reminder-modal" class="hidden fixed inset-0 bg-gray-900/50 z-[70] p-4 sm:p-6 flex items-center justify-center overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="incomplete-overtime-reminder-title">
    <div class="w-full max-w-2xl max-h-[min(90vh,720px)] flex flex-col p-6 sm:p-8 border border-orange-300 shadow-xl rounded-xl bg-white my-auto" onclick="event.stopPropagation()">
        <div class="flex items-start gap-4 mb-5 flex-shrink-0">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 id="incomplete-overtime-reminder-title" class="text-xl font-bold text-gray-900">Incomplete Additional Time details</h3>
                <p class="mt-2 text-sm sm:text-base text-gray-600 leading-relaxed">
                    You have {{ count($incompleteOvertimeReminderItems) }} Additional Time request(s) from Record Attendance that still need a <strong>reason</strong> and <strong>Hubstaff screenshot(s)</strong> (ClickUp links remain optional).
                </p>
                <p class="mt-2 text-sm font-medium text-red-700">
                    If not completed within {{ $incompleteOvertimeWindowDays }} days of filing, each request will be <strong>automatically rejected</strong>.
                </p>
            </div>
            <button type="button" onclick="closeIncompleteOvertimeReminderModal()" class="text-gray-400 hover:text-gray-600 flex-shrink-0" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <ul class="mb-5 flex-1 min-h-[8rem] max-h-[min(22rem,45vh)] overflow-y-auto space-y-0 rounded-lg border border-orange-200 bg-orange-50/60 p-3 sm:p-4">
            @foreach($incompleteOvertimeReminderItems as $item)
                <li class="border-b border-orange-100 last:border-0">
                    <div class="flex items-center gap-2 sm:gap-3 py-2.5 text-sm text-gray-800 min-w-0"
                         title="Reject by {{ $item['deadline_formatted'] }}{{ !empty($item['time_remaining_label']) ? ' — '.$item['time_remaining_label'].' remaining' : '' }}">
                        <span class="font-semibold text-gray-900 whitespace-nowrap shrink-0 inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-orange-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ $item['date_label_short'] ?? $item['date_label'] }}
                            @if(!empty($item['hours']))
                                <span class="font-mono font-medium text-gray-600">· {{ $item['hours'] }}</span>
                            @endif
                        </span>
                        <span class="text-gray-400 shrink-0" aria-hidden="true">|</span>
                        <span class="text-gray-600 whitespace-nowrap min-w-0 truncate">
                            Reject {{ $item['deadline_short'] ?? $item['deadline_formatted'] }}
                            @if(!empty($item['time_remaining_short']))
                                <span class="text-orange-800 font-medium">({{ $item['time_remaining_short'] }})</span>
                            @elseif(!empty($item['past_due']))
                                <span class="text-red-700 font-semibold">(overdue)</span>
                            @endif
                        </span>
                        <span class="text-gray-400 shrink-0 ml-auto" aria-hidden="true">|</span>
                        <a href="{{ $item['complete_url'] }}"
                           class="font-semibold text-orange-700 hover:text-orange-900 underline whitespace-nowrap shrink-0">
                            Complete details
                        </a>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 flex-shrink-0 pt-1">
            <button type="button"
                    onclick="closeIncompleteOvertimeReminderModal()"
                    class="inline-flex justify-center items-center px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Remind me later
            </button>
            <a href="{{ route('user.leave-requests.index') }}"
               class="inline-flex justify-center items-center px-5 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Go to Leave Requests
            </a>
        </div>
    </div>
</div>
@endif

<!-- Attendance Policy Prompt Modal -->
<div id="attendance-policy-modal" class="hidden fixed inset-0 bg-gray-900/40 z-[60] p-4 flex items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="attendance-policy-modal-title">
    <div class="w-full max-w-2xl p-5 sm:p-6 border border-amber-300 shadow-xl rounded-xl bg-amber-50" onclick="event.stopPropagation()">
        <div class="flex items-start gap-4 mb-4">
            <div id="attendance-policy-modal-icon" class="flex-shrink-0 w-11 h-11 rounded-full bg-amber-200 flex items-center justify-center text-amber-800" aria-hidden="true"></div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <h3 id="attendance-policy-modal-title" class="text-lg font-bold text-amber-900">Warning</h3>
                    <button type="button" onclick="closeAttendancePolicyModal(false)" class="text-amber-500 hover:text-amber-700 flex-shrink-0" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div id="attendance-policy-modal-body" class="text-sm text-amber-900 space-y-2 pl-0 sm:pl-[3.75rem] -mt-1"></div>
        <div class="mt-5 flex justify-end gap-3">
            <button type="button"
                    onclick="closeAttendancePolicyModal(false)"
                    class="inline-flex items-center px-4 py-2 border border-amber-300 rounded-lg text-amber-800 bg-white hover:bg-amber-100 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel
            </button>
            <button type="button"
                    onclick="closeAttendancePolicyModal(true)"
                    class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Continue
            </button>
        </div>
    </div>
</div>

@php
    $existingTimeRequestSlots = $existingTimeRequestSlots ?? [];
    $pendingTimeRequestByDate = $pendingTimeRequestByDate ?? [];
@endphp

<script id="existing-time-request-slots" type="application/json">{!! json_encode($existingTimeRequestSlots) !!}</script>
<script id="pending-time-request-by-date" type="application/json">{!! json_encode($pendingTimeRequestByDate) !!}</script>
<script>
const existingTimeRequestSlots = JSON.parse(
    document.getElementById('existing-time-request-slots')?.textContent || '{}'
);
const pendingTimeRequestByDate = JSON.parse(
    document.getElementById('pending-time-request-by-date')?.textContent || '{}'
);
const dtrBeyondStandardHoursLabel = 'Additional Time';

let attendancePolicyModalResolver = null;

function closeIncompleteOvertimeReminderModal() {
    const modal = document.getElementById('incomplete-overtime-reminder-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function openIncompleteOvertimeReminderModal() {
    const modal = document.getElementById('incomplete-overtime-reminder-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeAttendancePolicyModal(confirmed) {
    const modal = document.getElementById('attendance-policy-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
    if (attendancePolicyModalResolver) {
        attendancePolicyModalResolver(confirmed);
        attendancePolicyModalResolver = null;
    }
}

const attendancePolicyModalIcons = {
    'over-eight': '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    'under-eight': '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>',
    warning: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
};

const attendancePolicyBodyBullet = '<span class="shrink-0 w-4 text-center font-bold text-amber-800 leading-5" aria-hidden="true">•</span>';

const attendancePolicyBodyIcons = {
    question: '<svg class="w-4 h-4 shrink-0 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
};

function escapeAttendancePolicyHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatAttendancePolicyModalBody(message) {
    const lines = String(message).split('\n');
    const html = [];

    lines.forEach((line) => {
        const trimmed = line.trim();
        if (!trimmed) {
            return;
        }

        const escaped = escapeAttendancePolicyHtml(line);

        if (line.startsWith('• ')) {
            html.push(
                '<div class="flex items-start gap-2 rounded-md bg-amber-100/60 px-2 py-1.5">'
                + attendancePolicyBodyBullet
                + '<span>' + escapeAttendancePolicyHtml(line.slice(2)) + '</span></div>'
            );
            return;
        }

        if (trimmed === 'Do you want to continue?') {
            html.push(
                '<p class="mt-3 pt-2 border-t border-amber-200 font-semibold text-amber-950 flex items-center gap-2">'
                + attendancePolicyBodyIcons.question
                + '<span>' + escaped + '</span></p>'
            );
            return;
        }

        if (trimmed.includes('Pending Time Requests') || trimmed.includes('Leave Requests') || trimmed.includes('08:00')
            || trimmed.includes('Terms and Conditions') || trimmed.includes('violation')) {
            html.push(
                '<p class="flex items-start gap-2 text-amber-950">'
                + attendancePolicyBodyBullet
                + '<span>' + escaped + '</span></p>'
            );
            return;
        }

        html.push('<p class="font-medium text-amber-950">' + escaped + '</p>');
    });

    return html.join('');
}

function showAttendancePolicyModal(title, message, variant = 'warning') {
    const modal = document.getElementById('attendance-policy-modal');
    const titleEl = document.getElementById('attendance-policy-modal-title');
    const bodyEl = document.getElementById('attendance-policy-modal-body');
    const iconEl = document.getElementById('attendance-policy-modal-icon');
    if (!modal || !titleEl || !bodyEl) {
        return Promise.resolve(window.confirm(message));
    }

    titleEl.textContent = title;
    bodyEl.innerHTML = formatAttendancePolicyModalBody(message);
    if (iconEl) {
        iconEl.innerHTML = attendancePolicyModalIcons[variant] || attendancePolicyModalIcons.warning;
    }
    modal.classList.remove('hidden');

    return new Promise((resolve) => {
        attendancePolicyModalResolver = resolve;
    });
}

function openRecordAttendanceModal() {
    const modal = document.getElementById('record-attendance-modal');
    if (modal) {
        modal.classList.remove('hidden');
        updateDaysList();
    }
}

function closeRecordAttendanceModal() {
    const modal = document.getElementById('record-attendance-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.getElementById('record-attendance-form').reset();
        document.getElementById('days-container').innerHTML = '<p class="text-sm text-gray-500 text-center">Select a date range to see days</p>';
    }
}

function updateDaysList() {
    const dateFrom = document.getElementById('attendance_date_from').value;
    const dateTo = document.getElementById('attendance_date_to').value;
    const container = document.getElementById('days-container');
    
    if (!dateFrom || !dateTo) {
        container.innerHTML = '<p class="text-sm text-gray-500 text-center">Please select both date from and date to</p>';
        return;
    }
    
    // Parse dates using local timezone to avoid timezone issues
    const fromParts = dateFrom.split('-');
    const toParts = dateTo.split('-');
    const fromDate = new Date(parseInt(fromParts[0]), parseInt(fromParts[1]) - 1, parseInt(fromParts[2]));
    const toDate = new Date(parseInt(toParts[0]), parseInt(toParts[1]) - 1, parseInt(toParts[2]));
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // Normalize dates to compare only date part (ignore time)
    const fromDateOnly = new Date(fromDate.getFullYear(), fromDate.getMonth(), fromDate.getDate());
    const toDateOnly = new Date(toDate.getFullYear(), toDate.getMonth(), toDate.getDate());
    const todayDateOnly = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    
    if (fromDateOnly > toDateOnly) {
        container.innerHTML = '<p class="text-sm text-red-500 text-center">Date From must be before Date To</p>';
        return;
    }
    
    // Check if any date is in the future (using date-only comparison)
    if (fromDateOnly > todayDateOnly || toDateOnly > todayDateOnly) {
        container.innerHTML = '<p class="text-sm text-red-500 text-center">Cannot select future dates. Only past and today\'s dates are allowed.</p>';
        return;
    }
    
    // Get filter range
    const filterFrom = '{{ request("date_from") }}';
    const filterTo = '{{ request("date_to") }}';
    
    if (filterFrom && filterTo) {
        const filterFromParts = filterFrom.split('-');
        const filterToParts = filterTo.split('-');
        const filterFromDate = new Date(parseInt(filterFromParts[0]), parseInt(filterFromParts[1]) - 1, parseInt(filterFromParts[2]));
        const filterToDate = new Date(parseInt(filterToParts[0]), parseInt(filterToParts[1]) - 1, parseInt(filterToParts[2]));
        
        if (fromDateOnly < filterFromDate || toDateOnly > filterToDate) {
            container.innerHTML = '<p class="text-sm text-red-500 text-center">Date range must be within the selected filter range</p>';
            return;
        }
    }
    
    // Generate days list
    let html = '';
    const currentDate = new Date(fromDate.getTime());
    const endDate = new Date(toDate.getTime());
    let dayIndex = 0;
    
    while (currentDate <= endDate) {
        // Format date as YYYY-MM-DD (timezone-safe, using local date components)
        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        const day = String(currentDate.getDate()).padStart(2, '0');
        const dateStr = `${year}-${month}-${day}`;
        
        const dayName = currentDate.toLocaleDateString('en-US', { weekday: 'short' });
        const dayNum = currentDate.getDate();
        const monthName = currentDate.toLocaleDateString('en-US', { month: 'short' });
        const pendingDay = pendingTimeRequestByDate[dateStr] || null;
        const prefillTime = pendingDay ? pendingDay.formatted_total : '';
        
        html += `
            <div class="day-row flex flex-col gap-2 p-2 bg-gray-50 rounded border border-gray-200" data-date="${dateStr}" data-has-pending="${pendingDay ? '1' : '0'}">
                <div class="flex items-center gap-3">
                <div class="w-24 text-sm font-medium text-gray-700">
                    ${dayName}, ${monthName} ${dayNum}
                </div>
                <input type="date" 
                       name="days[${dayIndex}][date]" 
                       value="${dateStr}" 
                       hidden>
                <div class="flex-1 space-y-1.5">
                    <div class="relative">
                        <input type="text" 
                               name="days[${dayIndex}][time]" 
                               id="time-input-${dayIndex}"
                               value="${prefillTime}"
                               placeholder="08:00"
                               class="time-input w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-center font-mono"
                               style="text-align: center;"
                               oninput="formatTimeInput(this)"
                               onblur="formatTimeOnBlur(this)">
                        <button type="button" 
                                onclick="clearTimeField('time-input-${dayIndex}')"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                                title="Clear time">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                </div>
            </div>
        `;

        const existingSlots = existingTimeRequestSlots[dateStr] || [];
        if (existingSlots.includes('regular') && existingSlots.includes('overtime')) {
            html += `
                <div class="mt-1 mb-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1">
                    Regular and ${dtrBeyondStandardHoursLabel} requests already exist for ${dateStr}.
                </div>
            `;
        } else if (existingSlots.includes('regular')) {
            html += `
                <div class="mt-1 mb-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1">
                    A pending time request exists for ${dateStr}. Enter time above 08:00 to file ${dtrBeyondStandardHoursLabel} automatically in Leave Requests.
                </div>
            `;
        } else if (existingSlots.includes('overtime')) {
            html += `
                <div class="mt-1 mb-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1">
                    An ${dtrBeyondStandardHoursLabel} request already exists for ${dateStr}. You may still file regular time.
                </div>
            `;
        }
        
        // Move to next day (add 1 day)
        currentDate.setDate(currentDate.getDate() + 1);
        dayIndex++;
    }
    
    if (html === '') {
        container.innerHTML = '<p class="text-sm text-gray-500 text-center">No days in range</p>';
    } else {
        container.innerHTML = html;
        
        // Update date_from and date_to to match the actual generated days (fix timezone issues)
        setTimeout(() => {
            const dayDateInputs = document.querySelectorAll('input[name^="days"][name$="[date]"]');
            if (dayDateInputs.length > 0) {
                const firstDate = dayDateInputs[0].value;
                const lastDate = dayDateInputs[dayDateInputs.length - 1].value;
                
                // Update the form date fields to match the actual generated days
                const dateFromInput = document.getElementById('attendance_date_from');
                const dateToInput = document.getElementById('attendance_date_to');
                
                if (dateFromInput && dateToInput) {
                    dateFromInput.value = firstDate;
                    dateToInput.value = lastDate;
                }
            }
        }, 100);
    }
}

// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
    const dateFromInput = document.getElementById('attendance_date_from');
    const dateToInput = document.getElementById('attendance_date_to');
    const attendanceForm = document.getElementById('record-attendance-form');
    
    if (dateFromInput) {
        dateFromInput.addEventListener('change', updateDaysList);
    }
    if (dateToInput) {
        dateToInput.addEventListener('change', updateDaysList);
    }
    
    // Set default dates if filter range exists
    const filterFrom = '{{ request("date_from") }}';
    const filterTo = '{{ request("date_to") }}';
    const today = new Date().toISOString().split('T')[0];
    
    if (filterFrom && filterTo && dateFromInput && dateToInput) {
        // Use today if within filter range, otherwise use filterFrom (but not future)
        const defaultDate = (today >= filterFrom && today <= filterTo) ? today : filterFrom;
        dateFromInput.value = defaultDate;
        dateToInput.value = defaultDate;
    } else if (dateFromInput && dateToInput) {
        // If no filter, default to today
        dateFromInput.value = today;
        dateToInput.value = today;
    }

    if (attendanceForm) {
        attendanceForm.addEventListener('submit', async function (e) {
            // Always stop the native submit first; resume manually after async modal checks.
            e.preventDefault();
            await validateAttendanceForm(e);
        });
    }
});

// Format time input (same as admin DTR time input)
function formatTimeInput(input) {
    const cursorPos = input.selectionStart;
    const before = input.value;
    
    // Format the value using the same logic as admin
    function formatTime(value) {
        // Keep only digits, max 4
        let digits = value.replace(/\D/g, '').slice(0, 4);
        if (digits.length <= 2) {
            return digits;
        }
        const h = digits.slice(0, 2);
        const m = digits.slice(2);
        return m ? h + ':' + m : h;
    }
    
    input.value = formatTime(input.value);
    
    // Best-effort keep cursor near end
    if (document.activeElement === input) {
        input.selectionStart = input.selectionEnd = input.value.length;
    }
}

// Format time to HH:MM format on blur (when user finishes input)
function formatTimeOnBlur(input) {
    let value = input.value.trim();
    
    if (value === '') {
        input.value = '';
        return;
    }
    
    // Remove all non-digit characters
    let digits = value.replace(/\D/g, '');
    
    if (digits.length === 0) {
        input.value = '';
        return;
    }
    
    // Format to HH:MM (always 2 digits for hours and minutes)
    let hours = '';
    let minutes = '';
    
    if (value.includes(':')) {
        // Already has colon, split it
        const parts = value.split(':');
        hours = parts[0].replace(/\D/g, '').slice(-2); // Take last 2 digits for hours
        minutes = parts[1].replace(/\D/g, '').slice(0, 2); // Take first 2 digits for minutes
    } else {
        // No colon, split digits (last 2 are minutes)
        if (digits.length >= 2) {
            hours = digits.slice(0, digits.length - 2).slice(-2); // Take last 2 digits before minutes
            minutes = digits.slice(-2); // Last 2 digits are minutes
        } else {
            // Less than 2 digits, treat as hours only
            hours = digits.padStart(2, '0');
            minutes = '00';
        }
    }
    
    // Pad hours to 2 digits (always HH format)
    hours = hours.padStart(2, '0').slice(0, 2);
    
    // Pad minutes to 2 digits (always MM format)
    minutes = minutes.padStart(2, '0').slice(0, 2);
    
    // Format final value as HH:MM
    input.value = hours + ':' + minutes;
}


// Clear time field
function clearTimeField(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.value = '';
        input.focus();
    }
}

function formatAttendanceDayLabel(dateStr) {
    if (!dateStr) {
        return 'Unknown date';
    }
    const d = new Date(dateStr + 'T00:00:00');
    if (Number.isNaN(d.getTime())) {
        return dateStr;
    }
    return d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
}

function describeAttendanceHourSplit(totalMinutes) {
    const regularMinutes = Math.min(totalMinutes, 480);
    const overtimeMinutes = Math.max(0, totalMinutes - 480);
    const fmt = (m) => {
        const h = String(Math.floor(m / 60)).padStart(2, '0');
        const min = String(m % 60).padStart(2, '0');
        return `${h}:${min}`;
    };

    return {
        entered: fmt(totalMinutes),
        regular: fmt(regularMinutes),
        overtime: fmt(overtimeMinutes),
    };
}

// Validate attendance form before submission
async function validateAttendanceForm(event) {
    const form = document.getElementById('record-attendance-form');
    if (!form) return true;

    if (form.dataset.submittingConfirmed === '1') {
        return true;
    }
    
    const timeInputs = form.querySelectorAll('input[name^="days"][name$="[time]"]');
    let hasValidTime = false;
    const invalidFields = [];
    
    const validEntries = [];
    timeInputs.forEach((input, index) => {
        const timeValue = input.value.trim();
        // Check if time is in HH:MM format
        const timePattern = /^[0-9]{2}:[0-9]{2}$/;
        
        if (timeValue && timePattern.test(timeValue)) {
            hasValidTime = true;
            const [hh, mm] = timeValue.split(':').map(Number);
            const minutes = (hh * 60) + mm;
            const dayContainer = input.closest('.day-row');
            const dateInput = dayContainer?.querySelector('input[name^="days"][name$="[date]"]');
            const dateValue = dateInput?.value || '';
            const slots = existingTimeRequestSlots[dateValue] || [];
            const pendingDay = pendingTimeRequestByDate[dateValue] || null;
            const effectiveMinutes = pendingDay
                ? Math.max(minutes, pendingDay.total_minutes || 0)
                : minutes;
            if (slots.includes('overtime')) {
                invalidFields.push(`An ${dtrBeyondStandardHoursLabel} request already exists for ${dateValue} (check Leave Requests).`);
                return;
            }
            if (slots.includes('regular') && slots.includes('overtime')) {
                invalidFields.push(`Regular and ${dtrBeyondStandardHoursLabel} requests already exist for ${dateValue}.`);
                return;
            }
            if (slots.includes('regular') && effectiveMinutes > 0 && effectiveMinutes <= 480) {
                invalidFields.push(`A pending time request exists for ${dateValue}. Enter time above 08:00 to file ${dtrBeyondStandardHoursLabel}.`);
                return;
            }
            validEntries.push({
                date: dateValue,
                time: timeValue,
                minutes: effectiveMinutes,
            });
        } else if (timeValue) {
            invalidFields.push(`Day ${index + 1} has invalid time format "${timeValue}". Please use HH:MM format (e.g., 00:00, 08:30).`);
        }
    });
    
    if (!hasValidTime) {
        event.preventDefault();
        alert('Please enter at least one valid time in HH:MM format (e.g., 08:00).\n\nEmpty or invalid time fields will be ignored.');
        return false;
    }
    
    const overEightEntries = validEntries.filter((entry) => entry.minutes > 480);
    const underEightEntries = validEntries.filter((entry) => entry.minutes > 0 && entry.minutes < 480);
    const overEightHoursCount = overEightEntries.length;
    const underEightHoursCount = underEightEntries.length;

    if (overEightHoursCount > 0) {
        const overEightDetailLines = overEightEntries.map((entry) => {
            const split = describeAttendanceHourSplit(entry.minutes);
            const dayLabel = formatAttendanceDayLabel(entry.date);
            return `• ${dayLabel}: you entered ${split.entered} → ${split.regular} regular + ${split.overtime} ${dtrBeyondStandardHoursLabel}`;
        });

        const overEightMessage = [
            overEightEntries.length === 1
                ? 'You entered time greater than 08:00:'
                : 'You entered time greater than 08:00 on these days:',
            '',
            ...overEightDetailLines,
            '',
            'Regular hours go to Pending Time Requests on this page.',
            `${dtrBeyondStandardHoursLabel} goes to Leave Requests — submit a reason to complete the request before admin can approve.`,
            '',
            'Do you want to continue?'
        ].join('\n');

        const overEightProceed = await showAttendancePolicyModal('Over 08:00 Time Request Warning', overEightMessage, 'over-eight');
        if (!overEightProceed) {
            event.preventDefault();
            return false;
        }
    }

    if (underEightHoursCount > 0) {
        const underEightDetailLines = underEightEntries.map((entry) => {
            const dayLabel = formatAttendanceDayLabel(entry.date);
            return `• ${dayLabel}: you entered ${entry.time} (below 08:00)`;
        });

        const underEightMessage = [
            underEightEntries.length === 1
                ? 'You entered time below 08:00:'
                : 'You entered time below 08:00 on these days:',
            '',
            ...underEightDetailLines,
            '',
            'You need to complete the required time of 08:00.',
            'If this issue still persists on the following day, this will be a violation of our Terms and Conditions.',
            '',
            'Do you want to continue?'
        ].join('\n');

        const underEightProceed = await showAttendancePolicyModal('Below 08:00 Time Request Warning', underEightMessage, 'under-eight');
        if (!underEightProceed) {
            event.preventDefault();
            return false;
        }
    }
    
    // Remove empty time fields before submission
    timeInputs.forEach((input) => {
        const timeValue = input.value.trim();
        const timePattern = /^[0-9]{2}:[0-9]{2}$/;
        
        if (!timeValue || !timePattern.test(timeValue)) {
            // Remove the parent day container if time is invalid
            const dayContainer = input.closest('.day-row');
            if (dayContainer) {
                const dateInput = dayContainer.querySelector('input[type="date"]');
                if (dateInput) {
                    dateInput.remove();
                }
                input.remove();
            }
        }
    });
    
    event.preventDefault();
    form.dataset.submittingConfirmed = '1';
    form.submit();
    return false;
}

// Close modal when clicking outside
document.getElementById('record-attendance-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRecordAttendanceModal();
    }
});

document.getElementById('attendance-policy-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAttendancePolicyModal(false);
    }
});

document.getElementById('incomplete-overtime-reminder-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeIncompleteOvertimeReminderModal();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    openIncompleteOvertimeReminderModal();
});
</script>

@endif
@endsection

