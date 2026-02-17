<?php

namespace App\Models;

use App\Services\ConfessionCensorService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class ConfessionPost extends Model
{
    protected $fillable = [
        'confession_topic_id',
        'content',
        'text_size',
        'image_path',
        'codename',
        'ip_address',
        'user_agent',
        'upvotes_count',
        'downvotes_count',
    ];

    /** Tailwind text size class for post content (normal, medium, large). */
    public function getTextSizeClassAttribute(): string
    {
        return match ($this->text_size ?? 'normal') {
            'large' => 'text-lg sm:text-xl',
            'medium' => 'text-base sm:text-lg',
            default => 'text-[15px] sm:text-base',
        };
    }

    protected $casts = [
        'upvotes_count' => 'integer',
        'downvotes_count' => 'integer',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ConfessionTopic::class, 'confession_topic_id');
    }

    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(ConfessionHashtag::class, 'confession_post_hashtag', 'confession_post_id', 'confession_hashtag_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ConfessionComment::class)->whereNull('parent_id');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(ConfessionComment::class);
    }

    /** Latest comment by created_at (for preview on cards). */
    public function latestComment(): HasOne
    {
        return $this->hasOne(ConfessionComment::class)->latestOfMany('created_at');
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
