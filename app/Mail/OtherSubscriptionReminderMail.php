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
        $action = $this->subscription->auto_renew ? 'renews' : 'ends';

        if ($daysLeft === 0) {
            $subject = sprintf(
                'Subscription %s today: %s',
                $action === 'renews' ? 'renews' : 'ends',
                $this->subscription->name
            );
        } elseif ($daysLeft === 1) {
            $subject = sprintf('Subscription %s tomorrow: %s', $action, $this->subscription->name);
        } else {
            $subject = sprintf(
                'Subscription %s in %d days: %s',
                $action,
                max(0, (int) $daysLeft),
                $this->subscription->name
            );
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
