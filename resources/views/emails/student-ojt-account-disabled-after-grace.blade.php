<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Account disabled</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">Student account disabled</h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">Hello {{ $user->name }},</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <p style="margin: 0 0 12px 0;">
                    Your student account in <strong>{{ $appName }}</strong> has been <strong>automatically disabled</strong>
                    because the post–OJT completion access period has ended.
                </p>
                <p style="margin: 0 0 12px 0;">
                    You cannot sign in until the <strong>administration reactivates</strong> your account.
                    Please contact the administration if you still need access or believe this was done in error.
                </p>
                <p style="margin: 0; font-size: 13px; color: #6b7280;">
                    This notice applies to student accounts only and is sent when the scheduled access period after completing required OJT hours has finished.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
