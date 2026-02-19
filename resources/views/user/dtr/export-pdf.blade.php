<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Time Record (DTR)</title>
    <style>
        @page { margin: 18mm 12mm 14mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111827; }
        header { position: fixed; top: -14mm; left: 0; right: 0; height: 12mm; border-bottom: 1px solid #E5E7EB; }
        footer { position: fixed; bottom: -10mm; left: 0; right: 0; height: 10mm; border-top: 1px solid #E5E7EB; color: #6B7280; font-size: 9px; }
        .header-inner, .footer-inner { width: 100%; padding: 4px 12mm; box-sizing: border-box; }
        .title { font-size: 14px; font-weight: bold; margin: 0; }
        .muted { color: #6B7280; font-size: 9px; }
        .table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 8.5px; }
        .table th, .table td { border: 1px solid #E5E7EB; padding: 3px 4px; text-align: left; vertical-align: top; word-wrap: break-word; word-break: break-word; }
        .table th { background: #F3F4F6; font-weight: 600; font-size: 9px; }
        .right { text-align: right; }
        .center { text-align: center; }
        thead { display: table-header-group; }
        tbody tr { page-break-inside: avoid; }
        .summary-box { background: #EEF2FF; border: 1px solid #C7D2FE; border-radius: 4px; padding: 6px; margin: 8px 0; }
        .summary-title { font-weight: bold; font-size: 10px; color: #3730A3; margin-bottom: 4px; }
        .summary-row { display: table; width: 100%; margin: 2px 0; }
        .summary-label { display: table-cell; font-weight: 600; width: 40%; }
        .summary-value { display: table-cell; font-weight: bold; color: #1E40AF; }
        .week-header { background: #F9FAFB; border: 1px solid #E5E7EB; padding: 6px; margin: 8px 0 4px 0; border-radius: 4px; }
        .week-label { font-weight: bold; font-size: 10px; color: #111827; }
    </style>
</head>
<body>
    <header>
        <div class="header-inner">
            <div class="title">Daily Time Record (DTR)</div>
            <div class="muted">
                Employee: {{ $user->name }}@if($user->department) • {{ $user->department->name }}@endif
                • Date Range: {{ $dateFrom }} @if($dateFrom !== 'All Time' && $dateTo !== 'All Time') to {{ $dateTo }} @endif
                • Total Records: {{ $totalRecords }}
            </div>
        </div>
    </header>
    <footer>
        <div class="footer-inner">Page <span class="page"></span> of <span class="topage"></span></div>
    </footer>

    <div class="summary-box">
        <div class="summary-title">Summary</div>
        <div class="summary-row">
            <span class="summary-label">Total Hours:</span>
            <span class="summary-value">{{ $totalHoursFormatted }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Overtime:</span>
            <span class="summary-value">{{ $totalOvertimeFormatted }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Records:</span>
            <span class="summary-value">{{ $totalRecords }}</span>
        </div>
    </div>

    @foreach($groupedByWeek as $weekKey => $week)
        <div class="week-header">
            <div class="week-label">{{ $week['label'] }}</div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 8%;">Day</th>
                    <th style="width: 12%;" class="right">Worked Hours</th>
                    <th style="width: 12%;" class="right">Added Time</th>
                    <th style="width: 12%;" class="right">Total Hours</th>
                    <th style="width: 12%;" class="right">Overtime</th>
                    <th style="width: 10%;" class="center">Status</th>
                    <th style="width: 22%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($week['records'] as $dtr)
                    @php
                        // Determine status first
                        if ($dtr->status === 'travel') {
                            $statusLabel = 'TRAVEL';
                            $isCompleted = false;
                        } elseif ($dtr->status === 'on_leave') {
                            $statusLabel = 'On Leave';
                            $isCompleted = false;
                        } elseif ($dtr->status === 'absent') {
                            $statusLabel = 'Absent';
                            $isCompleted = false;
                        } else {
                            $totalMinutesForStatus = (int) round(($dtr->total_hours ?? 0) * 60);
                            if ($totalMinutesForStatus < 480) {
                                $statusLabel = 'Under Time';
                                $isCompleted = false;
                            } else {
                                $statusLabel = 'Completed';
                                $isCompleted = true;
                            }
                        }

                        // Calculate time values
                        $workedHours = max(($dtr->total_hours ?? 0) - ($dtr->added_time_from_note ?? 0), 0);
                        $workedMinutes = (int) round($workedHours * 60);
                        $workedH = intdiv($workedMinutes, 60);
                        $workedM = $workedMinutes % 60;
                        $workedFormatted = sprintf('%02d:%02d', $workedH, $workedM);
                        $workedOver8Hours = $workedMinutes > 480; // 8 hours = 480 minutes

                        $extraMinutes = (int) round(($dtr->added_time_from_note ?? 0) * 60);
                        $extraH = intdiv($extraMinutes, 60);
                        $extraM = $extraMinutes % 60;
                        $extraFormatted = sprintf('%02d:%02d', $extraH, $extraM);

                        $totalMinutes = (int) round(($dtr->total_hours ?? 0) * 60);
                        $totalH = intdiv($totalMinutes, 60);
                        $totalM = $totalMinutes % 60;
                        $totalFormatted = sprintf('%02d:%02d', $totalH, $totalM);
                        $totalOver8Hours = $totalMinutes > 480; // 8 hours = 480 minutes

                        $otMinutes = (int) round(($dtr->overtime_hours ?? 0) * 60);
                        $otH = intdiv($otMinutes, 60);
                        $otM = $otMinutes % 60;
                        $otFormatted = sprintf('%02d:%02d', $otH, $otM);

                        $remarks = $dtr->remarks ?: '-';
                    @endphp
                    <tr>
                        <td>{{ $dtr->date->format('M d, Y') }}</td>
                        <td>{{ $dtr->date->format('D') }}</td>
                        <td class="right">
                            @if($isCompleted || $workedOver8Hours)
                                ✓
                            @else
                                {{ $workedMinutes > 0 ? $workedFormatted : '00:00' }}
                            @endif
                        </td>
                        <td class="right">
                            @if($isCompleted || $extraMinutes > 0)
                                ✓
                            @else
                                -
                            @endif
                        </td>
                        <td class="right">
                            @if($isCompleted || $totalOver8Hours)
                                ✓
                            @else
                                {{ $totalMinutes > 0 ? $totalFormatted : '00:00' }}
                            @endif
                        </td>
                        <td class="right">
                            @if($isCompleted || $otMinutes > 0)
                                ✓
                            @else
                                -
                            @endif
                        </td>
                        <td class="center">{{ $statusLabel }}</td>
                        <td>{{ $remarks }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>
