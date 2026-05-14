<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChatTicket extends Model
{
    protected $fillable = [
        'ticket_number',
        'user_id',
        'subject',
        'status',
        'priority',
        'description',
        'closed_at',
        'closed_by',
        'close_reason',
        'reopened_at',
        'reopened_by',
        'reopen_reason',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = static::generateTicketNumber();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'ticket_number', 'ticket_number');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeReopened($query)
    {
        return $query->where('status', 'reopened');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isReopened(): bool
    {
        return $this->status === 'reopened';
    }

    public function isReopenRequested(): bool
    {
        return $this->status === 'reopen_requested';
    }

    public function close(User $admin, ?string $reason = null): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $admin->id,
            'close_reason' => $reason,
        ]);

        // Close all messages in this ticket
        $this->messages()->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $admin->id,
        ]);
    }

    public function requestReopen(User $user, string $reason): void
    {
        $this->update([
            'status' => 'reopen_requested',
            'reopen_requested_at' => now(),
            'reopen_request_reason' => $reason,
        ]);
    }

    public function approveReopen(User $admin, ?string $reason = null): void
    {
        $this->update([
            'status' => 'reopened',
            'reopened_at' => now(),
            'reopened_by' => $admin->id,
            'reopen_reason' => $reason,
            'reopen_requested_at' => null,
            'reopen_request_reason' => null,
        ]);

        // Reopen all messages in this ticket
        $this->messages()->update([
            'status' => 'open',
            'closed_at' => null,
            'closed_by' => null,
        ]);
    }

    public function reopen(User $user, ?string $reason = null): void
    {
        $this->update([
            'status' => 'reopened',
            'reopened_at' => now(),
            'reopened_by' => $user->id,
            'reopen_reason' => $reason,
        ]);

        // Reopen all messages in this ticket
        $this->messages()->update([
            'status' => 'open',
            'closed_at' => null,
            'closed_by' => null,
        ]);
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'open' => 'bg-green-100 text-green-800',
            'closed' => 'bg-gray-100 text-gray-800',
            'reopened' => 'bg-yellow-100 text-yellow-800',
            'reopen_requested' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPriorityBadgeClass(): string
    {
        return match($this->priority) {
            'low' => 'bg-blue-100 text-blue-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'high' => 'bg-orange-100 text-orange-800',
            'urgent' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public static function generateTicketNumber(): string
    {
        do {
            $ticketNumber = 'TKT-' . strtoupper(Str::random(8));
        } while (static::where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }

    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    public function getUnreadMessagesCountAttribute()
    {
        return $this->messages()
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->count();
    }
}
