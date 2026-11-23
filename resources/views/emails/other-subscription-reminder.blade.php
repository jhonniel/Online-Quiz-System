<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Other Subscription Reminder</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    @php
        $daysLeft = $subscription->daysUntilEnd();
        $action = $subscription->auto_renew ? 'renew' : 'end';
        $actionLabel = $subscription->auto_renew ? 'will renew' : 'will end';
        if ($daysLeft === 0) {
            $timing = $subscription->auto_renew ? 'renews today' : 'ends today';
        } elseif ($daysLeft === 1) {
            $timing = $actionLabel.' tomorrow';
        } else {
            $timing = $actionLabel.' in '.$daysLeft.' days';
        }
    @endphp
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">
                    Subscription reminder
                </h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                    <strong>{{ $subscription->name }}</strong> {{ $timing }}.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <h2 style="margin: 0 0 8px 0; font-size: 16px; color: #111827;">Subscription details</h2>
                <p style="margin: 0 0 12px 0;">
                    <strong>Name:</strong> {{ $subscription->name }}<br>
                    @if($subscription->provider)
                        <strong>Provider:</strong> {{ $subscription->provider }}<br>
                    @endif
                    <strong>Billing type:</strong> {{ $subscription->billing_type_label }}<br>
                    <strong>Price:</strong> {{ $subscription->formatted_price }}<br>
                    <strong>Start date:</strong> {{ $subscription->starts_on?->format('F d, Y') }}<br>
                    <strong>End date:</strong> {{ $subscription->ends_on?->format('F d, Y') }}<br>
                    <strong>Auto renew:</strong> {{ $subscription->auto_renew ? 'Yes' : 'No' }}
                </p>

                @if($subscription->notes)
                    <h2 style="margin: 0 0 8px 0; font-size: 16px; color: #111827;">Notes</h2>
                    <p style="margin: 0 0 16px 0; white-space: pre-line;">{{ $subscription->notes }}</p>
                @endif

                <p style="margin: 0 0 16px 0;">
                    <a href="{{ url('/admin/other-subscriptions/'.$subscription->uuid.'/edit') }}"
                       style="display: inline-block; padding: 10px 18px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 9999px; font-weight: 600; font-size: 14px;">
                        Review subscription
                    </a>
                </p>

                <p style="margin: 0; font-size: 13px; color: #6b7280;">
                    You are receiving this because your email is listed for Other Subscription reminders.
                    Update notification emails in Admin → Settings.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
