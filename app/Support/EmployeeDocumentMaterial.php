<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class EmployeeDocumentMaterial
{
    /** @var array<string, array{setting_key: string, setting_label: string, storage_dir: string, default_name: string, not_found_message: string, legacy_setting_key?: string, legacy_default_name?: string}> */
    public const TYPES = [
        'handbook' => [
            'setting_key' => 'employee_handbook_material_pdfs',
            'legacy_setting_key' => 'employee_handbook_material_pdf',
            'setting_label' => 'Employee Handbook material PDFs',
            'storage_dir' => 'employee-documents/handbook',
            'default_name' => 'Handbook',
            'legacy_default_name' => 'Employee Handbook',
            'not_found_message' => 'Employee Handbook PDF not found.',
        ],
        'policy' => [
            'setting_key' => 'employee_policy_material_pdfs',
            'setting_label' => 'Employee Policy material PDFs',
            'storage_dir' => 'employee-documents/policy',
            'default_name' => 'Policy',
            'not_found_message' => 'Employee Policy PDF not found.',
        ],
    ];

    public static function supports(string $type): bool
    {
        return isset(self::TYPES[$type]);
    }

    /**
     * @return list<array{id: string, name: string, path: string, disk: string, sort: int}>
     */
    public static function all(string $type): array
    {
        self::assertType($type);
        self::migrateLegacyIfNeeded($type);

        $raw = Setting::get(self::config($type)['setting_key'], '[]');
        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;

        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->map(fn (mixed $item): array => self::normalizeItem($type, is_array($item) ? $item : []))
            ->filter(fn (array $item): bool => $item['id'] !== '' && $item['path'] !== '')
            ->sortBy('sort')
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: string, name: string, path: string, disk: string, sort: int}>
     */
    public static function available(string $type): array
    {
        return array_values(array_filter(
            self::all($type),
            fn (array $item): bool => self::resolveDiskForPath($item['path'], $item['disk']) !== null
        ));
    }

    /**
     * @return array{id: string, name: string, path: string, disk: string, sort: int}|null
     */
    public static function find(string $type, string $id): ?array
    {
        foreach (self::available($type) as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param  list<array{id?: string, name?: string, sort?: int}>  $nameUpdates
     * @param  list<string>  $removeIds
     * @param  list<array{name: string, pdf: UploadedFile}>  $newUploads
     */
    public static function syncFromAdminInput(string $type, array $nameUpdates, array $removeIds, array $newUploads): void
    {
        self::assertType($type);
        $config = self::config($type);

        $items = collect(self::all($type))->keyBy('id');

        foreach ($removeIds as $removeId) {
            $removeId = trim((string) $removeId);
            if ($removeId === '') {
                continue;
            }

            $existing = $items->get($removeId);
            if (is_array($existing)) {
                self::deleteFile($existing['path'], $existing['disk']);
                $items->forget($removeId);
            }
        }

        foreach ($nameUpdates as $update) {
            $id = trim((string) ($update['id'] ?? ''));
            if ($id === '' || ! $items->has($id)) {
                continue;
            }

            $item = $items->get($id);
            $name = trim((string) ($update['name'] ?? ''));
            if ($name !== '') {
                $item['name'] = $name;
            }

            if (isset($update['sort']) && is_numeric($update['sort'])) {
                $item['sort'] = (int) $update['sort'];
            }

            $items->put($id, $item);
        }

        $maxSort = $items->max(fn (array $item): int => (int) ($item['sort'] ?? 0)) ?? -1;

        foreach ($newUploads as $upload) {
            $name = trim((string) ($upload['name'] ?? ''));
            $file = $upload['pdf'] ?? null;

            if ($name === '' || ! $file instanceof UploadedFile) {
                continue;
            }

            $disk = self::preferredStorageDisk();
            $path = $file->store(self::storageDirectory($type), $disk);
            $maxSort++;
            $id = Str::uuid()->toString();

            $items->put($id, [
                'id' => $id,
                'name' => $name,
                'path' => $path,
                'disk' => $disk,
                'sort' => $maxSort,
            ]);
        }

        $normalized = $items->values()
            ->map(fn (array $item): array => self::normalizeItem($type, $item))
            ->filter(fn (array $item): bool => $item['id'] !== '' && $item['path'] !== '')
            ->sortBy('sort')
            ->values()
            ->all();

        Setting::set(
            $config['setting_key'],
            json_encode($normalized, JSON_UNESCAPED_UNICODE),
            'json',
            $config['setting_label']
        );
        Setting::clearCache();
    }

    public static function streamResponseForId(string $type, string $id, string $disposition = 'inline'): Response
    {
        self::assertType($type);
        $config = self::config($type);

        $item = self::find($type, $id);
        abort_if($item === null, 404, $config['not_found_message']);

        $disk = self::resolveDiskForPath($item['path'], $item['disk']);
        abort_if($disk === null, 404, $config['not_found_message']);

        $contents = Storage::disk($disk)->get($item['path']);
        abort_if(! is_string($contents), 404, $config['not_found_message']);

        $filename = self::attachmentFilename($type, $item);

        return response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
        ]);
    }

    /**
     * Employee hire policy/handbook PDFs (not used for internship or OJT).
     *
     * @return list<array{id: string, name: string, path: string, disk: string, sort: int}>
     */
    public static function hiredEmailPolicyMaterials(): array
    {
        return self::available('policy');
    }

    /**
     * Employee handbook PDFs for employee hires (not internship or OJT).
     *
     * @return list<array{id: string, name: string, path: string, disk: string, sort: int}>
     */
    public static function hiredEmailHandbookMaterials(): array
    {
        return self::available('handbook');
    }

    /**
     * @param  list<array{id: string, name: string, path: string, disk: string, sort: int}>  $items
     * @return list<Attachment>
     */
    public static function mailAttachmentsFromItems(string $type, array $items): array
    {
        $attachments = [];

        foreach ($items as $item) {
            $attachment = self::mailAttachmentFromItem($type, $item);
            if ($attachment !== null) {
                $attachments[] = $attachment;
            }
        }

        return $attachments;
    }

    /**
     * @param  array{id: string, name: string, path: string, disk: string, sort: int}  $item
     */
    public static function mailAttachmentFromItem(string $type, array $item): ?Attachment
    {
        $path = trim((string) ($item['path'] ?? ''));
        if ($path === '') {
            return null;
        }

        $disk = self::resolveDiskForPath($path, (string) ($item['disk'] ?? ''));
        if ($disk === null) {
            Log::warning('Employee document material missing for mail attachment', [
                'type' => $type,
                'name' => $item['name'] ?? null,
                'path' => $path,
            ]);

            return null;
        }

        try {
            $filename = self::attachmentFilename($type, $item);

            return Attachment::fromData(
                fn () => Storage::disk($disk)->get($path),
                $filename
            )->withMime('application/pdf');
        } catch (\Throwable $exception) {
            Log::error('Failed to build employee document mail attachment', [
                'type' => $type,
                'name' => $item['name'] ?? null,
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array{id: string, name: string, path: string, disk: string, sort: int}  $item
     */
    public static function attachmentFilename(string $type, array $item): string
    {
        return self::safeFilename($type, (string) ($item['name'] ?? ''), (string) ($item['path'] ?? ''));
    }

    public static function settingKey(string $type): string
    {
        return self::config($type)['setting_key'];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{id: string, name: string, path: string, disk: string, sort: int}
     */
    private static function normalizeItem(string $type, array $item): array
    {
        $config = self::config($type);
        $id = trim((string) ($item['id'] ?? ''));
        if ($id === '') {
            $id = Str::uuid()->toString();
        }

        return [
            'id' => $id,
            'name' => trim((string) ($item['name'] ?? '')) !== ''
                ? trim((string) $item['name'])
                : $config['default_name'],
            'path' => trim((string) ($item['path'] ?? '')),
            'disk' => trim((string) ($item['disk'] ?? '')),
            'sort' => isset($item['sort']) && is_numeric($item['sort']) ? (int) $item['sort'] : 0,
        ];
    }

    private static function migrateLegacyIfNeeded(string $type): void
    {
        $config = self::config($type);
        $legacyKey = $config['legacy_setting_key'] ?? null;
        if ($legacyKey === null) {
            return;
        }

        $current = Setting::get($config['setting_key'], '[]');
        $decoded = is_string($current) ? json_decode($current, true) : $current;

        if (is_array($decoded) && $decoded !== []) {
            return;
        }

        $legacyPath = trim((string) Setting::get($legacyKey, ''));
        if ($legacyPath === '') {
            return;
        }

        $disk = self::resolveDiskForPath($legacyPath) ?? self::preferredStorageDisk();

        $item = [
            'id' => Str::uuid()->toString(),
            'name' => $config['legacy_default_name'] ?? $config['default_name'],
            'path' => $legacyPath,
            'disk' => $disk,
            'sort' => 0,
        ];

        Setting::set(
            $config['setting_key'],
            json_encode([$item], JSON_UNESCAPED_UNICODE),
            'json',
            $config['setting_label']
        );
        Setting::clearCache();
    }

    private static function deleteFile(string $path, string $preferredDisk = ''): void
    {
        if ($path === '') {
            return;
        }

        foreach (array_values(array_unique(array_filter([$preferredDisk, ...self::candidateDisks()]))) as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                }
            } catch (\Throwable) {
                continue;
            }
        }
    }

    private static function safeFilename(string $type, string $name, string $path): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = basename($path) !== '' ? basename($path) : self::config($type)['default_name'];
        }

        return str_ends_with(strtolower($base), '.pdf') ? $base : $base.'.pdf';
    }

    public static function resolveDiskForPath(string $path, string $preferredDisk = ''): ?string
    {
        if ($path === '') {
            return null;
        }

        foreach (array_values(array_unique(array_filter([$preferredDisk, ...self::candidateDisks()]))) as $disk) {
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

    private static function storageDirectory(string $type): string
    {
        $assetRoot = trim((string) Setting::get('asset_storage_root', ''), '/');
        $dir = self::config($type)['storage_dir'];

        return $assetRoot !== '' ? $assetRoot.'/'.$dir : $dir;
    }

    /**
     * @return list<string>
     */
    private static function candidateDisks(): array
    {
        return ['spaces', 'digitalocean', 'public', 'local'];
    }

    /**
     * @return array{setting_key: string, setting_label: string, storage_dir: string, default_name: string, not_found_message: string, legacy_setting_key?: string, legacy_default_name?: string}
     */
    private static function config(string $type): array
    {
        self::assertType($type);

        return self::TYPES[$type];
    }

    private static function assertType(string $type): void
    {
        abort_unless(self::supports($type), 404);
    }
}
