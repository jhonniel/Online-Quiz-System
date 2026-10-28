<?php

namespace App\Support;

use App\Models\ApiEndpointMetric;

class ApiEndpointHealth
{
    /**
     * @return array{status: string, last_status_code: int|null}
     */
    public static function resolve(?ApiEndpointMetric $metric, ?int $latestPointStatusCode = null): array
    {
        $requestCount = (int) ($metric->request_count ?? 0);
        $code = $metric?->last_status_code;

        if ($code === null && $latestPointStatusCode !== null) {
            $code = $latestPointStatusCode;
        }

        if ($code === null && $requestCount > 0) {
            $failures = (int) ($metric->failure_count ?? 0);
            $code = $failures > 0 ? 500 : 200;
        }

        if ($code === null) {
            return [
                'status' => 'unknown',
                'last_status_code' => null,
            ];
        }

        $code = (int) $code;

        if ($code >= 500) {
            return [
                'status' => 'failing',
                'last_status_code' => $code,
            ];
        }

        if ($code >= 400) {
            return [
                'status' => 'degraded',
                'last_status_code' => $code,
            ];
        }

        return [
            'status' => 'healthy',
            'last_status_code' => $code,
        ];
    }
}
