<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmployeeFileTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'body',
        'custom_fields',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public const SLUG_CERTIFICATE_OF_EMPLOYMENT = 'certificate-of-employment';

    public const CATEGORIES = [
        'certificate' => 'Certificate',
        'payslip' => 'Payslip',
        'letter' => 'Letter',
        'other' => 'Other',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    public function fileRequests(): HasMany
    {
        return $this->hasMany(EmployeeFileRequest::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst(str_replace('_', ' ', (string) $this->category));
    }

    /** @return list<array{key: string, label: string, default?: string}> */
    public function customFieldDefinitions(): array
    {
        return collect($this->custom_fields ?? [])
            ->filter(fn ($field) => is_array($field) && !empty($field['key']))
            ->values()
            ->all();
    }

    public function isCertificateOfEmployment(): bool
    {
        return $this->slug === self::SLUG_CERTIFICATE_OF_EMPLOYMENT;
    }
}
