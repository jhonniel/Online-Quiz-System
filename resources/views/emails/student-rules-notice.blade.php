<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rules notice</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">
                    @if($noticeType === 'final')
                        Final notice
                    @else
                        Rules violation notice
                    @endif
                </h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                    Hello {{ $user->name }},
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <p style="margin: 0 0 12px 0;">
                    A notice has been applied to your student account in <strong>{{ config('app.name') }}</strong>.
                    Please review the <strong>Terms of Reference (TOR)</strong> for any rules you may have violated
                    and comply with the rules and regulations going forward.
                </p>
                @if(!empty($noticeMessage))
                    <p style="margin: 0 0 12px 0; padding: 12px 14px; background: #fef3c7; border-radius: 8px; border: 1px solid #fcd34d; color: #78350f;">
                        <strong>Message from administration:</strong><br>
                        {{ $noticeMessage }}
                    </p>
                @endif
                <p style="margin: 0 0 16px 0;">
                    <a href="{{ $torUrl }}"
                       style="display: inline-block; padding: 10px 18px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 9999px; font-weight: 600; font-size: 14px;">
                        View TOR (Terms of Reference)
                    </a>
                </p>
                <p style="margin: 0; font-size: 13px; color: #6b7280;">
                    After signing in, you can also open the TOR from your student portal when available.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
