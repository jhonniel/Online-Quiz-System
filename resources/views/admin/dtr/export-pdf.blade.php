<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee DTR Export</title>
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
        .employee-header { background: #F9FAFB; border: 1px solid #E5E7EB; padding: 6px; margin: 8px 0 4px 0; border-radius: 4px; }
        .employee-name { font-weight: bold; font-size: 10px; color: #111827; }
        .employee-email { font-size: 8.5px; color: #6B7280; }
    </style>
</head>
<body>
    <header>
        <div class="header-inner">
            <div class="title">Employee Daily Time Record (DTR) Export</div>
            <div class="muted">
                @if($selectedDepartment) Department: {{ $selectedDepartment->name }} • @endif
                Date Range: {{ $dateFrom }} @if($dateFrom !== 'All Time' && $dateTo !== 'All Time') to {{ $dateTo }} @endif
                @if($selectedEmployee) • Employee: {{ $selectedEmployee->name }} @endif
                • Total Records: {{ $totalRecords }}
            </div>
        </div>
    </header>
    <footer>
        <div class="footer-inner">Page <span class="page"></span> of <span class="topage"></span></div>
    </footer>

    @foreach($groupedByEmployee as $employeeGroup)
        <div class="employee-header">
            <div class="employee-name">{{ $employeeGroup['employee']->name }}</div>
            <div class="employee-email">{{ $employeeGroup['employee']->email }}@if($employeeGroup['employee']->department) • {{ $employeeGroup['employee']->department->name }}@endif</div>
            <div class="muted" style="margin-top: 2px;">
                Total Hours: {{ $employeeGroup['total_hours_formatted'] }} •
                Total Overtime: {{ $employeeGroup['total_overtime_formatted'] }} •
                Total Deficit: <span style="color: #DC2626;">{{ $employeeGroup['total_deficit_formatted'] }}</span> •
                Balance (Overtime - Deficit): <span style="color: {{ $employeeGroup['is_balance_negative'] ? '#DC2626' : '#059669' }};">{{ $employeeGroup['balance_overtime_formatted'] }}</span> •
                Records: {{ count($employeeGroup['records']) }}
            </div>
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
                @foreach($employeeGroup['records'] as $dtr)
                    @php
                        $workedHours = max(($dtr->total_hours ?? 0) - ($dtr->added_time_from_note ?? 0), 0);
                        $workedMinutes = (int) round($workedHours * 60);
                        $workedH = intdiv($workedMinutes, 60);
                        $workedM = $workedMinutes % 60;
                        $workedFormatted = sprintf('%02d:%02d', $workedH, $workedM);

                        $extraMinutes = (int) round(($dtr->added_time_from_note ?? 0) * 60);
                        $extraH = intdiv($extraMinutes, 60);
                        $extraM = $extraMinutes % 60;
                        $extraFormatted = sprintf('%02d:%02d', $extraH, $extraM);

                        $totalMinutes = (int) round(($dtr->total_hours ?? 0) * 60);
                        $totalH = intdiv($totalMinutes, 60);
                        $totalM = $totalMinutes % 60;
                        $totalFormatted = sprintf('%02d:%02d', $totalH, $totalM);

                        $otMinutes = (int) round(($dtr->overtime_hours ?? 0) * 60);
                        $otH = intdiv($otMinutes, 60);
                        $otM = $otMinutes % 60;
                        $otFormatted = sprintf('%02d:%02d', $otH, $otM);

                        // Status logic: same as DTR list
                        if ($dtr->status === 'travel') {
                            $statusLabel = 'Travel';
                        } else {
                            // Otherwise, show Under Time or Completed based on total hours
                            $totalMinutesForStatus = (int) round(($dtr->total_hours ?? 0) * 60);
                            if ($totalMinutesForStatus < 480) {
                                $statusLabel = 'Under Time';
                            } else {
                                $statusLabel = 'Completed';
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ $dtr->date->format('M d, Y') }}</td>
                        <td>{{ $dtr->date->format('D') }}</td>
                        <td class="right">{{ $workedMinutes > 0 ? $workedFormatted : '00:00' }}</td>
                        <td class="right">{{ $extraMinutes > 0 ? $extraFormatted : '00:00' }}</td>
                        <td class="right">{{ $totalMinutes > 0 ? $totalFormatted : '00:00' }}</td>
                        <td class="right">{{ $otMinutes > 0 ? $otFormatted : '00:00' }}</td>
                        <td class="center">{{ $statusLabel }}</td>
                        <td>{{ $dtr->remarks ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>
