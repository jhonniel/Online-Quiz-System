<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function exclusions(): HasMany
    {
        return $this->hasMany(DtrHolidayExclusion::class);
    }

    /**
     * Whether this employee was excluded from the holiday (treated as a regular workday).
     */
    public function excludesUser(?int $userId): bool
    {
        if ($userId === null) {
            return false;
        }

        if ($this->relationLoaded('exclusions')) {
            return $this->exclusions->contains('user_id', $userId);
        }

        return $this->exclusions()->where('user_id', $userId)->exists();
    }
}
