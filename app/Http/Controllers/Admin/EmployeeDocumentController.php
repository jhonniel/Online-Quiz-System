<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDocumentSignature;
use App\Models\User;
use App\Support\AdminEmployeeDepartmentScope;
use App\Support\EmployeeDocumentTemplate;
use App\Support\EmployeeSampleDocument;
use App\Support\UserESignatureStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentController extends Controller
{
    public function signatures(Request $request)
    {
        abort_unless($this->requireAuthUser()->canAccessAnyEmployeeDocumentFeature(), 403);

        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $query = User::query()
            ->where('role', 'employee')
            ->with('department:id,name');

        $this->applyEmployeeScope($query);

        if ($search !== '') {
            $query->where(function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status === 'with_signature') {
            $query->whereNotNull('e_signature_path')->where('e_signature_path', '!=', '');
        } elseif ($status === 'without_signature') {
            $query->where(function ($userQuery) {
                $userQuery->whereNull('e_signature_path')->orWhere('e_signature_path', '=', '');
            });
        }

        $employees = $query
            ->orderBy('name')
            ->paginate(24)
            ->appends($request->query());

        return view('admin.employee-documents.signatures', [
            'employees' => $employees,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function uploadEmployeeESignature(Request $request, User $employee)
    {
        abort_unless($this->requireAuthUser()->canAccessAnyEmployeeDocumentFeature(), 403);
        abort_unless($employee->role === 'employee', 404);
        abort_unless($this->canAccessEmployee($employee), 403);

        $validated = $request->validate([
            'e_signature' => 'required|file|mimes:png|max:1536',
        ], [
            'e_signature.required' => 'Please choose a PNG e-signature file.',
            'e_signature.mimes' => 'E-signature must be a PNG file.',
            'e_signature.max' => 'E-signature must not be larger than 1.5MB.',
        ]);

        $path = UserESignatureStorage::store($employee, $validated['e_signature']);
        $employee->update(['e_signature_path' => $path]);

        return redirect()
            ->route('admin.employee-documents.signatures', $request->only(['search', 'status', 'page']))
            ->with('success', 'E-signature uploaded for '.$employee->name.'.');
    }

    public function removeEmployeeESignature(Request $request, User $employee)
    {
        abort_unless($this->requireAuthUser()->canAccessAnyEmployeeDocumentFeature(), 403);
        abort_unless($employee->role === 'employee', 404);
        abort_unless($this->canAccessEmployee($employee), 403);

        UserESignatureStorage::delete($employee->e_signature_path);
        $employee->update(['e_signature_path' => null]);

        return redirect()
            ->route('admin.employee-documents.signatures', $request->only(['search', 'status', 'page']))
            ->with('success', 'E-signature removed for '.$employee->name.'.');
    }

    public function index(Request $request, string $type)
    {
        abort_unless(EmployeeSampleDocument::isValidType($type), 404);
        $this->authorizeDocumentType($type);

        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $query = User::query()
            ->where('role', 'employee')
            ->with([
                'department:id,name',
                'employeeDocumentSignatures' => fn ($signatureQuery) => $signatureQuery->where('document_type', $type),
            ]);

        $this->applyEmployeeScope($query);

        if ($search !== '') {
            $query->where(function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status === 'signed') {
            $query->whereHas('employeeDocumentSignatures', function ($signatureQuery) use ($type) {
                $signatureQuery->where('document_type', $type)
                    ->whereNotNull('signed_at')
                    ->whereNotNull('signed_document_path')
                    ->where('signed_document_path', '!=', '');
            });
        } elseif ($status === 'pending') {
            $query->whereDoesntHave('employeeDocumentSignatures', function ($signatureQuery) use ($type) {
                $signatureQuery->where('document_type', $type)
                    ->whereNotNull('signed_at')
                    ->whereNotNull('signed_document_path')
                    ->where('signed_document_path', '!=', '');
            });
        }

        $employees = $query
            ->orderBy('name')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.employee-documents.index', [
            'type' => $type,
            'label' => EmployeeSampleDocument::label($type),
            'title' => EmployeeSampleDocument::title($type),
            'employees' => $employees,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function preview(EmployeeDocumentSignature $signature)
    {
        $this->authorizeDocumentType($signature->document_type);
        $signature->load('user');
        abort_unless($signature->user?->role === 'employee', 404);
        abort_unless($this->canAccessEmployee($signature->user), 403);
        abort_unless($signature->isSigned(), 404);

        return $this->streamStoredPdf(
            (string) $signature->signed_document_path,
            (string) ($signature->storage_disk ?? ''),
            'employee-'.$signature->document_type.'-signed.pdf'
        );
    }

    public function editTemplate(string $type)
    {
        abort_unless(EmployeeSampleDocument::isValidType($type), 404);
        abort_unless(EmployeeDocumentTemplate::supports($type), 404);
        $this->authorizeDocumentType($type);

        $templateHtml = EmployeeDocumentTemplate::editorTemplateHtml($type);

        return view('admin.employee-documents.template', [
            'type' => $type,
            'label' => EmployeeSampleDocument::label($type),
            'title' => EmployeeSampleDocument::title($type),
            'settingKey' => EmployeeDocumentTemplate::settingKey($type),
            'templateHtml' => $templateHtml,
            'defaultTemplateHtml' => EmployeeDocumentTemplate::defaultTemplateHtmlForEditor($type),
            'hasCustomTemplate' => EmployeeDocumentTemplate::hasCustomTemplate($type),
            'placeholders' => EmployeeDocumentTemplate::placeholders($type),
            'previewHtml' => EmployeeDocumentTemplate::previewHtmlFromTemplate($type, $templateHtml),
            'previewPlaceholders' => EmployeeDocumentTemplate::previewPlaceholderMap($type),
        ]);
    }

    public function updateTemplate(Request $request, string $type)
    {
        abort_unless(EmployeeSampleDocument::isValidType($type), 404);
        abort_unless(EmployeeDocumentTemplate::supports($type), 404);
        $this->authorizeDocumentType($type);

        $request->validate([
            'template_html' => 'nullable|string|max:65000',
        ]);

        $html = (string) ($request->input('template_html') ?? '');
        EmployeeDocumentTemplate::saveTemplateHtml($type, $html);
        Cache::forget('setting.'.EmployeeDocumentTemplate::settingKey($type));

        return redirect()
            ->route('admin.employee-documents.template', $type)
            ->with('success', EmployeeSampleDocument::label($type).' template saved. Employees will see the updated content on their next view.');
    }

    public function previewEmployee(Request $request, string $type, User $employee)
    {
        abort_unless(EmployeeSampleDocument::isValidType($type), 404);
        $this->authorizeDocumentType($type);
        abort_unless($employee->role === 'employee', 404);
        abort_unless($this->canAccessEmployee($employee), 403);

        $employee->loadMissing('department');

        $signature = EmployeeDocumentSignature::query()
            ->where('user_id', $employee->id)
            ->where('document_type', $type)
            ->first();

        if ($signature?->isSigned()) {
            return $this->streamStoredPdf(
                (string) $signature->signed_document_path,
                (string) ($signature->storage_disk ?? ''),
                'employee-'.$type.'-signed.pdf'
            );
        }

        $pdfBinary = EmployeeSampleDocument::renderPdfBinary($employee, $type, null);
        $filename = EmployeeSampleDocument::pdfFilename($type, false);

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    private function authorizeDocumentType(string $type): void
    {
        $feature = match ($type) {
            'nda' => 'employee_nda',
            'contract' => 'employee_contract',
            'policy' => 'employee_policy',
            'handbook' => 'employee_handbook',
            default => null,
        };

        abort_unless($feature !== null, 404);
        abort_unless($this->requireAuthUser()->canAccessEmployeeFeature($feature), 403);
    }

    private function canAccessEmployee(?User $employee): bool
    {
        return AdminEmployeeDepartmentScope::canAccessEmployeeForDocuments($this->requireAuthUser(), $employee);
    }

    private function applyEmployeeScope($query): void
    {
        AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($query, $this->requireAuthUser());
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
