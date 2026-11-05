<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Starlinks Export</title>
    <style>
        @page { margin: 14mm 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 4px 0; }
        .meta { font-size: 9px; color: #4b5563; margin-bottom: 6px; }
        .summary { font-size: 9px; margin-bottom: 10px; }
        .summary span { display: inline-block; margin-right: 14px; }
        .summary .pending { color: #92400e; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #d1d5db; padding: 4px; text-align: left; vertical-align: top; word-wrap: break-word; }
        th { background: #f3f4f6; font-weight: 700; font-size: 8.5px; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        tr.has-pending td { background: #fffbeb; }
        .label { color: #6b7280; }
        .main { font-weight: 700; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-weight: 700; }
        .badge-pending { background: #fde68a; color: #92400e; }
        .badge-clear { background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>
    <h1>Starlinks Export</h1>
    <div class="meta">
        Generated: {{ now()->format('M d, Y h:i A') }}
        @if(!empty($search))
            | Search: "{{ $search }}"
        @endif
        @if(!empty($statusFilter))
            | Status: {{ $statusFilter }}
        @endif
        @if(!empty($accountEmailFilter))
            | Account/Email: "{{ $accountEmailFilter }}"
        @endif
        @if(!empty($clientNameFilter))
            | Client Name: "{{ $clientNameFilter }}"
        @endif
    </div>
    <div class="summary">
        <span>Total devices: <strong>{{ count($rows) }}</strong></span>
        <span class="pending">With pending billing: {{ $pendingBillingCount }}</span>
    </div>

    @php
        $line = function (array $row, string $key, ?string $label = null) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value === '') {
                return '';
            }

            return '<div>'.($label ? '<span class="label">'.e($label).':</span> ' : '').e($value).'</div>';
        };
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">ID</th>
                <th style="width: 17%;">Device</th>
                <th style="width: 17%;">Account</th>
                <th style="width: 11%;">Location</th>
                <th style="width: 12%;">Plan &amp; Status</th>
                <th style="width: 14%;">Billing</th>
                <th style="width: 13%;">Pending Billing</th>
                <th style="width: 12%;">Replacement</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr class="{{ $row['Pending Billing'] === 'Yes' ? 'has-pending' : '' }}">
                    <td>{{ $row['ID'] }}</td>
                    <td>
                        <div class="main">{{ $row['Starlink ID'] ?: '—' }}</div>
                        {!! $line($row, 'Serial Number', 'Serial') !!}
                        {!! $line($row, 'Kit Number', 'Kit') !!}
                        {!! $line($row, 'Router ID', 'Router') !!}
                        {!! $line($row, 'SSID', 'SSID') !!}
                    </td>
                    <td>
                        <div class="main">{{ $row['Account/Email'] ?: '—' }}</div>
                        {!! $line($row, 'Linked Account Name') !!}
                        {!! $line($row, 'Contact Email', 'Contact') !!}
                        {!! $line($row, 'End User Email', 'End user') !!}
                    </td>
                    <td>
                        <div>{{ $row['Office/Location'] ?: '—' }}</div>
                        @if($hasMunicipalityColumn)
                            {!! $line($row, 'Client Name', 'Client') !!}
                        @endif
                    </td>
                    <td>
                        <div class="main">{{ $row['Plan'] ?: '—' }}</div>
                        {!! $line($row, 'Plan Type', 'Type') !!}
                        {!! $line($row, 'Status', 'Status') !!}
                        {!! $line($row, 'PO No.', 'PO') !!}
                    </td>
                    <td>
                        {!! $line($row, 'Billing Interval', 'Interval') !!}
                        {!! $line($row, 'Start Date', 'Start') !!}
                        {!! $line($row, 'Last Paid Date', 'Last paid') !!}
                        {!! $line($row, 'Advance Paid Until', 'Advance until') !!}
                        {!! $line($row, 'Next Billing Date', 'Next billing') !!}
                    </td>
                    <td>
                        @if($row['Pending Billing'] === 'Yes')
                            <span class="badge badge-pending">Pending</span>
                            <div>{{ $row['Overdue Cycles'] }} unpaid {{ (int) $row['Overdue Cycles'] === 1 ? 'cycle' : 'cycles' }}</div>
                            {!! $line($row, 'Pending Since', 'Since') !!}
                        @else
                            <span class="badge badge-clear">None</span>
                        @endif
                    </td>
                    <td>
                        {!! $line($row, 'Replaced At', 'Replaced') !!}
                        {!! $line($row, 'Replacement Note') !!}
                        @if(($row['Replaced At'] ?? '') === '' && ($row['Replacement Note'] ?? '') === '')
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
