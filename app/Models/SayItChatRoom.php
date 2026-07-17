<?php

namespace App\Models;

use App\Helpers\SayItHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SayItChatRoom extends Model
{
    use SoftDeletes;

    public const CODE_FREEZE = 'freeze';

    public const CODE_DELETE = 'delete';

    public const CODE_GIBBERISH = 'gibberish';

    protected $table = 'sayit_chat_rooms';

    protected $fillable = [
        'name',
        'slug',
        'creator_codename',
        'avatar_path',
        'password_hash',
        'password_encrypted',
        'freeze_code',
        'delete_code',
        'gibberish_code',
        'is_frozen',
        'gibberish_until',
        'freeze_code_used_at',
        'freeze_code_used_by',
        'freeze_code_used_ip',
        'delete_code_used_at',
        'delete_code_used_by',
        'delete_code_used_ip',
        'gibberish_code_used_at',
        'gibberish_code_used_by',
        'gibberish_code_used_ip',
        'last_message_at',
    ];

    protected $casts = [
        'is_frozen' => 'boolean',
        'gibberish_until' => 'datetime',
        'freeze_code_used_at' => 'datetime',
        'delete_code_used_at' => 'datetime',
        'gibberish_code_used_at' => 'datetime',
        'last_message_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'password_hash',
        'password_encrypted',
        'freeze_code',
        'delete_code',
        'gibberish_code',
    ];

    protected $appends = [
        'avatar_url',
        'is_password_protected',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getIsPasswordProtectedAttribute(): bool
    {
        return $this->hasPassword();
    }

    public function hasPassword(): bool
    {
        return filled($this->password_hash);
    }

    public function setRoomPassword(?string $plainPassword): void
    {
        $plainPassword = is_string($plainPassword) ? trim($plainPassword) : '';
        if ($plainPassword === '') {
            $this->password_hash = null;
            $this->password_encrypted = null;

            return;
        }

        $this->password_hash = Hash::make($plainPassword);
        $this->password_encrypted = Crypt::encryptString($plainPassword);
    }

    public function checkPassword(string $plainPassword): bool
    {
        if (! $this->hasPassword()) {
            return true;
        }

        return Hash::check($plainPassword, $this->password_hash);
    }

    /**
     * Plaintext password for the creator (ownership required).
     */
    public function revealPasswordFor(Request $request): ?string
    {
        if (! $this->hasPassword() || ! $this->isOwnedBy($request)) {
            return null;
        }

        return $this->decryptStoredPassword();
    }

    /**
     * Plaintext password for admin moderation records.
     */
    public function revealPasswordForAdmin(): ?string
    {
        if (! $this->hasPassword()) {
            return null;
        }

        return $this->decryptStoredPassword();
    }

    public function decryptStoredPassword(): ?string
    {
        if (! filled($this->password_encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->password_encrypted);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function isUnlockedFor(Request $request): bool
    {
        if (! $this->hasPassword()) {
            return true;
        }

        $activeRoomId = $request->session()->get('sayit_chat_active_room');

        return (string) $activeRoomId === (string) $this->id;
    }

    public function unlockFor(Request $request): void
    {
        $request->session()->put('sayit_chat_active_room', (string) $this->id);
    }

    public static function clearRoomUnlock(Request $request): void
    {
        $request->session()->forget('sayit_chat_active_room');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SayItChatMessage::class, 'sayit_chat_room_id')->orderBy('created_at');
    }

    public function isOwnedBy(Request $request): bool
    {
        $sessionCodename = $request->session()->get('sayit_codename');

        return is_string($sessionCodename)
            && $sessionCodename !== ''
            && $sessionCodename === $this->creator_codename;
    }

    public function isGibberishActive(): bool
    {
        return $this->gibberish_until !== null && $this->gibberish_until->isFuture();
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        $primary = SayItHelper::confessionStorageDisk();
        foreach (array_unique([$primary, 'digitalocean', 'spaces', 's3', 'public', 'local']) as $disk) {
            if (! is_array(config("filesystems.disks.{$disk}"))) {
                continue;
            }
            try {
                return Storage::disk($disk)->url($this->avatar_path);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    public function deleteAvatarFile(): void
    {
        if (empty($this->avatar_path)) {
            return;
        }

        $path = $this->avatar_path;
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

    /**
     * @return array{freeze: string, delete: string, gibberish: string}
     */
    public static function generateModerationCodes(): array
    {
        return [
            'freeze' => self::makeUniqueCode(),
            'delete' => self::makeUniqueCode(),
            'gibberish' => self::makeUniqueCode(),
        ];
    }

    public static function makeUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
        } while (
            static::withTrashed()
                ->where(function ($q) use ($code) {
                    $q->where('freeze_code', $code)
                        ->orWhere('delete_code', $code)
                        ->orWhere('gibberish_code', $code);
                })
                ->exists()
        );

        return $code;
    }

    /**
     * Match an unused moderation code. Returns effect type or null.
     */
    public function matchUnusedModerationCode(string $body): ?string
    {
        $candidate = strtoupper(trim($body));
        if ($candidate === '') {
            return null;
        }

        if ($this->freeze_code && strtoupper($this->freeze_code) === $candidate && ! $this->freeze_code_used_at) {
            return self::CODE_FREEZE;
        }
        if ($this->delete_code && strtoupper($this->delete_code) === $candidate && ! $this->delete_code_used_at) {
            return self::CODE_DELETE;
        }
        if ($this->gibberish_code && strtoupper($this->gibberish_code) === $candidate && ! $this->gibberish_code_used_at) {
            return self::CODE_GIBBERISH;
        }

        return null;
    }

    /**
     * @return array{effect: string, message: string}
     */
    public function activateModerationCode(string $effect, string $codename, ?string $ip): array
    {
        if ($effect === self::CODE_FREEZE) {
            $this->forceFill([
                'is_frozen' => true,
                'freeze_code_used_at' => now(),
                'freeze_code_used_by' => $codename,
                'freeze_code_used_ip' => $ip,
            ])->save();

            return [
                'effect' => self::CODE_FREEZE,
                'message' => 'This room has been frozen. No new messages can be sent.',
            ];
        }

        if ($effect === self::CODE_GIBBERISH) {
            $this->forceFill([
                'gibberish_until' => now()->addHour(),
                'gibberish_code_used_at' => now(),
                'gibberish_code_used_by' => $codename,
                'gibberish_code_used_ip' => $ip,
            ])->save();

            return [
                'effect' => self::CODE_GIBBERISH,
                'message' => 'This room has entered gibberish mode for 1 hour.',
            ];
        }

        // delete
        $this->forceFill([
            'delete_code_used_at' => now(),
            'delete_code_used_by' => $codename,
            'delete_code_used_ip' => $ip,
        ])->save();
        $this->delete();

        return [
            'effect' => self::CODE_DELETE,
            'message' => 'This room has been deleted.',
        ];
    }

    /**
     * @return array{is_frozen: bool, gibberish_active: bool, gibberish_until: ?string}
     */
    public function publicStatusPayload(): array
    {
        return [
            'is_frozen' => (bool) $this->is_frozen,
            'gibberish_active' => $this->isGibberishActive(),
            'gibberish_until' => $this->isGibberishActive()
                ? ($this->gibberish_until?->toIso8601String() ?? null)
                : null,
        ];
    }

    /**
     * Admin-facing code records.
     *
     * @return list<array{type: string, label: string, code: ?string, used_at: ?string, used_by: ?string, used_ip: ?string}>
     */
    public function moderationCodeRecords(): array
    {
        return [
            [
                'type' => self::CODE_FREEZE,
                'label' => 'Freeze room',
                'code' => $this->freeze_code,
                'used_at' => $this->freeze_code_used_at?->toDateTimeString(),
                'used_by' => $this->freeze_code_used_by,
                'used_ip' => $this->freeze_code_used_ip,
            ],
            [
                'type' => self::CODE_DELETE,
                'label' => 'Delete room',
                'code' => $this->delete_code,
                'used_at' => $this->delete_code_used_at?->toDateTimeString(),
                'used_by' => $this->delete_code_used_by,
                'used_ip' => $this->delete_code_used_ip,
            ],
            [
                'type' => self::CODE_GIBBERISH,
                'label' => 'Gibberish (1 hour)',
                'code' => $this->gibberish_code,
                'used_at' => $this->gibberish_code_used_at?->toDateTimeString(),
                'used_by' => $this->gibberish_code_used_by,
                'used_ip' => $this->gibberish_code_used_ip,
            ],
        ];
    }

    protected static function booted(): void
    {
        static::forceDeleting(function (SayItChatRoom $room) {
            $room->deleteAvatarFile();
        });
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
