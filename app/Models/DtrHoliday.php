<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtrHoliday extends Model
{
    public const TYPE_REGULAR = 'regular';

    public const TYPE_SPECIAL = 'special';

    protected $fillable = [
        'date',
        'name',
        'type',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /** @return array<string, string> */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_REGULAR => 'Regular Holiday',
            self::TYPE_SPECIAL => 'Special Holiday',
        ];
    }

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->type] ?? 'Regular Holiday';
    }

    public function isSpecial(): bool
    {
        return $this->type === self::TYPE_SPECIAL;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
