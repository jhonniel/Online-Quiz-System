@php
    $effectiveDate = $leaveRequest->created_at->format('F d, Y');
    $startDate = $leaveRequest->start_date->format('F d, Y');
    $endDate = ($leaveRequest->end_date ?? $leaveRequest->start_date)->format('F d, Y');
    $lengthText = $leaveRequest->duration_display_label;
    $reasonText = $leaveRequest->reason ?: '_______________________________________________';
    $employee = $leaveRequest->user;
    $raw = $leaveRequest->reason ?? '';
@endphp

@if(in_array($leaveRequest->type, ['vacation_leave', 'sick_leave', 'offset'], true))
    <div class="letter-content">
        <p class="letter-date">{{ $effectiveDate }}</p>
        <div class="letter-body">
            <p><strong>{{ $letterAddressee ?? 'Dear HR Admin,' }}</strong></p>
            <p>
                Please accept this letter as formal request for a leave of absence. My leave is due to
                <span class="emphasis">{{ $reasonText }}</span>.
                I am requesting a leave of
                <span class="emphasis">{{ $lengthText }}</span>.
                The leave will last from
                <span class="emphasis">{{ $startDate }}</span>
                until
                <span class="emphasis">{{ $endDate }}</span>.
            </p>
            <p>
                If my leave of absence is approved, I'll try my best to assist with any questions by phone call
                or chat provided that I have the means or I can connect with the internet.
            </p>
            <p>
                Please let me know if you have any questions and an appropriate time for us to speak to
                discuss the terms of my leave of absence.
            </p>
            <p>Thank you for understanding.</p>
            <p>Best regards,</p>
            <p>Truly yours,</p>
        </div>

        @include('admin.leave-requests.partials.show-pdf-signatories')
    </div>

@elseif($leaveRequest->type === 'work_from_home')
    @php
        $mode = '';
        $remoteAddress = '';
        $reasonAbsence = '';
        $tasks = '';

        if (preg_match('/Mode:\s*(.+)/', $raw, $m)) {
            $mode = trim($m[1]);
        }
        if (preg_match('/Remote Address:\s*(.+)/', $raw, $m)) {
            $remoteAddress = trim($m[1]);
        }
        if (preg_match('/Additional Explanation:\s*(.+)\z/s', $raw, $m)) {
            $reasonAbsence = trim($m[1]);
        }
        if (preg_match('/Tasks \/ ClickUp Links:\s*(.+?)(?:\n+Additional Explanation:|\z)/s', $raw, $m)) {
            $tasks = trim($m[1]);
        }
    @endphp

    <div class="letter-content">
        <p class="letter-date">{{ $effectiveDate }}</p>
        <div class="letter-body">
            <p><strong>{{ $letterAddressee ?? 'Dear HR Admin,' }}</strong></p>
            <p>
                Please accept this letter as official notice that I will be
                <span class="emphasis">[{{ $mode ?: 'working remotely or request to be excused' }}]</span>
                at
                <span class="emphasis">[{{ $remoteAddress ?: 'remote address' }}]</span>
                and was or will be unable to report to work on or from
                <span class="emphasis">[{{ $startDate }}]</span>
                to
                <span class="emphasis">[{{ $endDate }}]</span>
                due to
                <span class="emphasis">[{{ $reasonAbsence ?: 'reasons for absence' }}]</span>.
            </p>
            <p><strong>[Strictly List down Task Listed in ClickUp for Devs via link]</strong> {!! nl2br(e($tasks)) !!}</p>
            <p>Thank you for understanding.</p>
            <p>Best regards,</p>
            <p>Truly yours,</p>
        </div>

        @include('admin.leave-requests.partials.show-pdf-signatories')
    </div>

@elseif($leaveRequest->type === 'overtime')
    @php
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

    <div class="letter-content">
        <p class="letter-date">{{ $effectiveDate }}</p>
        <div class="letter-body">
            <p><strong>{{ $letterAddressee ?? 'Dear HR Admin,' }}</strong></p>
            <p>
                I respectfully request your approval for an additional
                <span class="emphasis">{{ $otHours ?: '[hours, HH:MM]' }}</span>
                of overtime worked on
                <span class="emphasis">{{ $otDates ?: '[date(s)]' }}</span>@if(!empty($otReason)),
                due to
                <span class="emphasis">{{ $otReason }}</span>@else.@endif
            </p>
            <p><strong>Tasks completed (ClickUp links)</strong> {!! nl2br(e($otTasks)) !!}</p>
            <p>Thank you for understanding.</p>
            <p>Best regards,</p>
            <p>Truly yours,</p>
        </div>

        @include('admin.leave-requests.partials.show-pdf-signatories')
    </div>
@endif
