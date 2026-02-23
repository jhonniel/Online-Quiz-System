<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Billing Statement #{{ $billingStatement->id }}</title>
    <style>
        @page { margin: 18mm 12mm 14mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #111827; }
        header { position: fixed; top: -14mm; left: 0; right: 0; height: 12mm; border-bottom: 1px solid #E5E7EB; }
        footer { position: fixed; bottom: -10mm; left: 0; right: 0; height: 10mm; border-top: 1px solid #E5E7EB; color: #6B7280; font-size: 9px; }
        .header-inner, .footer-inner { width: 100%; padding: 4px 12mm; box-sizing: border-box; }
        .title { font-size: 14px; font-weight: bold; margin: 0; }
        .muted { color: #6B7280; font-size: 9.8px; }
        .table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .table th, .table td { border: 1px solid #E5E7EB; padding: 4px 6px; text-align: left; vertical-align: top; word-wrap: break-word; word-break: break-word; }
        .table th { background: #F3F4F6; font-weight: 600; font-size: 10px; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        .marked-by { margin: 10px 0; padding: 8px; background: #F9FAFB; border: 1px solid #E5E7EB; font-size: 10px; }
    </style>
</head>
<body>
    <header>
        <div class="header-inner">
            <div class="title">Billing Statement #{{ $billingStatement->id }}</div>
            <div class="muted">{{ $billingStatement->type === 'advance' ? 'Advance payment' : ucfirst($billingStatement->type) . ' billing' }} • {{ $billingStatement->created_at->format('M j, Y g:i A') }}</div>
        </div>
    </header>
    <footer>
        <div class="footer-inner">Page <span class="page"></span> of <span class="topage"></span></div>
    </footer>

    <div class="marked-by">
        <strong>{{ $billingStatement->type === 'advance' ? 'Advance payment by:' : 'Marked as paid by:' }}</strong> {{ $billingStatement->markedByUser?->name ?? $billingStatement->markedByUser?->email ?? '—' }}
        on {{ $billingStatement->created_at->format('M j, Y g:i A') }}
    </div>

    <h2 style="margin-top: 10px; font-size: 13.5px;">{{ $billingStatement->type === 'advance' ? 'Advanced' : 'Paid' }} devices ({{ count($starlinks) }})</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Device</th>
                <th>Account</th>
                <th>Plan</th>
                <th>{{ $billingStatement->type === 'advance' ? 'Advanced until' : 'Billing period paid' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($starlinks as $starlink)
                <tr>
                    <td>{{ $starlink->starlink_id ?: $starlink->serial_number ?: '—' }}</td>
                    <td>{{ $starlink->account_linked_email ?? $starlink->linkedAccount?->email ?? '—' }}</td>
                    <td>{{ $starlink->subscriptionPlanType?->name ?? $starlink->plan ?? '—' }}</td>
                    <td>{{ $billingStatement->type === 'advance' ? ($starlink->advance_payment_until?->format('M j, Y') ?? $billingStatement->advance_payment_until?->format('M j, Y') ?? '—') : ($starlink->last_paid_date?->format('M j, Y') ?? '—') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
