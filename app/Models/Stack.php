<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Stack extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'image',
        'color',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Get the image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // Try DigitalOcean Spaces first
        try {
            $assetDisk = 'digitalocean';
            if (Storage::disk($assetDisk)->exists($this->image)) {
                return Storage::disk($assetDisk)->url($this->image);
            }
        } catch (\Exception $e) {
            // Fallback to public disk if DigitalOcean fails
        }

        // Fallback to public disk
        try {
            if (Storage::disk('public')->exists($this->image)) {
                return Storage::disk('public')->url($this->image);
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return null;
    }
}
