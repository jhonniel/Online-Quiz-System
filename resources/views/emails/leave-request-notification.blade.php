<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Leave Request Submitted</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">
                    New Request Submitted
                </h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                    A new {{ $leaveRequest->type_label }} request has been submitted and requires your review.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <h2 style="margin: 0 0 8px 0; font-size: 16px; color: #111827;">Employee Information</h2>
                <p style="margin: 0 0 12px 0;">
                    <strong>Name:</strong> {{ $leaveRequest->user->name }}<br>
                    <strong>Email:</strong> {{ $leaveRequest->user->email }}
                </p>

                <h2 style="margin: 0 0 8px 0; font-size: 16px; color: #111827;">Request Details</h2>
                <p style="margin: 0 0 12px 0;">
                    <strong>Request Type:</strong> {{ $leaveRequest->type_label }}<br>
                    <strong>Start Date:</strong> {{ $leaveRequest->start_date->format('F d, Y') }}<br>
                    @if($leaveRequest->end_date && $leaveRequest->end_date->format('Y-m-d') !== $leaveRequest->start_date->format('Y-m-d'))
                        <strong>End Date:</strong> {{ $leaveRequest->end_date->format('F d, Y') }}<br>
                        <strong>Duration:</strong> {{ $leaveRequest->days }} {{ $leaveRequest->days == 1 ? 'day' : 'days' }}<br>
                    @else
                        <strong>Date:</strong> {{ $leaveRequest->start_date->format('F d, Y') }}<br>
                    @endif
                    <strong>Status:</strong> Pending Review
                </p>

                @if($leaveRequest->reason)
                    <h2 style="margin: 0 0 8px 0; font-size: 16px; color: #111827;">Reason</h2>
                    <p style="margin: 0 0 16px 0; white-space: pre-line;">
                        {{ $leaveRequest->reason }}
                    </p>
                @endif

                <p style="margin: 0 0 16px 0;">
                    <a href="{{ url('/admin/leave-requests/' . $leaveRequest->id) }}"
                       style="display: inline-block; padding: 10px 18px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 9999px; font-weight: 600; font-size: 14px;">
                        Review Leave Request
                    </a>
                </p>

                <p style="margin: 0; font-size: 12px; color: #6b7280;">
                    <strong>Request ID:</strong> #{{ $leaveRequest->id }}<br>
                    <strong>Submitted:</strong> {{ $leaveRequest->created_at->format('F j, Y \\a\\t g:i A') }}
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

