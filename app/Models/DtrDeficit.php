<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtrDeficit extends Model
{
    protected $fillable = [
        'user_id',
        'week_start_date',
        'week_end_date',
        'deficit_hours',
        'is_applied',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'deficit_hours' => 'decimal:2',
        'is_applied' => 'boolean',
    ];

    /**
     * Get the user that owns the deficit record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
