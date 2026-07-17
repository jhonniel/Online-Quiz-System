<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SayItChatRoom extends Model
{
    use SoftDeletes;

    protected $table = 'sayit_chat_rooms';

    protected $fillable = [
        'name',
        'slug',
        'creator_codename',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SayItChatMessage::class, 'sayit_chat_room_id')->orderBy('created_at');
    }

    public static function uniqueSlugFromName(string $name): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'room';
        }

        $slug = $base;
        $i = 2;
        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
