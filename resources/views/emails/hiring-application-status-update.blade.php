<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Application Status Update</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">
                    Application Status Update
                </h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                    Hello {{ $application->first_name }},
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                @php
                    $positionTitle = $position?->title ?? ($application->position_applied ?? 'Position');
                @endphp

                @if($status === 'accepted')
                    <h2 style="margin: 0 0 8px 0; font-size: 18px; color: #16a34a;">🎉 Congratulations!</h2>
                    <p style="margin: 0 0 12px 0;">
                        Your application for the <strong>{{ $positionTitle }}</strong> position has been <strong>accepted</strong>!
                    </p>

                    @if($application->acceptance_token)
                        <p style="margin: 0 0 12px 0;">
                            <strong>Please proceed</strong> to create your account and continue with the next steps in the hiring process.
                        </p>
                        <p style="margin: 0 0 16px 0;">
                            <a href="{{ url('/hiring/accept/' . $application->acceptance_token) }}"
                               style="display: inline-block; padding: 10px 18px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 9999px; font-weight: 600; font-size: 14px;">
                                Proceed to Create Account
                            </a>
                        </p>
                    @else
                        <p style="margin: 0 0 12px 0;">
                            <strong>Please proceed</strong> with the next steps as instructed by our team.
                        </p>
                    @endif

                @elseif($status === 'rejected')
                    <h2 style="margin: 0 0 8px 0; font-size: 18px; color: #b91c1c;">Application Update</h2>
                    <p style="margin: 0 0 12px 0;">
                        We regret to inform you that your application for the <strong>{{ $positionTitle }}</strong> position has been
                        <strong>rejected</strong> at this time.
                    </p>
                @else
                    <h2 style="margin: 0 0 8px 0; font-size: 18px; color: #111827;">
                        Application Status: {{ ucfirst($status) }}
                    </h2>
                    <p style="margin: 0 0 12px 0;">
                        Your application for the <strong>{{ $positionTitle }}</strong> position status has been updated
                        to <strong>{{ ucfirst($status) }}</strong>.
                    </p>
                @endif

                @if($statusMessage)
                    <h3 style="margin: 16px 0 8px 0; font-size: 16px; color: #111827;">Additional Information</h3>
                    <p style="margin: 0 0 12px 0; white-space: pre-line;">
                        {{ $statusMessage }}
                    </p>
                @endif

                <p style="margin: 16px 0 0 0;">
                    Thank you for your interest in joining our team.
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

