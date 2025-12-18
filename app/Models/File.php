<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class File extends Model
{
    protected $fillable = [
        'name',
        'original_name',
        'path',
        'thumbnail_path',
        'type',
        'mime_type',
        'size',
        'folder_id',
        'uploaded_by',
        'description',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Get the user who uploaded this file.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the parent folder.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(File::class, 'folder_id');
    }

    /**
     * Get all files/folders in this folder.
     */
    public function children(): HasMany
    {
        return $this->hasMany(File::class, 'folder_id');
    }

    /**
     * Get users who have permissions to this file/folder.
     */
    public function sharedWith(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'file_user_permissions', 'file_id', 'user_id')
            ->withPivot(['can_view', 'can_upload', 'granted_by'])
            ->withTimestamps();
    }

    /**
     * Get permissions for a specific user.
     */
    public function getUserPermission($userId)
    {
        return $this->sharedWith()->where('user_id', $userId)->first();
    }

    /**
     * Check if user can view this file/folder.
     */
    public function canUserView($userId): bool
    {
        // Owner can always view
        if ((int) $this->uploaded_by === (int) $userId) {
            return true;
        }

        // Inherit permissions from parent folders:
        // If a folder is shared, all descendants should be viewable.
        $current = $this;
        while ($current) {
            $permission = DB::table('file_user_permissions')
                ->where('file_id', $current->id)
                ->where('user_id', $userId)
                ->first();

            if ($permission && (bool) $permission->can_view) {
                return true;
            }

            $current = $current->folder; // walk up the tree
        }

        return false;
    }

    /**
     * Check if user can upload to this folder.
     */
    public function canUserUpload($userId): bool
    {
        // Only folders can have upload permissions
        if (!$this->isFolder()) {
            return false;
        }

        // Owner can always upload
        if ((int) $this->uploaded_by === (int) $userId) {
            return true;
        }

        // Inherit upload permissions from parent folders too.
        $current = $this;
        while ($current) {
            $permission = DB::table('file_user_permissions')
                ->where('file_id', $current->id)
                ->where('user_id', $userId)
                ->first();

            if ($permission && (bool) $permission->can_upload) {
                return true;
            }

            $current = $current->folder; // walk up the tree
        }

        return false;
    }

    /**
     * Check if this is a folder.
     */
    public function isFolder(): bool
    {
        return $this->type === 'folder';
    }

    /**
     * Check if this is a file.
     */
    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->size) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
