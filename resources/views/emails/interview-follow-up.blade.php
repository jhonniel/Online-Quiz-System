<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Interview Follow-Up</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">
                    Interview Follow-Up
                </h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                    Hello {{ $application->first_name }},
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                @php
                    $positionTitle = $position ? $position->title : ($application->position_applied ?? 'position');
                @endphp
                <p style="margin: 0 0 12px 0;">
                    We hope this message finds you well. This is a follow-up regarding your scheduled interview for the
                    <strong>{{ $positionTitle }}</strong> position.
                </p>

                <h2 style="margin: 16px 0 8px 0; font-size: 16px; color: #111827;">Interview Details</h2>
                <p style="margin: 0 0 8px 0;">
                    <strong>Date &amp; Time:</strong> {{ $interviewDate->format('F j, Y \\a\\t g:i A') }}
                </p>
                @if(($application->interview_format ?? 'on_site') === 'online' && $application->interview_meeting_link)
                    <p style="margin: 0 0 12px 0;">
                        <strong>Meeting link:</strong>
                        <a href="{{ $application->interview_meeting_link }}" style="color: #2563eb; text-decoration: underline; word-break: break-all;">{{ $application->interview_meeting_link }}</a>
                    </p>
                @endif
                @if($application->interview_date)
                    <p style="margin: 0 0 12px 0;">
                        <strong>Original Schedule:</strong> {{ $application->interview_date->format('F j, Y \\a\\t g:i A') }}
                    </p>
                @endif

                <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 16px 0;">

                <h2 style="margin: 0 0 8px 0; font-size: 16px; color: #111827;">Need to Reschedule?</h2>
                <p style="margin: 0 0 12px 0;">
                    If you need to reschedule your interview, please contact us through our social media channels:
                </p>
                @if($socialMediaLink)
                    <p style="margin: 0 0 12px 0;">
                        <strong>Contact us here:</strong>
                        <a href="{{ $socialMediaLink }}" style="color: #2563eb; text-decoration: underline;">
                            {{ $socialMediaLink }}
                        </a>
                    </p>
                @else
                    <p style="margin: 0 0 12px 0;">
                        Please contact us through our official social media channels to discuss rescheduling options.
                    </p>
                @endif

                <p style="margin: 0 0 16px 0;">
                    We understand that circumstances can change, and we're happy to work with you to find a time that works for both parties.
                </p>

                <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 16px 0;">

                <h2 style="margin: 0 0 8px 0; font-size: 16px; color: #111827;">What to Expect</h2>
                <p style="margin: 0 0 12px 0;">
                    Please come prepared for the interview. If you have any questions before the interview, feel free to reach out to us.
                </p>

                <p style="margin: 0;">
                    We look forward to meeting with you!
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

