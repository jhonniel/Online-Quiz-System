<?php

namespace App\Mail;

use App\Models\OtherSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtherSubscriptionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public OtherSubscription $subscription)
    {
    }

    public function envelope(): Envelope
    {
        $daysLeft = $this->subscription->daysUntilEnd();
        $name = $this->subscription->name;

        if ($daysLeft !== null && $daysLeft < 0) {
            $overdueDays = abs((int) $daysLeft);
            $subject = $overdueDays === 1
                ? sprintf('Subscription overdue (1 day): %s', $name)
                : sprintf('Subscription overdue (%d days): %s', $overdueDays, $name);
        } else {
            $action = $this->subscription->auto_renew ? 'renews' : 'due';
            $subject = sprintf('Subscription %s today: %s', $action, $name);
        }

        return new Envelope(subject: $subject.' — '.config('app.name'));
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.other-subscription-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
