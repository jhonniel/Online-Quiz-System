<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Hiring Application Received</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">
                    New Hiring Application Received
                </h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                    A new hiring application has been submitted for the
                    <strong>{{ $position->title ?? $application->position_applied }}</strong> position.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <h2 style="margin: 0 0 8px 0; font-size: 16px; color: #111827;">Applicant Information</h2>
                <ul style="margin: 0 0 16px 20px; padding: 0;">
                    <li><strong>Name:</strong> {{ $application->first_name }} {{ $application->last_name }}</li>
                    <li><strong>Email:</strong> {{ $application->email }}</li>
                    <li><strong>Phone:</strong> {{ $application->phone }}</li>
                    @if($application->address)
                        <li><strong>Address:</strong> {{ $application->address }}</li>
                    @endif
                    @if($application->birth_date)
                        <li><strong>Date of Birth:</strong> {{ $application->birth_date->format('F j, Y') }}</li>
                    @endif
                </ul>

                @if($application->cover_letter)
                    <h2 style="margin: 0 0 8px 0; font-size: 16px; color: #111827;">Cover Letter</h2>
                    <p style="margin: 0 0 16px 0; white-space: pre-line;">
                        {{ $application->cover_letter }}
                    </p>
                @endif

                @if($application->resume_link)
                    <h2 style="margin: 0 0 8px 0; font-size: 16px; color: #111827;">Resume</h2>
                    <p style="margin: 0 0 16px 0;">
                        <a href="{{ $application->resume_link }}" style="color: #2563eb; text-decoration: underline;">
                            View Resume
                        </a>
                    </p>
                @endif

                <p style="margin: 0 0 16px 0;">
                    <a href="{{ url('/admin/hiring-applications/' . $application->id) }}"
                       style="display: inline-block; padding: 10px 18px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 9999px; font-weight: 600; font-size: 14px;">
                        View Application Details
                    </a>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 24px 20px 24px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280;">
                <p style="margin: 0;">
                    Thanks,<br>
                    {{ config('app.name') }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

