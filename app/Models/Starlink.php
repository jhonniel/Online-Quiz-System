<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Starlink extends Model
{
    protected $fillable = [
        'linked_account_id',
        'account_linked_email',
        'starlink_id',
        'serial_number',
        'kit_number',
        'router_id',
        'ssid',
        'wifi_password',
        'office_location',
        'municipality',
        'start_date',
        'advance_payment_until',
        'last_paid_date',
        'billing_interval',
        'po_no',
        'contact_email',
        'plan',
        'subscription_plan_type_id',
        'status',
        'end_user_email',
    ];

    protected $casts = [
        'start_date' => 'date',
        'advance_payment_until' => 'date',
        'last_paid_date' => 'date',
    ];

    /**
     * Next billing date based on last_paid_date, advance_payment_until (if extends further), or start_date if never paid.
     * Excludes periods already covered by mark-as-paid or advance payment.
     */
    public function getNextBillingDateAttribute(): ?\Carbon\Carbon
    {
        if (! $this->start_date) {
            return null;
        }
        $interval = $this->billing_interval ?? 'monthly';
        $today = now()->startOfDay();
        $billingDay = $this->start_date->day;

        // Anchor = most recent date covered (last_paid_date or advance_payment_until, whichever is later)
        $anchor = null;
        if ($this->last_paid_date) {
            $anchor = $this->last_paid_date->copy()->startOfDay();
        }
        if ($this->advance_payment_until && $this->advance_payment_until->copy()->startOfDay()->gt($anchor ?? $today->copy()->subYear())) {
            $adv = $this->advance_payment_until->copy()->startOfDay();
            $anchor = ($anchor === null || $adv->gt($anchor)) ? $adv : $anchor;
        }

        if ($anchor) {
            $next = $interval === 'yearly' ? $anchor->copy()->addYear() : $anchor->copy()->addMonth();
            if ($interval === 'monthly') {
                $day = min($billingDay, $next->copy()->endOfMonth()->day);
                $next->day($day);
            }
            return $next;
        }

        $check = $this->start_date->copy()->startOfDay();
        while ($check->lt($today)) {
            if ($interval === 'yearly') {
                $check->addYear();
            } else {
                $check->addMonth();
                $day = min($billingDay, $check->copy()->endOfMonth()->day);
                $check->day($day);
            }
        }
        return $check;
    }

    public function linkedAccount(): BelongsTo
    {
        return $this->belongsTo(LinkedAccount::class);
    }

    public function subscriptionPlanType(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlanType::class);
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
