<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDocumentSignature;
use App\Models\User;
use App\Support\EmployeeSampleDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->requireEmployee($request);

        $signatures = EmployeeDocumentSignature::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('document_type');

        $documents = collect(EmployeeSampleDocument::TYPES)->map(function (string $type) use ($signatures) {
            $signature = $signatures->get($type);

            return [
                'type' => $type,
                'label' => EmployeeSampleDocument::label($type),
                'title' => EmployeeSampleDocument::title($type),
                'signed' => $signature?->isSigned() ?? false,
                'signed_at' => $signature?->signed_at,
            ];
        });

        return view('user.employee-documents.index', compact('documents', 'user'));
    }

    public function show(Request $request, string $type)
    {
        $user = $this->requireEmployee($request);
        abort_unless(EmployeeSampleDocument::isValidType($type), 404);

        $user->loadMissing('department');

        $signature = EmployeeDocumentSignature::query()
            ->where('user_id', $user->id)
            ->where('document_type', $type)
            ->first();

        return view('user.employee-documents.show', [
            'user' => $user,
            'type' => $type,
            'label' => EmployeeSampleDocument::label($type),
            'title' => EmployeeSampleDocument::title($type),
            'paragraphs' => EmployeeSampleDocument::paragraphs($user, $type),
            'signature' => $signature,
        ]);
    }

    public function sign(Request $request, string $type)
    {
        $user = $this->requireEmployee($request);
        abort_unless(EmployeeSampleDocument::isValidType($type), 404);

        if (! $user->hasESignature()) {
            return redirect()
                ->route('user.employee-documents.show', $type)
                ->withErrors([
                    'signature' => 'Upload your e-signature on your profile before signing documents.',
                ]);
        }

        $existing = EmployeeDocumentSignature::query()
            ->where('user_id', $user->id)
            ->where('document_type', $type)
            ->first();

        if ($existing?->isSigned()) {
            return redirect()
                ->route('user.employee-documents.show', $type)
                ->withErrors([
                    'signature' => 'You have already signed this document.',
                ]);
        }

        $user->loadMissing('department');
        $signedAt = now();
        $pdfBinary = EmployeeSampleDocument::renderSignedPdfBinary($user, $type, $signedAt);
        [$storedPath, $disk] = $this->storePdfFile($pdfBinary, $user->id, $type);

        if ($existing && $existing->signed_document_path) {
            $this->deleteFileIfExists((string) $existing->signed_document_path, (string) ($existing->storage_disk ?? ''));
        }

        EmployeeDocumentSignature::updateOrCreate(
            ['user_id' => $user->id, 'document_type' => $type],
            [
                'signed_at' => $signedAt,
                'signed_document_path' => $storedPath,
                'storage_disk' => $disk,
            ]
        );

        return redirect()
            ->route('user.employee-documents.show', $type)
            ->with('success', EmployeeSampleDocument::label($type).' signed successfully.');
    }

    public function generatePdf(Request $request, string $type)
    {
        $user = $this->requireEmployee($request);
        abort_unless(EmployeeSampleDocument::isValidType($type), 404);

        $user->loadMissing('department');

        $signature = EmployeeDocumentSignature::query()
            ->where('user_id', $user->id)
            ->where('document_type', $type)
            ->first();

        $isSigned = $signature?->isSigned() ?? false;
        $signedAt = $isSigned ? $signature->signed_at : null;
        $pdfBinary = EmployeeSampleDocument::renderPdfBinary($user, $type, $signedAt);

        $filename = EmployeeSampleDocument::pdfFilename($type, $isSigned);
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
        ]);
    }

    public function preview(Request $request, string $type)
    {
        $user = $this->requireEmployee($request);
        abort_unless(EmployeeSampleDocument::isValidType($type), 404);

        $signature = EmployeeDocumentSignature::query()
            ->where('user_id', $user->id)
            ->where('document_type', $type)
            ->first();

        abort_unless($signature?->isSigned(), 404);

        return $this->streamStoredPdf(
            (string) $signature->signed_document_path,
            (string) ($signature->storage_disk ?? ''),
            'employee-'.$type.'-signed.pdf'
        );
    }

    private function requireEmployee(Request $request): User
    {
        abort_unless(User::employeeDocumentsNavEnabled(), 403);

        /** @var User|null $user */
        $user = $request->user();
        abort_unless($user && $user->role === 'employee', 403);

        return $user;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function storePdfFile(string $pdfBinary, int $userId, string $type): array
    {
        $dir = 'employee-signed-documents';
        $assetDisk = 'digitalocean';
        $doConfigured = ! empty(env('DIGITALOCEAN_SPACES_KEY') ?: env('DO_SPACES_KEY'))
            && ! empty(env('DIGITALOCEAN_SPACES_SECRET') ?: env('DO_SPACES_SECRET'))
            && ! empty(env('DIGITALOCEAN_SPACES_BUCKET') ?: env('DO_SPACES_BUCKET'));

        if ($doConfigured) {
            $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
            $dir = $assetRoot ? $assetRoot.'/'.$dir : $dir;
        }

        $disk = $doConfigured ? $assetDisk : 'public';
        $filename = $userId.'-'.$type.'-'.now()->format('YmdHis').'.pdf';
        $path = $dir.'/'.$filename;

        Storage::disk($disk)->put($path, $pdfBinary);

        return [$path, $disk];
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

        $contents = Storage::disk($disk)->get($path);
        abort_if(! is_string($contents), 404);

        return response($contents, 200, [
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
