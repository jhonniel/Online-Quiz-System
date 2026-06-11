<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

final class EmployeeHandbookMaterial
{
    public const SETTING_KEY = 'employee_handbook_material_pdf';

    public static function storedPath(): string
    {
        return trim((string) Setting::get(self::SETTING_KEY, ''));
    }

    public static function isAvailable(): bool
    {
        $path = self::storedPath();

        return $path !== '' && self::resolveDiskForPath($path) !== null;
    }

    public static function storeUpload(UploadedFile $file): string
    {
        self::deleteStored();

        $disk = self::preferredStorageDisk();
        $directory = self::storageDirectory();
        $path = $file->store($directory, $disk);

        Setting::set(self::SETTING_KEY, $path, 'file', 'Employee Handbook material PDF');
        Setting::clearCache();

        return $path;
    }

    public static function deleteStored(): void
    {
        $path = self::storedPath();
        if ($path === '') {
            return;
        }

        foreach (self::candidateDisks() as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                }
            } catch (\Throwable) {
                continue;
            }
        }

        Setting::set(self::SETTING_KEY, '', 'file', 'Employee Handbook material PDF');
        Setting::clearCache();
    }

    public static function streamResponse(string $disposition = 'inline'): Response
    {
        $path = self::storedPath();
        abort_if($path === '', 404, 'Employee Handbook PDF not found.');

        $disk = self::resolveDiskForPath($path);
        abort_if($disk === null, 404, 'Employee Handbook PDF not found.');

        $contents = Storage::disk($disk)->get($path);
        abort_if(! is_string($contents), 404, 'Employee Handbook PDF not found.');

        $filename = basename($path) !== '' ? basename($path) : 'employee-handbook.pdf';

        return response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
        ]);
    }

    public static function resolveDiskForPath(string $path): ?string
    {
        if ($path === '') {
            return null;
        }

        foreach (self::candidateDisks() as $disk) {
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

    private static function preferredStorageDisk(): string
    {
        $digitaloceanConfig = config('filesystems.disks.digitalocean', []);
        $isDigitaloceanConfigured = ! empty($digitaloceanConfig['bucket'])
            && ! empty($digitaloceanConfig['key'])
            && ! empty($digitaloceanConfig['secret']);

        if ($isDigitaloceanConfigured) {
            return 'digitalocean';
        }

        $spacesConfig = config('filesystems.disks.spaces', []);
        $isSpacesConfigured = ! empty($spacesConfig['bucket'])
            && ! empty($spacesConfig['key'])
            && ! empty($spacesConfig['secret'])
            && ! empty($spacesConfig['endpoint']);

        return $isSpacesConfigured ? 'spaces' : 'public';
    }

    private static function storageDirectory(): string
    {
        $assetRoot = trim((string) Setting::get('asset_storage_root', ''), '/');

        return $assetRoot !== ''
            ? $assetRoot.'/employee-documents/handbook'
            : 'employee-documents/handbook';
    }

    /**
     * @return list<string>
     */
    private static function candidateDisks(): array
    {
        return ['spaces', 'digitalocean', 'public', 'local'];
    }
}
