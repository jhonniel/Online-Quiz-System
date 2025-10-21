<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Static methods for creating notifications
    public static function createFriendRequestNotification($userId, $senderId, $senderName): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'friend_request',
            'title' => 'New Friend Request',
            'message' => "{$senderName} sent you a friend request",
            'data' => [
                'sender_id' => $senderId,
                'sender_name' => $senderName,
            ],
        ]);
    }

    public static function createMessageNotification($userId, $senderId, $senderName, $messagePreview = null): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'message',
            'title' => 'New Message',
            'message' => $messagePreview ? "{$senderName}: {$messagePreview}" : "You have a new message from {$senderName}",
            'data' => [
                'sender_id' => $senderId,
                'sender_name' => $senderName,
            ],
        ]);
    }

    public static function createForumMentionNotification($userId, $senderId, $senderName, $threadId, $threadTitle, $commentContent): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'forum_mention',
            'title' => 'You were mentioned in a forum post',
            'message' => "{$senderName} mentioned you in a comment on '{$threadTitle}'",
            'data' => [
                'sender_id' => $senderId,
                'sender_name' => $senderName,
                'thread_id' => $threadId,
                'thread_title' => $threadTitle,
                'comment_content' => $commentContent,
            ],
        ]);
    }

    public static function createForumReplyNotification($userId, $senderId, $senderName, $threadId, $threadTitle, $commentContent): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'forum_reply',
            'title' => 'New reply to your comment',
            'message' => "{$senderName} replied to your comment on '{$threadTitle}'",
            'data' => [
                'sender_id' => $senderId,
                'sender_name' => $senderName,
                'thread_id' => $threadId,
                'thread_title' => $threadTitle,
                'comment_content' => $commentContent,
            ],
        ]);
    }

    public static function createAdminNotification($userId, $title, $message, $type = 'admin_notification'): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => [
                'admin_sent' => true,
            ],
        ]);
    }

    public static function createSystemUpdateNotification($userId, $title, $message): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'system_update',
            'title' => $title,
            'message' => $message,
            'data' => [
                'system_generated' => true,
            ],
        ]);
    }

    public static function createBugAlertNotification($userId, $title, $message): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => 'bug_alert',
            'title' => $title,
            'message' => $message,
            'data' => [
                'system_generated' => true,
                'priority' => 'high',
            ],
        ]);
    }
}
