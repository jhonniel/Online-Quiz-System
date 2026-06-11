<?php

namespace App\Support;

use App\Models\Friendship;
use App\Models\User;

final class AnonymousChatEligibility
{
    public static function isEligibleTarget(User $viewer, User $target): bool
    {
        if ($viewer->id === $target->id) {
            return false;
        }

        if (! $target->is_active || $target->role === 'admin') {
            return false;
        }

        return ! self::isBlockedBetween($viewer->id, $target->id);
    }

    public static function isBlockedBetween(int $userIdA, int $userIdB): bool
    {
        return Friendship::query()
            ->where('status', 'blocked')
            ->where(function ($query) use ($userIdA, $userIdB) {
                $query->where(function ($inner) use ($userIdA, $userIdB) {
                    $inner->where('user_id', $userIdA)->where('friend_id', $userIdB);
                })->orWhere(function ($inner) use ($userIdA, $userIdB) {
                    $inner->where('user_id', $userIdB)->where('friend_id', $userIdA);
                });
            })
            ->exists();
    }

    /**
     * @return list<int>
     */
    public static function blockedUserIdsFor(User $user): array
    {
        return Friendship::query()
            ->where('status', 'blocked')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('friend_id', $user->id);
            })
            ->get()
            ->flatMap(fn (Friendship $friendship) => [$friendship->user_id, $friendship->friend_id])
            ->unique()
            ->reject(fn (int $id) => $id === $user->id)
            ->values()
            ->all();
    }
}
