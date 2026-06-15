<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDocumentSignature;
use App\Models\User;
use App\Support\EmployeeContractDocument;
use App\Support\EmployeeDocumentSigning;
use App\Support\EmployeeHandbookDocument;
use App\Support\EmployeeHandbookMaterial;
use App\Support\EmployeeNdaDocument;
use App\Support\EmployeePolicyDocument;
use App\Support\EmployeePolicyMaterial;
use App\Support\EmployeeSampleDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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

        $signature = EmployeeDocumentSigning::ensureSigned($user, $type);

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

        $p12Password = null;

        if ($user->hasP12Certificate()) {
            $request->validate([
                'p12_certificate_password' => 'required|string|max:255',
            ], [
                'p12_certificate_password.required' => 'P12 certificate password is required.',
            ]);

            $p12Password = (string) $request->input('p12_certificate_password');
            $contents = $this->readP12Contents((string) $user->p12_certificate_path);
            $certs = [];

            if ($contents === null || ! openssl_pkcs12_read($contents, $certs, $p12Password)) {
                throw ValidationException::withMessages([
                    'p12_certificate_password' => ['The P12 certificate password is incorrect.'],
                ]);
            }
        } else {
            $request->validate([
                'password' => 'required|string',
            ], [
                'password.required' => 'Enter your password to confirm signing.',
            ]);

            if (! Hash::check((string) $request->input('password'), (string) $user->password)) {
                throw ValidationException::withMessages([
                    'password' => ['Your password is incorrect.'],
                ]);
            }
        }

        $user->loadMissing('department');
        EmployeeDocumentSigning::sign($user, $type, $existing, $p12Password);

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

    public function handbookMaterial(Request $request, string $id)
    {
        return $this->showDocumentMaterial($request, 'handbook', $id);
    }

    public function streamHandbookMaterial(Request $request, string $id)
    {
        return $this->streamDocumentMaterial($request, 'handbook', $id);
    }

    public function policyMaterial(Request $request, string $id)
    {
        return $this->showDocumentMaterial($request, 'policy', $id);
    }

    public function streamPolicyMaterial(Request $request, string $id)
    {
        return $this->streamDocumentMaterial($request, 'policy', $id);
    }

    private function showDocumentMaterial(Request $request, string $type, string $id)
    {
        $this->requireEmployee($request);

        $material = $type === 'handbook'
            ? EmployeeHandbookMaterial::find($id)
            : EmployeePolicyMaterial::find($id);
        abort_if($material === null, 404);

        $routePrefix = 'user.employee-documents.'.$type.'-material';

        return view('user.employee-documents.document-material', [
            'material' => $material,
            'subtitle' => $type === 'handbook'
                ? 'Employee handbook material — read-only PDF viewer.'
                : 'Company policy material — read-only PDF viewer.',
            'pdfUrl' => route($routePrefix.'.pdf', $material['id']),
            'downloadUrl' => route($routePrefix.'.pdf', ['id' => $material['id'], 'download' => 1]),
        ]);
    }

    private function streamDocumentMaterial(Request $request, string $type, string $id)
    {
        $this->requireEmployee($request);

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return $type === 'handbook'
            ? EmployeeHandbookMaterial::streamResponseForId($id, $disposition)
            : EmployeePolicyMaterial::streamResponseForId($id, $disposition);
    }

    private function requireEmployee(Request $request): User
    {
        abort_unless(User::employeeDocumentsNavEnabled(), 403);

        /** @var User|null $user */
        $user = $request->user();
        abort_unless($user && $user->isStaffMember(), 403);

        return $user;
    }

    private function readP12Contents(string $path): ?string
    {
        foreach (['digitalocean', 'public', 'local', config('filesystems.default', 'local')] as $diskName) {
            try {
                $disk = Storage::disk($diskName);
                if ($disk->exists($path)) {
                    $contents = $disk->get($path);

                    return is_string($contents) && $contents !== '' ? $contents : null;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
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
