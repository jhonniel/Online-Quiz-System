<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Stack extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'image',
        'color',
        'is_active',
        'order',
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
        return $query->orderBy('order');
    }

    /**
     * Get the image URL using the image proxy
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        // If it's already a full URL (external), return it as-is
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        // Use proxy route to avoid CORS issues
        // Encode the path to handle special characters
        $encodedPath = base64_encode($this->image);
        // Base64 strings contain +, /, and = which need special handling in URLs
        $urlEncodedPath = str_replace(['+', '/', '='], ['%2B', '%2F', '%3D'], $encodedPath);
        // Construct URL manually to avoid route helper encoding issues
        return url('/image-proxy/' . $urlEncodedPath);
    }
}
