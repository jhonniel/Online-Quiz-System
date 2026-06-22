<?php

namespace App\Jobs;

use App\Support\IpGeolocationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResolveIpGeolocationBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  list<string>  $ipAddresses
     */
    public function __construct(public array $ipAddresses) {}

    public function handle(): void
    {
        foreach ($this->ipAddresses as $ip) {
            IpGeolocationService::resolve(trim((string) $ip));
        }
    }
}
