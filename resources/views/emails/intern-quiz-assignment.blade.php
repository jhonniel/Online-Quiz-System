<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quiz assigned</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">You have been assigned a quiz</h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">Hello {{ $application->first_name }},</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <p style="margin: 0 0 12px 0;">
                    As part of your internship program, please complete the following quiz in the system.
                </p>
                <table cellpadding="0" cellspacing="0" style="width: 100%; margin: 16px 0; background: #f3f4f6; border-radius: 8px;">
                    <tr>
                        <td style="padding: 14px 16px;">
                            <p style="margin: 0 0 6px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">Quiz title</p>
                            <p style="margin: 0; font-size: 17px; font-weight: 700; color: #111827;">{{ $quiz->title }}</p>
                            <p style="margin: 12px 0 6px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">Quiz code</p>
                            <p style="margin: 0; font-size: 18px; font-family: ui-monospace, monospace; font-weight: 600; color: #4f46e5;">{{ $quiz->quiz_code ?: '—' }}</p>
                            @if($dueDate)
                                <p style="margin: 12px 0 0 0; font-size: 13px; color: #4b5563;">
                                    <strong>Due:</strong> {{ \Carbon\Carbon::parse($dueDate)->timezone(config('app.timezone'))->format('F j, Y g:i A') }}
                                </p>
                            @endif
                        </td>
                    </tr>
                </table>
                <p style="margin: 0 0 12px 0;">
                    <strong>What to do next</strong>
                </p>
                <ol style="margin: 0 0 16px 0; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Log in with your account email: <strong>{{ $user->email }}</strong></li>
                    <li style="margin-bottom: 8px;">Open <strong>Quizzes</strong> from your dashboard, or use the button below.</li>
                    <li>If your quiz uses a code entry screen, use the <strong>Quiz code</strong> shown above.</li>
                </ol>
                <p style="margin: 0 0 12px 0;">
                    <a href="{{ $loginUrl }}" style="display: inline-block; padding: 10px 18px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 9999px; font-weight: 600; font-size: 14px;">Log in</a>
                    <a href="{{ $quizzesUrl }}" style="display: inline-block; margin-left: 8px; padding: 10px 18px; background-color: #e0e7ff; color: #3730a3; text-decoration: none; border-radius: 9999px; font-weight: 600; font-size: 14px;">Go to Quizzes</a>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 24px 20px 24px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280;">
                <p style="margin: 0;">Best regards,<br>Infosoft-Studio</p>
            </td>
        </tr>
    </table>
</body>
</html>
