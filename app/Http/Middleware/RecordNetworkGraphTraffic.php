<?php

namespace App\Http\Middleware;

use App\Services\NetworkGraph\NetworkGraphRecorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordNetworkGraphTraffic
{
    public function __construct(
        private readonly NetworkGraphRecorder $recorder
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $this->recorder->recordHttpRequest($request, $response);
        } catch (\Throwable) {
            // Never break the app if graph recording fails.
        }

        return $response;
    }
}
