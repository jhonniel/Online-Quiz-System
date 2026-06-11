<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Set TRUSTED_PROXIES=* in production behind nginx, Cloudflare, or a load balancer
     * so request()->ip() records the real client IP in User Activity Logs.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    public function __construct()
    {
        $trusted = env('TRUSTED_PROXIES');

        if ($trusted === '*' || $trusted === '**') {
            $this->proxies = '*';
        } elseif (is_string($trusted) && trim($trusted) !== '') {
            $this->proxies = array_values(array_filter(array_map('trim', explode(',', $trusted))));
        }
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_PREFIX;
}

