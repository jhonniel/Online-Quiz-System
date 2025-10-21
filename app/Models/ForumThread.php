<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ForumThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'title',
        'content',
        'image',
        'is_published',
        'is_pinned',
        'views_count',
        'likes_count',
        'comments_count',
        'saves_count',
        'shares_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    // Relationships
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ForumComment::class, 'thread_id');
    }

    public function topLevelComments(): HasMany
    {
        return $this->hasMany(ForumComment::class, 'thread_id')->whereNull('parent_id');
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(ForumLike::class, 'likeable');
    }

    public function saves(): HasMany
    {
        return $this->hasMany(ForumSave::class, 'thread_id');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ForumShare::class, 'thread_id');
    }

    // Helper methods
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return null;
    }

    public function hasImage(): bool
    {
        return !empty($this->image);
    }

    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function isSavedBy(User $user): bool
    {
        return $this->saves()->where('user_id', $user->id)->exists();
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function updateCounts(): void
    {
        $this->update([
            'likes_count' => $this->likes()->count(),
            'comments_count' => $this->comments()->count(),
            'saves_count' => $this->saves()->count(),
            'shares_count' => $this->shares()->count(),
        ]);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('is_pinned', 'desc')->orderBy('created_at', 'desc');
    }
}
