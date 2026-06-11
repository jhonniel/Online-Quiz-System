<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDocumentSignature;
use App\Models\User;
use App\Support\EmployeeDocumentSigning;
use App\Support\EmployeeContractDocument;
use App\Support\EmployeeHandbookDocument;
use App\Support\EmployeeHandbookMaterial;
use App\Support\EmployeeNdaDocument;
use App\Support\EmployeePolicyDocument;
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

        $wasUnsigned = ! EmployeeDocumentSignature::query()
            ->where('user_id', $user->id)
            ->where('document_type', $type)
            ->whereNotNull('signed_at')
            ->exists();

        $signature = EmployeeDocumentSigning::ensureSigned($user, $type);

        if ($wasUnsigned && $signature->isSigned()) {
            session()->flash('success', EmployeeSampleDocument::label($type).' signed automatically with your profile e-signature.');
        }

        return view('user.employee-documents.show', [
            'user' => $user,
            'type' => $type,
            'label' => EmployeeSampleDocument::label($type),
            'title' => EmployeeSampleDocument::title($type),
            'paragraphs' => EmployeeSampleDocument::paragraphs($user, $type),
            'ndaView' => $type === 'nda'
                ? EmployeeNdaDocument::viewData($user, $signature->signed_at)
                : null,
            'policyView' => $type === 'policy'
                ? EmployeePolicyDocument::viewData($user, $signature->signed_at)
                : null,
            'contractView' => $type === 'contract'
                ? EmployeeContractDocument::viewData($user, $signature->signed_at)
                : null,
            'handbookView' => $type === 'handbook'
                ? EmployeeHandbookDocument::viewData($user, $signature->signed_at)
                : null,
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

        EmployeeDocumentSigning::sign($user, $type, $existing);

        return redirect()
            ->route('user.employee-documents.show', $type)
            ->with('success', EmployeeSampleDocument::label($type).' signed successfully.');
    }

    public function generatePdf(Request $request, string $type)
    {
        $user = $this->requireEmployee($request);
        abort_unless(EmployeeSampleDocument::isValidType($type), 404);

        $user->loadMissing('department');

        $signature = EmployeeDocumentSigning::ensureSigned($user, $type);

        $isSigned = $signature->isSigned();
        $pdfBinary = $isSigned
            ? EmployeeSampleDocument::renderSignedPdfBinary($user, $type, $signature->signed_at)
            : EmployeeSampleDocument::renderPdfBinary($user, $type, null);

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

        $signature = EmployeeDocumentSigning::ensureSigned($user, $type);

        abort_unless($signature->isSigned(), 404);

        return $this->streamStoredPdf(
            (string) $signature->signed_document_path,
            (string) ($signature->storage_disk ?? ''),
            'employee-'.$type.'-signed.pdf'
        );
    }

    public function handbookMaterial(Request $request)
    {
        $this->requireEmployee($request);

        return view('user.employee-documents.handbook-material', [
            'handbookAvailable' => EmployeeHandbookMaterial::isAvailable(),
            'handbookPdfUrl' => EmployeeHandbookMaterial::isAvailable()
                ? route('user.employee-documents.handbook-material.pdf')
                : null,
        ]);
    }

    public function streamHandbookMaterial(Request $request)
    {
        $this->requireEmployee($request);

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return EmployeeHandbookMaterial::streamResponse($disposition);
    }

    private function requireEmployee(Request $request): User
    {
        abort_unless(User::employeeDocumentsNavEnabled(), 403);

        /** @var User|null $user */
        $user = $request->user();
        abort_unless($user && $user->role === 'employee', 403);

        return $user;
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
