<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAnnouncementAcknowledgment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'system_announcement_id',
        'acknowledged_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(SystemAnnouncement::class, 'system_announcement_id');
    }
}
