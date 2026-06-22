<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpGeolocation extends Model
{
    protected $fillable = [
        'ip_address',
        'latitude',
        'longitude',
        'location_label',
        'source',
        'resolved_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'resolved_at' => 'datetime',
    ];
}
