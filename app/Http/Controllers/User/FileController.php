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
     * Get S3 client + bucket for DigitalOcean Spaces.
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
     * Create a presigned upload URL so the browser uploads directly to Spaces.
     */
    public function presignUpload(Request $request)
    {
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
     * Initiate a multipart upload (chunked upload).
     */
    public function initiateMultipartUpload(Request $request)
    {
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
                if (method_exists(Storage::disk($assetDisk), 'temporaryUrl')) {
                    $url = Storage::disk($assetDisk)->temporaryUrl($file->path, now()->addMinutes(60));
                } else {
                    $url = Storage::disk($assetDisk)->url($file->path);
                }
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


