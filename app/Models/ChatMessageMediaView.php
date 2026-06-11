<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessageMediaView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'chat_message_media_id',
        'user_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(ChatMessageMedia::class, 'chat_message_media_id');
    }
}
