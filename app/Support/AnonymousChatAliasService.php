<?php

namespace App\Support;

final class AnonymousChatAliasService
{
    /** @var list<string> */
    private const ANIMALS = [
        'Fox', 'Wolf', 'Bear', 'Eagle', 'Owl', 'Hawk', 'Raven', 'Lynx',
        'Otter', 'Deer', 'Hare', 'Badger', 'Seal', 'Panda', 'Tiger',
        'Lion', 'Panther', 'Falcon', 'Phoenix', 'Dragon', 'Jaguar',
    ];

    public static function generate(): string
    {
        $animal = self::ANIMALS[array_rand(self::ANIMALS)];
        $number = random_int(1000, 9999);

        return 'Anonymous '.$animal.' '.$number;
    }

    /**
     * Alias shown in browse list before a chat starts (does not reveal identity).
     */
    public static function browseLabel(int $viewerId, int $targetId): string
    {
        $animals = self::ANIMALS;
        $index = abs(crc32($viewerId.':'.$targetId)) % count($animals);
        $number = (abs(crc32($targetId.':'.$viewerId)) % 9000) + 1000;

        return 'Anonymous '.$animals[$index].' '.$number;
    }
}
