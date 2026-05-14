<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'ticket_number',
        'user_id',
        'admin_id',
        'message',
        'sender_type',
        'status',
        'is_read',
        'read_at',
        'closed_at',
        'closed_by',
        'close_reason',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(ChatTicket::class, 'ticket_number', 'ticket_number');
    }

    public function scopeFromUser($query)
    {
        return $query->where('sender_type', 'user');
    }

    public function scopeFromAdmin($query)
    {
        return $query->where('sender_type', 'admin');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeByTicket($query, $ticketNumber)
    {
        return $query->where('ticket_number', $ticketNumber);
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function isFromUser(): bool
    {
        return $this->sender_type === 'user';
    }

    public function isFromAdmin(): bool
    {
        return $this->sender_type === 'admin';
    }

    public function isUnread(): bool
    {
        return !$this->is_read;
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
