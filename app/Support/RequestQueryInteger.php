<?php

namespace App\Support;

use Illuminate\Http\Request;

final class RequestQueryInteger
{
    /**
     * Parse a positive integer from request input (query/body).
     * Rejects empty values, non-numeric strings, zero, and literal "null".
     */
    public static function positiveInt(Request $request, string $key): ?int
    {
        if (! $request->has($key)) {
            return null;
        }

        $raw = $request->input($key);
        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_string($raw) && strtolower(trim($raw)) === 'null') {
            return null;
        }

        $validated = filter_var($raw, FILTER_VALIDATE_INT);
        if ($validated === false || $validated < 1) {
            return null;
        }

        return $validated;
    }
}
