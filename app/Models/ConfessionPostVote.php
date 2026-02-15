<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfessionPostVote extends Model
{
    protected $fillable = ['confession_post_id', 'ip_address', 'vote'];

    protected $casts = [
        'vote' => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(ConfessionPost::class, 'confession_post_id');
    }
}
