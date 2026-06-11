<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessageMedia extends Model
{
    public const TYPE_DIRECT = 'direct';

    public const TYPE_GROUP = 'group';

    public const TYPE_ANONYMOUS = 'anonymous';

    public const MODE_VIEW_ONCE = 'view_once';

    public const MODE_STAY_24H = 'stay_24h';

    protected $table = 'chat_message_media';

    protected $fillable = [
        'chat_type',
        'message_id',
        'uploaded_by',
        'path',
        'disk',
        'mime_type',
        'size_bytes',
        'mode',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'size_bytes' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function views(): HasMany
    {
        return $this->hasMany(ChatMessageMediaView::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function viewedBy(int $userId): bool
    {
        return $this->views()->where('user_id', $userId)->exists();
    }
}
