<?php

namespace App\Support;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class FileStreamResponse
{
    public static function inline(File $file): StreamedResponse|\Illuminate\Http\Response
    {
        if ($file->isFolder()) {
            abort(404, 'Cannot view a folder.');
        }

        $mimeType = $file->mime_type ?: 'application/octet-stream';
        $disposition = 'inline; filename="' . addslashes($file->original_name ?: $file->name) . '"';

        try {
            if (Storage::disk('digitalocean')->exists($file->path)) {
                $stream = Storage::disk('digitalocean')->readStream($file->path);
                if ($stream) {
                    return response()->stream(function () use ($stream) {
                        fpassthru($stream);
                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }, 200, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => $disposition,
                        'Accept-Ranges' => 'bytes',
                    ]);
                }
            }
        } catch (\Throwable) {
            // Fall through to public disk.
        }

        if (Storage::disk('public')->exists($file->path)) {
            return response()->file(Storage::disk('public')->path($file->path), [
                'Content-Type' => $mimeType,
                'Content-Disposition' => $disposition,
                'Accept-Ranges' => 'bytes',
            ]);
        }

        abort(404, 'File not found.');
    }

    public static function download(File $file): StreamedResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        if ($file->isFolder()) {
            abort(404, 'Cannot download a folder.');
        }

        try {
            if (Storage::disk('digitalocean')->exists($file->path)) {
                return Storage::disk('digitalocean')->download($file->path, $file->original_name ?: $file->name);
            }
        } catch (\Throwable) {
            // Fall through to public disk.
        }

        if (Storage::disk('public')->exists($file->path)) {
            return Storage::disk('public')->download($file->path, $file->original_name ?: $file->name);
        }

        abort(404, 'File not found.');
    }

    public static function inlinePath(string $path, ?string $downloadName = null): StreamedResponse|\Illuminate\Http\Response
    {
        $path = ltrim($path, '/');
        if ($path === '') {
            abort(404, 'File not found.');
        }

        $filename = $downloadName ?: basename($path);
        $mimeType = self::mimeTypeForPath($path);
        $disposition = 'inline; filename="'.addslashes($filename).'"';

        try {
            if (Storage::disk('digitalocean')->exists($path)) {
                $stream = Storage::disk('digitalocean')->readStream($path);
                if ($stream) {
                    return response()->stream(function () use ($stream) {
                        fpassthru($stream);
                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }, 200, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => $disposition,
                        'Accept-Ranges' => 'bytes',
                    ]);
                }
            }
        } catch (\Throwable) {
            // Fall through to public disk.
        }

        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path), [
                'Content-Type' => $mimeType,
                'Content-Disposition' => $disposition,
                'Accept-Ranges' => 'bytes',
            ]);
        }

        abort(404, 'File not found.');
    }

    private static function mimeTypeForPath(string $path): string
    {
        try {
            $mime = Storage::disk('digitalocean')->mimeType($path);
            if (is_string($mime) && $mime !== '') {
                return $mime;
            }
        } catch (\Throwable) {
            // Fall through to extension guess.
        }

        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };
    }
}
