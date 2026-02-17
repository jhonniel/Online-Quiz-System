<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $isReschedule ? 'Interview Rescheduled' : 'Interview Scheduled' }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">
                    {{ $isReschedule ? '📅 Interview Rescheduled' : '📅 Interview Scheduled' }}
                </h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                    Hello {{ $application->first_name }},
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <p style="margin: 0 0 12px 0;">
                    Your interview for the <strong>{{ $position->title ?? $application->position_applied }}</strong>
                    position has been <strong>{{ $isReschedule ? 'rescheduled' : 'scheduled' }}</strong>.
                </p>

                <h2 style="margin: 16px 0 8px 0; font-size: 16px; color: #111827;">Interview Details</h2>
                <p style="margin: 0 0 12px 0;">
                    <strong>Date &amp; Time:</strong> {{ \Carbon\Carbon::parse($interviewDate)->format('F j, Y g:i A') }}
                </p>

                @if($address)
                    <p style="margin: 0 0 12px 0;">
                        <strong>Location:</strong><br>
                        {{ $address }}
                    </p>
                @endif

                @if($adminNotes)
                    <h3 style="margin: 16px 0 8px 0; font-size: 15px; color: #111827;">Additional Information</h3>
                    <p style="margin: 0 0 12px 0; white-space: pre-line;">
                        {{ $adminNotes }}
                    </p>
                @endif

                <p style="margin: 0 0 12px 0;">
                    @if($isReschedule)
                        Please note the new interview date above. If you have any questions or concerns, please contact us.
                    @else
                        Please make sure to be available on this date. We will contact you with further details about the interview location and time.
                    @endif
                </p>

                <p style="margin: 0;">
                    Thank you for your interest in joining our team. We look forward to meeting you!
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 24px 20px 24px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280;">
                <p style="margin: 0;">
                    Best regards,<br>
                    Infosoft-Studio
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

