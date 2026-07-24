<?php

namespace App\Console\Commands;

use App\Models\ConfessionPost;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DeleteSayItUnengagedPostsCommand extends Command
{
    protected $signature = 'sayit:delete-unengaged
                            {--dry-run : List posts that would be deleted without deleting them}';

    protected $description = 'Delete Say-it posts that have no likes and no comments after 7 days from post date';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $live = ConfessionPost::eligibleForAutoDelete()->get();
        $trashed = ConfessionPost::onlyTrashed()->eligibleForAutoDelete()->get();
        $posts = $live->merge($trashed);

        if ($posts->isEmpty()) {
            $this->info('No posts eligible for auto-deletion (0 likes, 0 comments, older than 7 days).');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn('Dry run – the following '.$posts->count().' post(s) would be permanently deleted:');
            foreach ($posts as $post) {
                $this->line(
                    '  ID '.$post->id
                    .' – '.Str::limit($post->content ?: '[Image post]', 60)
                    .' – posted '.$post->created_at->diffForHumans()
                    .($post->trashed() ? ' (soft-deleted)' : '')
                );
            }

            return self::SUCCESS;
        }

        $count = ConfessionPost::purgeUnengagedDue();
        $this->info("Permanently deleted {$count} unengaged Say-it post(s).");

        return self::SUCCESS;
    }
}
