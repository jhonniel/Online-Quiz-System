@php
    $dtr = $record;
    $userDtrStatus = \App\Support\DtrEmployeeAttendance::resolveUserDtrStatus($dtr);
    $statusLabel = $userDtrStatus['label'];
    $statusClass = $userDtrStatus['class'];
    $isCompleted = $userDtrStatus['is_completed'];
    $isApprovedWfh = \App\Support\DtrEmployeeAttendance::hasApprovedWorkFromHome($dtr);

    $workedHours = \App\Support\DtrEmployeeAttendance::displayedHubstaffHours($dtr);
    $leaveHours = \App\Support\DtrEmployeeAttendance::displayedLeaveHours($dtr);
    $leaveMinutes = (int) round($leaveHours * 60);
    $leaveH = intdiv($leaveMinutes, 60);
    $leaveM = $leaveMinutes % 60;
    $leaveFormatted = sprintf('%02d:%02d', $leaveH, $leaveM);
    $workedMinutes = (int) round($workedHours * 60);
    $workedH = intdiv($workedMinutes, 60);
    $workedM = $workedMinutes % 60;
    $workedFormatted = sprintf('%02d:%02d', $workedH, $workedM);

    $totalFormatted = \App\Support\DtrEmployeeAttendance::formatTotalHoursColumn($dtr);
    $totalMinutes = (int) round(\App\Support\DtrEmployeeAttendance::displayedTotalHoursForColumn($dtr) * 60);
@endphp
<div class="mobile-card">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="mobile-card-title">{{ $dtr->date->format('M d, Y') }}</p>
            <p class="text-xs text-slate-500">{{ $dtr->date->format('l') }}</p>
        </div>
        <span class="px-2 py-1 inline-flex shrink-0 text-xs font-semibold rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
    </div>
    <dl class="mobile-card-kv">
        <dt>Hubstaff Hours</dt>
        <dd>{{ ($isCompleted && ! $isApprovedWfh && ($dtr->status ?? '') !== 'on_leave' && ($dtr->status ?? '') !== 'travel') ? 'Complete' : ($workedMinutes > 0 ? $workedFormatted : '00:00') }}</dd>
        @if($leaveMinutes > 0)
            @php $leaveTypeLabel = \App\Support\DtrEmployeeAttendance::displayedLeaveTypeLabel($dtr); @endphp
            <dt>Leave/Offset{{ $leaveTypeLabel ? ' ('.$leaveTypeLabel.')' : '' }}</dt>
            <dd>{{ $leaveFormatted }}</dd>
        @endif
        <dt>Total</dt>
        <dd class="text-right leading-tight">
            @if($isCompleted && ($dtr->status ?? '') !== 'on_leave' && ($dtr->status ?? '') !== 'travel')
                Complete
            @else
                <div class="tabular-nums">{{ $totalMinutes > 0 ? $totalFormatted : '00:00' }}</div>
                @php $dailyDeficitFormatted = \App\Support\DtrEmployeeAttendance::formatDailyDeficitForColumn($dtr); @endphp
                @if($dailyDeficitFormatted)
                    <div class="text-[8px] font-normal text-red-500 tabular-nums mt-0.5" title="{{ \App\Support\DtrEmployeeAttendance::dailyColumnDeficitMinutes($dtr) }} minutes below 08:00">Deficit {{ $dailyDeficitFormatted }}</div>
                @endif
            @endif
        </dd>
        @if($dtr->remarks)
            <dt class="col-span-2">Remarks</dt>
            <dd class="col-span-2 text-left font-normal text-gray-600">{{ $dtr->remarks }}</dd>
        @endif
    </dl>
</div>
