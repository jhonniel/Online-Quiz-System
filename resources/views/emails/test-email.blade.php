<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Email</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 24px 24px 16px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0; font-size: 20px; color: #111827;">
                    Test Email from {{ config('app.name') }}
                </h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; color: #374151; font-size: 14px; line-height: 1.6;">
                <p style="margin: 0 0 12px 0;">
                    This is a test email from your <strong>{{ config('app.name') }}</strong> system.
                </p>
                <p style="margin: 0 0 16px 0;">
                    If you received this email, it means your email configuration is working correctly.
                </p>
                <p style="margin: 0 0 8px 0; font-weight: 600;">
                    Email Configuration Details:
                </p>
                <ul style="margin: 0 0 16px 20px; padding: 0;">
                    <li>Mail Driver: {{ config('mail.default') }}</li>
                    <li>From Address: {{ config('mail.from.address') }}</li>
                    <li>From Name: {{ config('mail.from.name') }}</li>
                </ul>
                <p style="margin: 0 0 4px 0; font-size: 12px; color: #6b7280;">
                    Sent at: {{ now()->format('F j, Y \\a\\t g:i A') }}
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 24px 20px 24px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280;">
                <p style="margin: 0 0 4px 0;">
                    This is an automated test email. No action is required.
                </p>
                <p style="margin: 0;">
                    Thanks,<br>
                    {{ config('app.name') }} System
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

