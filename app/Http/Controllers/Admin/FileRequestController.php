<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeFileRequest;
use App\Models\EmployeeFileTemplate;
use App\Models\User;
use App\Support\AdminEmployeeDepartmentScope;
use App\Support\EmployeeFileTemplateRenderer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileRequestController extends Controller
{
    private const SPACES_DISK = 'digitalocean';

    private const MAX_UPLOAD_KB = 20480;

    private const ALLOWED_EXTENSIONS = [
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

        $scopedEmployeeIds = $this->scopedEmployeeQuery()->pluck('id');

        $pendingRequests = EmployeeFileRequest::query()
            ->pending()
            ->with(['employee:id,name,email,department_id', 'employee.department:id,name'])
            ->whereIn('user_id', $scopedEmployeeIds)
            ->latest()
            ->get();

        $recordsQuery = EmployeeFileRequest::query()
            ->whereHas('employee', function ($query) {
                $this->applyEmployeeScope($query);
            });

        $recentRequests = (clone $recordsQuery)
            ->with(['template:id,name,category', 'employee:id,name,email,department_id', 'employee.department:id,name', 'generator:id,name'])
            ->latest()
            ->paginate(15)
            ->appends($request->query());

        $stats = [
            'pending' => $pendingRequests->count(),
            'records_total' => (clone $recordsQuery)->count(),
            'fulfilled' => (clone $recordsQuery)->where('status', EmployeeFileRequest::STATUS_FULFILLED)->count(),
            'rejected' => (clone $recordsQuery)->where('status', EmployeeFileRequest::STATUS_REJECTED)->count(),
        ];

        return view('admin.employee-management.file-request.index', compact(
            'employees',
            'pendingRequests',
            'recentRequests',
            'stats'
        ));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate(array_merge([
            'employee_id' => ['required', 'exists:users,id'],
            'title' => ['nullable', 'string', 'max:255'],
        ], $this->uploadFileRules()), [
            'file.required' => 'Please choose a file to send.',
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
            'status' => EmployeeFileRequest::STATUS_FULFILLED,
            'fulfilled_at' => now(),
            'original_filename' => $originalName,
            'mime_type' => $uploaded->getMimeType() ?: $this->guessMimeType($originalName),
            'field_values' => null,
            'rendered_html' => null,
        ]);

        if (! $this->isSpacesConfigured()) {
            return back()
                ->withInput()
                ->with('error', $this->spacesNotConfiguredMessage());
        }

        try {
            $this->attachUploadedFile($fileRequest, $uploaded);
        } catch (\Throwable $e) {
            $fileRequest->delete();
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Could not upload the file to DigitalOcean Spaces. Please try again.');
        }

        return redirect()
            ->route('admin.file-request.index')
            ->with('success', 'File sent to '.$employee->name.'.');
    }

    public function fulfill(Request $request, EmployeeFileRequest $fileRequest)
    {
        $this->authorizeAccess();

        $fileRequest->loadMissing('employee');
        if (! $fileRequest->isPending() || ! $this->canAccessEmployee($fileRequest->employee)) {
            abort(404);
        }

        if (! $request->hasFile('file') || ! $request->file('file')->isValid()) {
            return redirect()
                ->to(route('admin.file-request.index').'#request-'.$fileRequest->id)
                ->withErrors(['file' => 'Please choose a valid file to upload. If the file is large, check server upload limits (post_max_size / upload_max_filesize).'])
                ->withInput();
        }

        $validator = Validator::make(
            $request->all(),
            array_merge(
                $this->uploadFileRules(),
                ['admin_notes' => ['nullable', 'string', 'max:2000']]
            ),
            ['file.required' => 'Please choose a file to send to the employee.']
        );

        if ($validator->fails()) {
            return redirect()
                ->to(route('admin.file-request.index').'#request-'.$fileRequest->id)
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        if (! $this->isSpacesConfigured()) {
            return redirect()
                ->to(route('admin.file-request.index').'#request-'.$fileRequest->id)
                ->with('error', $this->spacesNotConfiguredMessage())
                ->withInput();
        }

        $uploaded = $request->file('file');
        $originalName = $uploaded->getClientOriginalName();

        try {
            $this->attachUploadedFile($fileRequest, $uploaded);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->to(route('admin.file-request.index').'#request-'.$fileRequest->id)
                ->with('error', 'Could not upload the file to DigitalOcean Spaces. Please try again.')
                ->withInput();
        }

        $fileRequest->update([
            'generated_by' => auth()->id(),
            'status' => EmployeeFileRequest::STATUS_FULFILLED,
            'fulfilled_at' => now(),
            'original_filename' => $originalName,
            'mime_type' => $uploaded->getMimeType() ?: $this->guessMimeType($originalName),
            'admin_notes' => trim((string) ($validated['admin_notes'] ?? '')) ?: $fileRequest->admin_notes,
        ]);

        return redirect()
            ->route('admin.file-request.index')
            ->with('success', 'Request fulfilled — file sent to '.$fileRequest->employee->name.'.');
    }

    public function reject(Request $request, EmployeeFileRequest $fileRequest)
    {
        $this->authorizeAccess();

        $fileRequest->loadMissing('employee');
        if (! $fileRequest->isPending() || ! $this->canAccessEmployee($fileRequest->employee)) {
            abort(404);
        }

        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:2000'],
        ], [
            'admin_notes.required' => 'Please provide a reason or note for the employee.',
        ]);

        $fileRequest->update([
            'status' => EmployeeFileRequest::STATUS_REJECTED,
            'generated_by' => auth()->id(),
            'admin_notes' => trim($validated['admin_notes']),
        ]);

        return redirect()
            ->route('admin.file-request.index')
            ->with('success', 'Request declined. The employee will see your note.');
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
            $disk = $this->resolveStorageDiskForPath($fileRequest) ?? $fileRequest->storage_disk ?? 'digitalocean';
            try {
                Storage::disk($disk)->delete($fileRequest->pdf_path);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $fileRequest->delete();

        return redirect()
            ->route('admin.file-request.index')
            ->with('success', 'File record deleted.');
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return;
        }

        if ($user->canAccessEmployeeFeature('file_request')) {
            return;
        }

        abort(403, 'Access denied. You do not have permission to access File Request.');
    }

    private function scopedEmployeeQuery()
    {
        $query = User::query()->where('role', 'employee');
        AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($query, auth()->user());

        return $query;
    }

    private function applyEmployeeScope($query): void
    {
        AdminEmployeeDepartmentScope::applyToEmployeeQueryForDocuments($query, auth()->user());
    }

    private function canAccessEmployee(?User $employee): bool
    {
        return AdminEmployeeDepartmentScope::canAccessEmployeeForDocuments(auth()->user(), $employee);
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
        return $this->resolveStorageDiskForPath($fileRequest) !== null;
    }

    private function respondWithStoredFile(EmployeeFileRequest $fileRequest, bool $inline): \Symfony\Component\HttpFoundation\Response
    {
        $diskName = $this->resolveStorageDiskForPath($fileRequest);
        if ($diskName === null) {
            abort(404, 'File not found.');
        }

        $disk = Storage::disk($diskName);
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

    private function attachUploadedFile(EmployeeFileRequest $fileRequest, \Illuminate\Http\UploadedFile $uploaded): void
    {
        if (! $this->isSpacesConfigured()) {
            throw new \RuntimeException($this->spacesNotConfiguredMessage());
        }

        $path = $this->buildSpacesObjectKey($fileRequest, $uploaded);
        $contents = file_get_contents($uploaded->getRealPath());

        if ($contents === false) {
            throw new \RuntimeException('Could not read the uploaded file from disk.');
        }

        Storage::disk(self::SPACES_DISK)->put($path, $contents, 'public');

        $fileRequest->update([
            'pdf_path' => $path,
            'storage_disk' => self::SPACES_DISK,
        ]);
    }

    private function buildSpacesObjectKey(EmployeeFileRequest $fileRequest, \Illuminate\Http\UploadedFile $uploaded): string
    {
        $root = trim((string) env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
        $dir = $root ? $root.'/employee-file-requests' : 'employee-file-requests';
        $storedName = Str::uuid()->toString().'.'.($uploaded->getClientOriginalExtension() ?: 'bin');

        return $dir.'/'.$fileRequest->id.'/'.$storedName;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function uploadFileRules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:'.self::MAX_UPLOAD_KB,
                function (string $attribute, $value, \Closure $fail): void {
                    if (! $value instanceof \Illuminate\Http\UploadedFile) {
                        $fail('Invalid upload.');

                        return;
                    }
                    $ext = strtolower($value->getClientOriginalExtension() ?: '');
                    if ($ext === '' || ! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                        $fail('Allowed file types: PDF, Word, Excel, and images (.pdf, .doc, .docx, .xls, .xlsx, .png, .jpg, .jpeg, .webp).');
                    }
                },
            ],
        ];
    }

    private function isSpacesConfigured(): bool
    {
        $cfg = config('filesystems.disks.'.self::SPACES_DISK, []);
        $bucket = $cfg['bucket'] ?? null;
        $endpoint = $cfg['endpoint'] ?? null;
        $key = $cfg['key'] ?? null;
        $secret = $cfg['secret'] ?? null;

        return ! empty($bucket) && ! empty($endpoint) && ! empty($key) && ! empty($secret);
    }

    private function spacesNotConfiguredMessage(): string
    {
        return 'File upload requires DigitalOcean Spaces. Set DIGITALOCEAN_SPACES_* or DO_SPACES_* in .env.';
    }

    private function resolveStorageDiskForPath(EmployeeFileRequest $fileRequest): ?string
    {
        if (! $fileRequest->pdf_path) {
            return null;
        }

        $disks = array_values(array_unique(array_filter([
            $fileRequest->storage_disk ?: self::SPACES_DISK,
            self::SPACES_DISK,
            'public',
        ])));

        foreach ($disks as $disk) {
            try {
                if (Storage::disk($disk)->exists($fileRequest->pdf_path)) {
                    return $disk;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
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
