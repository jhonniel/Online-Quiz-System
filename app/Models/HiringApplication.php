<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HiringApplication extends Model
{
    protected $fillable = [
        'hiring_position_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'birth_date',
        'address',
        'position_applied',
        'cover_letter',
        'resume_path',
        'resume_link',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'acceptance_token',
        'token_expires_at',
        'user_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'reviewed_at' => 'datetime',
        'token_expires_at' => 'datetime',
    ];

    // Relationships
    public function hiringPosition()
    {
        return $this->belongsTo(HiringPosition::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function generateAcceptanceToken()
    {
        $this->acceptance_token = Str::random(64);
        $this->token_expires_at = now()->addDays(30); // Token valid for 30 days
        $this->save();
        return $this->acceptance_token;
    }

    public function isTokenValid()
    {
        return $this->acceptance_token && 
               $this->token_expires_at && 
               $this->token_expires_at->isFuture();
    }
}
