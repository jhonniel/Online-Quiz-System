<?php

namespace App\Services\SayItImageGeneration;

use App\Models\Setting;

/**
 * Say-it image generation: admin Settings (database) override config / .env.
 */
final class SayItImageSettings
{
    private static function dbString(string $key): string
    {
        $v = Setting::get($key);
        if (is_string($v)) {
            $t = trim($v);

            return $t;
        }

        return '';
    }

    public static function driver(): string
    {
        $v = self::dbString('sayit_image_driver');
        if ($v !== '') {
            return strtolower($v);
        }

        return strtolower((string) config('sayit_image.driver', 'disabled'));
    }

    /**
     * Whether the Say-it composer may show “Generate image” and call the generate API.
     * Admin setting overrides config when set; unset DB value falls back to config (default true).
     */
    public static function isComposerGenerateImageFeatureEnabled(): bool
    {
        $v = Setting::get('sayit_composer_ai_image_enabled');
        if ($v === 'disabled' || $v === false || $v === '0' || $v === 0) {
            return false;
        }
        if ($v === 'enabled' || $v === true || $v === '1' || $v === 1) {
            return true;
        }

        return (bool) config('sayit_image.composer_ai_image_enabled', true);
    }

    public static function sdWebuiBaseUrl(): string
    {
        $v = self::dbString('sayit_sd_webui_base_url');
        if ($v !== '') {
            return rtrim($v, '/');
        }

        return rtrim((string) config('sayit_image.sd_webui.base_url', ''), '/');
    }

    /**
     * Optional URL used only for outbound HTTP from Laravel (e.g. http://127.0.0.1:7860 on the app server).
     */
    public static function sdWebuiInternalBaseUrl(): string
    {
        $v = self::dbString('sayit_sd_webui_internal_base_url');
        if ($v !== '') {
            return rtrim($v, '/');
        }

        return rtrim((string) config('sayit_image.sd_webui.internal_base_url', ''), '/');
    }

    /**
     * Explicit proxy secret from .env; overrides the automatic APP_KEY-derived secret.
     */
    public static function sdWebuiProxySecret(): string
    {
        return trim((string) config('sayit_image.sd_webui.proxy_secret', ''));
    }

    /**
     * Shared secret for Say-it reverse proxies (/sdapi, /comfyui): env SD_WEBUI_PROXY_SECRET or HMAC(APP_KEY).
     */
    public static function sdWebuiEffectiveProxySecret(): string
    {
        $explicit = self::sdWebuiProxySecret();
        if ($explicit !== '') {
            return $explicit;
        }

        $key = (string) config('app.key', '');
        if ($key === '') {
            return '';
        }

        return hash_hmac('sha256', 'sayit-sd-webui-proxy-v1', $key);
    }

    /** Shared by SD Web UI and ComfyUI proxies (same value as sdWebuiEffectiveProxySecret). */
    public static function sayItImageProxySecret(): string
    {
        return self::sdWebuiEffectiveProxySecret();
    }

    /**
     * Outbound calls use the public Base URL and the proxy when both URLs are set and differ.
     */
    public static function sdWebuiUsesPublicProxy(): bool
    {
        $public = self::sdWebuiBaseUrl();
        $internal = self::sdWebuiInternalBaseUrl();
        if ($public === '' || $internal === '') {
            return false;
        }

        return self::normalizeUrlForCompare($public) !== self::normalizeUrlForCompare($internal);
    }

    /**
     * Effective base URL for SD Web UI API requests.
     */
    public static function sdWebuiRequestBaseUrl(): string
    {
        if (self::sdWebuiUsesPublicProxy()) {
            return rtrim(self::sdWebuiBaseUrl(), '/');
        }

        $internal = self::sdWebuiInternalBaseUrl();

        return $internal !== '' ? $internal : self::sdWebuiBaseUrl();
    }

    private static function normalizeUrlForCompare(string $url): string
    {
        return rtrim(strtolower($url), '/');
    }

    public static function sdWebuiVerifySsl(): bool
    {
        $v = Setting::get('sayit_sd_webui_verify_ssl');
        if ($v === 'enabled' || $v === true || $v === '1' || $v === 1) {
            return true;
        }
        if ($v === 'disabled' || $v === false || $v === '0' || $v === 0) {
            return false;
        }

        return (bool) config('sayit_image.sd_webui.verify', true);
    }

    public static function httpTimeout(): int
    {
        $v = Setting::get('sayit_image_http_timeout');
        if (is_numeric($v)) {
            $n = (int) $v;

            return max(30, min(600, $n));
        }

        return (int) config('sayit_image.sd_webui.timeout', 180);
    }

    public static function comfyuiBaseUrl(): string
    {
        $v = self::dbString('sayit_comfyui_base_url');
        if ($v !== '') {
            return rtrim($v, '/');
        }

        return rtrim((string) config('sayit_image.comfyui.base_url', ''), '/');
    }

    public static function comfyuiInternalBaseUrl(): string
    {
        $v = self::dbString('sayit_comfyui_internal_base_url');
        if ($v !== '') {
            return rtrim($v, '/');
        }

        return rtrim((string) config('sayit_image.comfyui.internal_base_url', ''), '/');
    }

    public static function comfyuiUsesPublicProxy(): bool
    {
        $public = self::comfyuiBaseUrl();
        $internal = self::comfyuiInternalBaseUrl();
        if ($public === '' || $internal === '') {
            return false;
        }

        return self::normalizeUrlForCompare($public) !== self::normalizeUrlForCompare($internal);
    }

    public static function comfyuiRequestBaseUrl(): string
    {
        if (self::comfyuiUsesPublicProxy()) {
            return rtrim(self::comfyuiBaseUrl(), '/');
        }

        $internal = self::comfyuiInternalBaseUrl();

        return $internal !== '' ? $internal : self::comfyuiBaseUrl();
    }

    public static function comfyuiWorkflowPath(): string
    {
        $v = self::dbString('sayit_comfyui_workflow_path');
        if ($v !== '') {
            if (str_starts_with($v, '/') || (strlen($v) > 2 && ctype_alpha($v[0]) && $v[1] === ':')) {
                return $v;
            }

            return base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($v, '/')));
        }

        return (string) (config('sayit_image.comfyui.workflow_path') ?: storage_path('app/comfyui/sayit_workflow_api.json'));
    }

    public static function comfyuiPromptPlaceholder(): string
    {
        $v = self::dbString('sayit_comfyui_prompt_placeholder');
        if ($v !== '') {
            return $v;
        }

        return (string) config('sayit_image.comfyui.prompt_placeholder', '__SAYIT_PROMPT__');
    }

    public static function comfyuiVerifySsl(): bool
    {
        $v = Setting::get('sayit_comfyui_verify_ssl');
        if ($v === 'enabled' || $v === true || $v === '1' || $v === 1) {
            return true;
        }
        if ($v === 'disabled' || $v === false || $v === '0' || $v === 0) {
            return false;
        }

        return (bool) config('sayit_image.comfyui.verify', true);
    }

    public static function comfyuiPollIntervalMs(): int
    {
        return (int) config('sayit_image.comfyui.poll_interval_ms', 1000);
    }

    public static function comfyuiPollMaxAttempts(): int
    {
        return (int) config('sayit_image.comfyui.poll_max_attempts', 180);
    }

    public static function isConfigured(): bool
    {
        return match (self::driver()) {
            'sdwebui' => self::isSdWebUiConfigured(),
            'comfyui' => self::isComfyUiConfigured(),
            default => false,
        };
    }

    private static function isSdWebUiConfigured(): bool
    {
        $public = self::sdWebuiBaseUrl();
        $internal = self::sdWebuiInternalBaseUrl();
        if ($public !== '' && $internal === '') {
            return false;
        }
        if (self::sdWebuiUsesPublicProxy()) {
            return $public !== '' && $internal !== '';
        }

        return self::sdWebuiRequestBaseUrl() !== '';
    }

    private static function isComfyUiConfigured(): bool
    {
        if (! is_readable(self::comfyuiWorkflowPath())) {
            return false;
        }
        $public = self::comfyuiBaseUrl();
        $internal = self::comfyuiInternalBaseUrl();
        if ($public !== '' && $internal === '') {
            return false;
        }
        if (self::comfyuiUsesPublicProxy()) {
            return $public !== '' && $internal !== '';
        }

        return self::comfyuiRequestBaseUrl() !== '';
    }
}
