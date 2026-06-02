<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetworkGraphEdge extends Model
{
    protected $fillable = [
        'source_node_key',
        'target_node_key',
        'edge_type',
        'weight',
        'last_seen_at',
    ];

    protected $casts = [
        'weight' => 'integer',
        'last_seen_at' => 'datetime',
    ];
}
