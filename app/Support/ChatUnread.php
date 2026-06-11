<?php

namespace App\Support;

use App\Models\AnonymousChatRoom;
use App\Models\GroupChat;
use App\Models\User;
use App\Models\UserChatMessage;
use Illuminate\Support\Str;

final class ChatUnread
{
    public static function friendUnreadCount(User $user, int $friendId): int
    {
        return UserChatMessage::query()
            ->where('sender_id', $friendId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    public static function friendLatestPreview(User $user, int $friendId): ?string
    {
        $message = UserChatMessage::betweenUsers($user->id, $friendId)
            ->latest()
            ->value('message');

        return $message ? self::preview((string) $message) : null;
    }

    public static function anonymousUnreadCount(AnonymousChatRoom $room, int $userId): int
    {
        $lastRead = $room->participants()
            ->where('user_id', $userId)
            ->value('last_read_at');

        return $room->messages()
            ->where('sender_id', '!=', $userId)
            ->when($lastRead, fn ($query) => $query->where('created_at', '>', $lastRead))
            ->count();
    }

    public static function anonymousLatestPreview(AnonymousChatRoom $room, int $userId): ?string
    {
        $message = $room->messages()
            ->where('sender_id', '!=', $userId)
            ->latest()
            ->value('message');

        return $message ? self::preview((string) $message) : null;
    }

    public static function markAnonymousRead(AnonymousChatRoom $room, int $userId): void
    {
        $room->participants()
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);
    }

    public static function groupUnreadCount(GroupChat $group, int $userId): int
    {
        $member = $group->members()->where('users.id', $userId)->first();
        $lastRead = $member?->pivot?->last_read_at;

        return $group->messages()
            ->where('sender_id', '!=', $userId)
            ->when($lastRead, fn ($query) => $query->where('created_at', '>', $lastRead))
            ->count();
    }

    public static function groupLatestPreview(GroupChat $group): ?string
    {
        $message = $group->messages()->latest()->value('message');

        return $message ? self::preview((string) $message) : null;
    }

    public static function markGroupRead(GroupChat $group, int $userId): void
    {
        $group->members()->updateExistingPivot($userId, ['last_read_at' => now()]);
    }

    public static function totalUnreadFor(User $user): int
    {
        $direct = $user->unreadMessageCount();

        $anonymous = AnonymousChatRoom::query()
            ->with('participants')
            ->where(function ($query) use ($user) {
                $query->where('user_one_id', $user->id)
                    ->orWhere('user_two_id', $user->id);
            })
            ->get()
            ->sum(fn (AnonymousChatRoom $room) => self::anonymousUnreadCount($room, $user->id));

        $groups = $user->groupChats()
            ->get()
            ->sum(fn (GroupChat $group) => self::groupUnreadCount($group, $user->id));

        return $direct + $anonymous + $groups;
    }

    /**
     * @return array{
     *     friends: list<array{id: int, unread_count: int, preview: ?string, has_new: bool}>,
     *     groups: list<array{id: int, unread_count: int, preview: ?string, has_new: bool}>,
     *     anonymous: list<array{room_id: int, unread_count: int, preview: ?string, has_new: bool}>,
     *     total: int
     * }
     */
    public static function sidebarPayload(User $user): array
    {
        $friendsAsUser = $user->friends()->get();
        $friendsAsFriend = $user->acceptedFriends()->get();
        $friends = $friendsAsUser->merge($friendsAsFriend)->unique('id');

        $friendPayload = $friends->map(function (User $friend) use ($user) {
            $unread = self::friendUnreadCount($user, $friend->id);

            return [
                'id' => $friend->id,
                'unread_count' => $unread,
                'preview' => self::friendLatestPreview($user, $friend->id),
                'has_new' => $unread > 0,
            ];
        })->values()->all();

        $groupPayload = $user->groupChats()
            ->get()
            ->map(function (GroupChat $group) use ($user) {
                $unread = self::groupUnreadCount($group, $user->id);

                return [
                    'id' => $group->id,
                    'unread_count' => $unread,
                    'preview' => self::groupLatestPreview($group),
                    'has_new' => $unread > 0,
                ];
            })
            ->values()
            ->all();

        $anonymousPayload = AnonymousChatRoom::query()
            ->with('participants')
            ->where(function ($query) use ($user) {
                $query->where('user_one_id', $user->id)
                    ->orWhere('user_two_id', $user->id);
            })
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (AnonymousChatRoom $room) use ($user) {
                $unread = self::anonymousUnreadCount($room, $user->id);

                return [
                    'room_id' => $room->id,
                    'unread_count' => $unread,
                    'preview' => self::anonymousLatestPreview($room, $user->id),
                    'has_new' => $unread > 0,
                ];
            })
            ->values()
            ->all();

        return [
            'friends' => $friendPayload,
            'groups' => $groupPayload,
            'anonymous' => $anonymousPayload,
            'total' => self::totalUnreadFor($user),
        ];
    }

    public static function preview(string $message, int $length = 42): string
    {
        $text = trim($message);

        return Str::length($text) > $length ? Str::substr($text, 0, $length).'…' : $text;
    }
}
