<?php

namespace App\Models;

use App\Services\ConfessionCensorService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SayItChatMessage extends Model
{
    use SoftDeletes;

    protected $table = 'sayit_chat_messages';

    protected $fillable = [
        'sayit_chat_room_id',
        'codename',
        'body',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(SayItChatRoom::class, 'sayit_chat_room_id');
    }

    public function getDisplayBodyAttribute(): string
    {
        return ConfessionCensorService::censor($this->body);
    }

    /**
     * @return array{id: int, codename: string, body: string, is_own: bool, created_at: string, created_at_human: string}
     */
    public function toClientPayload(string $sessionCodename): array
    {
        return [
            'id' => $this->id,
            'codename' => $this->codename,
            'body' => $this->display_body,
            'is_own' => $this->codename === $sessionCodename,
            'created_at' => $this->created_at?->toIso8601String() ?? '',
            'created_at_human' => $this->created_at?->diffForHumans() ?? '',
        ];
    }
}
