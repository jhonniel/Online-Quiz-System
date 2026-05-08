<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiEndpointMetric extends Model
{
    protected $fillable = [
        'route_key',
        'method',
        'uri',
        'route_name',
        'request_count',
        'success_count',
        'failure_count',
        'last_status_code',
        'avg_response_time_ms',
        'last_response_at',
        'last_failure_at',
    ];
}
