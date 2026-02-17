<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfessionCommentVote extends Model
{
    protected $table = 'confession_comment_votes';

    protected $fillable = ['confession_comment_id', 'ip_address', 'vote'];

    protected $casts = [
        'vote' => 'integer',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(ConfessionComment::class, 'confession_comment_id');
    }
}
