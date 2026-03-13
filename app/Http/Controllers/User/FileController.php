<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Check if DigitalOcean Spaces is configured.
     */
    private function isSpacesConfigured(): bool
    {
        $cfg = config('filesystems.disks.digitalocean', []);
        $bucket = $cfg['bucket'] ?? null;
        $endpoint = $cfg['endpoint'] ?? null;
        $key = $cfg['key'] ?? null;
        $secret = $cfg['secret'] ?? null;

        return !empty($bucket) && !empty($endpoint) && !empty($key) && !empty($secret);
    }

    /**
     * Get S3 client + bucket for DigitalOcean Spaces.
     * For browser uploads to work, the Space must have CORS configured (see docs/SPACES_CORS_SETUP.md).
     */
    private function getSpacesClientAndBucket(): array
    {
        $cfg = config('filesystems.disks.digitalocean', []);
        $bucket = $cfg['bucket'] ?? null;
        $endpoint = $cfg['endpoint'] ?? null;
        $region = $cfg['region'] ?? 'us-east-1';
        $key = $cfg['key'] ?? null;
        $secret = $cfg['secret'] ?? null;

        if (!$bucket || !$endpoint || !$key || !$secret) {
            throw new \RuntimeException('DigitalOcean Spaces disk is not configured correctly.');
        }

        $endpoint = preg_match('#^https?://#i', $endpoint) ? $endpoint : 'https://' . $endpoint;
        $host = parse_url($endpoint, PHP_URL_HOST) ?: '';
        $usePathStyle = true;
        if ($host && str_contains($host, $bucket . '.')) {
            $usePathStyle = false;
        }

        $client = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region' => $region,
            'endpoint' => $endpoint,
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
            'signature_version' => 'v4',
            'use_path_style_endpoint' => $usePathStyle,
        ]);

        return [$client, $bucket];
    }

    /**
     * Build base file directory in Spaces for a folder (includes hierarchy).
     */
    private function buildSpacesDir(?int $folderId): array
    {
        $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
        $fileDir = $assetRoot ? $assetRoot . '/file-storage' : 'file-storage';
        $folder = null;

        if ($folderId) {
            $folder = File::where('id', $folderId)
                ->where('type', 'folder')
                ->with('folder')
                ->firstOrFail();

            $folderPath = $this->getFolderPath($folder);
            if ($folderPath) {
                $fileDir = $fileDir . '/' . $folderPath;
            }
        }

        return [$fileDir, $folder];
    }

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

        // Users who can access Files (employees and students) – folders can be shared with them
        $users = User::where('id', '!=', auth()->id())
            ->whereIn('role', ['employee', 'student'])
            ->orderBy('role')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return view('user.files.index', compact('files', 'currentFolder', 'breadcrumbs', 'users'));
    }

    /**
     * Create a new folder (user files).
     */
    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'folder_id' => 'nullable|exists:files,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $userId = auth()->id();
        $folderId = $request->filled('folder_id') ? $request->folder_id : null;

        if ($folderId) {
            $parentFolder = File::where('id', $folderId)
                ->where('type', 'folder')
                ->firstOrFail();

            if (!$parentFolder->canUserUpload($userId)) {
                return redirect()->back()->withErrors(['error' => 'You do not have permission to create folders here.']);
            }
        }

        $existingFolder = File::where('folder_id', $folderId)
            ->where('type', 'folder')
            ->where('name', $request->name)
            ->where('uploaded_by', $userId)
            ->first();

        if ($existingFolder) {
            return redirect()->back()->withErrors(['name' => 'A folder with this name already exists in this location.']);
        }

        File::create([
            'name' => $request->name,
            'original_name' => null,
            'path' => '',
            'type' => 'folder',
            'mime_type' => null,
            'size' => null,
            'folder_id' => $folderId,
            'uploaded_by' => $userId,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Folder created successfully.');
    }

    /**
     * Store a newly uploaded file for the user.
     * Files are uploaded to DigitalOcean Spaces when configured.
     */
    public function store(Request $request)
    {
        $request->merge([
            'folder_id' => $request->filled('folder_id') ? $request->folder_id : null,
        ]);

        $request->validate([
            'file' => 'required|file|max:5242880', // 5GB max
            'folder_id' => 'nullable|exists:files,id',
            'description' => 'nullable|string|max:1000',
        ], [
            'file.max' => 'The file size must not exceed 5GB.',
        ]);

        if (!$this->isSpacesConfigured()) {
            $message = 'File upload requires DigitalOcean Spaces to be configured. Please set DIGITALOCEAN_SPACES_* in .env.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $message], 503);
            }
            return redirect()->back()->withErrors(['error' => $message]);
        }

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

        $assetDisk = 'digitalocean';
        $assetRoot = trim(env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
        $fileDirSpaces = $assetRoot ? $assetRoot . '/file-storage' : 'file-storage';
        if ($request->folder_id) {
            $folder = File::where('id', $request->folder_id)->where('type', 'folder')->with('folder')->firstOrFail();
            $folderPath = $this->getFolderPath($folder);
            if ($folderPath) {
                $fileDirSpaces = $fileDirSpaces . '/' . $folderPath;
            }
        }
        $path = $file->storeAs($fileDirSpaces, $filename, $assetDisk);

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
     * Create a presigned upload URL so the browser uploads directly to Spaces.
     */
    public function presignUpload(Request $request)
    {
        if (!$this->isSpacesConfigured()) {
            return response()->json(['message' => 'File upload requires DigitalOcean Spaces to be configured. Please set DIGITALOCEAN_SPACES_* in .env.'], 503);
        }

        $validated = $request->validate([
            'original_name' => 'required|string|max:255',
            'mime_type' => 'nullable|string|max:255',
            'size' => 'required|integer|min:1|max:5368709120', // 5GB
            'folder_id' => 'nullable|exists:files,id',
        ]);

        $userId = auth()->id();

        if (!empty($validated['folder_id'])) {
            $folder = File::where('id', $validated['folder_id'])
                ->where('type', 'folder')
                ->firstOrFail();

            if (!$folder->canUserUpload($userId)) {
                return response()->json(['message' => 'You do not have permission to upload to this folder.'], 403);
            }
        }

        $extension = pathinfo($validated['original_name'], PATHINFO_EXTENSION);
        $extension = $extension ? strtolower($extension) : 'bin';

        $filename = Str::random(40) . '.' . $extension;
        [$fileDir] = $this->buildSpacesDir($validated['folder_id'] ?? null);
        $key = trim($fileDir . '/' . $filename, '/');

        [$client, $bucket] = $this->getSpacesClientAndBucket();

        $contentType = $validated['mime_type'] ?: 'application/octet-stream';

        $command = $client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key' => $key,
            'ContentType' => $contentType,
        ]);

        $presigned = $client->createPresignedRequest($command, '+60 minutes');

        $headers = [];
        foreach ($presigned->getHeaders() as $name => $values) {
            $headers[$name] = implode(', ', $values);
        }
        unset($headers['Host'], $headers['host']);

        return response()->json([
            'upload_url' => (string) $presigned->getUri(),
            'path' => $key,
            'headers' => $headers,
            'max_size' => 5368709120,
        ]);
    }

    /**
     * Confirm a direct Spaces upload and create the DB record.
     */
    public function confirmUpload(Request $request)
    {
        $validated = $request->validate([
            'path' => 'required|string|max:2048',
            'original_name' => 'required|string|max:255',
            'mime_type' => 'nullable|string|max:255',
            'size' => 'required|integer|min:1|max:5368709120',
            'folder_id' => 'nullable|exists:files,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $userId = auth()->id();

        if (!empty($validated['folder_id'])) {
            $folder = File::where('id', $validated['folder_id'])
                ->where('type', 'folder')
                ->firstOrFail();

            if (!$folder->canUserUpload($userId)) {
                return response()->json(['message' => 'You do not have permission to upload to this folder.'], 403);
            }
        }

        $assetDisk = 'digitalocean';
        try {
            if (!Storage::disk($assetDisk)->exists($validated['path'])) {
                return response()->json(['message' => 'Upload not found in Spaces. Please retry.'], 422);
            }
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Could not verify Spaces upload.'], 500);
        }

        $originalName = $validated['original_name'];

        $fileModel = File::create([
            'name' => pathinfo($originalName, PATHINFO_FILENAME),
            'original_name' => $originalName,
            'path' => $validated['path'],
            'type' => 'file',
            'mime_type' => $validated['mime_type'] ?: null,
            'size' => (int) $validated['size'],
            'folder_id' => $validated['folder_id'] ?? null,
            'uploaded_by' => $userId,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'file' => $fileModel->fresh(),
        ]);
    }

    /**
     * Initiate a multipart upload (chunked upload) to Spaces.
     */
    public function initiateMultipartUpload(Request $request)
    {
        if (!$this->isSpacesConfigured()) {
            return response()->json(['message' => 'File upload requires DigitalOcean Spaces to be configured. Please set DIGITALOCEAN_SPACES_* in .env.'], 503);
        }

        $validated = $request->validate([
            'original_name' => 'required|string|max:255',
            'mime_type' => 'nullable|string|max:255',
            'size' => 'required|integer|min:1|max:5368709120', // 5GB
            'folder_id' => 'nullable|exists:files,id',
        ]);

        $userId = auth()->id();

        if (!empty($validated['folder_id'])) {
            $folder = File::where('id', $validated['folder_id'])
                ->where('type', 'folder')
                ->firstOrFail();

            if (!$folder->canUserUpload($userId)) {
                return response()->json(['message' => 'You do not have permission to upload to this folder.'], 403);
            }
        }

        $extension = pathinfo($validated['original_name'], PATHINFO_EXTENSION);
        $extension = $extension ? strtolower($extension) : 'bin';

        $filename = Str::random(40) . '.' . $extension;
        [$fileDir] = $this->buildSpacesDir($validated['folder_id'] ?? null);
        $key = trim($fileDir . '/' . $filename, '/');

        [$client, $bucket] = $this->getSpacesClientAndBucket();

        $contentType = $validated['mime_type'] ?: 'application/octet-stream';

        $result = $client->createMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $key,
            'ContentType' => $contentType,
        ]);

        $uploadId = $result['UploadId'];

        return response()->json([
            'upload_id' => $uploadId,
            'path' => $key,
            'chunk_size' => 10 * 1024 * 1024, // 10MB chunks
        ]);
    }

    /**
     * Get presigned URL for uploading a chunk.
     */
    public function presignChunk(Request $request)
    {
        $validated = $request->validate([
            'upload_id' => 'required|string|max:255',
            'path' => 'required|string|max:2048',
            'part_number' => 'required|integer|min:1|max:10000',
        ]);

        [$client, $bucket] = $this->getSpacesClientAndBucket();

        $command = $client->getCommand('UploadPart', [
            'Bucket' => $bucket,
            'Key' => $validated['path'],
            'UploadId' => $validated['upload_id'],
            'PartNumber' => $validated['part_number'],
        ]);

        $presigned = $client->createPresignedRequest($command, '+60 minutes');

        $headers = [];
        foreach ($presigned->getHeaders() as $name => $values) {
            $headers[$name] = implode(', ', $values);
        }
        unset($headers['Host'], $headers['host']);

        return response()->json([
            'upload_url' => (string) $presigned->getUri(),
            'headers' => $headers,
        ]);
    }

    /**
     * Complete multipart upload and create DB record.
     */
    public function completeMultipartUpload(Request $request)
    {
        $validated = $request->validate([
            'upload_id' => 'required|string|max:255',
            'path' => 'required|string|max:2048',
            'parts' => 'required|array|min:1',
            'parts.*.part_number' => 'required|integer|min:1',
            'parts.*.etag' => 'required|string|max:255',
            'original_name' => 'required|string|max:255',
            'mime_type' => 'nullable|string|max:255',
            'size' => 'required|integer|min:1|max:5368709120',
            'folder_id' => 'nullable|exists:files,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $userId = auth()->id();

        if (!empty($validated['folder_id'])) {
            $folder = File::where('id', $validated['folder_id'])
                ->where('type', 'folder')
                ->firstOrFail();

            if (!$folder->canUserUpload($userId)) {
                return response()->json(['message' => 'You do not have permission to upload to this folder.'], 403);
            }
        }

        [$client, $bucket] = $this->getSpacesClientAndBucket();

        $parts = [];
        foreach ($validated['parts'] as $part) {
            $parts[] = [
                'PartNumber' => $part['part_number'],
                'ETag' => $part['etag'],
            ];
        }

        usort($parts, function($a, $b) {
            return $a['PartNumber'] <=> $b['PartNumber'];
        });

        try {
            $result = $client->completeMultipartUpload([
                'Bucket' => $bucket,
                'Key' => $validated['path'],
                'UploadId' => $validated['upload_id'],
                'MultipartUpload' => [
                    'Parts' => $parts,
                ],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to complete multipart upload', [
                'upload_id' => $validated['upload_id'],
                'path' => $validated['path'],
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Failed to complete upload: ' . $e->getMessage()], 500);
        }

        $assetDisk = 'digitalocean';
        try {
            if (!Storage::disk($assetDisk)->exists($validated['path'])) {
                return response()->json(['message' => 'Upload not found in Spaces. Please retry.'], 422);
            }
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Could not verify Spaces upload.'], 500);
        }

        $originalName = $validated['original_name'];
        $mimeType = $validated['mime_type'] ?: null;
        $size = (int) $validated['size'];
        $fileDir = trim(dirname($validated['path']), '/');

        $fileModel = File::create([
            'name' => pathinfo($originalName, PATHINFO_FILENAME),
            'original_name' => $originalName,
            'path' => $validated['path'],
            'type' => 'file',
            'mime_type' => $mimeType,
            'size' => $size,
            'folder_id' => $validated['folder_id'] ?? null,
            'uploaded_by' => $userId,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'file' => $fileModel->fresh(),
        ]);
    }

    /**
     * Abort multipart upload (cleanup).
     */
    public function abortMultipartUpload(Request $request)
    {
        $validated = $request->validate([
            'upload_id' => 'required|string|max:255',
            'path' => 'required|string|max:2048',
        ]);

        [$client, $bucket] = $this->getSpacesClientAndBucket();

        try {
            $client->abortMultipartUpload([
                'Bucket' => $bucket,
                'Key' => $validated['path'],
                'UploadId' => $validated['upload_id'],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to abort multipart upload', [
                'upload_id' => $validated['upload_id'],
                'path' => $validated['path'],
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['success' => true]);
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
     * View a file (stream with inline disposition so it can be played in-browser, e.g. video/audio).
     */
    public function view(File $file)
    {
        $userId = auth()->id();

        if (!$file->canUserView($userId) && $file->uploaded_by != $userId) {
            abort(403, 'You do not have permission to view this file.');
        }

        if ($file->isFolder()) {
            return redirect('/files?folder_id=' . $file->id);
        }

        $mimeType = $file->mime_type ?: 'application/octet-stream';
        $disposition = 'inline; filename="' . addslashes($file->original_name ?: $file->name) . '"';

        try {
            if ($this->isSpacesConfigured() && Storage::disk('digitalocean')->exists($file->path)) {
                $stream = Storage::disk('digitalocean')->readStream($file->path);
                if ($stream) {
                    return response()->stream(function () use ($stream) {
                        fpassthru($stream);
                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }, 200, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => $disposition,
                        'Accept-Ranges' => 'bytes',
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Fall through to public disk
        }

        if (Storage::disk('public')->exists($file->path)) {
            return response()->file(Storage::disk('public')->path($file->path), [
                'Content-Type' => $mimeType,
                'Content-Disposition' => $disposition,
                'Accept-Ranges' => 'bytes',
            ]);
        }

        abort(404, 'File not found.');
    }

    /**
     * Share a file or folder with another user (add to folder / grant access).
     */
    public function share(Request $request, File $file)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'can_view' => 'boolean',
            'can_upload' => 'boolean',
        ]);

        if ($file->uploaded_by != auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'Only the owner can share this ' . $file->type . '.']);
        }

        if ((int) $request->user_id === auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot share with yourself.']);
        }

        $canUpload = $file->isFolder() ? ($request->boolean('can_upload')) : false;

        DB::table('file_user_permissions')->updateOrInsert(
            [
                'file_id' => $file->id,
                'user_id' => $request->user_id,
            ],
            [
                'can_view' => $request->boolean('can_view', true),
                'can_upload' => $canUpload,
                'granted_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return redirect()->back()->with('success', ucfirst($file->type) . ' shared successfully.');
    }

    /**
     * Remove sharing permission for a user.
     */
    public function unshare(Request $request, File $file)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        if ($file->uploaded_by != auth()->id()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Only the owner can remove sharing.'], 403);
            }
            return redirect()->back()->withErrors(['error' => 'Only the owner can remove sharing.']);
        }

        DB::table('file_user_permissions')
            ->where('file_id', $file->id)
            ->where('user_id', $request->user_id)
            ->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Sharing removed.']);
        }

        return redirect()->back()->with('success', 'Sharing removed.');
    }

    /**
     * Get users this file/folder is shared with. Owner and any user with access can view.
     */
    public function getSharedUsers(File $file)
    {
        $userId = auth()->id();
        if ($file->uploaded_by != $userId && !$file->canUserView($userId)) {
            abort(403, 'You do not have permission to view who this is shared with.');
        }

        $sharedUsers = $file->sharedWith()->get();

        return response()->json($sharedUsers);
    }

    /**
     * Delete a file or folder. Only the owner can delete; folders are recursively deleted.
     */
    public function destroy(File $file)
    {
        if ($file->uploaded_by != auth()->id()) {
            abort(403, 'Only the owner can delete this ' . $file->type . '.');
        }

        $this->deleteFileOrFolderRecursive($file);

        return redirect()->back()->with('success', ucfirst($file->type) . ' deleted successfully.');
    }

    /**
     * Recursively delete a file or folder and its contents (storage + DB). No redirect.
     */
    private function deleteFileOrFolderRecursive(File $file): void
    {
        if ($file->isFolder()) {
            foreach ($file->children()->get() as $child) {
                $this->deleteFileOrFolderRecursive($child);
            }
        } else {
            try {
                if ($file->path && Storage::disk('digitalocean')->exists($file->path)) {
                    Storage::disk('digitalocean')->delete($file->path);
                }
            } catch (\Exception $e) {
                try {
                    if ($file->path && Storage::disk('public')->exists($file->path)) {
                        Storage::disk('public')->delete($file->path);
                    }
                } catch (\Exception $e2) {
                    // Ignore
                }
            }
            if (!empty($file->thumbnail_path)) {
                try {
                    if (Storage::disk('digitalocean')->exists($file->thumbnail_path)) {
                        Storage::disk('digitalocean')->delete($file->thumbnail_path);
                    }
                } catch (\Exception $e) {
                    // Ignore
                }
                try {
                    if (Storage::disk('public')->exists($file->thumbnail_path)) {
                        Storage::disk('public')->delete($file->thumbnail_path);
                    }
                } catch (\Exception $e) {
                    // Ignore
                }
            }
        }

        DB::table('file_user_permissions')->where('file_id', $file->id)->delete();
        $file->delete();
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


