<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class StudentNdaStorage
{
    public const DISK = 'digitalocean';

    private const DIRECTORY = 'student-nda-documents';

    public static function isConfigured(): bool
    {
        $cfg = config('filesystems.disks.'.self::DISK, []);
        $bucket = $cfg['bucket'] ?? null;
        $endpoint = $cfg['endpoint'] ?? null;
        $key = $cfg['key'] ?? null;
        $secret = $cfg['secret'] ?? null;

        return ! empty($bucket) && ! empty($endpoint) && ! empty($key) && ! empty($secret);
    }

    public static function notConfiguredMessage(): string
    {
        return 'NDA upload requires DigitalOcean Spaces. Set DIGITALOCEAN_SPACES_* or DO_SPACES_* in .env.';
    }

    public static function storageDirectory(): string
    {
        $dir = self::DIRECTORY;
        $assetRoot = trim((string) env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');

        if ($assetRoot !== '') {
            $dir = $assetRoot.'/'.$dir;
        }

        return $dir;
    }

    /**
     * @return array{0: string, 1: string}
     */
    public static function store(UploadedFile $file): array
    {
        if (! self::isConfigured()) {
            throw new \RuntimeException(self::notConfiguredMessage());
        }

        return [(string) $file->store(self::storageDirectory(), self::DISK), self::DISK];
    }

    public static function delete(string $path, string $preferredDisk = ''): void
    {
        if ($path === '') {
            return;
        }

        $disks = array_values(array_unique(array_filter([
            $preferredDisk,
            self::DISK,
            'public',
            config('filesystems.default', 'local'),
        ])));

        foreach ($disks as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);

                    return;
                }
            } catch (\Throwable) {
                continue;
            }
        }
    }

    public static function resolveDiskForPath(string $path, string $preferredDisk = ''): ?string
    {
        if ($path === '') {
            return null;
        }

        $candidateDisks = array_values(array_unique(array_filter([
            $preferredDisk,
            self::DISK,
            'public',
            'local',
        ])));

        foreach ($candidateDisks as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    return $disk;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }
}
