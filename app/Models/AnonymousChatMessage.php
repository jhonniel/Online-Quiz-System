<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnonymousChatMessage extends Model
{
    protected $fillable = [
        'anonymous_chat_room_id',
        'sender_id',
        'message',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(AnonymousChatRoom::class, 'anonymous_chat_room_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
