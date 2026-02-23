<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Omada extends Model
{
    protected $fillable = [
        'account_linked_email',
        'site',
        'office',
        'type',
        'serial_number',
        'mac_address',
        'license',
        'license_expiration',
        'subscription_plan_type_id',
    ];

    protected $casts = [
        'license_expiration' => 'date',
    ];

    /**
     * Status is derived from license_expiration: Active if today <= expiration, Expired otherwise.
     */
    public function getStatusAttribute(): string
    {
        if (! $this->license_expiration) {
            return '—';
        }
        return $this->license_expiration->isPast()
            ? 'Expired'
            : 'Active';
    }

    public function isActive(): bool
    {
        if (! $this->license_expiration) {
            return false;
        }
        return ! $this->license_expiration->isPast();
    }

    public function subscriptionPlanType(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlanType::class);
    }

    /**
     * Compatibility: some Laravel versions call hasAnyGetMutator(); delegate to hasGetMutator when a key is given.
     */
    public function hasAnyGetMutator($key = null): bool
    {
        if ($key !== null && $key !== '') {
            return $this->hasGetMutator($key);
        }
        return false;
    }
}
