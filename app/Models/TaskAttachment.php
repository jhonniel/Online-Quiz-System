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
        'uploaded_by',
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
        // Uploader of the file (uses uploaded_by column)
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Helper methods
    public function getFileUrlAttribute()
    {
        if (!$this->file_path) {
            return null;
        }

        // Check if DigitalOcean Spaces is configured
        $useDigitalOcean = !empty(env('DIGITALOCEAN_SPACES_KEY')) 
            && !empty(env('DIGITALOCEAN_SPACES_SECRET')) 
            && !empty(env('DIGITALOCEAN_SPACES_BUCKET'))
            && !empty(env('DIGITALOCEAN_SPACES_ENDPOINT'));

        // Try DigitalOcean first if configured
        if ($useDigitalOcean) {
            try {
                if (Storage::disk('digitalocean')->exists($this->file_path)) {
                    return Storage::disk('digitalocean')->url($this->file_path);
                }
            } catch (\Exception $e) {
                // Fallback to public disk
            }
        }

        // Fallback to public disk
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
