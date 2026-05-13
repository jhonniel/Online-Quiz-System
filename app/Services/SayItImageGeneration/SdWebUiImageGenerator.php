<?php

namespace App\Services\SayItImageGeneration;

use Illuminate\Support\Facades\Http;

/**
 * Automatic1111 / SD Web UI compatible txt2img API.
 */
final class SdWebUiImageGenerator implements SayItImageGeneratorContract
{
    public function generate(string $prompt): string
    {
        $base = SayItImageSettings::sdWebuiRequestBaseUrl();
        if ($base === '') {
            throw new SayItImageGenerationException(
                'SD Web UI URL is not set. In Admin → Settings → Say-it, set Internal API URL and/or Base URL.'
            );
        }

        $url = $base.'/sdapi/v1/txt2img';
        $payload = [
            'prompt' => $prompt,
            'negative_prompt' => (string) config('sayit_image.sd_webui.negative_prompt'),
            'steps' => (int) config('sayit_image.sd_webui.steps'),
            'width' => (int) config('sayit_image.sd_webui.width'),
            'height' => (int) config('sayit_image.sd_webui.height'),
            'cfg_scale' => (float) config('sayit_image.sd_webui.cfg_scale'),
            'sampler_name' => (string) config('sayit_image.sd_webui.sampler_name'),
            'seed' => -1,
            'batch_size' => 1,
            'n_iter' => 1,
        ];

        $client = Http::withOptions([
            'verify' => SayItImageSettings::sdWebuiVerifySsl(),
        ])
            ->timeout(SayItImageSettings::httpTimeout());

        if (SayItImageSettings::sdWebuiUsesPublicProxy()) {
            $secret = SayItImageSettings::sayItImageProxySecret();
            $client = $client->withHeaders([
                'X-SayIt-Image-Proxy-Secret' => $secret,
                'X-SayIt-Sd-Proxy-Secret' => $secret,
            ]);
        }

        $response = SayItImageTransportExceptionMapper::wrap(
            'Stable Diffusion Web UI',
            $base,
            fn () => $client->acceptJson()->asJson()->post($url, $payload)
        );

        if ($response->failed()) {
            throw new SayItImageGenerationException(
                self::httpErrorMessage($response, $base)
            );
        }

        $decoded = $response->json();
        $images = is_array($decoded) && isset($decoded['images']) && is_array($decoded['images'])
            ? $decoded['images']
            : null;

        if (! is_array($images) || ! isset($images[0]) || ! is_string($images[0]) || $images[0] === '') {
            $extra = '';
            if (is_array($decoded) && array_key_exists('detail', $decoded)) {
                $d = $decoded['detail'];
                if (is_string($d) && $d !== '') {
                    $extra = ' '.$d;
                } elseif (is_array($d)) {
                    $extra = ' '.json_encode($d, JSON_UNESCAPED_UNICODE);
                }
            }

            throw new SayItImageGenerationException('No image was returned from SD Web UI.'.$extra);
        }

        $binary = base64_decode($images[0], true);
        if ($binary === false || $binary === '') {
            throw new SayItImageGenerationException('Could not decode the image from SD Web UI.');
        }

        SayItImageBinaryValidator::assertPngJpegWebp($binary);

        return $binary;
    }

    private static function httpErrorMessage(\Illuminate\Http\Client\Response $response, string $base): string
    {
        $status = $response->status();
        $body = is_string($response->body()) ? $response->body() : '';

        if ($status === 404
            && SayItImageSettings::sdWebuiInternalBaseUrl() === ''
            && SayItImageSettings::sdWebuiBaseUrl() !== '') {
            return 'SD Web UI: set Internal API URL (e.g. http://127.0.0.1:7860) in Admin → Settings → Say-it. '
                .'When Base URL is your public HTTPS site, Internal must be where Automatic1111 listens so /sdapi/ can be proxied. Current request base: '.$base;
        }

        if ($status === 404 && self::looksLikeWrongServer($body)) {
            $hint = SayItImageSettings::sdWebuiBaseUrl() !== '' && SayItImageSettings::sdWebuiInternalBaseUrl() === ''
                ? 'Set Internal API URL (e.g. http://127.0.0.1:7860) so this app can proxy /sdapi/ to Automatic1111. '
                : 'Confirm Internal API URL points at Web UI with --api, and Base URL is your public site only if Internal differs (same-origin proxy). ';

            return 'SD Web UI URL is wrong: requests are hitting this app (or another non-Web UI server), not Automatic1111. '
                .$hint
                .'Current request base: '.$base;
        }

        $snippet = strlen($body) > 200 ? substr($body, 0, 200).'…' : $body;

        return 'Stable Diffusion Web UI request failed (HTTP '.$status.'). '.$snippet;
    }

    private static function looksLikeWrongServer(string $body): bool
    {
        if ($body === '') {
            return false;
        }

        if (str_contains($body, 'NotFoundHttpException')) {
            return true;
        }

        return str_contains($body, 'sdapi')
            && (str_contains($body, 'could not be found') || str_contains($body, 'Route ['));
    }
}
