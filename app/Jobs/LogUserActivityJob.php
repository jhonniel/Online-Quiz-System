<?php

namespace App\Jobs;

use App\Models\UserActivity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class LogUserActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $userId,
        public string $activityType,
        public ?string $action,
        public array $metadata,
        public ?string $pageUrl,
        public ?string $ipAddress,
        public ?string $userAgent,
    ) {}

    public function handle(): void
    {
        UserActivity::create([
            'user_id' => $this->userId,
            'activity_type' => Str::limit($this->activityType, 255, ''),
            'action' => $this->action !== null ? Str::limit($this->action, 255, '') : null,
            'page_url' => $this->pageUrl !== null && $this->pageUrl !== '' ? Str::limit($this->pageUrl, 255, '') : null,
            'ip_address' => $this->ipAddress !== null ? Str::limit($this->ipAddress, 255, '') : null,
            'user_agent' => $this->userAgent !== null && $this->userAgent !== '' ? Str::limit($this->userAgent, 255, '') : null,
            'metadata' => $this->metadata,
            'created_at' => now(),
        ]);
    }
}
