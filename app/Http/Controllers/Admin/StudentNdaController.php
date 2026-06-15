<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentNda;
use App\Support\StudentNdaStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentNdaController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $statusFilter = trim((string) $request->query('status', ''));

        $ndas = StudentNda::query()
            ->with(['user.university', 'reviewer'])
            ->whereNotNull('signed_document_path')
            ->where('signed_document_path', '!=', '')
            ->when($statusFilter !== '', function ($query) use ($statusFilter): void {
                if ($statusFilter === StudentNda::STATUS_PENDING) {
                    $query->where(function ($q): void {
                        $q->where('approval_status', StudentNda::STATUS_PENDING)
                            ->orWhereNull('approval_status');
                    });
                } else {
                    $query->where('approval_status', $statusFilter);
                }
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByRaw("CASE WHEN approval_status = 'pending' OR approval_status IS NULL THEN 0 ELSE 1 END")
            ->orderByDesc('signed_uploaded_at')
            ->paginate(20)
            ->appends($request->query());

        $uploadedQuery = StudentNda::query()
            ->whereNotNull('signed_document_path')
            ->where('signed_document_path', '!=', '');

        $pendingCount = (clone $uploadedQuery)->where(function ($q): void {
            $q->where('approval_status', StudentNda::STATUS_PENDING)
                ->orWhereNull('approval_status');
        })->count();

        $approvedCount = (clone $uploadedQuery)
            ->where('approval_status', StudentNda::STATUS_APPROVED)
            ->count();

        $rejectedCount = StudentNda::query()
            ->where('approval_status', StudentNda::STATUS_REJECTED)
            ->count();

        $totalCount = $pendingCount + $approvedCount + $rejectedCount;

        return view('admin.student-management.nda-files', compact(
            'ndas',
            'search',
            'statusFilter',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalCount'
        ));
    }

    public function approve(StudentNda $studentNda)
    {
        abort_unless($studentNda->user?->role === 'student', 404);
        abort_unless($studentNda->hasSignedUpload(), 404);

        $studentNda->update([
            'approval_status' => StudentNda::STATUS_APPROVED,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_notes' => null,
            'reupload_allowed' => false,
        ]);

        return redirect()->route('admin.student-nda-files.index')
            ->with('success', "NDA approved for {$studentNda->user->name}. The student can now record attendance.");
    }

    public function reject(Request $request, StudentNda $studentNda)
    {
        abort_unless($studentNda->user?->role === 'student', 404);
        abort_unless($studentNda->hasSignedUpload(), 404);

        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($studentNda, $validated): void {
            $studentNda->removeSignedDocument();

            $studentNda->update([
                'approval_status' => StudentNda::STATUS_REJECTED,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => trim((string) ($validated['review_notes'] ?? '')) ?: null,
                'reupload_allowed' => true,
                'signed_document_path' => null,
                'storage_disk' => null,
                'signed_uploaded_at' => null,
            ]);
        });

        $studentNda->refresh();

        return redirect()->route('admin.student-nda-files.index')
            ->with('success', "NDA rejected for {$studentNda->user->name}. The signed file was removed from storage and the student may upload a corrected NDA.");
    }

    public function allowReupload(StudentNda $studentNda)
    {
        abort_unless($studentNda->user?->role === 'student', 404);

        $studentNda->update([
            'reupload_allowed' => true,
        ]);

        return redirect()->route('admin.student-nda-files.index')
            ->with('success', "Reupload allowed for {$studentNda->user->name}.");
    }

    public function preview(StudentNda $studentNda)
    {
        abort_unless($studentNda->user?->role === 'student', 404);

        $path = (string) ($studentNda->signed_document_path ?? '');
        abort_if($path === '', 404);

        $disk = StudentNdaStorage::resolveDiskForPath($path, (string) ($studentNda->storage_disk ?? ''));
        abort_if($disk === null, 404);

        return Storage::disk($disk)->response($path, 'student-nda-signed.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="student-nda-signed.pdf"',
        ]);
    }
}
