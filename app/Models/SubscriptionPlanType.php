<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlanType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'subscription_type',
        'billing_type',
        'billing_interval_months',
        'billing_day',
    ];

    public const SUBSCRIPTION_TYPES = ['starlink', 'omada'];
    public const BILLING_TYPES = ['monthly', 'yearly', 'custom'];

    public function getSubscriptionTypeLabelAttribute(): string
    {
        return match ($this->subscription_type) {
            'starlink' => 'Starlink',
            'omada' => 'Omada',
            default => $this->subscription_type ?? '—',
        };
    }

    public function getBillingTypeLabelAttribute(): string
    {
        return match ($this->billing_type) {
            'monthly' => 'Monthly',
            'yearly' => 'Yearly',
            'custom' => $this->getCustomBillingLabel(),
            default => $this->billing_type ?? '—',
        };
    }

    private function getCustomBillingLabel(): string
    {
        $months = $this->billing_interval_months ?? 1;
        $day = $this->billing_day ? " on day {$this->billing_day}" : '';
        return "Every {$months} month(s){$day}";
    }
}
