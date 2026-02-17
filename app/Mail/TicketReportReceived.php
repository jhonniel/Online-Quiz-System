<?php

namespace App\Mail;

use App\Models\TicketReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketReportReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TicketReport $ticket
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Report Received – Ticket #' . $this->ticket->ticket_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-report-received',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
