<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentNda;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentNdaController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $ndas = StudentNda::query()
            ->with(['user.university'])
            ->whereNotNull('signed_document_path')
            ->where('signed_document_path', '!=', '')
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
            ->orderByDesc('signed_uploaded_at')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.student-management.nda-files', compact('ndas', 'search'));
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

        $disk = $this->resolveDiskForPath($path, (string) ($studentNda->storage_disk ?? ''));
        abort_if($disk === null, 404);

        return Storage::disk($disk)->response($path, 'student-nda-signed.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="student-nda-signed.pdf"',
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
