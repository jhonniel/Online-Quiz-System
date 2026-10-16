@php
    $dtr = $record;
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
        $isCompleted = true;
    } elseif ($dtr->status === 'travel') {
        $statusLabel = 'TRAVEL';
        $statusClass = 'bg-blue-100 text-blue-800';
        $isCompleted = true;
    } else {
        $totalMinutesForStatus = (int) round(\App\Support\DtrEmployeeAttendance::displayedDailyTotal($dtr) * 60);
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
            <p class="text-xs text-gray-500">{{ $dtr->date->format('l') }}</p>
        </div>
        <span class="px-2 py-1 inline-flex shrink-0 text-xs font-semibold rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
    </div>
    <dl class="mobile-card-kv">
        <dt>Hubstaff Hours</dt>
        <dd>{{ $isCompleted ? 'Complete' : ($workedMinutes > 0 ? $workedFormatted : '00:00') }}</dd>
        @if($leaveMinutes > 0)
            @php $leaveTypeLabel = \App\Support\DtrEmployeeAttendance::displayedLeaveTypeLabel($dtr); @endphp
            <dt>Leave/Offset{{ $leaveTypeLabel ? ' ('.$leaveTypeLabel.')' : '' }}</dt>
            <dd>{{ $leaveFormatted }}</dd>
        @endif
        <dt>Total</dt>
        <dd class="flex items-center justify-end gap-2">
            @if($isCompleted && ($dtr->status ?? '') !== 'on_leave' && ($dtr->status ?? '') !== 'travel')
                Complete
            @else
                <span>{{ $totalMinutes > 0 ? $totalFormatted : '00:00' }}</span>
                @php $dailyDeficitFormatted = \App\Support\DtrEmployeeAttendance::formatDailyDeficitForColumn($dtr); @endphp
                @if($dailyDeficitFormatted)
                    <span class="text-xs font-semibold text-red-600">{{ $dailyDeficitFormatted }}</span>
                @endif
            @endif
        </dd>
        @if($dtr->remarks)
            <dt class="col-span-2">Remarks</dt>
            <dd class="col-span-2 text-left font-normal text-gray-600">{{ $dtr->remarks }}</dd>
        @endif
    </dl>
</div>
