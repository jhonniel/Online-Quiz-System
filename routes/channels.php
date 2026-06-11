<?php

use App\Models\AnonymousChatRoom;
use App\Models\Friendship;
use App\Models\GroupChat;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.direct.{userOne}.{userTwo}', function ($user, int $userOne, int $userTwo) {
    $userId = (int) $user->id;

    if ($userId !== $userOne && $userId !== $userTwo) {
        return false;
    }

    return Friendship::query()
        ->where('status', 'accepted')
        ->where(function ($query) use ($userOne, $userTwo) {
            $query->where(function ($inner) use ($userOne, $userTwo) {
                $inner->where('user_id', $userOne)->where('friend_id', $userTwo);
            })->orWhere(function ($inner) use ($userOne, $userTwo) {
                $inner->where('user_id', $userTwo)->where('friend_id', $userOne);
            });
        })
        ->exists();
});

Broadcast::channel('chat.group.{groupId}', function ($user, int $groupId) {
    $groupChat = GroupChat::query()->find($groupId);

    if (! $groupChat || ! $groupChat->hasMember($user->id)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});

Broadcast::channel('chat.anonymous.{roomId}', function ($user, int $roomId) {
    $room = AnonymousChatRoom::query()->find($roomId);

    if (! $room instanceof AnonymousChatRoom || ! $room->includesUser($user->id)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});

Broadcast::channel('chat.presence', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});
