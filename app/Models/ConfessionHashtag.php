<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ConfessionHashtag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'posts_count',
    ];

    protected $casts = [
        'posts_count' => 'integer',
    ];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(ConfessionPost::class, 'confession_post_hashtag', 'confession_hashtag_id', 'confession_post_id');
    }

    public static function extractFromContent(string $content): array
    {
        if (trim($content) === '') {
            return [];
        }
        if (preg_match_all('/#([a-zA-Z0-9_\p{L}]+)/u', $content, $m)) {
            return array_unique(array_map('strtolower', $m[1]));
        }
        return [];
    }

    public static function findOrCreateBySlug(string $slug): self
    {
        $name = '#' . $slug;
        return self::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'posts_count' => 0]
        );
    }
}
