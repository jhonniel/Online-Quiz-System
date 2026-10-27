<?php

namespace App\Support;

use App\Models\Setting;

class LoginPageTheme
{
    public const DEFAULT = 'default';

    /** @deprecated Legacy combined panel; use boo, witch, or ghosto */
    public const HALLOWEEN = 'halloween';

    public const BOO = 'boo';

    public const WITCH = 'witch';

    public const GHOSTO = 'ghosto';

    public const CHRISTMAS = 'christmas';

    public const HOHOHO = 'hohoho';

    /** @return list<string> */
    public static function allowed(): array
    {
        return [
            self::DEFAULT,
            self::BOO,
            self::WITCH,
            self::GHOSTO,
            self::HALLOWEEN,
            self::CHRISTMAS,
            self::HOHOHO,
        ];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::DEFAULT => 'Default — learning illustration',
            self::BOO => 'Boo — floating ghost',
            self::WITCH => 'Witch — broom & moon',
            self::GHOSTO => 'Ghosto — hat & pumpkin',
            self::CHRISTMAS => 'Christmas — winter scene',
            self::HOHOHO => 'Ho Ho Ho — animated Santa',
        ];
    }

    public static function normalize(mixed $value): string
    {
        if (is_array($value)) {
            $value = '';
        }

        $value = strtolower(trim((string) $value));
        $value = trim($value, '"');

        return in_array($value, self::allowed(), true) ? $value : self::DEFAULT;
    }

    public static function current(): string
    {
        return self::normalize(Setting::get('login_page_theme', self::DEFAULT));
    }
}
