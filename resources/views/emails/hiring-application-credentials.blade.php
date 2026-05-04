<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Application Accepted - Your Account Credentials</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">
                    🎉 Congratulations! Your Application Has Been Accepted
                </h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                    Hello {{ $application->first_name }},
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <p style="margin: 0 0 12px 0;">
                    We are pleased to inform you that your application for the
                    <strong>{{ $position->title ?? $application->position_applied }}</strong> position has been
                    <strong>accepted</strong>!
                </p>

                <h2 style="margin: 16px 0 8px 0; font-size: 16px; color: #111827;">Your Account Credentials</h2>
                <p style="margin: 0 0 12px 0;">
                    Your account has been automatically created and activated. You can now log in to view the status of your application and proceed with the next steps.
                </p>
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

                @if($interviewDate)
                    <h2 style="margin: 16px 0 8px 0; font-size: 16px; color: #111827;">Interview Scheduled</h2>
                    <p style="margin: 0 0 12px 0;">
                        Your interview has been scheduled for:
                    </p>
                    <p style="margin: 0 0 12px 0;">
                        <strong>Date &amp; Time:</strong> {{ \Carbon\Carbon::parse($interviewDate)->format('F j, Y g:i A') }}
                    </p>
                    <p style="margin: 0 0 12px 0;">
                        Please make sure to be available on this date and time. We will contact you with further details about the interview location if needed.
                    </p>
                @else
                    <h2 style="margin: 16px 0 8px 0; font-size: 16px; color: #111827;">Application under review</h2>
                    <p style="margin: 0 0 12px 0;">
                        This email is only a confirmation that we have received your application and that it is <strong>now with our team for review</strong>. It is <strong>not</strong> an interview invitation and does <strong>not</strong> schedule an interview.
                    </p>
                    <p style="margin: 0 0 12px 0;">
                        We will send you a <strong>separate email</strong> after our team has finished reviewing your application. Please watch your inbox (including spam or promotions folders).
                    </p>
                @endif

                <h2 style="margin: 16px 0 8px 0; font-size: 16px; color: #111827;">Important Notes</h2>
                <ul style="margin: 0 0 12px 20px; padding: 0;">
                    <li>Please keep your credentials secure and do not share them with anyone.</li>
                    <li>You can change your password after logging in.</li>
                    <li>Your account is already active, so you can log in immediately.</li>
                    <li>Use the login button above or visit our website to access your account.</li>
                    @if($position && strcasecmp($position->employment_type ?? '', 'Internship') === 0)
                        <li><strong>Please read the TOR (Term of Reference) PDF file attached to this email.</strong></li>
                    @endif
                </ul>

                <p style="margin: 0 0 0 0;">
                    Thank you for your interest in joining our team. We look forward to working with you!
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

