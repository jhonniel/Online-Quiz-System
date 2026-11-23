<?php

namespace App\Mail;

use App\Models\Starlink;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StarlinkBillingReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{
     *     kind: 'overdue'|'upcoming'|null,
     *     overdue_cycles: int,
     *     first_due: \Carbon\Carbon|null,
     *     next_billing_date: \Carbon\Carbon|null,
     *     days_until: int|null,
     *     notify_for_date: \Carbon\Carbon|null
     * }  $notice
     */
    public function __construct(
        public Starlink $starlink,
        public array $notice,
        public string $pdfBinary
    ) {
    }

    public function envelope(): Envelope
    {
        $label = $this->starlink->starlink_id
            ?: ($this->starlink->serial_number ?: ('#'.$this->starlink->id));

        if (($this->notice['kind'] ?? null) === 'overdue') {
            $cycles = (int) ($this->notice['overdue_cycles'] ?? 0);
            $subject = $cycles > 1
                ? sprintf('Starlink overdue billing (×%d): %s', $cycles, $label)
                : sprintf('Starlink overdue billing: %s', $label);
        } else {
            $days = $this->notice['days_until'] ?? null;
            if ($days === 0) {
                $subject = sprintf('Starlink billing due today: %s', $label);
            } elseif ($days === 1) {
                $subject = sprintf('Starlink billing due tomorrow: %s', $label);
            } else {
                $subject = sprintf(
                    'Starlink billing due in %d days: %s',
                    max(0, (int) $days),
                    $label
                );
            }
        }

        return new Envelope(subject: $subject.' — '.config('app.name'));
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.starlink-billing-reminder',
        );
    }

    public function attachments(): array
    {
        $safeId = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($this->starlink->starlink_id ?: $this->starlink->id));
        $kind = ($this->notice['kind'] ?? 'billing') === 'overdue' ? 'overdue' : 'renewal';
        $fileName = sprintf('starlink-%s-%s.pdf', $safeId, $kind);

        return [
            Attachment::fromData(fn () => $this->pdfBinary, $fileName)
                ->withMime('application/pdf'),
        ];
    }
}
