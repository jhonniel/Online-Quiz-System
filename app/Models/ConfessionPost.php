<?php

namespace App\Models;

use App\Services\ConfessionCensorService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ConfessionPost extends Model
{
    protected $fillable = [
        'content',
        'image_path',
        'codename',
        'ip_address',
        'user_agent',
        'upvotes_count',
        'downvotes_count',
    ];

    protected $casts = [
        'upvotes_count' => 'integer',
        'downvotes_count' => 'integer',
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(ConfessionComment::class)->whereNull('parent_id')->orderBy('created_at');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(ConfessionComment::class)->orderBy('created_at');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ConfessionPostVote::class);
    }

    public function getScoreAttribute(): int
    {
        return $this->upvotes_count - $this->downvotes_count;
    }

    public function getTotalEngagementAttribute(): int
    {
        return $this->allComments()->count() + $this->upvotes_count + $this->downvotes_count;
    }

    /**
     * Resolve image URL from Spaces (digitalocean) or fallback to public disk for legacy uploads.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }
        if (Storage::disk('digitalocean')->exists($this->image_path)) {
            return Storage::disk('digitalocean')->url($this->image_path);
        }
        if (Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }
        return null;
    }

    /**
     * Content with banned words replaced by asterisks.
     */
    public function getCensoredContentAttribute(): string
    {
        return ConfessionCensorService::censor($this->content);
    }
}
