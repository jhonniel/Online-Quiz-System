<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>OJT hours completed</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">Congratulations</h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">Hello {{ $user->name }},</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <p style="margin: 0 0 12px 0;">
                    You have completed the <strong>required OJT (on-the-job training) hours</strong> recorded in
                    <strong>{{ $appName }}</strong>. Well done on reaching this milestone.
                </p>
                <p style="margin: 0 0 12px 0;">
                    Your student account will be <strong>automatically disabled</strong> once <strong>{{ $graceDays }} full days</strong>
                    have passed from your recorded completion date
                    (<strong>{{ $requirementMetAt->timezone(config('app.timezone'))->format('F j, Y') }}</strong>).
                    After that date, you will no longer be able to sign in until the administration reactivates your account.
                </p>
                <p style="margin: 0 0 12px 0; padding: 12px 14px; background: #eff6ff; border-radius: 8px; border: 1px solid #bfdbfe; color: #1e3a8a;">
                    <strong>Access end (scheduled):</strong>
                    {{ $accessEndDate->timezone(config('app.timezone'))->format('F j, Y') }}
                    (start of that day, {{ config('app.timezone') }}).
                </p>
                <p style="margin: 0; font-size: 13px; color: #6b7280;">
                    If you need your access extended or have questions, please contact your coordinator or the administration before your access ends.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
