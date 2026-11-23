<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OtherSubscription extends Model
{
    public const BILLING_TYPES = [
        'monthly' => 'Monthly',
        'annual' => 'Annual',
    ];

    protected $fillable = [
        'uuid',
        'name',
        'client_name',
        'client_email',
        'provider',
        'other_subscription_provider_id',
        'billing_type',
        'price',
        'currency',
        'starts_on',
        'ends_on',
        'auto_renew',
        'notify_days_before',
        'notes',
        'last_notified_at',
        'last_notified_for_ends_on',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'auto_renew' => 'boolean',
        'notify_days_before' => 'integer',
        'last_notified_at' => 'datetime',
        'last_notified_for_ends_on' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $subscription): void {
            if (empty($subscription->uuid)) {
                $subscription->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function subscriptionProvider(): BelongsTo
    {
        return $this->belongsTo(OtherSubscriptionProvider::class, 'other_subscription_provider_id');
    }

    public function providerDisplayName(): string
    {
        return (string) ($this->subscriptionProvider?->name ?: $this->provider ?: '—');
    }

    public function getStatusAttribute(): string
    {
        if (! $this->ends_on) {
            return '—';
        }

        return $this->ends_on->startOfDay()->lt(now()->startOfDay())
            ? 'Expired'
            : 'Active';
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    public function isExpired(): bool
    {
        return $this->status === 'Expired';
    }

    public function daysUntilEnd(): ?int
    {
        if (! $this->ends_on) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->ends_on->copy()->startOfDay(), false);
    }

    public function getBillingTypeLabelAttribute(): string
    {
        return self::BILLING_TYPES[$this->billing_type] ?? ucfirst((string) $this->billing_type);
    }

    public function getFormattedPriceAttribute(): string
    {
        $currency = strtoupper(trim((string) ($this->currency ?: 'PHP')));

        return $currency.' '.number_format((float) $this->price, 2);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereDate('ends_on', '>=', now()->toDateString());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereDate('ends_on', '<', now()->toDateString());
    }

    public function scopeDueForReminder(Builder $query): Builder
    {
        $today = now()->startOfDay();

        return $query->whereDate('ends_on', '>=', $today->toDateString())
            ->where(function (Builder $q) use ($today): void {
                $q->whereNull('last_notified_for_ends_on')
                    ->orWhereColumn('last_notified_for_ends_on', '!=', 'ends_on')
                    ->orWhereNull('last_notified_at')
                    ->orWhere('last_notified_at', '<', $today->copy()->subDay());
            });
    }

    public function shouldNotifyToday(): bool
    {
        if (! $this->ends_on) {
            return false;
        }

        $daysLeft = $this->daysUntilEnd();
        if ($daysLeft === null || $daysLeft < 0) {
            return false;
        }

        $threshold = max(0, (int) ($this->notify_days_before ?? 7));
        if ($daysLeft > $threshold) {
            return false;
        }

        // One reminder per end-date cycle (re-sends only if ends_on changes).
        if ($this->last_notified_for_ends_on
            && $this->last_notified_for_ends_on->equalTo($this->ends_on)
        ) {
            return false;
        }

        return true;
    }
}
