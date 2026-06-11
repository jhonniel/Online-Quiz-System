<?php

namespace App\Support;

final class ChatChannels
{
    public static function direct(int $userIdA, int $userIdB): string
    {
        $ids = [$userIdA, $userIdB];
        sort($ids);

        return 'chat.direct.'.$ids[0].'.'.$ids[1];
    }

    public static function group(int $groupChatId): string
    {
        return 'chat.group.'.$groupChatId;
    }

    public static function anonymous(int $roomId): string
    {
        return 'chat.anonymous.'.$roomId;
    }

    public static function presence(): string
    {
        return 'chat.presence';
    }
}
