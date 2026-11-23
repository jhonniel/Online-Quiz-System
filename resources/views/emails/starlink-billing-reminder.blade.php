<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Starlink Billing Reminder</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    @php
        $isOverdue = ($notice['kind'] ?? null) === 'overdue';
        $daysUntil = $notice['days_until'] ?? null;
        if ($isOverdue) {
            $cycles = (int) ($notice['overdue_cycles'] ?? 0);
            $timing = $cycles > 1
                ? "has {$cycles} overdue billing cycles"
                : 'has overdue billing';
        } elseif ($daysUntil === 0) {
            $timing = 'is due for billing today';
        } elseif ($daysUntil === 1) {
            $timing = 'is due for billing tomorrow';
        } else {
            $timing = 'is due for billing in '.(int) $daysUntil.' days';
        }
        $deviceLabel = $starlink->starlink_id ?: ($starlink->serial_number ?: ('#'.$starlink->id));
        $account = $starlink->account_linked_email ?: ($starlink->linkedAccount->email ?? '—');
    @endphp
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">
                    {{ $isOverdue ? 'Starlink overdue billing' : 'Starlink upcoming billing' }}
                </h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                    Device <strong>{{ $deviceLabel }}</strong> {{ $timing }}.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <h2 style="margin: 0 0 8px 0; font-size: 16px; color: #111827;">Device details</h2>
                <p style="margin: 0 0 12px 0;">
                    <strong>Starlink ID:</strong> {{ $starlink->starlink_id ?: '—' }}<br>
                    <strong>Serial:</strong> {{ $starlink->serial_number ?: '—' }}<br>
                    <strong>Kit:</strong> {{ $starlink->kit_number ?: '—' }}<br>
                    <strong>Account/Email:</strong> {{ $account }}<br>
                    @if($starlink->linkedAccount?->name)
                        <strong>Linked account:</strong> {{ $starlink->linkedAccount->name }}<br>
                    @endif
                    <strong>Location:</strong> {{ $starlink->office_location ?: '—' }}<br>
                    <strong>Client:</strong> {{ $starlink->municipality ?: '—' }}<br>
                    <strong>Plan:</strong> {{ $starlink->plan ?: ($starlink->subscriptionPlanType?->name ?: '—') }}<br>
                    <strong>Status:</strong> {{ $starlink->status ?: '—' }}<br>
                    <strong>Billing interval:</strong> {{ ucfirst($starlink->billing_interval ?? 'monthly') }}<br>
                    <strong>Last paid:</strong> {{ $starlink->last_paid_date?->format('F d, Y') ?: '—' }}<br>
                    <strong>Advance paid until:</strong> {{ $starlink->advance_payment_until?->format('F d, Y') ?: '—' }}<br>
                    @if($isOverdue)
                        <strong>Pending since:</strong> {{ $notice['first_due']?->format('F d, Y') ?: '—' }}<br>
                        <strong>Overdue cycles:</strong> {{ (int) ($notice['overdue_cycles'] ?? 0) }}
                    @else
                        <strong>Next billing date:</strong> {{ $notice['next_billing_date']?->format('F d, Y') ?: '—' }}
                    @endif
                </p>

                <p style="margin: 0 0 16px 0; padding: 12px; background: {{ $isOverdue ? '#fff7ed' : '#eef2ff' }}; border-radius: 8px; color: {{ $isOverdue ? '#9a3412' : '#3730a3' }};">
                    A PDF summary for this device is attached to this email.
                </p>

                <p style="margin: 0 0 16px 0;">
                    <a href="{{ url('/admin/billing') }}"
                       style="display: inline-block; padding: 10px 18px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 9999px; font-weight: 600; font-size: 14px;">
                        Open billing
                    </a>
                    &nbsp;
                    <a href="{{ url('/admin/starlinks/'.$starlink->id.'/edit') }}"
                       style="display: inline-block; padding: 10px 18px; background-color: #e5e7eb; color: #111827; text-decoration: none; border-radius: 9999px; font-weight: 600; font-size: 14px;">
                        Edit device
                    </a>
                </p>

                <p style="margin: 0; font-size: 13px; color: #6b7280;">
                    You are receiving this because your email is listed for Starlink billing reminders.
                    Update notification emails in Admin → Settings.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
