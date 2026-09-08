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
                    @if($status === 'hired' && ($isInternshipHire ?? false))
                        Internship Application Accepted
                    @elseif($status === 'hired')
                        Welcome to the Team
                    @else
                        Application Status Update
                    @endif
                </h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                    @if($status === 'rejected')
                        Application Update
                    @elseif($status === 'hired' && ($isInternshipHire ?? false))
                        Your internship application has been accepted
                    @elseif($status === 'hired')
                        You have been selected to join our team
                    @else
                        Hello {{ $application->first_name }},
                    @endif
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                @php
                    $positionTitle = $position?->title ?? ($application->position_applied ?? 'Position');
                    $applicantName = trim($application->full_name ?? trim(($application->first_name ?? '').' '.($application->last_name ?? '')));
                    $startDate = $application->start_date ?? null;
                    if ($startDate && ! $startDate instanceof \Carbon\Carbon) {
                        $startDate = \Carbon\Carbon::parse($startDate);
                    }
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
                    <p style="margin: 0 0 16px 0;">
                        Dear {{ $applicantName !== '' ? $applicantName : 'Applicant' }},
                    </p>
                    <p style="margin: 0 0 12px 0;">
                        Thank you for your interest in becoming part of our team and for considering us as part of your career journey.
                    </p>
                    <p style="margin: 0 0 12px 0;">
                        After careful consideration, we have decided to move forward with another candidate at this time. While we will not be proceeding with your application, we truly appreciate your interest and the time you have invested in the process.
                    </p>
                    <p style="margin: 0 0 12px 0;">
                        We encourage you to continue pursuing opportunities that match your skills and goals, and we wish you continued success in your future endeavors.
                    </p>

                    @if(trim((string) ($statusMessage ?? '')) !== '')
                        <p style="margin: 0 0 12px 0; white-space: pre-line;">
                            {{ trim($statusMessage) }}
                        </p>
                    @endif

                    <p style="margin: 0 0 12px 0;">
                        Thank you once again for your interest in our organization.
                    </p>

                @elseif($status === 'hired' && ($isInternshipHire ?? false))
                    <p style="margin: 0 0 16px 0;">
                        Dear {{ $applicantName !== '' ? $applicantName : 'Applicant' }},
                    </p>
                    <p style="margin: 0 0 12px 0;">
                        Congratulations! Your internship application has been accepted.
                    </p>

                    @if(trim((string) ($statusMessage ?? '')) !== '' && trim((string) $statusMessage) !== 'Congratulations! Your internship application has been accepted.')
                        <p style="margin: 0 0 12px 0; white-space: pre-line;">
                            {{ trim($statusMessage) }}
                        </p>
                    @endif

                    <p style="margin: 0 0 12px 0;">
                        We look forward to working with you.
                    </p>

                @elseif($status === 'hired')
                    <p style="margin: 0 0 16px 0;">
                        Dear {{ $applicantName !== '' ? $applicantName : 'Applicant' }},
                    </p>
                    <p style="margin: 0 0 12px 0;">
                        We are pleased to inform you that you have been selected to join our team.
                    </p>

                    @if($startDate)
                        <p style="margin: 0 0 12px 0;">
                            Your start date will be <strong>{{ $startDate->format('F j, Y') }}</strong>. Please make sure to be available and ready to begin on this date. Further details regarding your schedule, responsibilities, and other onboarding information will be provided separately.
                        </p>
                    @else
                        <p style="margin: 0 0 12px 0;">
                            Further details regarding your start date, schedule, responsibilities, and other onboarding information will be provided separately.
                        </p>
                    @endif

                    <p style="margin: 0 0 12px 0;">
                        We are excited to have you join us and look forward to working with you.
                    </p>
                    <p style="margin: 0 0 12px 0;">
                        Welcome to the team!
                    </p>

                    @if(trim((string) ($statusMessage ?? '')) !== '')
                        <p style="margin: 0 0 12px 0; white-space: pre-line;">
                            {{ trim($statusMessage) }}
                        </p>
                    @endif

                @else
                    <h2 style="margin: 0 0 8px 0; font-size: 18px; color: #111827;">
                        Application Status: {{ ucfirst($status) }}
                    </h2>
                    <p style="margin: 0 0 12px 0;">
                        Your application for the <strong>{{ $positionTitle }}</strong> position status has been updated
                        to <strong>{{ ucfirst($status) }}</strong>.
                    </p>
                @endif

                @if($statusMessage && ! in_array($status, ['rejected', 'hired'], true))
                    <h3 style="margin: 16px 0 8px 0; font-size: 16px; color: #111827;">Additional Information</h3>
                    <p style="margin: 0 0 12px 0; white-space: pre-line;">
                        {{ $statusMessage }}
                    </p>
                @endif

                @if(! in_array($status, ['rejected', 'hired'], true))
                <p style="margin: 16px 0 0 0;">
                    Thank you for your interest in joining our team.
                </p>
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 24px 20px 24px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280;">
                <p style="margin: 0;">
                    Best regards,<br>
                    {{ in_array($status, ['rejected', 'hired'], true) ? 'Infosoft' : 'Infosoft-Studio' }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
