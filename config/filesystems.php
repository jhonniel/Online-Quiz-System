<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],
        'spaces' => [
            'driver' => 's3',
            'key' => env('DO_SPACES_KEY'),
            'secret' => env('DO_SPACES_SECRET'),
            'endpoint' => env('DO_SPACES_ENDPOINT'),
            'region' => env('DO_SPACES_REGION'),
            'bucket' => env('DO_SPACES_BUCKET'),
            'url' => env('DO_SPACES_URL'),
            'visibility' => 'public',
        ],
        'digitalocean' => [
            'driver' => 's3',
            'key' => env('DIGITALOCEAN_SPACES_KEY') ?: env('DO_SPACES_KEY'),
            'secret' => env('DIGITALOCEAN_SPACES_SECRET') ?: env('DO_SPACES_SECRET'),
            'endpoint' => env('DIGITALOCEAN_SPACES_ENDPOINT') ?: env('DO_SPACES_ENDPOINT'),
            'region' => env('DIGITALOCEAN_SPACES_REGION') ?: env('DO_SPACES_REGION', 'us-east-1'),
            'bucket' => env('DIGITALOCEAN_SPACES_BUCKET') ?: env('DO_SPACES_BUCKET'),
            'url' => env('DIGITALOCEAN_SPACES_URL') ?: env('DO_SPACES_URL'),
            'visibility' => 'public',
        ],
    ],
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
