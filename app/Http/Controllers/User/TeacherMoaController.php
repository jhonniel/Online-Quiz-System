<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeacherMoaController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless($user && $user->role === 'teacher', 403);

        return view('user.teacher-moa', [
            'teacher' => $user,
            'moaUrl' => $user->moa_document_path ? url('/teacher/moa/preview') : '',
        ]);
    }

    public function preview()
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user && $user->role === 'teacher', 403);

        $path = (string) ($user->moa_document_path ?? '');
        abort_if($path === '', 404);

        $disk = $this->resolveDiskForPath($path);
        abort_if($disk === null, 404);

        return Storage::disk($disk)->response($path, 'teacher-moa.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="teacher-moa.pdf"',
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $user->role === 'teacher', 403);

        $hasExistingMoa = ! empty($user->moa_document_path);
        if ($hasExistingMoa && ! (bool) $user->moa_reupload_allowed) {
            return redirect()->back()->withErrors([
                'moa_pdf' => 'You already uploaded an MOA. Reupload is disabled until the company reopens MOA upload.',
            ]);
        }

        $validated = $request->validate([
            'moa_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $storedPath = $this->storeMoaFile($validated['moa_pdf']);

        if ($hasExistingMoa) {
            $this->deleteFileIfExists((string) $user->moa_document_path);
        }

        $user->update([
            'moa_document_path' => $storedPath,
            'moa_uploaded_at' => now(),
            // Auto-lock again after successful upload.
            'moa_reupload_allowed' => false,
        ]);

        return redirect(url('/teacher/moa'))
            ->with('success', 'MOA uploaded successfully.');
    }

    private function storeMoaFile(\Illuminate\Http\UploadedFile $file): string
    {
        $dir = 'teacher-moa-documents';
        $assetDisk = 'digitalocean';
        $doConfigured = ! empty(env('DIGITALOCEAN_SPACES_KEY') ?: env('DO_SPACES_KEY'))
            && ! empty(env('DIGITALOCEAN_SPACES_SECRET') ?: env('DO_SPACES_SECRET'))
            && ! empty(env('DIGITALOCEAN_SPACES_BUCKET') ?: env('DO_SPACES_BUCKET'));

        if ($doConfigured) {
            $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
            $dir = $assetRoot ? $assetRoot.'/'.$dir : $dir;
        }

        $disk = $doConfigured ? $assetDisk : 'public';

        return (string) $file->store($dir, $disk);
    }

    private function deleteFileIfExists(string $path): void
    {
        if ($path === '') {
            return;
        }

        $assetDisk = 'digitalocean';
        $doConfigured = ! empty(env('DIGITALOCEAN_SPACES_KEY') ?: env('DO_SPACES_KEY'))
            && ! empty(env('DIGITALOCEAN_SPACES_SECRET') ?: env('DO_SPACES_SECRET'))
            && ! empty(env('DIGITALOCEAN_SPACES_BUCKET') ?: env('DO_SPACES_BUCKET'));
        $disk = $doConfigured ? $assetDisk : config('filesystems.default', 'local');

        try {
            Storage::disk($disk)->delete($path);
        } catch (\Throwable $e) {
            // Ignore stale file deletion failures.
        }
    }

    private function resolveDiskForPath(string $path): ?string
    {
        $candidateDisks = ['digitalocean', 'public', 'local'];

        foreach ($candidateDisks as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    return $disk;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }
}
