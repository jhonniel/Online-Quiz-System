<?php

namespace App\Support;

use App\Models\EmployeeDocumentSignature;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

final class EmployeeDocumentSigning
{
    /**
     * Sign the document when the employee has a profile e-signature and it is not signed yet.
     */
    public static function ensureSigned(User $user, string $type): EmployeeDocumentSignature
    {
        abort_unless(EmployeeSampleDocument::isValidType($type), 404);

        $existing = EmployeeDocumentSignature::query()
            ->where('user_id', $user->id)
            ->where('document_type', $type)
            ->first();

        if ($existing?->isSigned()) {
            return $existing;
        }

        if (! $user->hasESignature()) {
            return $existing ?? EmployeeDocumentSignature::make([
                'user_id' => $user->id,
                'document_type' => $type,
            ]);
        }

        return self::sign($user, $type, $existing);
    }

    public static function sign(User $user, string $type, ?EmployeeDocumentSignature $existing = null): EmployeeDocumentSignature
    {
        abort_unless(EmployeeSampleDocument::isValidType($type), 404);

        if (! $user->hasESignature()) {
            throw new \InvalidArgumentException('Employee does not have an e-signature on file.');
        }

        $existing ??= EmployeeDocumentSignature::query()
            ->where('user_id', $user->id)
            ->where('document_type', $type)
            ->first();

        if ($existing?->isSigned()) {
            return $existing;
        }

        $user->loadMissing('department');
        $signedAt = now();
        $pdfBinary = EmployeeSampleDocument::renderSignedPdfBinary($user, $type, $signedAt);
        [$storedPath, $disk] = self::storePdfFile($pdfBinary, $user->id, $type);

        if ($existing && $existing->signed_document_path) {
            self::deleteFileIfExists((string) $existing->signed_document_path, (string) ($existing->storage_disk ?? ''));
        }

        return EmployeeDocumentSignature::updateOrCreate(
            ['user_id' => $user->id, 'document_type' => $type],
            [
                'signed_at' => $signedAt,
                'signed_document_path' => $storedPath,
                'storage_disk' => $disk,
            ]
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function storePdfFile(string $pdfBinary, int $userId, string $type): array
    {
        $dir = 'employee-signed-documents';
        $doConfigured = ! empty(env('DIGITALOCEAN_SPACES_KEY') ?: env('DO_SPACES_KEY'))
            && ! empty(env('DIGITALOCEAN_SPACES_SECRET') ?: env('DO_SPACES_SECRET'))
            && ! empty(env('DIGITALOCEAN_SPACES_BUCKET') ?: env('DO_SPACES_BUCKET'));

        if ($doConfigured) {
            $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
            $dir = $assetRoot ? $assetRoot.'/'.$dir : $dir;
        }

        $disk = $doConfigured ? 'digitalocean' : 'public';
        $filename = $userId.'-'.$type.'-'.now()->format('YmdHis').'.pdf';
        $path = $dir.'/'.$filename;

        Storage::disk($disk)->put($path, $pdfBinary);

        return [$path, $disk];
    }

    private static function deleteFileIfExists(string $path, string $preferredDisk = ''): void
    {
        if ($path === '') {
            return;
        }

        foreach (array_values(array_unique(array_filter([
            $preferredDisk,
            'digitalocean',
            'public',
            config('filesystems.default', 'local'),
        ]))) as $disk) {
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
}
