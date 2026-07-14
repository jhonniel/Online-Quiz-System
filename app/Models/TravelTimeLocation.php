<?php

namespace App\Models;

use App\Helpers\TimeExtraction;
use Illuminate\Database\Eloquent\Model;

class TravelTimeLocation extends Model
{
    public const TRIP_ONE_WAY = 'one_way';

    public const TRIP_ROUND_TRIP = 'round_trip';

    protected $fillable = [
        'name',
        'hours',
        'trip_type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'hours' => 'float',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * @return array<string, string>
     */
    public static function tripTypes(): array
    {
        return [
            self::TRIP_ONE_WAY => 'One Way',
            self::TRIP_ROUND_TRIP => 'Round Trip',
        ];
    }

    public function tripTypeLabel(): string
    {
        return self::tripTypes()[$this->trip_type] ?? ucfirst(str_replace('_', ' ', (string) $this->trip_type));
    }

    public function hoursFormatted(): string
    {
        return TimeExtraction::decimalToHhMm((float) $this->hours);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
