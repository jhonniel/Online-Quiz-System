<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Account Credentials</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">
                    Your Account Credentials
                </h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                    Hello {{ $user->name }},
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <p style="margin: 0 0 12px 0;">
                    Your account has been created in our system. Below are your login credentials:
                </p>

                <h2 style="margin: 16px 0 8px 0; font-size: 16px; color: #111827;">Your Account Credentials</h2>
                <p style="margin: 0 0 12px 0;">
                    <strong>Email:</strong> {{ $email }}<br>
                    <strong>Password:</strong> {{ $password }}
                </p>

                <p style="margin: 0 0 16px 0;">
                    <a href="{{ $loginUrl }}"
                       style="display: inline-block; padding: 10px 18px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 9999px; font-weight: 600; font-size: 14px;">
                        Login to Your Account
                    </a>
                </p>

                <h2 style="margin: 16px 0 8px 0; font-size: 16px; color: #111827;">Important Notes</h2>
                <ul style="margin: 0 0 12px 20px; padding: 0;">
                    <li>Please keep your credentials secure and do not share them with anyone.</li>
                    <li>You can change your password after logging in.</li>
                    <li>Use the login button above or visit: {{ $loginUrl }}</li>
                </ul>

                <p style="margin: 0;">
                    If you have any questions or need assistance, please contact the administrator.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 24px 20px 24px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280;">
                <p style="margin: 0;">
                    Best regards,<br>
                    {{ config('app.name') }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>


