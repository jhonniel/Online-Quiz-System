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

                @php
                    $effectiveMeetingLink = trim((string) ($meetingLink ?? $application->interview_meeting_link ?? ''));
                @endphp

                <p style="margin: 0 0 12px 0;">
                    @if(($interviewFormat ?? 'on_site') === 'online')
                        This interview will be held <strong>online</strong>@if($effectiveMeetingLink !== ''). Use the meeting link below at the scheduled date and time.@endif
                    @else
                        This interview will be held <strong>on-site</strong>. See the location below when we have provided an address.
                    @endif
                </p>

                <h2 style="margin: 16px 0 8px 0; font-size: 16px; color: #111827;">Interview Details</h2>
                <p style="margin: 0 0 12px 0;">
                    <strong>Date &amp; Time:</strong> {{ \Carbon\Carbon::parse($interviewDate)->format('F j, Y g:i A') }}
                </p>

                <p style="margin: 0 0 12px 0;">
                    <strong>Format:</strong>
                    @if(($interviewFormat ?? 'on_site') === 'online')
                        Online (virtual meeting)
                    @else
                        On-site
                    @endif
                </p>

                @if(($interviewFormat ?? 'on_site') === 'online' && $effectiveMeetingLink !== '')
                    <p style="margin: 0 0 12px 0;">
                        <strong>Meeting link:</strong><br>
                        <a href="{{ $effectiveMeetingLink }}" style="color: #2563eb; text-decoration: underline; word-break: break-all;">{{ $effectiveMeetingLink }}</a>
                    </p>
                @elseif(($interviewFormat ?? 'on_site') === 'online')
                    <p style="margin: 0 0 12px 0;">
                        <strong>Meeting link:</strong> We will send or confirm your meeting link separately. If you need it urgently, please reply to this email.
                    </p>
                @elseif(($interviewFormat ?? 'on_site') === 'on_site' && $address)
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
                        Please note the new interview details above. If you have any questions or concerns, please contact us.
                    @elseif(($interviewFormat ?? 'on_site') === 'online')
                        @if($effectiveMeetingLink !== '')
                            Please join using the meeting link at the scheduled time. If you have trouble accessing the link, reply to this email or contact us.
                        @else
                            If you have any questions about this online interview, please reply to this email or contact us.
                        @endif
                    @else
                        Please make sure to be available on this date. We will contact you with further details about the interview location if needed.
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

