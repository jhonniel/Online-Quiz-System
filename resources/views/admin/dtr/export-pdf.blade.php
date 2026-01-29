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
                Total Deficit: <span style="color: #DC2626;">{{ $employeeGroup['total_deficit_formatted'] }}</span> •
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

                        // Build remarks with leave request info (excluding overtime type)
                        $remarks = $dtr->remarks ?: '';
                        
                        // Check for travel request
                        $dateKey = $dtr->date->format('Y-m-d');
                        $employeeId = $dtr->user_id;
                        $travelRequest = $travelRequestMap[$employeeId][$dateKey] ?? null;
                        
                        if ($travelRequest) {
                            $travelTypeLabel = $travelRequest->type_label ?? 'Travel';
                            if ($remarks) {
                                $remarks = $remarks . ' | Travel: ' . $travelTypeLabel;
                            } else {
                                $remarks = 'Travel: ' . $travelTypeLabel;
                            }
                        } elseif (isset($dtr->leave_request) && $dtr->leave_request) {
                            $leaveTypeLabel = $dtr->leave_request->type_label ?? ucfirst(str_replace('_', ' ', $dtr->leave_request->type));
                            if ($remarks) {
                                $remarks = $remarks . ' | Leave: ' . $leaveTypeLabel;
                            } else {
                                $remarks = 'Leave: ' . $leaveTypeLabel;
                            }
                        }
                        
                        // If status is travel but no travel request found, add travel note
                        if ($dtr->status === 'travel' && !$travelRequest && strpos($remarks, 'Travel') === false) {
                            if ($remarks) {
                                $remarks = $remarks . ' | Travel';
                            } else {
                                $remarks = 'Travel';
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
                        <td>{{ $remarks ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $employeeId = $employeeGroup['employee']->id;
            $hasLeaveRecords = isset($leaveRequestMap[$employeeId]) && count($leaveRequestMap[$employeeId]) > 0;
            $hasTravelRecords = isset($travelRequestMap[$employeeId]) && count($travelRequestMap[$employeeId]) > 0;
            
            // Get leave dates that don't have DTR records
            $leaveDatesWithoutDtr = [];
            if ($hasLeaveRecords) {
                foreach ($leaveRequestMap[$employeeId] as $dateKey => $leave) {
                    $hasDtr = false;
                    foreach ($employeeGroup['records'] as $dtr) {
                        if ($dtr->date->format('Y-m-d') === $dateKey) {
                            $hasDtr = true;
                            break;
                        }
                    }
                    if (!$hasDtr) {
                        $leaveDatesWithoutDtr[$dateKey] = $leave;
                    }
                }
            }
            
            // Get travel dates that don't have DTR records
            $travelDatesWithoutDtr = [];
            if ($hasTravelRecords) {
                foreach ($travelRequestMap[$employeeId] as $dateKey => $travel) {
                    $hasDtr = false;
                    foreach ($employeeGroup['records'] as $dtr) {
                        if ($dtr->date->format('Y-m-d') === $dateKey) {
                            $hasDtr = true;
                            break;
                        }
                    }
                    if (!$hasDtr) {
                        $travelDatesWithoutDtr[$dateKey] = $travel;
                    }
                }
            }
        @endphp

        @if(count($leaveDatesWithoutDtr) > 0)
            <div style="margin-top: 12px; margin-bottom: 4px;">
                <div style="font-weight: bold; font-size: 10px; color: #1E40AF; background: #DBEAFE; padding: 4px 6px; border-radius: 4px;">
                    Leave Records (No DTR Entry)
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
                    @foreach($leaveDatesWithoutDtr as $dateKey => $leave)
                        @php
                            $leaveDate = \Carbon\Carbon::parse($dateKey);
                            $leaveTypeLabel = $leave->type_label ?? ucfirst(str_replace('_', ' ', $leave->type));
                        @endphp
                        <tr style="background-color: #F0F9FF;">
                            <td>{{ $leaveDate->format('M d, Y') }}</td>
                            <td>{{ $leaveDate->format('D') }}</td>
                            <td class="right">00:00</td>
                            <td class="right">00:00</td>
                            <td class="right">00:00</td>
                            <td class="right">00:00</td>
                            <td class="center">On Leave</td>
                            <td>
                                Leave: {{ $leaveTypeLabel }}
                                @if($leave->reason)
                                    - {{ \Illuminate\Support\Str::limit($leave->reason, 50) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(count($travelDatesWithoutDtr) > 0)
            <div style="margin-top: 12px; margin-bottom: 4px;">
                <div style="font-weight: bold; font-size: 10px; color: #7C3AED; background: #EDE9FE; padding: 4px 6px; border-radius: 4px;">
                    Travel Records (No DTR Entry)
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
                    @foreach($travelDatesWithoutDtr as $dateKey => $travel)
                        @php
                            $travelDate = \Carbon\Carbon::parse($dateKey);
                        @endphp
                        <tr style="background-color: #FAF5FF;">
                            <td>{{ $travelDate->format('M d, Y') }}</td>
                            <td>{{ $travelDate->format('D') }}</td>
                            <td class="right">00:00</td>
                            <td class="right">00:00</td>
                            <td class="right">00:00</td>
                            <td class="right">00:00</td>
                            <td class="center">Travel</td>
                            <td>
                                Travel Leave
                                @if($travel->reason)
                                    - {{ \Illuminate\Support\Str::limit($travel->reason, 50) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
</body>
</html>
