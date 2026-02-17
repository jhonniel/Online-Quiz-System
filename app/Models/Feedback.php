<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'images',
        'priority',
        'status',
        'admin_response',
        'assigned_to',
        'admin_responded_at',
    ];

    protected $casts = [
        'admin_responded_at' => 'datetime',
        'images' => 'array',
    ];

    /**
     * Get the user who submitted the feedback
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin assigned to handle this feedback
     */
    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the type badge class for display
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match($this->type) {
            'bug_report' => 'bg-red-100 text-red-800',
            'feature_request' => 'bg-blue-100 text-blue-800',
            'improvement' => 'bg-green-100 text-green-800',
            'general' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the priority badge class for display
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match($this->priority) {
            'low' => 'bg-gray-100 text-gray-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'high' => 'bg-orange-100 text-orange-800',
            'critical' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the status badge class for display
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'in_review' => 'bg-blue-100 text-blue-800',
            'in_progress' => 'bg-purple-100 text-purple-800',
            'completed' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the type icon for display
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'bug_report' => '🐛',
            'feature_request' => '💡',
            'improvement' => '⚡',
            'general' => '💬',
            default => '💬',
        };
    }

    /**
     * Get the priority icon for display
     */
    public function getPriorityIconAttribute(): string
    {
        return match($this->priority) {
            'low' => '🟢',
            'medium' => '🟡',
            'high' => '🟠',
            'critical' => '🔴',
            default => '🟡',
        };
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for filtering by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Get all image URLs
     */
    public function getImageUrlsAttribute(): array
    {
        if ($this->images && is_array($this->images)) {
            return array_map(function($image) {
                return asset('storage/' . $image);
            }, $this->images);
        }
        return [];
    }

    /**
     * Get the first image URL (for backward compatibility)
     */
    public function getImageUrlAttribute(): ?string
    {
        $urls = $this->image_urls;
        return !empty($urls) ? $urls[0] : null;
    }

    /**
     * Check if feedback has images
     */
    public function hasImage(): bool
    {
        return !empty($this->images) && is_array($this->images) && count($this->images) > 0;
    }

    /**
     * Get the number of images
     */
    public function getImageCountAttribute(): int
    {
        return $this->hasImage() ? count($this->images) : 0;
    }

    /**
     * Get image paths for storage
     */
    public function getImagePathsAttribute(): array
    {
        if ($this->images && is_array($this->images)) {
            return array_map(function($image) {
                return storage_path('app/public/' . $image);
            }, $this->images);
        }
        return [];
    }
}
