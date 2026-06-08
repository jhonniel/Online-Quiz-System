<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\StudentNda;
use App\Models\User;
use App\Support\StudentNdaDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentNdaController extends Controller
{
    public function index()
    {
        $user = $this->requireStudent();

        $nda = StudentNda::query()->where('user_id', $user->id)->first();
        $previewUrl = ($nda && $nda->hasSignedUpload())
            ? route('user.nda.preview')
            : '';

        return view('user.student-nda', [
            'student' => $user,
            'nda' => $nda,
            'previewUrl' => $previewUrl,
            'branding' => StudentNdaDocument::branding(),
        ]);
    }

    public function generatePdf(Request $request)
    {
        $user = $this->requireStudent();
        $validated = $request->validate(StudentNdaDocument::validationRules());
        $nda = StudentNdaDocument::fromInput($validated);
        $nda->user_id = $user->id;

        StudentNda::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $nda->full_name,
                'id_number' => $nda->id_number,
                'valid_id_type' => $nda->valid_id_type,
                'city' => $nda->city,
                'agreement_date' => $nda->agreement_date,
            ]
        );

        $filename = 'nda-'.str($nda->full_name)->slug('-').'.pdf';
        $pdfBinary = StudentNdaDocument::renderUnsignedPdfBinary($nda);

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->requireStudent();
        $existing = StudentNda::query()->where('user_id', $user->id)->first();

        if ($existing && ! $existing->canUploadSignedDocument()) {
            return redirect()->back()->withErrors([
                'signed_pdf' => 'You already uploaded a signed NDA. Reupload is disabled until an administrator allows it.',
            ]);
        }

        $validated = $request->validate(array_merge(
            StudentNdaDocument::validationRules(),
            ['signed_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240']]
        ));

        $ndaForValidation = StudentNdaDocument::fromInput($validated);
        $signedPdfError = StudentNdaDocument::validateSignedUpload($validated['signed_pdf'], $ndaForValidation);
        if ($signedPdfError !== null) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['signed_pdf' => $signedPdfError]);
        }

        if ($existing && $existing->hasSignedUpload()) {
            $this->deleteFileIfExists((string) $existing->signed_document_path, (string) ($existing->storage_disk ?? ''));
        }

        [$storedPath, $disk] = $this->storePdfFile($validated['signed_pdf']);

        StudentNda::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => trim((string) $validated['full_name']),
                'id_number' => trim((string) $validated['id_number']),
                'valid_id_type' => trim((string) $validated['valid_id_type']),
                'city' => trim((string) $validated['city']),
                'agreement_date' => $validated['agreement_date'],
                'signed_document_path' => $storedPath,
                'storage_disk' => $disk,
                'signed_uploaded_at' => now(),
                'reupload_allowed' => false,
            ]
        );

        return redirect()->route('user.nda.index')
            ->with('success', 'Signed NDA uploaded successfully.');
    }

    public function preview()
    {
        $user = $this->requireStudent();
        $nda = StudentNda::query()->where('user_id', $user->id)->first();
        abort_unless($nda && $nda->hasSignedUpload(), 404);

        return $this->streamStoredPdf(
            (string) $nda->signed_document_path,
            (string) ($nda->storage_disk ?? ''),
            'student-nda-signed.pdf'
        );
    }

    private function requireStudent(): User
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user && $user->role === 'student', 403);

        return $user;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function storePdfFile(\Illuminate\Http\UploadedFile $file): array
    {
        $dir = 'student-nda-documents';
        $assetDisk = 'digitalocean';
        $doConfigured = ! empty(env('DIGITALOCEAN_SPACES_KEY') ?: env('DO_SPACES_KEY'))
            && ! empty(env('DIGITALOCEAN_SPACES_SECRET') ?: env('DO_SPACES_SECRET'))
            && ! empty(env('DIGITALOCEAN_SPACES_BUCKET') ?: env('DO_SPACES_BUCKET'));

        if ($doConfigured) {
            $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
            $dir = $assetRoot ? $assetRoot.'/'.$dir : $dir;
        }

        $disk = $doConfigured ? $assetDisk : 'public';

        return [(string) $file->store($dir, $disk), $disk];
    }

    private function deleteFileIfExists(string $path, string $preferredDisk = ''): void
    {
        if ($path === '') {
            return;
        }

        $disks = array_values(array_unique(array_filter([
            $preferredDisk,
            'digitalocean',
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

    private function streamStoredPdf(string $path, string $preferredDisk, string $filename)
    {
        $disk = $this->resolveDiskForPath($path, $preferredDisk);
        abort_if($disk === null, 404);

        return Storage::disk($disk)->response($path, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    private function resolveDiskForPath(string $path, string $preferredDisk = ''): ?string
    {
        $candidateDisks = array_values(array_unique(array_filter([
            $preferredDisk,
            'digitalocean',
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
