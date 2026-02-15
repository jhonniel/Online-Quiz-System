<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            // Endpoint already includes bucket, don't use path-style.
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
     * Display a listing of files and folders.
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
        // (Sharing a folder should automatically share its contents.)
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

        // Get all folders for folder selection dropdown
        $allFolders = File::where('type', 'folder')
            ->orderBy('name')
            ->get();

        // Get all users for sharing (excluding current user)
        $users = User::where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get();

        return view('admin.files.index', compact('files', 'currentFolder', 'breadcrumbs', 'allFolders', 'users'));
    }

    /**
     * Store a newly uploaded file.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5242880', // 5GB max (5120MB = 5242880KB)
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

            // Build folder path hierarchy
            $folderPath = $this->getFolderPath($folder);
            if ($folderPath) {
                $fileDir = $fileDir . '/' . $folderPath;
            }
        }

        // Ensure directory structure exists (Laravel's storeAs will create it automatically)
        $path = $file->storeAs($fileDir, $filename, $assetDisk);

        // Generate thumbnail for images
        $thumbnailPath = null;
        if (str_starts_with($mimeType, 'image/')) {
            try {
                $thumbnailPath = $this->generateThumbnail($file, $fileDir, $assetDisk);
            } catch (\Exception $e) {
                // If thumbnail generation fails, continue without thumbnail
                \Log::warning('Thumbnail generation failed: ' . $e->getMessage());
            }
        }

        // Create file record
        File::create([
            'name' => pathinfo($originalName, PATHINFO_FILENAME),
            'original_name' => $originalName,
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'type' => 'file',
            'mime_type' => $mimeType,
            'size' => $size,
            'folder_id' => $request->folder_id,
            'uploaded_by' => auth()->id(),
            'description' => $request->description,
        ]);

        // Return JSON response for AJAX requests
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

        // Check upload permission if uploading to a folder
        if (!empty($validated['folder_id'])) {
            $folder = File::where('id', $validated['folder_id'])
                ->where('type', 'folder')
                ->firstOrFail();

            if (!$folder->canUserUpload($userId)) {
                return response()->json(['message' => 'You do not have permission to upload to this folder.'], 403);
            }
        }

        // Determine extension from original name
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

        // Do not force Host header from presigned request
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

        // Verify object exists in Spaces
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

        // Optional: generate thumbnail for images (only if reasonably small)
        try {
            if ($mimeType && str_starts_with($mimeType, 'image/') && $size <= (25 * 1024 * 1024)) {
                $thumb = $this->generateThumbnailFromStorage($validated['path'], $fileDir, $assetDisk);
                if ($thumb) {
                    $fileModel->update(['thumbnail_path' => $thumb]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Thumbnail generation skipped/failed for direct upload', [
                'file_id' => $fileModel->id,
                'error' => $e->getMessage(),
            ]);
        }

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

        // Check upload permission if uploading to a folder
        if (!empty($validated['folder_id'])) {
            $folder = File::where('id', $validated['folder_id'])
                ->where('type', 'folder')
                ->firstOrFail();

            if (!$folder->canUserUpload($userId)) {
                return response()->json(['message' => 'You do not have permission to upload to this folder.'], 403);
            }
        }

        // Determine extension from original name
        $extension = pathinfo($validated['original_name'], PATHINFO_EXTENSION);
        $extension = $extension ? strtolower($extension) : 'bin';

        $filename = Str::random(40) . '.' . $extension;
        [$fileDir] = $this->buildSpacesDir($validated['folder_id'] ?? null);
        $key = trim($fileDir . '/' . $filename, '/');

        [$client, $bucket] = $this->getSpacesClientAndBucket();

        $contentType = $validated['mime_type'] ?: 'application/octet-stream';

        // Create multipart upload
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

        // Prepare parts array for CompleteMultipartUpload
        $parts = [];
        foreach ($validated['parts'] as $part) {
            $parts[] = [
                'PartNumber' => $part['part_number'],
                'ETag' => $part['etag'],
            ];
        }

        // Sort parts by part number
        usort($parts, function($a, $b) {
            return $a['PartNumber'] <=> $b['PartNumber'];
        });

        // Complete multipart upload
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
            Log::error('Failed to complete multipart upload', [
                'upload_id' => $validated['upload_id'],
                'path' => $validated['path'],
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Failed to complete upload: ' . $e->getMessage()], 500);
        }

        // Verify object exists
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

        // Create file record
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

        // Generate thumbnail for images (only if reasonably small)
        try {
            if ($mimeType && str_starts_with($mimeType, 'image/') && $size <= (25 * 1024 * 1024)) {
                $thumb = $this->generateThumbnailFromStorage($validated['path'], $fileDir, $assetDisk);
                if ($thumb) {
                    $fileModel->update(['thumbnail_path' => $thumb]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Thumbnail generation skipped/failed for chunked upload', [
                'file_id' => $fileModel->id,
                'error' => $e->getMessage(),
            ]);
        }

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
            Log::warning('Failed to abort multipart upload', [
                'upload_id' => $validated['upload_id'],
                'path' => $validated['path'],
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Create a new folder.
     */
    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'folder_id' => 'nullable|exists:files,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $userId = auth()->id();

        // Check upload permission if creating folder inside another folder
        if ($request->folder_id) {
            $parentFolder = File::where('id', $request->folder_id)
                ->where('type', 'folder')
                ->firstOrFail();

            if (!$parentFolder->canUserUpload($userId)) {
                return redirect()->back()->withErrors(['error' => 'You do not have permission to create folders here.']);
            }
        }

        // Check if folder name already exists in the same parent folder
        $existingFolder = File::where('folder_id', $request->folder_id)
            ->where('type', 'folder')
            ->where('name', $request->name)
            ->first();

        if ($existingFolder) {
            return redirect()->back()->withErrors(['name' => 'A folder with this name already exists in this location.']);
        }

        File::create([
            'name' => $request->name,
            'original_name' => null,
            'path' => '', // Folders don't have storage paths
            'type' => 'folder',
            'mime_type' => null,
            'size' => null,
            'folder_id' => $request->folder_id,
            'uploaded_by' => auth()->id(),
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Folder created successfully.');
    }

    /**
     * Download a file.
     */
    public function download(File $file)
    {
        if ($file->isFolder()) {
            abort(404, 'Cannot download a folder.');
        }

        try {
            $assetDisk = 'digitalocean';
            if (Storage::disk($assetDisk)->exists($file->path)) {
                return Storage::disk($assetDisk)->download($file->path, $file->original_name ?: $file->name);
            }
        } catch (\Exception $e) {
            // Fallback to public disk
            if (Storage::disk('public')->exists($file->path)) {
                return Storage::disk('public')->download($file->path, $file->original_name ?: $file->name);
            }
        }

        abort(404, 'File not found.');
    }

    /**
     * Update file/folder name or description.
     */
    public function update(Request $request, File $file)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Check if name already exists in the same parent folder
        $existing = File::where('folder_id', $file->folder_id)
            ->where('type', $file->type)
            ->where('name', $request->name)
            ->where('id', '!=', $file->id)
            ->first();

        if ($existing) {
            return redirect()->back()->withErrors(['name' => 'A ' . ($file->isFolder() ? 'folder' : 'file') . ' with this name already exists in this location.']);
        }

        $file->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', ucfirst($file->type) . ' updated successfully.');
    }

    /**
     * Remove the specified file or folder.
     */
    public function destroy(File $file)
    {
        if ($file->isFolder()) {
            // Check if folder has children
            if ($file->children()->count() > 0) {
                return redirect()->back()->withErrors(['error' => 'Cannot delete folder. Please delete all files and folders inside first.']);
            }
        } else {
            // Delete physical file
            try {
                $assetDisk = 'digitalocean';
                if (Storage::disk($assetDisk)->exists($file->path)) {
                    Storage::disk($assetDisk)->delete($file->path);
                }
            } catch (\Exception $e) {
                // Try public disk as fallback
                try {
                    if (Storage::disk('public')->exists($file->path)) {
                        Storage::disk('public')->delete($file->path);
                    }
                } catch (\Exception $e2) {
                    // Ignore deletion errors
                }
            }
        }

        $file->delete();

        return redirect()->back()->with('success', ucfirst($file->type) . ' deleted successfully.');
    }

    /**
     * Get folder path for storage organization in DigitalOcean Spaces.
     * Builds the full path from root to the specified folder.
     */
    private function getFolderPath(File $folder): string
    {
        $path = [];
        $current = $folder;

        // Traverse up the folder hierarchy to build the full path
        while ($current && $current->isFolder()) {
            // Use folder ID and slugged name for unique, safe folder names
            $folderName = $current->id . '_' . Str::slug($current->name);
            array_unshift($path, $folderName);
            $current = $current->folder;
        }

        return implode('/', $path);
    }

    /**
     * Generate thumbnail for image files.
     */
    private function generateThumbnail($file, $fileDir, $disk)
    {
        // Check if GD or Imagick is available
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            return null;
        }

        try {
            // Create thumbnail directory
            $thumbnailDir = $fileDir . '/thumbnails';

            // Read image content
            $imageContent = file_get_contents($file->getRealPath());
            $image = imagecreatefromstring($imageContent);

            if (!$image) {
                return null;
            }

            // Get original dimensions
            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);

            // Calculate thumbnail dimensions (max 300x300, maintain aspect ratio)
            $maxWidth = 300;
            $maxHeight = 300;

            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $thumbWidth = (int)($originalWidth * $ratio);
            $thumbHeight = (int)($originalHeight * $ratio);

            // Create thumbnail
            $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);

            // Preserve transparency for PNG/GIF
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $thumbWidth, $thumbHeight, $transparent);

            // Resize image
            imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $originalWidth, $originalHeight);

            // Generate thumbnail filename
            $thumbnailFilename = 'thumb_' . Str::random(40) . '.jpg';
            $thumbnailPath = $thumbnailDir . '/' . $thumbnailFilename;

            // Save thumbnail to temporary file first
            $tempPath = sys_get_temp_dir() . '/' . $thumbnailFilename;
            imagejpeg($thumbnail, $tempPath, 85);

            // Upload thumbnail to storage
            Storage::disk($disk)->put($thumbnailPath, file_get_contents($tempPath));

            // Clean up
            imagedestroy($image);
            imagedestroy($thumbnail);
            @unlink($tempPath);

            return $thumbnailPath;
        } catch (\Exception $e) {
            \Log::error('Thumbnail generation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate thumbnail by reading the image from Spaces.
     * Only call this for reasonably small images (to avoid huge memory usage).
     */
    private function generateThumbnailFromStorage(string $path, string $fileDir, string $disk): ?string
    {
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            return null;
        }

        try {
            $thumbnailDir = trim($fileDir, '/') . '/thumbnails';

            $imageContent = Storage::disk($disk)->get($path);
            $image = imagecreatefromstring($imageContent);
            if (!$image) {
                return null;
            }

            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);

            $maxWidth = 300;
            $maxHeight = 300;
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $thumbWidth = (int)($originalWidth * $ratio);
            $thumbHeight = (int)($originalHeight * $ratio);

            $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);

            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $thumbWidth, $thumbHeight, $transparent);

            imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $originalWidth, $originalHeight);

            $thumbnailFilename = 'thumb_' . Str::random(40) . '.jpg';
            $thumbnailPath = $thumbnailDir . '/' . $thumbnailFilename;

            $tempPath = sys_get_temp_dir() . '/' . $thumbnailFilename;
            imagejpeg($thumbnail, $tempPath, 85);

            Storage::disk($disk)->put($thumbnailPath, file_get_contents($tempPath));

            imagedestroy($image);
            imagedestroy($thumbnail);
            @unlink($tempPath);

            return $thumbnailPath;
        } catch (\Throwable $e) {
            Log::error('Thumbnail generation error (storage): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Share a file or folder with users.
     */
    public function share(Request $request, File $file)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'can_view' => 'boolean',
            'can_upload' => 'boolean',
        ]);

        // Only owner can share
        if ($file->uploaded_by != auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'Only the owner can share this ' . $file->type . '.']);
        }

        // Can't share with yourself
        if ($request->user_id == auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot share with yourself.']);
        }

        // For files, can_upload should be false
        $canUpload = $file->isFolder() ? ($request->can_upload ?? false) : false;

        // Update or create permission
        // Note: keep it SQLite-safe (NOW() is not supported there).
        DB::table('file_user_permissions')->updateOrInsert(
            [
                'file_id' => $file->id,
                'user_id' => $request->user_id,
            ],
            [
                'can_view' => (bool) ($request->can_view ?? true),
                'can_upload' => (bool) $canUpload,
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

        // Only owner can unshare
        if ($file->uploaded_by != auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'Only the owner can remove sharing permissions.']);
        }

        DB::table('file_user_permissions')
            ->where('file_id', $file->id)
            ->where('user_id', $request->user_id)
            ->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Sharing permission removed successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Sharing permission removed successfully.');
    }

    /**
     * Get users with permissions for a file/folder.
     */
    public function getSharedUsers(File $file)
    {
        // Only owner can view shared users
        if ($file->uploaded_by != auth()->id()) {
            abort(403, 'Only the owner can view shared users.');
        }

        $sharedUsers = $file->sharedWith()->get();

        return response()->json($sharedUsers);
    }

    /**
     * Get file URL for viewing.
     */
    public function view(File $file)
    {
        if (!$file->canUserView(auth()->id()) && $file->uploaded_by != auth()->id()) {
            abort(403, 'You do not have permission to view this file.');
        }

        if ($file->isFolder()) {
            return redirect('/admin/files?folder_id=' . $file->id);
        }

        try {
            $assetDisk = 'digitalocean';
            if (Storage::disk($assetDisk)->exists($file->path)) {
                // Prefer signed URL (works even if bucket is private)
                if (method_exists(Storage::disk($assetDisk), 'temporaryUrl')) {
                    $url = Storage::disk($assetDisk)->temporaryUrl($file->path, now()->addMinutes(60));
                } else {
                    $url = Storage::disk($assetDisk)->url($file->path);
                }
                return redirect($url);
            }
        } catch (\Exception $e) {
            // Fallback to public disk
            if (Storage::disk('public')->exists($file->path)) {
                return Storage::disk('public')->response($file->path, $file->original_name);
            }
        }

        abort(404, 'File not found.');
    }
}
