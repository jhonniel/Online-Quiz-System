<?php

namespace App\Models;

use App\Support\AnonymousChatAliasService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnonymousChatRoom extends Model
{
    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'created_by',
    ];

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(AnonymousChatParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AnonymousChatMessage::class)->orderBy('created_at');
    }

    public static function normalizePair(int $userIdA, int $userIdB): array
    {
        return $userIdA < $userIdB ? [$userIdA, $userIdB] : [$userIdB, $userIdA];
    }

    public static function findForUsers(int $userIdA, int $userIdB): ?self
    {
        [$userOneId, $userTwoId] = self::normalizePair($userIdA, $userIdB);

        return self::query()
            ->where('user_one_id', $userOneId)
            ->where('user_two_id', $userTwoId)
            ->first();
    }

    public static function findOrCreateBetween(User $initiator, User $target): self
    {
        $existing = self::findForUsers($initiator->id, $target->id);
        if ($existing instanceof self) {
            return $existing->load(['participants', 'userOne', 'userTwo']);
        }

        [$userOneId, $userTwoId] = self::normalizePair($initiator->id, $target->id);

        $room = self::create([
            'user_one_id' => $userOneId,
            'user_two_id' => $userTwoId,
            'created_by' => $initiator->id,
        ]);

        $room->participants()->createMany([
            [
                'user_id' => $initiator->id,
                'display_alias' => AnonymousChatAliasService::generate(),
                'joined_at' => now(),
            ],
            [
                'user_id' => $target->id,
                'display_alias' => AnonymousChatAliasService::generate(),
                'joined_at' => now(),
            ],
        ]);

        return $room->load(['participants', 'userOne', 'userTwo']);
    }

    public function includesUser(int $userId): bool
    {
        return (int) $this->user_one_id === $userId || (int) $this->user_two_id === $userId;
    }

    public function peerUser(User $user): ?User
    {
        if ((int) $this->user_one_id === $user->id) {
            return $this->userTwo;
        }

        if ((int) $this->user_two_id === $user->id) {
            return $this->userOne;
        }

        return null;
    }

    public function aliasForUser(int $userId): ?string
    {
        return $this->participants()
            ->where('user_id', $userId)
            ->value('display_alias');
    }

    public function senderAlias(int $senderId): string
    {
        return (string) ($this->aliasForUser($senderId) ?: 'Anonymous');
    }
}
