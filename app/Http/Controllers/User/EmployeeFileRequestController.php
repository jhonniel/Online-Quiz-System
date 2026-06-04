<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EmployeeFileRequest;
use App\Support\EmployeeDocumentRequestTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeFileRequestController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->role !== 'employee') {
            abort(403, 'Only employees can request documents.');
        }

        $requests = EmployeeFileRequest::query()
            ->forEmployee($user->id)
            ->with(['generator:id,name'])
            ->latest()
            ->paginate(15);

        $documentTypes = EmployeeDocumentRequestTypes::labels();

        return view('user.employee-file-requests.index', compact('requests', 'documentTypes'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'employee') {
            abort(403, 'Only employees can request documents.');
        }

        $allowedTypes = EmployeeDocumentRequestTypes::keys();
        if ($allowedTypes === []) {
            return redirect()
                ->route('user.employee-file-requests.index')
                ->withErrors(['request_type' => 'No document types are available. Please contact HR.']);
        }

        $validated = $request->validate([
            'request_type' => ['required', 'string', 'in:'.implode(',', $allowedTypes)],
            'employee_notes' => ['nullable', 'string', 'max:2000'],
            'purpose' => ['nullable', 'string', 'max:500'],
        ]);

        $type = $validated['request_type'];
        $title = EmployeeDocumentRequestTypes::label($type);
        $notes = trim((string) ($validated['employee_notes'] ?? ''));
        $purpose = trim((string) ($validated['purpose'] ?? ''));

        if ($purpose !== '') {
            $notes = $notes !== ''
                ? $notes."\n\nPurpose: ".$purpose
                : 'Purpose: '.$purpose;
        }

        EmployeeFileRequest::create([
            'employee_file_template_id' => null,
            'user_id' => $user->id,
            'generated_by' => null,
            'title' => $title,
            'status' => EmployeeFileRequest::STATUS_PENDING,
            'request_type' => $type,
            'employee_notes' => $notes !== '' ? $notes : null,
        ]);

        return redirect()
            ->route('user.employee-file-requests.index')
            ->with('success', 'Your document request was submitted. HR will process it and upload the file when ready.');
    }

    public function download(EmployeeFileRequest $employeeFileRequest)
    {
        return $this->serveFile($employeeFileRequest, inline: false);
    }

    public function view(EmployeeFileRequest $employeeFileRequest)
    {
        return $this->serveFile($employeeFileRequest, inline: true);
    }

    private function serveFile(EmployeeFileRequest $fileRequest, bool $inline)
    {
        $user = auth()->user();
        if ($user->role !== 'employee' || (int) $fileRequest->user_id !== (int) $user->id) {
            abort(403);
        }

        if (! $fileRequest->isFulfilled() || ! $fileRequest->pdf_path) {
            abort(404, 'This document is not available yet.');
        }

        $diskName = $this->resolveStorageDisk($fileRequest);
        if ($diskName === null) {
            abort(404, 'File not found.');
        }

        $disk = Storage::disk($diskName);
        $filename = $fileRequest->original_filename ?: basename($fileRequest->pdf_path);
        $mime = $fileRequest->mime_type
            ?: ($disk->mimeType($fileRequest->pdf_path) ?: 'application/octet-stream');
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

    private function resolveStorageDisk(EmployeeFileRequest $fileRequest): ?string
    {
        if (! $fileRequest->pdf_path) {
            return null;
        }

        $disks = array_values(array_unique(array_filter([
            $fileRequest->storage_disk ?: 'digitalocean',
            'digitalocean',
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
}
