<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupChatMessage extends Model
{
    protected $fillable = [
        'group_chat_id',
        'sender_id',
        'message',
    ];

    public function groupChat(): BelongsTo
    {
        return $this->belongsTo(GroupChat::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
