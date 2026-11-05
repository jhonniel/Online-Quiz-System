<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

/**
 * Device identifiers (serial number, kit number, MAC address, ...) must not repeat.
 * Comparison ignores letter case and surrounding spaces, so "SN-001" and " sn-001 " count as the same device.
 */
class UniqueDeviceIdentifier implements ValidationRule
{
    public function __construct(
        private readonly string $table,
        private readonly string $column,
        private readonly string $label,
        private readonly ?int $ignoreId = null,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (self::isTaken($this->table, $this->column, $value, $this->ignoreId)) {
            $fail("This {$this->label} is already used by another device.");
        }
    }

    public static function isTaken(string $table, string $column, mixed $value, ?int $ignoreId = null): bool
    {
        $normalized = mb_strtolower(trim((string) $value));
        if ($normalized === '') {
            return false;
        }

        return DB::table($table)
            ->whereRaw("LOWER(TRIM({$column})) = ?", [$normalized])
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
