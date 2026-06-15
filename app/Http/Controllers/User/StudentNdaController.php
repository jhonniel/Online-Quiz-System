<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\StudentNda;
use App\Models\User;
use App\Support\StudentNdaDocument;
use App\Support\StudentNdaStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentNdaController extends Controller
{
    public function index()
    {
        $user = $this->requireStudent();

        $nda = StudentNda::query()->where('user_id', $user->id)->first();

        if ($nda) {
            $nda->purgeRejectedSignedDocument();
            $nda->refresh();
        }

        $previewUrl = ($nda && $nda->hasViewableSignedUpload())
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

        $existing = StudentNda::query()->where('user_id', $user->id)->first();
        if ($existing && ! $existing->canEditNdaDetails()) {
            return redirect()->route('user.nda.index')->withErrors([
                'full_name' => 'Your NDA is approved and can no longer be edited or regenerated.',
            ]);
        }

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

        if (! StudentNdaStorage::isConfigured()) {
            return redirect()->back()->withErrors([
                'signed_pdf' => StudentNdaStorage::notConfiguredMessage(),
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
            StudentNdaStorage::delete(
                (string) $existing->signed_document_path,
                (string) ($existing->storage_disk ?? '')
            );
        }

        [$storedPath, $disk] = StudentNdaStorage::store($validated['signed_pdf']);

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
                'approval_status' => StudentNda::STATUS_PENDING,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'review_notes' => null,
            ]
        );

        return redirect()->route('user.nda.index')
            ->with('success', 'Signed NDA uploaded successfully. An administrator must approve it before you can record attendance.');
    }

    public function preview()
    {
        $user = $this->requireStudent();
        $nda = StudentNda::query()->where('user_id', $user->id)->first();
        abort_unless($nda && $nda->hasViewableSignedUpload(), 404);

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

    private function streamStoredPdf(string $path, string $preferredDisk, string $filename)
    {
        $disk = StudentNdaStorage::resolveDiskForPath($path, $preferredDisk);
        abort_if($disk === null, 404);

        return Storage::disk($disk)->response($path, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
