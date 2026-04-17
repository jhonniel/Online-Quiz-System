<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dtr extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'time_in',
        'time_out',
        'break_start',
        'break_end',
        'total_hours',
        'overtime_hours',
        'activity_percentage',
        'remarks',
        'added_time_from_note',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'activity_percentage' => 'decimal:2',
    ];

    /**
     * Get the user that owns the DTR record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'present' => 'bg-green-100 text-green-800',
            'absent' => 'bg-red-100 text-red-800',
            'late' => 'bg-yellow-100 text-yellow-800',
            'half_day' => 'bg-orange-100 text-orange-800',
            'on_leave' => 'bg-blue-100 text-blue-800',
            'travel' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
