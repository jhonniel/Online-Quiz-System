<?php

/**
 * Say-it AI image generation (Stable Diffusion / ComfyUI over HTTP).
 *
 * The official laravel/ai package targets Laravel 12+ / PHP 8.3+; this app uses
 * Laravel's HTTP client against local or self-hosted APIs instead.
 *
 * @see https://github.com/AUTOMATIC1111/stable-diffusion-webui/wiki/API
 * @see https://github.com/comfyanonymous/ComfyUI
 */
return [

    'driver' => env('SAYIT_IMAGE_DRIVER', 'disabled'),

    /** When false, Say-it composer hides “Generate image” even if a backend is configured (Admin can override). */
    'composer_ai_image_enabled' => filter_var(env('SAYIT_COMPOSER_AI_IMAGE_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    'sd_webui' => [
        'base_url' => rtrim((string) env('SD_WEBUI_BASE_URL', ''), '/'),
        /** When set, Laravel uses this for API HTTP calls instead of base_url (same server as Web UI). */
        'internal_base_url' => rtrim((string) env('SD_WEBUI_INTERNAL_BASE_URL', ''), '/'),
        'timeout' => (int) env('SAYIT_IMAGE_HTTP_TIMEOUT', 180),
        'verify' => filter_var(env('SD_WEBUI_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN),
        'width' => (int) env('SAYIT_SD_WIDTH', 512),
        'height' => (int) env('SAYIT_SD_HEIGHT', 512),
        'steps' => (int) env('SAYIT_SD_STEPS', 28),
        'cfg_scale' => (float) env('SAYIT_SD_CFG', 7.0),
        'sampler_name' => (string) env('SAYIT_SD_SAMPLER', 'Euler a'),
        'negative_prompt' => (string) env(
            'SAYIT_SD_NEGATIVE',
            'lowres, bad anatomy, bad hands, text, error, watermark, blurry, cropped, worst quality'
        ),
        /** Optional; if empty, proxy auth uses HMAC(APP_KEY). Set to override the automatic secret. */
        'proxy_secret' => (string) env('SD_WEBUI_PROXY_SECRET', ''),
    ],

    'comfyui' => [
        'base_url' => rtrim((string) env('COMFYUI_BASE_URL', ''), '/'),
        'internal_base_url' => rtrim((string) env('COMFYUI_INTERNAL_BASE_URL', ''), '/'),
        'workflow_path' => (string) (env('COMFYUI_WORKFLOW_PATH') ?: storage_path('app/comfyui/sayit_workflow_api.json')),
        'prompt_placeholder' => (string) env('COMFYUI_PROMPT_PLACEHOLDER', '__SAYIT_PROMPT__'),
        'timeout' => (int) env('SAYIT_IMAGE_HTTP_TIMEOUT', 180),
        'verify' => filter_var(env('COMFYUI_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN),
        'poll_interval_ms' => (int) env('COMFYUI_POLL_INTERVAL_MS', 1000),
        'poll_max_attempts' => (int) env('COMFYUI_POLL_MAX_ATTEMPTS', 180),
    ],

];
