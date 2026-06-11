<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnonymousChatParticipant extends Model
{
    protected $fillable = [
        'anonymous_chat_room_id',
        'user_id',
        'display_alias',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(AnonymousChatRoom::class, 'anonymous_chat_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
