<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingStatement extends Model
{
    protected $fillable = [
        'starlink_ids',
        'type',
        'marked_by',
        'advance_payment_until',
    ];

    protected $casts = [
        'starlink_ids' => 'array',
        'advance_payment_until' => 'date',
    ];

    public function markedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function getStarlinksAttribute()
    {
        $ids = $this->starlink_ids ?? [];
        if (empty($ids)) {
            return collect();
        }
        return Starlink::with(['linkedAccount', 'subscriptionPlanType'])
            ->whereIn('id', $ids)
            ->orderBy('account_linked_email')
            ->get();
    }
}
