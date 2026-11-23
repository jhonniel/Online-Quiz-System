<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Starlink Billing Notice</title>
    <style>
        @page { margin: 16mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 4px 0; }
        .meta { color: #6b7280; margin-bottom: 14px; font-size: 10px; }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 10px;
            margin-bottom: 12px;
        }
        .badge-overdue { background: #ffedd5; color: #9a3412; }
        .badge-upcoming { background: #e0e7ff; color: #3730a3; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 7px 8px; text-align: left; vertical-align: top; }
        th { width: 34%; background: #f3f4f6; font-weight: 700; color: #374151; }
        .footer { margin-top: 18px; font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>
    @php
        $isOverdue = ($notice['kind'] ?? null) === 'overdue';
        $account = $starlink->account_linked_email ?: ($starlink->linkedAccount->email ?? '—');
    @endphp

    <h1>Starlink Device Billing Notice</h1>
    <div class="meta">Generated: {{ now()->format('M d, Y h:i A') }}</div>

    <div class="badge {{ $isOverdue ? 'badge-overdue' : 'badge-upcoming' }}">
        @if($isOverdue)
            OVERDUE
            @if(($notice['overdue_cycles'] ?? 0) > 0)
                · {{ (int) $notice['overdue_cycles'] }} cycle{{ (int) $notice['overdue_cycles'] === 1 ? '' : 's' }}
            @endif
        @else
            UPCOMING RENEWAL / DUE
            @if(isset($notice['days_until']))
                · in {{ (int) $notice['days_until'] }} day{{ (int) $notice['days_until'] === 1 ? '' : 's' }}
            @endif
        @endif
    </div>

    <table>
        <tr><th>Starlink ID</th><td>{{ $starlink->starlink_id ?: '—' }}</td></tr>
        <tr><th>Serial Number</th><td>{{ $starlink->serial_number ?: '—' }}</td></tr>
        <tr><th>Kit Number</th><td>{{ $starlink->kit_number ?: '—' }}</td></tr>
        <tr><th>Router ID</th><td>{{ $starlink->router_id ?: '—' }}</td></tr>
        <tr><th>Account / Email</th><td>{{ $account }}</td></tr>
        <tr><th>Linked Account</th><td>{{ $starlink->linkedAccount?->name ?: '—' }}</td></tr>
        <tr><th>Office / Location</th><td>{{ $starlink->office_location ?: '—' }}</td></tr>
        <tr><th>Client Name</th><td>{{ $starlink->municipality ?: '—' }}</td></tr>
        <tr><th>Plan</th><td>{{ $starlink->plan ?: '—' }}</td></tr>
        <tr><th>Plan Type</th><td>{{ $starlink->subscriptionPlanType?->name ?: '—' }}</td></tr>
        <tr><th>Status</th><td>{{ $starlink->status ?: '—' }}</td></tr>
        <tr><th>Start Date</th><td>{{ $starlink->start_date?->format('M d, Y') ?: '—' }}</td></tr>
        <tr><th>Billing Interval</th><td>{{ ucfirst($starlink->billing_interval ?? 'monthly') }}</td></tr>
        <tr><th>Last Paid Date</th><td>{{ $starlink->last_paid_date?->format('M d, Y') ?: '—' }}</td></tr>
        <tr><th>Advance Paid Until</th><td>{{ $starlink->advance_payment_until?->format('M d, Y') ?: '—' }}</td></tr>
        <tr><th>Next Billing Date</th><td>{{ $notice['next_billing_date']?->format('M d, Y') ?: ($starlink->next_billing_date?->format('M d, Y') ?: '—') }}</td></tr>
        @if($isOverdue)
            <tr><th>Pending Since</th><td>{{ $notice['first_due']?->format('M d, Y') ?: '—' }}</td></tr>
            <tr><th>Overdue Cycles</th><td>{{ (int) ($notice['overdue_cycles'] ?? 0) }}</td></tr>
        @else
            <tr><th>Days Until Due</th><td>{{ isset($notice['days_until']) ? (int) $notice['days_until'] : '—' }}</td></tr>
        @endif
        <tr><th>Contact Email</th><td>{{ $starlink->contact_email ?: '—' }}</td></tr>
        <tr><th>End User Email</th><td>{{ $starlink->end_user_email ?: '—' }}</td></tr>
        <tr><th>PO No.</th><td>{{ $starlink->po_no ?: '—' }}</td></tr>
    </table>

    <div class="footer">
        This notice was generated for admin Starlink billing reminders.
        Manage recipients in Admin → Settings.
    </div>
</body>
</html>
