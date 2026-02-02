<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'read_at',
        'read_by',
        'admin_reply',
        'replied_at',
        'replied_by',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    // Relationships
    public function readBy()
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    public function repliedBy()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    public function scopeReplied($query)
    {
        return $query->where('status', 'replied');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    // Helper methods
    public function isNew()
    {
        return $this->status === 'new';
    }

    public function isRead()
    {
        return $this->status === 'read';
    }

    public function isReplied()
    {
        return $this->status === 'replied';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function markAsRead($user)
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
            'read_by' => $user->id,
        ]);
    }

    public function reply($adminReply, $user)
    {
        $this->update([
            'status' => 'replied',
            'admin_reply' => $adminReply,
            'replied_at' => now(),
            'replied_by' => $user->id,
        ]);
    }

    public function close()
    {
        $this->update([
            'status' => 'closed',
        ]);
    }
}
