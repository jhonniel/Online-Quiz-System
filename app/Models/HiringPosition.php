<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HiringPosition extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'requirements',
        'responsibilities',
        'department',
        'location',
        'employment_type',
        'salary_min',
        'salary_max',
        'is_active',
        'application_deadline',
        'application_count',
        'created_by',
        'thumbnail_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'application_deadline' => 'date',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
    ];

    // Relationships
    public function applications()
    {
        return $this->hasMany(HiringApplication::class, 'hiring_position_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper methods
    public function generateSlug()
    {
        $baseSlug = Str::slug($this->title);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function getUrlAttribute()
    {
        $baseUrl = \App\Models\Setting::get('hiring_application_url', 'hiring/apply');
        return url('/' . ltrim($baseUrl, '/') . '/' . $this->slug);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $path = $this->thumbnail_path;
        if (!$path) {
            return null;
        }

        // Try digitalocean disk first
        try {
            return \Storage::disk('digitalocean')->url($path);
        } catch (\Throwable $e) {
            // ignore
        }

        // Fallback to public
        try {
            if (\Storage::disk('public')->exists($path)) {
                return \Storage::disk('public')->url($path);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            return \Storage::url($path);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function isAcceptingApplications()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->application_deadline && $this->application_deadline->isPast()) {
            return false;
        }

        return true;
    }
}
