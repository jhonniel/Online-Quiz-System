<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeacherMoaController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $teachers = User::query()
            ->with(['university'])
            ->where('role', 'teacher')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.teachers.moa', compact('teachers', 'search'));
    }

    public function allowReupload(User $user)
    {
        abort_unless($user->role === 'teacher', 404);

        $user->update([
            'moa_reupload_allowed' => true,
        ]);

        return redirect(url('/admin/teachers-management/moa'))
            ->with('success', "Reupload allowed for {$user->name}.");
    }

    public function preview(User $user)
    {
        abort_unless($user->role === 'teacher', 404);

        $path = (string) ($user->moa_document_path ?? '');
        abort_if($path === '', 404);

        $disk = $this->resolveDiskForPath($path);
        abort_if($disk === null, 404);

        return Storage::disk($disk)->response($path, 'teacher-moa.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="teacher-moa.pdf"',
        ]);
    }

    private function resolveDiskForPath(string $path): ?string
    {
        $candidateDisks = ['digitalocean', 'public', 'local'];

        foreach ($candidateDisks as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    return $disk;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }
}
