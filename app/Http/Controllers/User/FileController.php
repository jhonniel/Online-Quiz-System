<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Display a listing of files and folders for the current user.
     */
    public function index(Request $request)
    {
        $folderId = $request->get('folder_id');
        $currentFolder = null;
        $userId = auth()->id();

        if ($folderId) {
            $currentFolder = File::where('id', $folderId)
                ->where('type', 'folder')
                ->firstOrFail();

            // Check if user has access to this folder
            if (!$currentFolder->canUserView($userId) && $currentFolder->uploaded_by != $userId) {
                abort(403, 'You do not have permission to access this folder.');
            }
        }

        // Get files and folders in current folder
        // IMPORTANT: if user can open the folder, they can see EVERYTHING inside it.
        if ($folderId) {
            $query = File::where('folder_id', $folderId)
                ->with(['uploader', 'sharedWith'])
                ->orderBy('type', 'desc')
                ->orderBy('name', 'asc');
        } else {
            // Root: show items user owns or items directly shared with them
            $query = File::whereNull('folder_id')
                ->where(function ($q) use ($userId) {
                    $q->where('uploaded_by', $userId)
                        ->orWhereHas('sharedWith', function ($sq) use ($userId) {
                            $sq->where('user_id', $userId)
                                ->where('can_view', true);
                        });
                })
                ->with(['uploader', 'sharedWith'])
                ->orderBy('type', 'desc')
                ->orderBy('name', 'asc');
        }

        $files = $query->paginate(20);

        // Get breadcrumb path
        $breadcrumbs = [];
        if ($currentFolder) {
            $folder = $currentFolder;
            while ($folder) {
                array_unshift($breadcrumbs, $folder);
                $folder = $folder->folder;
            }
        }

        return view('user.files.index', compact('files', 'currentFolder', 'breadcrumbs'));
    }

    /**
     * Store a newly uploaded file for the user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5242880', // 5GB max
            'folder_id' => 'nullable|exists:files,id',
            'description' => 'nullable|string|max:1000',
        ], [
            'file.max' => 'The file size must not exceed 5GB.',
        ]);

        $userId = auth()->id();

        // Check upload permission if uploading to a folder
        if ($request->folder_id) {
            $folder = File::where('id', $request->folder_id)
                ->where('type', 'folder')
                ->firstOrFail();

            if (!$folder->canUserUpload($userId)) {
                return redirect()->back()->withErrors(['error' => 'You do not have permission to upload to this folder.']);
            }
        }

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;

        // Store file in DigitalOcean Spaces with folder structure
        $assetDisk = 'digitalocean';
        $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
        $fileDir = $assetRoot ? $assetRoot . '/file-storage' : 'file-storage';

        // Build full folder path from root to current folder
        if ($request->folder_id) {
            $folder = File::where('id', $request->folder_id)
                ->where('type', 'folder')
                ->with('folder')
                ->firstOrFail();

            $folderPath = $this->getFolderPath($folder);
            if ($folderPath) {
                $fileDir = $fileDir . '/' . $folderPath;
            }
        }

        $path = $file->storeAs($fileDir, $filename, $assetDisk);

        // Create file record (no thumbnails here, admin side already handles images if needed)
        File::create([
            'name' => pathinfo($originalName, PATHINFO_FILENAME),
            'original_name' => $originalName,
            'path' => $path,
            'type' => 'file',
            'mime_type' => $mimeType,
            'size' => $size,
            'folder_id' => $request->folder_id,
            'uploaded_by' => $userId,
            'description' => $request->description,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'File uploaded successfully.');
    }

    /**
     * Download a file.
     */
    public function download(File $file)
    {
        $userId = auth()->id();

        if (!$file->canUserView($userId) && $file->uploaded_by != $userId) {
            abort(403, 'You do not have permission to download this file.');
        }

        if ($file->isFolder()) {
            abort(404, 'Cannot download a folder.');
        }

        try {
            $assetDisk = 'digitalocean';
            if (Storage::disk($assetDisk)->exists($file->path)) {
                return Storage::disk($assetDisk)->download($file->path, $file->original_name ?: $file->name);
            }
        } catch (\Exception $e) {
            if (Storage::disk('public')->exists($file->path)) {
                return Storage::disk('public')->download($file->path, $file->original_name ?: $file->name);
            }
        }

        abort(404, 'File not found.');
    }

    /**
     * View a file (redirect to cloud URL when possible).
     */
    public function view(File $file)
    {
        $userId = auth()->id();

        if (!$file->canUserView($userId) && $file->uploaded_by != $userId) {
            abort(403, 'You do not have permission to view this file.');
        }

        if ($file->isFolder()) {
            return redirect()->route('user.files.index', ['folder_id' => $file->id]);
        }

        try {
            $assetDisk = 'digitalocean';
            if (Storage::disk($assetDisk)->exists($file->path)) {
                $url = Storage::disk($assetDisk)->url($file->path);
                return redirect($url);
            }
        } catch (\Exception $e) {
            if (Storage::disk('public')->exists($file->path)) {
                return Storage::disk('public')->response($file->path, $file->original_name);
            }
        }

        abort(404, 'File not found.');
    }

    /**
     * Get folder path for storage organization.
     */
    private function getFolderPath(File $folder): string
    {
        $path = [];
        $current = $folder;

        while ($current) {
            array_unshift($path, $current->id . '_' . Str::slug($current->name));
            $current = $current->folder;
        }

        return implode('/', $path);
    }
}


