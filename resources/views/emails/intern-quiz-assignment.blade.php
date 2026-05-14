<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quiz assigned</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    @php
        $quizCount = $quizzes->count();
    @endphp
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">
                    @if($quizCount === 1)
                        You have been assigned a quiz
                    @else
                        You have been assigned {{ $quizCount }} quizzes
                    @endif
                </h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">Hello {{ $application->first_name }},</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <p style="margin: 0 0 12px 0;">
                    As part of your internship program, please complete the following quiz{{ $quizCount === 1 ? '' : 'zes' }} in the system. Each row shows the quiz title and the <strong>quiz code</strong> to use if prompted.
                </p>
                <p style="margin: 0 0 16px 0; padding: 14px 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 14px; color: #14532d; line-height: 1.55;">
                    <strong>Important:</strong> Completing {{ $quizCount === 1 ? 'this quiz' : 'these quizzes' }} is a required step in your internship process so you can move forward. After you have finished {{ $quizCount === 1 ? 'it' : 'them' }}, our team will send you a <strong>schedule for your interview</strong> by email.
                </p>
                <table cellpadding="0" cellspacing="0" width="100%" style="margin: 16px 0; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr style="background: #f3f4f6;">
                            <th align="left" style="padding: 10px 14px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Quiz title</th>
                            <th align="left" style="padding: 10px 14px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Quiz code</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quizzes as $q)
                            <tr style="background: {{ $loop->even ? '#fafafa' : '#ffffff' }};">
                                <td style="padding: 12px 14px; font-size: 15px; font-weight: 600; color: #111827; border-bottom: 1px solid #f3f4f6; vertical-align: top;">{{ $q->title }}</td>
                                <td style="padding: 12px 14px; font-size: 16px; font-family: ui-monospace, monospace; font-weight: 600; color: #4f46e5; border-bottom: 1px solid #f3f4f6; vertical-align: top;">{{ $q->quiz_code ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($dueDate)
                    <p style="margin: 0 0 16px 0; padding: 12px 14px; background: #eff6ff; border-radius: 8px; font-size: 13px; color: #1e3a8a;">
                        <strong>Due date (all listed above):</strong> {{ \Carbon\Carbon::parse($dueDate)->timezone(config('app.timezone'))->format('F j, Y g:i A') }}
                    </p>
                @endif
                <p style="margin: 0 0 12px 0;">
                    <strong>What to do next</strong>
                </p>
                <ol style="margin: 0 0 16px 0; padding-left: 20px;">
                    <li style="margin-bottom: 8px;">Log in with your account email: <strong>{{ $user->email }}</strong></li>
                    <li style="margin-bottom: 8px;">Open <strong>Quizzes</strong> from your dashboard, or use the button below.</li>
                    <li style="margin-bottom: 8px;">If your quiz uses a code entry screen, use the <strong>Quiz code</strong> from the table above for that quiz.</li>
                    <li style="margin-bottom: 0;">Complete every assigned quiz—this is part of moving forward with your internship. Once you are done, we will email you with your <strong>interview schedule</strong>.</li>
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
