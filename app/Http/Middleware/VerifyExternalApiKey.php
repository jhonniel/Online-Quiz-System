<?php

namespace App\Http\Middleware;

use App\Models\ExternalApiKey;
use App\Models\Setting;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyExternalApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $isEnabled = Setting::get('external_api_enabled', 'disabled') === 'enabled';
        if (! $isEnabled) {
            return new JsonResponse([
                'message' => 'External API access is currently disabled.',
            ], 403);
        }

        $providedKey = (string) ($request->header('X-API-Key') ?? $request->query('api_key', ''));
        if ($providedKey === '') {
            return new JsonResponse([
                'message' => 'Missing API key. Provide X-API-Key header.',
            ], 401);
        }

        $hashed = hash('sha256', $providedKey);
        $apiKey = ExternalApiKey::query()
            ->where('key_hash', $hashed)
            ->where('is_active', true)
            ->first();

        if (! $apiKey) {
            return new JsonResponse([
                'message' => 'Invalid or inactive API key.',
            ], 401);
        }

        $apiKey->forceFill([
            'last_used_at' => now(),
            'last_used_ip' => $request->ip(),
        ])->save();

        return $next($request);
    }
}
