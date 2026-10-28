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
                'message' => 'External API access is currently disabled. Enable it under Admin → API Monitoring → External API Access.',
                'code' => 'EXTERNAL_API_DISABLED',
            ], 403);
        }

        $providedKey = $this->extractProvidedKey($request);
        if ($providedKey === '') {
            return new JsonResponse([
                'message' => 'Missing API key. Send header X-API-Key, Authorization: Bearer <key>, or query api_key=.',
                'code' => 'MISSING_API_KEY',
            ], 401);
        }

        $hashed = hash('sha256', $providedKey);
        $apiKey = ExternalApiKey::query()
            ->where('key_hash', $hashed)
            ->where('is_active', true)
            ->first();

        if (! $apiKey) {
            return new JsonResponse([
                'message' => 'Invalid or inactive API key. Generate a new key in Admin → API Monitoring if needed.',
                'code' => 'INVALID_API_KEY',
            ], 401);
        }

        $apiKey->forceFill([
            'last_used_at' => now(),
            'last_used_ip' => $request->ip(),
        ])->save();

        return $next($request);
    }

    private function extractProvidedKey(Request $request): string
    {
        foreach (['X-API-Key', 'X-Api-Key', 'API-Key'] as $header) {
            $value = trim((string) $request->header($header, ''));
            if ($value !== '') {
                return $value;
            }
        }

        $authorization = trim((string) $request->header('Authorization', ''));
        if ($authorization !== '' && preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches) === 1) {
            return trim($matches[1]);
        }

        return trim((string) $request->query('api_key', ''));
    }
}
