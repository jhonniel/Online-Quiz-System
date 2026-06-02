<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetworkGraphNode extends Model
{
    protected $fillable = [
        'node_key',
        'label',
        'node_group',
        'color',
        'hit_count',
        'meta',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'hit_count' => 'integer',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];
}
