<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtrHolidayExclusion extends Model
{
    protected $fillable = [
        'dtr_holiday_id',
        'user_id',
        'excluded_by',
    ];

    public function holiday(): BelongsTo
    {
        return $this->belongsTo(DtrHoliday::class, 'dtr_holiday_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function excludedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'excluded_by');
    }
}
