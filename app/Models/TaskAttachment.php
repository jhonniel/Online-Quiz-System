<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class TaskAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];

    // Relationships
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function getFileUrlAttribute()
    {
        if (!$this->file_path) {
            return null;
        }

        $assetDisk = 'digitalocean';
        try {
            if (Storage::disk($assetDisk)->exists($this->file_path)) {
                return Storage::disk($assetDisk)->url($this->file_path);
            }
        } catch (\Exception $e) {
            // Fallback
        }

        try {
            if (Storage::disk('public')->exists($this->file_path)) {
                return Storage::disk('public')->url($this->file_path);
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return null;
    }

    public function isImage()
    {
        return in_array(strtolower($this->file_type ?? ''), ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
    }
}
