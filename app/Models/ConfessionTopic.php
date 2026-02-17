<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ConfessionTopic extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'posts_count',
    ];

    protected $casts = [
        'posts_count' => 'integer',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(ConfessionPost::class, 'confession_topic_id');
    }

    public static function findOrCreateByName(string $name): self
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Topic name cannot be empty.');
        }
        $slug = Str::slug($name);
        if (empty($slug)) {
            $slug = 'topic-' . Str::random(6);
        }
        return self::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'posts_count' => 0]
        );
    }
}
