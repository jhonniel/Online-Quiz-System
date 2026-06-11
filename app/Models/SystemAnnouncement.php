<?php

namespace App\Models;

use App\Support\SystemAnnouncementFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemAnnouncement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'feature_links',
        'is_published',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'feature_links' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acknowledgments(): HasMany
    {
        return $this->hasMany(EmployeeAnnouncementAcknowledgment::class);
    }

    public function scopePublished($query)
    {
        return $query
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function acknowledgedEmployeesCount(): int
    {
        return (int) $this->acknowledgments()->count();
    }

    public function displayContentHtml(): string
    {
        return SystemAnnouncementFormatter::toDisplayHtml((string) $this->content);
    }

    /**
     * @return list<array{url: string, label: string, href: string}>
     */
    public function resolvedFeatureLinks(): array
    {
        return collect($this->feature_links ?? [])
            ->map(function (mixed $link): ?array {
                if (! is_array($link)) {
                    return null;
                }

                $url = trim((string) ($link['url'] ?? ''));
                if ($url === '') {
                    return null;
                }

                $label = trim((string) ($link['label'] ?? ''));

                return [
                    'url' => $url,
                    'label' => $label !== '' ? $label : 'Open feature',
                    'href' => str_starts_with($url, '/') ? url($url) : $url,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function hasFeatureLinks(): bool
    {
        return $this->resolvedFeatureLinks() !== [];
    }

    public function featureLinksCount(): int
    {
        return count($this->resolvedFeatureLinks());
    }
}
