<?php

namespace App\Models;

use App\Helpers\SayItHelper;
use App\Services\ConfessionCensorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class SayItChatMessage extends Model
{
    use SoftDeletes;

    protected $table = 'sayit_chat_messages';

    protected $fillable = [
        'sayit_chat_room_id',
        'codename',
        'body',
        'image_path',
        'image_expires_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'image_expires_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(SayItChatRoom::class, 'sayit_chat_room_id');
    }

    public function getDisplayBodyAttribute(): string
    {
        return ConfessionCensorService::censor((string) $this->body);
    }

    public function hasActiveImage(): bool
    {
        return filled($this->image_path)
            && $this->image_expires_at
            && $this->image_expires_at->isFuture();
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->hasActiveImage()) {
            return null;
        }

        $primary = SayItHelper::confessionStorageDisk();
        foreach (array_unique([$primary, 'digitalocean', 'spaces', 's3', 'public', 'local']) as $disk) {
            if (! is_array(config("filesystems.disks.{$disk}"))) {
                continue;
            }
            try {
                return Storage::disk($disk)->url($this->image_path);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    public function deleteImageFile(): void
    {
        if (empty($this->image_path)) {
            return;
        }

        $path = $this->image_path;
        foreach (array_unique([SayItHelper::confessionStorageDisk(), 'digitalocean', 'spaces', 's3', 'public', 'local']) as $disk) {
            if (! is_array(config("filesystems.disks.{$disk}"))) {
                continue;
            }
            try {
                if (Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                }
            } catch (\Throwable $e) {
                // ignore cleanup errors
            }
        }
    }

    public function purgeExpiredImage(): bool
    {
        if (empty($this->image_path)) {
            return false;
        }

        if ($this->image_expires_at && $this->image_expires_at->isFuture()) {
            return false;
        }

        $this->deleteImageFile();
        $this->forceFill([
            'image_path' => null,
            'image_expires_at' => null,
        ])->save();

        if (trim((string) $this->body) === '') {
            $this->delete();
        }

        return true;
    }

    public function scopeWithExpiredImages(Builder $query): Builder
    {
        return $query->whereNotNull('image_path')
            ->where(function (Builder $q) {
                $q->whereNull('image_expires_at')
                    ->orWhere('image_expires_at', '<=', now());
            });
    }

    /**
     * @return array{id: int, codename: string, body: string, image_url: ?string, image_expires_at: ?string, is_own: bool, created_at: string, created_at_human: string}
     */
    public function toClientPayload(string $sessionCodename, bool $gibberish = false): array
    {
        $body = $this->display_body;
        if ($gibberish && $body !== '') {
            $body = self::toGibberish($body, (int) $this->id);
        }

        return [
            'id' => $this->id,
            'codename' => $this->codename,
            'body' => $body,
            'image_url' => $this->image_url,
            'image_expires_at' => $this->hasActiveImage()
                ? ($this->image_expires_at?->toIso8601String() ?? null)
                : null,
            'is_own' => $this->codename === $sessionCodename,
            'created_at' => $this->created_at?->toIso8601String() ?? '',
            'created_at_human' => $this->created_at?->diffForHumans() ?? '',
        ];
    }

    public static function toGibberish(string $text, int $seed): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz';
        $out = '';
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $ch = $text[$i];
            if (ctype_alpha($ch)) {
                $idx = (ord(strtolower($ch)) - 97 + $seed + $i * 7) % 26;
                $repl = $alphabet[$idx];
                $out .= ctype_upper($ch) ? strtoupper($repl) : $repl;
            } elseif (ctype_digit($ch)) {
                $out .= (string) (($seed + (int) $ch + $i) % 10);
            } else {
                $out .= $ch;
            }
        }

        return $out;
    }

    protected static function booted(): void
    {
        static::deleting(function (SayItChatMessage $message) {
            $message->deleteImageFile();
        });
    }
}
