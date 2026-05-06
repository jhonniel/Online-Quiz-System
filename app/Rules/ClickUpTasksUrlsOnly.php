<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Tasks / ClickUp field must contain only http(s) URLs (no free-text notes).
 * Multiple links may be separated by spaces, commas, semicolons, or new lines; optional leading bullets (-, *, •) per line are allowed.
 */
class ClickUpTasksUrlsOnly implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return;
        }

        $segments = $this->segmentsFromField($normalized);
        if ($segments === []) {
            $fail('The :attribute must include at least one valid http or https URL.');

            return;
        }

        foreach ($segments as $segment) {
            if (! $this->isValidHttpUrl($segment)) {
                $fail('The :attribute may only contain URLs (http or https). Do not add notes, sentences, or labels—put links only. Separate multiple links with spaces, commas, or new lines.');

                return;
            }
        }
    }

    /**
     * @return list<string>
     */
    private function segmentsFromField(string $normalized): array
    {
        $lines = preg_split('/\R+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);
        if ($lines === false) {
            return [];
        }

        $segments = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $line = preg_replace('/^[\-\*•\x{2022}]+\s*/u', '', $line);

            $parts = preg_split('/\s*[\x{FF0C};,]\s*/u', $line, -1, PREG_SPLIT_NO_EMPTY);
            if ($parts === false) {
                continue;
            }

            foreach ($parts as $part) {
                foreach (preg_split('/\s+/u', trim($part), -1, PREG_SPLIT_NO_EMPTY) as $token) {
                    $segments[] = $token;
                }
            }
        }

        return $segments;
    }

    private function isValidHttpUrl(string $candidate): bool
    {
        if (! preg_match('#^https?://#i', $candidate)) {
            return false;
        }

        return filter_var($candidate, FILTER_VALIDATE_URL) !== false;
    }
}
