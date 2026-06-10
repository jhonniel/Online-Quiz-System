<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class UserESignatureStorage
{
    /**
     * @return array{disk: string, e_signature_dir: string}
     */
    public static function storageContext(): array
    {
        $spacesConfigured = ! empty(env('DIGITALOCEAN_SPACES_KEY') ?: env('DO_SPACES_KEY'))
            && ! empty(env('DIGITALOCEAN_SPACES_SECRET') ?: env('DO_SPACES_SECRET'))
            && ! empty(env('DIGITALOCEAN_SPACES_BUCKET') ?: env('DO_SPACES_BUCKET'));
        $assetDisk = $spacesConfigured ? 'digitalocean' : 'public';
        $assetRoot = $spacesConfigured ? trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/') : '';

        return [
            'disk' => $assetDisk,
            'e_signature_dir' => $assetRoot ? $assetRoot.'/e-signatures' : 'e-signatures',
        ];
    }

    public static function store(User $user, UploadedFile $file): string
    {
        ['disk' => $disk, 'e_signature_dir' => $directory] = self::storageContext();

        self::delete($user->e_signature_path);

        $fileName = time().'_'.Str::random(10).'.png';
        $storedPath = $file->storeAs($directory, $fileName, $disk);

        if (! is_string($storedPath) || $storedPath === '') {
            throw new \RuntimeException('Failed to upload e-signature to storage.');
        }

        return $storedPath;
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        foreach (['digitalocean', 'public'] as $storageDisk) {
            try {
                if (Storage::disk($storageDisk)->exists($path)) {
                    Storage::disk($storageDisk)->delete($path);

                    return;
                }
            } catch (\Throwable) {
                continue;
            }
        }
    }
}
