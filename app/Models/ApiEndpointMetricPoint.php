<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiEndpointMetricPoint extends Model
{
    protected $fillable = [
        'route_key',
        'is_success',
        'status_code',
        'response_time_ms',
        'recorded_at',
    ];

    protected $casts = [
        'is_success' => 'boolean',
        'recorded_at' => 'datetime',
    ];
}
