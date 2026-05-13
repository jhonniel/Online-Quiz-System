<?php

namespace App\Models;

use App\Helpers\SayItHelper;
use App\Services\ConfessionCensorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ConfessionPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'confession_topic_id',
        'content',
        'text_size',
        'card_background',
        'card_background_mesh',
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
        'deleted_at' => 'datetime',
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

    protected static function booted(): void
    {
        static::deleting(function (ConfessionPost $post) {
            if (empty($post->image_path)) {
                return;
            }
            $path = $post->image_path;
            foreach (array_unique([SayItHelper::confessionStorageDisk(), 'digitalocean', 'spaces', 's3', 'public', 'local']) as $disk) {
                if (! is_array(config("filesystems.disks.{$disk}"))) {
                    continue;
                }
                try {
                    if (Storage::disk($disk)->exists($path)) {
                        Storage::disk($disk)->delete($path);
                    }
                } catch (\Throwable $e) {
                    // ignore cleanup errors
                }
            }
        });
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
     * Posts with no likes and no comments (scheduled for auto-delete after 1 week from post day).
     */
    public function scopeScheduledForDeletion(Builder $query): Builder
    {
        return $query->where('upvotes_count', 0)
            ->where('downvotes_count', 0)
            ->whereDoesntHave('allComments');
    }

    /**
     * Posts eligible to be auto-deleted now (no engagement and at least 7 days old).
     */
    public function scopeEligibleForAutoDelete(Builder $query): Builder
    {
        return $query->scheduledForDeletion()
            ->where('created_at', '<=', now()->subDays(7));
    }

    /**
     * Date when this post will be (or was) auto-deleted: 7 days after creation.
     */
    public function getAutoDeleteAtAttribute(): \Carbon\Carbon
    {
        return $this->created_at->copy()->addDays(7);
    }

    /**
     * Resolve image URL from Spaces (digitalocean) or fallback to public disk for legacy uploads.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }
        $primary = SayItHelper::confessionStorageDisk();
        foreach (array_unique([$primary, 'digitalocean', 'spaces', 's3', 'public', 'local']) as $disk) {
            if (! is_array(config("filesystems.disks.{$disk}"))) {
                continue;
            }
            try {
                if (Storage::disk($disk)->exists($this->image_path)) {
                    return Storage::disk($disk)->url($this->image_path);
                }
            } catch (\Throwable $e) {
                continue;
            }
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

    /**
     * Compatibility: some Laravel versions call hasAnyGetMutator(); delegate to hasGetMutator when a key is given.
     */
    public function hasAnyGetMutator($key = null): bool
    {
        if ($key !== null && $key !== '') {
            return $this->hasGetMutator($key);
        }

        return false;
    }
}
