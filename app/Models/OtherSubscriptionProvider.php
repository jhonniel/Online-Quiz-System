<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class OtherSubscriptionProvider extends Model
{
    protected $fillable = [
        'uuid',
        'name',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $provider): void {
            if (empty($provider->uuid)) {
                $provider->uuid = (string) Str::uuid();
            }
        });

        static::updating(function (self $provider): void {
            if ($provider->isDirty('name')) {
                OtherSubscription::query()
                    ->where('other_subscription_provider_id', $provider->id)
                    ->update(['provider' => $provider->name]);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(OtherSubscription::class, 'other_subscription_provider_id');
    }
}
