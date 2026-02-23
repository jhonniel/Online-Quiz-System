<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinkedAccount extends Model
{
    protected $fillable = [
        'email',
        'name',
        'provider',
        'user_id',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function starlinks(): HasMany
    {
        return $this->hasMany(Starlink::class);
    }

    /**
     * Compatibility: some Laravel versions call hasAnyGetMutator(); this model has no get mutators.
     */
    public function hasAnyGetMutator($key = null): bool
    {
        if ($key !== null && $key !== '') {
            return $this->hasGetMutator($key);
        }
        return false;
    }
}
