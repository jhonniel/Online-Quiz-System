<?php

namespace App\Console\Commands;

use App\Support\StoryService;
use Illuminate\Console\Command;

class PurgeExpiredUserStories extends Command
{
    protected $signature = 'stories:purge-expired';

    protected $description = 'Delete user stories older than 24 hours';

    public function handle(): int
    {
        $count = StoryService::purgeExpired();
        $this->info("Purged {$count} expired story item(s).");

        return self::SUCCESS;
    }
}
