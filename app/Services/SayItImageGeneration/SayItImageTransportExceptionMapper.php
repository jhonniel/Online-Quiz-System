<?php

namespace App\Services\SayItImageGeneration;

/**
 * Maps Laravel HTTP client transport failures to user-facing SayItImageGenerationException messages.
 */
final class SayItImageTransportExceptionMapper
{
    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function wrap(string $serviceLabel, string $baseUrl, callable $callback): mixed
    {
        try {
            return $callback();
        } catch (SayItImageGenerationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new SayItImageGenerationException(self::mapMessage($serviceLabel, $baseUrl, $e));
        }
    }

    public static function mapMessage(string $serviceLabel, string $baseUrl, \Throwable $e): string
    {
        $why = $e->getMessage();

        if (self::looksLikeConnectionFailure($why)) {
            $refused = self::looksLikeConnectionRefused($why);
            $hint = ' Ensure the service is running and reachable. If Laravel runs in Docker, use http://host.docker.internal:… (or your host IP) in Internal API URL instead of 127.0.0.1.';
            if (strcasecmp($serviceLabel, 'ComfyUI') === 0) {
                $hint = ' Start ComfyUI in its install directory (e.g. python main.py — default port 8188) and confirm http://127.0.0.1:8188 loads in a browser on the same machine that runs PHP.';
                if ($refused) {
                    $hint .= ' Your error is connection refused: nothing is listening at the configured URL yet (wrong URL/port, or ComfyUI not started).';
                }
                $hint .= ' If Laravel runs in Docker/WSL while ComfyUI is on the host OS, set Internal API URL to http://host.docker.internal:8188 instead of 127.0.0.1.';
            }

            return "Cannot connect to {$serviceLabel} at {$baseUrl}.{$hint} Check Admin → Settings → Say-it (Internal API URL / Base URL).";
        }

        if (self::looksLikeTimeout($why)) {
            return "The image server took too long to respond. Try a lower resolution or increase the HTTP timeout in Settings. ({$serviceLabel})";
        }

        if (self::looksLikeSslProblem($why)) {
            return "{$serviceLabel} SSL error: {$why} If you use a self-signed certificate, disable “Verify SSL” for that backend in Admin → Settings → Say-it.";
        }

        return "{$serviceLabel} connection error: {$why}";
    }

    private static function looksLikeConnectionFailure(string $message): bool
    {
        $m = strtolower($message);

        return str_contains($m, 'connection refused')
            || str_contains($m, 'could not connect')
            || str_contains($m, 'failed to connect')
            || str_contains($m, 'connection reset')
            || str_contains($m, 'name or service not known')
            || str_contains($m, 'nodename nor servname')
            || str_contains($m, 'curl error 7')
            || str_contains($m, 'curl error 6');
    }

    private static function looksLikeConnectionRefused(string $message): bool
    {
        $m = strtolower($message);

        return str_contains($m, 'connection refused')
            || str_contains($m, 'curl error 7');
    }

    private static function looksLikeTimeout(string $message): bool
    {
        $m = strtolower($message);

        return str_contains($m, 'operation timed out')
            || str_contains($m, 'timed out')
            || str_contains($m, 'curl error 28');
    }

    private static function looksLikeSslProblem(string $message): bool
    {
        $m = strtolower($message);

        return str_contains($m, 'ssl')
            || str_contains($m, 'certificate')
            || str_contains($m, 'curl error 60');
    }
}
