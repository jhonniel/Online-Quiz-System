<?php

namespace App\Models;

use App\Services\ConfessionCensorService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfessionComment extends Model
{
    protected $fillable = [
        'confession_post_id',
        'parent_id',
        'content',
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

    public function post(): BelongsTo
    {
        return $this->belongsTo(ConfessionPost::class, 'confession_post_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ConfessionComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ConfessionComment::class, 'parent_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ConfessionCommentVote::class, 'confession_comment_id');
    }

    public function getScoreAttribute(): int
    {
        return $this->upvotes_count - $this->downvotes_count;
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
