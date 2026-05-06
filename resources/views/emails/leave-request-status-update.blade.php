<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Request Status Update</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                @php
                    $statusLabel = $status === 'resubmission_requested' || ($status === 'pending' && $leaveRequest->reviewed_at)
                        ? 'Resubmission Required'
                        : ($status === 'for_more_verification'
                            ? 'For More Verification'
                            : ucfirst($status));
                @endphp
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">
                    Request Status Update
                </h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                    Your {{ $leaveRequest->type_label }} request has been <strong>{{ $statusLabel }}</strong>.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <p style="margin: 0 0 12px 0;">
                    Hello {{ $leaveRequest->user->name }},
                </p>

                <h2 style="margin: 0 0 8px 0; font-size: 16px; color: #111827;">Request Details</h2>
                <p style="margin: 0 0 12px 0;">
                    <strong>Request Type:</strong> {{ $leaveRequest->type_label }}<br>
                    <strong>Start Date:</strong> {{ $leaveRequest->start_date->format('F d, Y') }}<br>
                    @if($leaveRequest->end_date && $leaveRequest->end_date->format('Y-m-d') !== $leaveRequest->start_date->format('Y-m-d'))
                        <strong>End Date:</strong> {{ $leaveRequest->end_date->format('F d, Y') }}<br>
                        <strong>Duration:</strong> {{ $leaveRequest->duration_display_label }}<br>
                    @else
                        <strong>Date:</strong> {{ $leaveRequest->start_date->format('F d, Y') }}<br>
                    @endif
                </p>

                @php
                    $reviewerNotes = isset($adminNotes) ? trim((string) $adminNotes) : '';
                @endphp
                @if($reviewerNotes !== '')
                    <h2 style="margin: 0 0 8px 0; font-size: 16px; color: #111827;">Notes from reviewer</h2>
                    <p style="margin: 0 0 12px 0; white-space: pre-line;">
                        {{ $reviewerNotes }}
                    </p>
                @endif

                <p style="margin: 0 0 12px 0;">
                    @if($status === 'approved')
                        ✅ Your {{ $leaveRequest->type_label }} request has been <strong>{{ $statusLabel }}</strong>. Please make sure to coordinate with your team regarding your absence.
                    @elseif($status === 'rejected')
                        ❌ Your {{ $leaveRequest->type_label }} request has been <strong>{{ $statusLabel }}</strong>. If you have any questions, please contact your supervisor or HR.
                    @elseif($status === 'for_more_verification')
                        🔎 Your {{ $leaveRequest->type_label }} request is now <strong>{{ $statusLabel }}</strong>. The reviewer needs additional checks or clarifications. Please review the notes above and wait for the next update.
                    @elseif($status === 'resubmission_requested' || ($status === 'pending' && $leaveRequest->reviewed_at))
                        ⚠️ Your {{ $leaveRequest->type_label }} request has been <strong>{{ $statusLabel }}</strong>. Please review the reviewer notes above (if any) and make the necessary corrections. You can edit your request from your leave requests page.
                    @endif
                </p>

                <p style="margin: 0 0 16px 0;">
                    <a href="{{ url('/leave-requests/' . $leaveRequest->id) }}"
                       style="display: inline-block; padding: 10px 18px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 9999px; font-weight: 600; font-size: 14px;">
                        View Leave Request Details
                    </a>
                </p>

                <p style="margin: 0; font-size: 12px; color: #6b7280;">
                    <strong>Request ID:</strong> #{{ $leaveRequest->id }}<br>
                    <strong>Submitted:</strong> {{ $leaveRequest->created_at->format('F j, Y \\a\\t g:i A') }}<br>
                    <strong>Status Updated:</strong> {{ $leaveRequest->reviewed_at ? $leaveRequest->reviewed_at->format('F j, Y \\a\\t g:i A') : now()->format('F j, Y \\a\\t g:i A') }}
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 24px 20px 24px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280;">
                <p style="margin: 0;">
                    Thanks,<br>
                    Infosoft-Studio
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

