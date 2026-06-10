<?php

namespace App\Models;

use App\Support\SystemAnnouncementFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemAnnouncement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'is_published',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acknowledgments(): HasMany
    {
        return $this->hasMany(EmployeeAnnouncementAcknowledgment::class);
    }

    public function scopePublished($query)
    {
        return $query
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function acknowledgedEmployeesCount(): int
    {
        return (int) $this->acknowledgments()->count();
    }

    public function displayContentHtml(): string
    {
        return SystemAnnouncementFormatter::toDisplayHtml((string) $this->content);
    }
}
