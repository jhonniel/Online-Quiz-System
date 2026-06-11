<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGeoLocation extends Model
{
    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'accuracy',
        'source',
        'context',
        'ip_address',
        'captured_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy' => 'float',
        'captured_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
