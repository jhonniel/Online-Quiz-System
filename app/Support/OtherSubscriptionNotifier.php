<?php

namespace App\Support;

use App\Mail\OtherSubscriptionReminderMail;
use App\Models\OtherSubscription;
use App\Models\Setting;
use App\Services\MailConfigService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class OtherSubscriptionNotifier
{
    public const SETTING_KEY = 'other_subscription_notification_emails';

    /**
     * @return list<string>
     */
    public static function notificationEmails(): array
    {
        $raw = (string) Setting::get(self::SETTING_KEY, '');
        if (trim($raw) === '') {
            return [];
        }

        $emails = preg_split('/[\s,;]+/', $raw) ?: [];

        return collect($emails)
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter(fn ($email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $emails
     */
    public static function saveNotificationEmails(array $emails): void
    {
        $normalized = collect($emails)
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter(fn ($email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->implode(',');

        Setting::set(
            self::SETTING_KEY,
            $normalized,
            'text',
            'Email addresses notified only when Other Subscriptions are due today or overdue'
        );
    }

    /**
     * @return array{sent: int, skipped: int, failed: int}
     */
    public static function sendDueReminders(): array
    {
        $emails = self::notificationEmails();
        $sent = 0;
        $failed = 0;

        if ($emails === []) {
            return ['sent' => 0, 'skipped' => 0, 'failed' => 0];
        }

        MailConfigService::configure();

        $candidates = OtherSubscription::query()
            ->dueOrOverdue()
            ->orderBy('ends_on')
            ->get()
            ->filter(fn (OtherSubscription $subscription) => $subscription->shouldNotifyToday());

        foreach ($candidates as $subscription) {
            try {
                foreach ($emails as $email) {
                    Mail::to($email)->send(new OtherSubscriptionReminderMail($subscription));
                }

                $subscription->forceFill([
                    'last_notified_at' => now(),
                    'last_notified_for_ends_on' => $subscription->ends_on,
                ])->save();

                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Failed to send other subscription reminder', [
                    'subscription_uuid' => $subscription->uuid,
                    'name' => $subscription->name,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $skipped = max(0, OtherSubscription::query()->dueOrOverdue()->count() - $sent - $failed);

        return compact('sent', 'skipped', 'failed');
    }
}
