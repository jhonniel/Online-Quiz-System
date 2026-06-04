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

    $workedHours = max(($dtr->total_hours ?? 0) - ($dtr->added_time_from_note ?? 0), 0);
    $workedMinutes = (int) round($workedHours * 60);
    $workedH = intdiv($workedMinutes, 60);
    $workedM = $workedMinutes % 60;
    $workedFormatted = sprintf('%02d:%02d', $workedH, $workedM);

    $totalMinutes = (int) round(($dtr->total_hours ?? 0) * 60);
    $totalH = intdiv($totalMinutes, 60);
    $totalM = $totalMinutes % 60;
    $totalFormatted = sprintf('%02d:%02d', $totalH, $totalM);
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
        <dt>Worked</dt>
        <dd>{{ $isCompleted ? 'Complete' : ($workedMinutes > 0 ? $workedFormatted : '00:00') }}</dd>
        <dt>Total</dt>
        <dd>{{ $isCompleted ? 'Complete' : ($totalMinutes > 0 ? $totalFormatted : '00:00') }}</dd>
        @if($dtr->remarks)
            <dt class="col-span-2">Remarks</dt>
            <dd class="col-span-2 text-left font-normal text-gray-600">{{ $dtr->remarks }}</dd>
        @endif
    </dl>
</div>
