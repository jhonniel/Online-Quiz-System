<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'job_description',
        'supervisor_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all users belonging to this department
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope to get only active departments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * @return list<string>
     */
    public function jobDescriptionBullets(): array
    {
        if (! $this->job_description) {
            return [];
        }

        $lines = preg_split('/\R/u', (string) $this->job_description) ?: [];

        $bullets = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $line = preg_replace('/^[\-*•]\s*/u', '', $line) ?? $line;
            $line = trim($line);

            if ($line !== '') {
                $bullets[] = $line;
            }
        }

        return $bullets;
    }

    public function hasJobDescription(): bool
    {
        return count($this->jobDescriptionBullets()) > 0;
    }
}

