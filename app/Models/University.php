<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope a query to only include active universities.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the full name attribute (accessor)
     * Returns the name of the university, optionally with code
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        if ($this->code) {
            return $this->name . ' (' . $this->code . ')';
        }
        return $this->name;
    }
}
