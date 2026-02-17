<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Report Received – Ticket #{{ $ticket->ticket_number }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f9fafb; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #111827;">
                    Report Received
                </h1>
                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                    Your ticket has been submitted. Please save your ticket number for reference.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px 24px; font-size: 14px; color: #374151; line-height: 1.6;">
                <p style="margin: 0 0 16px 0;">
                    <strong>Ticket number:</strong> <span style="font-size: 18px; color: #2563eb;">{{ $ticket->ticket_number }}</span>
                </p>
                <p style="margin: 0 0 16px 0;">
                    <strong>Type:</strong> {{ \App\Models\TicketReport::problemTypes()[$ticket->type] ?? $ticket->type }}
                </p>
                <p style="margin: 0 0 16px 0;">
                    Our team will review your report and get back to you if needed. You may refer to your ticket number when inquiring.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
