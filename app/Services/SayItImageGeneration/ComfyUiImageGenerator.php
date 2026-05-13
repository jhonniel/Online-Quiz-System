<?php

namespace App\Services\SayItImageGeneration;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * ComfyUI HTTP API: POST /prompt then poll /history until an image output exists, then GET /view.
 *
 * Export your graph in ComfyUI (Save API Format) and replace the positive prompt string with the
 * placeholder from config (default __SAYIT_PROMPT__) as a JSON string value, e.g. "text": "__SAYIT_PROMPT__"
 * including JSON quotes via: str_replace('"__SAYIT_PROMPT__"', json_encode(...), $raw).
 *
 * When Base URL is your public site and Internal API URL is local, requests use /comfyui/* on this app (proxied).
 */
final class ComfyUiImageGenerator implements SayItImageGeneratorContract
{
    public function generate(string $prompt): string
    {
        $base = SayItImageSettings::comfyuiRequestBaseUrl();
        if ($base === '') {
            throw new SayItImageGenerationException(
                'ComfyUI URL is not set. In Admin → Settings → Say-it, set Internal API URL or Base URL to an address this server can reach (e.g. http://127.0.0.1:8188).'
            );
        }

        $path = SayItImageSettings::comfyuiWorkflowPath();
        $placeholder = SayItImageSettings::comfyuiPromptPlaceholder();
        if (! is_readable($path)) {
            throw new SayItImageGenerationException(
                'ComfyUI workflow file is missing or not readable. Copy your API workflow JSON to: '.$path
            );
        }

        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            throw new SayItImageGenerationException('Could not read the ComfyUI workflow file.');
        }

        $needle = '"'.$placeholder.'"';
        if (! str_contains($raw, $needle)) {
            throw new SayItImageGenerationException(
                'Workflow must contain the placeholder '.$needle.' (as a JSON string) for the text prompt.'
            );
        }

        $json = str_replace(
            $needle,
            json_encode($prompt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $raw
        );

        $workflow = json_decode($json, true);
        if (! is_array($workflow)) {
            throw new SayItImageGenerationException('Workflow JSON is invalid after inserting the prompt.');
        }

        $apiRoot = $this->comfyApiRoot($base);
        $clientId = (string) Str::uuid();

        try {
            $queued = SayItImageTransportExceptionMapper::wrap(
                'ComfyUI',
                $base,
                fn () => $this->comfyHttp(120)
                    ->acceptJson()
                    ->asJson()
                    ->post($apiRoot.'/prompt', [
                        'prompt' => $workflow,
                        'client_id' => $clientId,
                    ])
            );

            if ($queued->failed()) {
                throw new SayItImageGenerationException(
                    'ComfyUI /prompt failed (HTTP '.$queued->status().'). '.$this->shortBody($queued->body())
                );
            }

            $promptId = $queued->json('prompt_id');
            if (! is_string($promptId) || $promptId === '') {
                throw new SayItImageGenerationException('ComfyUI did not return a prompt_id.');
            }

            $intervalMs = max(200, SayItImageSettings::comfyuiPollIntervalMs());
            $maxAttempts = max(1, SayItImageSettings::comfyuiPollMaxAttempts());
            $httpTimeout = SayItImageSettings::httpTimeout();

            for ($i = 0; $i < $maxAttempts; $i++) {
                usleep($intervalMs * 1000);

                $entry = $this->fetchHistoryEntry($apiRoot, $promptId, $httpTimeout);
                if ($entry === null) {
                    continue;
                }

                $status = $entry['status'] ?? [];
                if (is_array($status) && ($status['status_str'] ?? '') === 'error') {
                    $messages = $status['messages'] ?? [];
                    $detail = is_array($messages) ? json_encode($messages) : (string) $messages;

                    throw new SayItImageGenerationException('ComfyUI reported an error: '.$detail);
                }

                $ref = $this->extractFirstImage($entry);
                if ($ref !== null) {
                    return $this->fetchView($apiRoot, $ref, $httpTimeout);
                }
            }

            throw new SayItImageGenerationException('Timed out waiting for ComfyUI to finish generating the image.');
        } catch (SayItImageGenerationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new SayItImageGenerationException(
                SayItImageTransportExceptionMapper::mapMessage('ComfyUI', $base, $e)
            );
        }
    }

    private function comfyApiRoot(string $requestBase): string
    {
        $r = rtrim($requestBase, '/');
        if (SayItImageSettings::comfyuiUsesPublicProxy()) {
            return $r.'/comfyui';
        }

        return $r;
    }

    private function comfyHttp(int $timeoutSeconds)
    {
        $verify = SayItImageSettings::comfyuiVerifySsl();
        $client = Http::withOptions(['verify' => $verify])->timeout($timeoutSeconds);
        if (SayItImageSettings::comfyuiUsesPublicProxy()) {
            $secret = SayItImageSettings::sayItImageProxySecret();
            $client = $client->withHeaders([
                'X-SayIt-Image-Proxy-Secret' => $secret,
                'X-SayIt-Sd-Proxy-Secret' => $secret,
            ]);
        }

        return $client;
    }

    /**
     * @return ?array<string, mixed>
     */
    private function fetchHistoryEntry(string $apiRoot, string $promptId, int $timeout): ?array
    {
        $r = $this->comfyHttp($timeout)->get($apiRoot.'/history/'.$promptId);
        if ($r->ok()) {
            $j = $r->json();
            if (is_array($j) && isset($j[$promptId]) && is_array($j[$promptId])) {
                return $j[$promptId];
            }
        }

        $r2 = $this->comfyHttp($timeout)->get($apiRoot.'/history');
        if ($r2->ok()) {
            $all = $r2->json();
            if (is_array($all) && isset($all[$promptId]) && is_array($all[$promptId])) {
                return $all[$promptId];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function extractFirstImage(array $entry): ?array
    {
        $outputs = $entry['outputs'] ?? null;
        if (! is_array($outputs)) {
            return null;
        }

        foreach ($outputs as $nodeOut) {
            if (! is_array($nodeOut)) {
                continue;
            }
            foreach ($nodeOut['images'] ?? [] as $img) {
                if (is_array($img) && ! empty($img['filename']) && is_string($img['filename'])) {
                    return $img;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $img
     */
    private function fetchView(string $apiRoot, array $img, int $timeout): string
    {
        $query = [
            'filename' => $img['filename'],
            'type' => is_string($img['type'] ?? null) ? $img['type'] : 'output',
            'subfolder' => is_string($img['subfolder'] ?? null) ? $img['subfolder'] : '',
        ];

        $body = $this->comfyHttp($timeout)
            ->get($apiRoot.'/view?'.http_build_query($query))
            ->body();

        SayItImageBinaryValidator::assertPngJpegWebp($body);

        return $body;
    }

    private function shortBody(?string $body): string
    {
        if ($body === null || $body === '') {
            return '';
        }
        $body = strip_tags($body);

        return strlen($body) > 240 ? substr($body, 0, 240).'…' : $body;
    }
}
