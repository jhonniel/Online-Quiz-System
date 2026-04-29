<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Problem Report - Ticket #{{ $ticket->ticket_number }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 680px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">New Problem Report Submitted</h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                    A new report-problem ticket was submitted and needs review.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <p style="margin: 0 0 10px 0;"><strong>Ticket #:</strong> <span style="color: #2563eb;">{{ $ticket->ticket_number }}</span></p>
                <p style="margin: 0 0 10px 0;"><strong>Type:</strong> {{ $ticket->type_label }}</p>
                <p style="margin: 0 0 10px 0;"><strong>Reporter:</strong> {{ $ticket->full_name }}</p>
                <p style="margin: 0 0 10px 0;"><strong>Email:</strong> {{ $ticket->email }}</p>
                @if(!empty($ticket->contact_number))
                    <p style="margin: 0 0 10px 0;"><strong>Contact:</strong> {{ $ticket->contact_number }}</p>
                @endif
                @if(!empty($ticket->office))
                    <p style="margin: 0 0 10px 0;"><strong>Office:</strong> {{ $ticket->office }}</p>
                @endif
                @if(!empty($ticket->address))
                    <p style="margin: 0 0 10px 0;"><strong>Address:</strong> {{ $ticket->address }}</p>
                @endif
                <p style="margin: 12px 0 6px 0;"><strong>Description:</strong></p>
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; white-space: pre-wrap;">{{ $ticket->description }}</div>
                <p style="margin: 16px 0 0 0;">
                    <a href="{{ url('/admin/tickets/' . $ticket->id) }}" style="display: inline-block; background: #4f46e5; color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 6px; font-weight: 600;">
                        Open Ticket in Admin
                    </a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
