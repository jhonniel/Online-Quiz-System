<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeFileRequest;
use App\Models\EmployeeFileTemplate;
use App\Models\User;
use App\Support\EmployeeFileTemplateRenderer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileRequestController extends Controller
{
    private const MAX_UPLOAD_KB = 20480;

    private const ALLOWED_MIMES = [
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'png',
        'jpg',
        'jpeg',
        'webp',
    ];

    public function __construct(
        private readonly EmployeeFileTemplateRenderer $renderer
    ) {}

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $employees = $this->scopedEmployeeQuery()
            ->with(['department:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'department_id', 'role']);

        $recentRequests = EmployeeFileRequest::query()
            ->with(['template:id,name,category', 'employee:id,name,email', 'generator:id,name'])
            ->whereHas('employee', function ($query) {
                $this->applyEmployeeScope($query);
            })
            ->latest()
            ->paginate(15)
            ->appends($request->query());

        return view('admin.employee-management.file-request.index', compact(
            'employees',
            'recentRequests'
        ));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:users,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'file' => [
                'required',
                'file',
                'max:'.self::MAX_UPLOAD_KB,
                'mimes:'.implode(',', self::ALLOWED_MIMES),
            ],
        ], [
            'file.required' => 'Please choose a file to send.',
            'file.mimes' => 'Allowed file types: PDF, Word, Excel, and images.',
        ]);

        $employee = $this->scopedEmployeeQuery()->findOrFail($validated['employee_id']);
        $uploaded = $request->file('file');
        $originalName = $uploaded->getClientOriginalName();
        $title = trim((string) ($validated['title'] ?? '')) ?: $originalName;

        $fileRequest = EmployeeFileRequest::create([
            'employee_file_template_id' => null,
            'user_id' => $employee->id,
            'generated_by' => auth()->id(),
            'title' => $title,
            'original_filename' => $originalName,
            'mime_type' => $uploaded->getMimeType() ?: $this->guessMimeType($originalName),
            'field_values' => null,
            'rendered_html' => null,
        ]);

        $disk = 'digitalocean';
        $root = trim((string) env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
        $dir = $root ? $root.'/employee-file-requests' : 'employee-file-requests';
        $storedName = Str::uuid()->toString().'.'.$uploaded->getClientOriginalExtension();
        $path = $dir.'/'.$fileRequest->id.'/'.$storedName;

        try {
            Storage::disk($disk)->put($path, file_get_contents($uploaded->getRealPath()));
            $fileRequest->update(['pdf_path' => $path]);
        } catch (\Throwable $e) {
            $fileRequest->delete();
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Could not upload the file. Please try again.');
        }

        return redirect()
            ->route('admin.file-request.index')
            ->with('success', 'File sent to '.$employee->name.'.');
    }

    public function view(EmployeeFileRequest $fileRequest)
    {
        $this->authorizeAccess();

        $fileRequest->loadMissing(['employee', 'template']);
        if (! $this->canAccessEmployee($fileRequest->employee)) {
            abort(403);
        }

        return $this->respondWithFile($fileRequest, inline: true);
    }

    public function download(EmployeeFileRequest $fileRequest)
    {
        $this->authorizeAccess();

        $fileRequest->loadMissing(['employee', 'template']);
        if (! $this->canAccessEmployee($fileRequest->employee)) {
            abort(403);
        }

        return $this->respondWithFile($fileRequest, inline: false);
    }

    public function destroy(EmployeeFileRequest $fileRequest)
    {
        $this->authorizeAccess();

        $fileRequest->loadMissing('employee');
        if (! $this->canAccessEmployee($fileRequest->employee)) {
            abort(403);
        }

        if ($fileRequest->pdf_path) {
            Storage::disk('digitalocean')->delete($fileRequest->pdf_path);
        }

        $fileRequest->delete();

        return redirect()
            ->route('admin.file-request.index')
            ->with('success', 'File record deleted.');
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        if (! $user->isAdmin() && ! $user->canAccessEmployeeManagement()) {
            abort(403, 'Access denied. You do not have permission to access Employee Management.');
        }
    }

    private function scopedEmployeeQuery()
    {
        $authUser = auth()->user();
        $query = User::query()->where('role', 'employee');
        $allowedDepartmentIds = $authUser->canAccessEmployeeManagement()
            ? $authUser->getAllowedDepartmentIds()
            : null;

        if ($allowedDepartmentIds !== null) {
            $query->whereIn('department_id', $allowedDepartmentIds);
        }

        return $query;
    }

    private function applyEmployeeScope($query): void
    {
        $allowedDepartmentIds = auth()->user()->getAllowedDepartmentIds();
        if ($allowedDepartmentIds !== null) {
            $query->whereIn('department_id', $allowedDepartmentIds);
        }
    }

    private function canAccessEmployee(?User $employee): bool
    {
        if (! $employee || $employee->role !== 'employee') {
            return false;
        }

        $allowedDepartmentIds = auth()->user()->getAllowedDepartmentIds();
        if ($allowedDepartmentIds === null) {
            return true;
        }

        return in_array((int) $employee->department_id, $allowedDepartmentIds, true);
    }

    private function respondWithFile(EmployeeFileRequest $fileRequest, bool $inline): \Symfony\Component\HttpFoundation\Response
    {
        if ($this->hasStoredFile($fileRequest)) {
            return $this->respondWithStoredFile($fileRequest, inline: $inline);
        }

        return $this->streamLegacyTemplatePdf($fileRequest, inline: $inline);
    }

    private function hasStoredFile(EmployeeFileRequest $fileRequest): bool
    {
        return $fileRequest->pdf_path
            && Storage::disk('digitalocean')->exists($fileRequest->pdf_path);
    }

    private function respondWithStoredFile(EmployeeFileRequest $fileRequest, bool $inline): \Symfony\Component\HttpFoundation\Response
    {
        $disk = Storage::disk('digitalocean');
        $filename = $fileRequest->original_filename ?: basename($fileRequest->pdf_path);
        $mime = $fileRequest->mime_type
            ?: ($disk->mimeType($fileRequest->pdf_path) ?: null)
            ?: $this->guessMimeType($filename);

        $disposition = ($inline ? 'inline' : 'attachment').'; filename="'.addslashes($filename).'"';

        return response()->stream(function () use ($disk, $fileRequest) {
            $stream = $disk->readStream($fileRequest->pdf_path);
            if ($stream === false) {
                throw new \RuntimeException('Could not read the stored file.');
            }

            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition,
        ]);
    }

    /**
     * Legacy template-generated documents (before upload-only flow).
     */
    private function streamLegacyTemplatePdf(EmployeeFileRequest $fileRequest, bool $inline)
    {
        $fileRequest->loadMissing(['template', 'employee']);

        if (! $fileRequest->employee) {
            abort(404, 'File not found.');
        }

        if (! $fileRequest->template) {
            abort(404, 'This file is no longer available. The stored copy could not be found.');
        }

        try {
            $html = $this->renderer->render(
                $fileRequest->template,
                $fileRequest->employee,
                is_array($fileRequest->field_values) ? $fileRequest->field_values : []
            );

            $filename = Str::slug($fileRequest->title).'.pdf';
            $pdf = Pdf::loadView('admin.employee-management.file-request.pdf', [
                'title' => $fileRequest->title,
                'html' => $html,
                'fullBleed' => $fileRequest->template->isCertificateOfEmployment(),
            ])->setPaper('a4', 'portrait');

            return $inline ? $pdf->stream($filename) : $pdf->download($filename);
        } catch (\Throwable $e) {
            report($e);
            abort(500, 'Could not open this file. Try Download instead, or upload a new copy.');
        }
    }

    private function guessMimeType(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
