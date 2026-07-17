<?php

namespace App\Console\Commands;

use App\Models\SayItChatMessage;
use Illuminate\Console\Command;

class PurgeExpiredSayItChatImages extends Command
{
    protected $signature = 'sayit:purge-expired-chat-images';

    protected $description = 'Delete Say-it chat message images that have passed their 1-hour expiry';

    public function handle(): int
    {
        $count = 0;

        SayItChatMessage::query()
            ->withExpiredImages()
            ->orderBy('id')
            ->chunkById(100, function ($messages) use (&$count) {
                foreach ($messages as $message) {
                    if ($message->purgeExpiredImage()) {
                        $count++;
                    }
                }
            });

        $this->info("Purged {$count} expired Say-it chat image(s).");

        return self::SUCCESS;
    }
}
