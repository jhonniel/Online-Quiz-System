<?php

namespace App\Console\Commands;

use App\Support\StarlinkBillingNotifier;
use Illuminate\Console\Command;

class NotifyStarlinkBillingRemindersCommand extends Command
{
    protected $signature = 'subscriptions:notify-starlink-billing';

    protected $description = 'Email configured admins when Starlink devices have upcoming renewals or overdue billing, with a PDF per device';

    public function handle(): int
    {
        $result = StarlinkBillingNotifier::sendDueReminders();

        $this->info(sprintf(
            'Starlink billing reminders: sent=%d skipped=%d failed=%d',
            $result['sent'],
            $result['skipped'],
            $result['failed']
        ));

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
