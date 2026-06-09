<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Approved Employee Leave Requests</title>
    <style>
        @page { margin: 24mm 10mm {{ !empty($includeVerificationQr) ? '24mm' : '14mm' }} 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111827; margin: 0; padding: 0; }
        header { position: fixed; top: -20mm; left: 0; right: 0; height: 22mm; border-bottom: 1px solid #E5E7EB; }
        footer { position: fixed; left: 0; right: 0; border-top: 1px solid #E5E7EB; color: #4B5563; font-size: 8px;
            @if(!empty($includeVerificationQr))
                bottom: -20mm; height: 22mm;
            @else
                bottom: -10mm; height: 10mm;
            @endif
        }
        .header-inner, .footer-inner { width: 100%; box-sizing: border-box; }
        .header-inner { padding: 0; }
        .footer-inner { padding: {{ !empty($includeVerificationQr) ? '4px 0 0' : '3px 0 0' }}; }
        .brand-table { width: 100%; border-collapse: collapse; }
        .brand-logo-cell { width: 58px; vertical-align: top; padding: 0 8px 0 0; }
        .brand-text-cell { vertical-align: top; }
        .brand-address-cell { vertical-align: top; padding: 0; }
        .brand-logo { width: 48px; height: 48px; object-fit: contain; display: block; }
        .system-name { font-size: 14px; font-weight: bold; margin: 0; padding: 0; color: #111827; line-height: 1.2; }
        .system-address { font-size: 8px; color: #4B5563; line-height: 1.3; margin: 2px 0 0 0; padding: 0; }
        .verify-table { width: 100%; border-collapse: collapse; }
        .verify-qr { width: 64px; height: 64px; }
        .verify-title { font-size: 9px; font-weight: bold; color: #111827; margin: 0 0 2px 0; }
        .verify-ref { font-family: DejaVu Sans Mono, monospace; font-size: 8px; color: #4338CA; margin: 0; }
        .footer-meta { font-size: 8px; color: #6B7280; margin: 0; }
        h1 { font-size: 13px; margin: 0 0 4px 0; }
        .meta { font-size: 8px; color: #4b5563; margin-bottom: 8px; line-height: 1.4; }
        table.data-table { width: 100%; border-collapse: collapse; }
        table.data-table th, table.data-table td { border: 1px solid #d1d5db; padding: 3px 4px; text-align: left; vertical-align: top; }
        table.data-table th { background: #f3f4f6; font-weight: 700; font-size: 8px; }
        thead { display: table-header-group; }
        tbody tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <header>
        <div class="header-inner">
            <table class="brand-table">
                <tr>
                    @if(!empty($branding['system_logo_data_uri']))
                        <td class="brand-logo-cell" rowspan="{{ !empty($branding['contact_address']) ? 2 : 1 }}">
                            <img src="{{ $branding['system_logo_data_uri'] }}" alt="Logo" class="brand-logo">
                        </td>
                    @endif
                    <td class="brand-text-cell">
                        <p class="system-name">{{ $branding['system_name'] ?? 'System' }}</p>
                    </td>
                </tr>
                @if(!empty($branding['contact_address']))
                    <tr>
                        <td class="brand-address-cell">
                            <p class="system-address">{{ $branding['contact_address'] }}</p>
                        </td>
                    </tr>
                @endif
            </table>
        </div>
    </header>

    <footer>
        <div class="footer-inner">
            @if(!empty($includeVerificationQr))
                <table class="verify-table">
                    <tr>
                        <td style="width: 70px; vertical-align: middle;">
                            @if(!empty($verificationQrDataUri))
                                <img src="{{ $verificationQrDataUri }}" alt="Verification QR" class="verify-qr">
                            @elseif(!empty($verificationQrSvg))
                                <div class="verify-qr" style="width: 64px; height: 64px; overflow: hidden;">
                                    {!! $verificationQrSvg !!}
                                </div>
                            @endif
                        </td>
                        <td style="vertical-align: middle;">
                            <p class="verify-title">Scan to verify this document is authentic</p>
                            <p class="verify-ref">Reference: {{ $verification->reference_code ?? '—' }}</p>
                            <p class="footer-meta">Generated {{ now()->format('M d, Y h:i A') }} • {{ $branding['system_name'] ?? 'System' }} • Page <span class="page"></span> of <span class="topage"></span></p>
                        </td>
                    </tr>
                </table>
            @else
                <p class="footer-meta">
                    Generated {{ now()->format('M d, Y h:i A') }} • {{ $branding['system_name'] ?? 'System' }} • Page <span class="page"></span> of <span class="topage"></span>
                </p>
            @endif
        </div>
    </footer>

    <h1>Approved Sick &amp; Vacation Leave Requests</h1>
    <div class="meta">
        Generated: {{ now()->format('M d, Y h:i A') }}
        | Employees: {{ $exportRows->count() }}
        | Approved Requests: {{ $totalApprovedRequests ?? 0 }}
        | Days Accumulated: {{ $totalDaysAccumulated ?? 0 }}
        | Types: {{ $exportMeta['types'] ?? 'Sick Leave & Vacation Leave' }}
        @if(!empty($exportMeta['department']))
            | Department: {{ $exportMeta['department'] }}
        @endif
        @if(!empty($exportMeta['employee']))
            | Employee: {{ $exportMeta['employee'] }}
        @endif
        @if(!empty($exportMeta['date_from']))
            | Date From: {{ $exportMeta['date_from'] }}
        @endif
        @if(!empty($exportMeta['date_to']))
            | Date To: {{ $exportMeta['date_to'] }}
        @endif
        @if(!empty($exportMeta['search']))
            | Search: "{{ $exportMeta['search'] }}"
        @endif
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Approved Requests</th>
                <th>Days Accumulated</th>
                <th>Available Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($exportRows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{ $row->user?->name ?? '—' }}<br>
                        <span style="color:#6b7280;font-size:7px;">{{ $row->user?->email ?? '' }}</span>
                    </td>
                    <td>{{ $row->user?->department?->name ?? '—' }}</td>
                    <td>{{ $row->approved_request_count }}</td>
                    <td>{{ $row->days_accumulated ?? 0 }}</td>
                    <td>{{ $row->available_balance ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No approved sick or vacation leave requests match the current filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
