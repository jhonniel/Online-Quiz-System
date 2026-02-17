<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypingIndicator extends Model
{
    protected $fillable = [
        'ticket_number',
        'user_id',
        'admin_id',
        'typer_type',
        'started_typing_at',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'started_typing_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeForTicket($query, $ticketNumber)
    {
        return $query->where('ticket_number', $ticketNumber);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('typer_type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('last_activity_at', '>', now()->subMinutes(2));
    }

    public function isExpired(): bool
    {
        return $this->last_activity_at < now()->subMinutes(2);
    }

    public function updateActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }
}