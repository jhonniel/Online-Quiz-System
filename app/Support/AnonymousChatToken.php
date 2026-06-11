<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;

final class AnonymousChatToken
{
    private const TTL_SECONDS = 86400;

    public static function issue(int $issuedToUserId, int $targetId): string
    {
        return Crypt::encryptString(json_encode([
            'target_id' => $targetId,
            'issued_to' => $issuedToUserId,
            'exp' => time() + self::TTL_SECONDS,
        ], JSON_THROW_ON_ERROR));
    }

    public static function resolve(string $token, int $forUserId): ?int
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }

        if (! is_array($payload)
            || (int) ($payload['issued_to'] ?? 0) !== $forUserId
            || (int) ($payload['exp'] ?? 0) < time()
        ) {
            return null;
        }

        $targetId = (int) ($payload['target_id'] ?? 0);

        return $targetId > 0 ? $targetId : null;
    }

    public static function resolveLegacy(string $token): ?int
    {
        try {
            $targetId = (int) Crypt::decryptString($token);

            return $targetId > 0 ? $targetId : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
