<?php

namespace App\Console\Commands;

use App\Support\ChatMediaService;
use Illuminate\Console\Command;

class PurgeExpiredChatMedia extends Command
{
    protected $signature = 'chat:purge-expired-media';

    protected $description = 'Delete chat images older than 24 hours';

    public function handle(): int
    {
        $count = ChatMediaService::purgeExpired();
        $this->info("Purged {$count} expired chat image(s).");

        return self::SUCCESS;
    }
}
