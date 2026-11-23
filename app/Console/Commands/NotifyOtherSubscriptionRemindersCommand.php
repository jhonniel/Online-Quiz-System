<?php

namespace App\Console\Commands;

use App\Support\OtherSubscriptionNotifier;
use Illuminate\Console\Command;

class NotifyOtherSubscriptionRemindersCommand extends Command
{
    protected $signature = 'subscriptions:notify-other-expiring';

    protected $description = 'Email configured admins when Other Subscriptions are ending or renewing soon';

    public function handle(): int
    {
        $result = OtherSubscriptionNotifier::sendDueReminders();

        $this->info(sprintf(
            'Other subscription reminders: sent=%d skipped=%d failed=%d',
            $result['sent'],
            $result['skipped'],
            $result['failed']
        ));

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
