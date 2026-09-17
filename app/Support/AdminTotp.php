<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

final class AdminTotp
{
    public const SESSION_PASSED_KEY = 'admin_totp_passed';

    public const RECOVERY_CODE_COUNT = 8;

    private Google2FA $google2fa;

    public function __construct(?Google2FA $google2fa = null)
    {
        $this->google2fa = $google2fa ?? new Google2FA;
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    public function companyName(): string
    {
        $name = trim((string) Setting::get('system_name', config('app.name', 'Admin')));

        return $name !== '' ? $name : 'Admin';
    }

    public function otpAuthUrl(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            $this->companyName(),
            (string) $user->email,
            $secret
        );
    }

    public function qrCodeDataUri(User $user, string $secret, int $size = 220): string
    {
        $svg = QrCode::format('svg')->size($size)->margin(1)->generate(
            $this->otpAuthUrl($user, $secret)
        );

        return 'data:image/svg+xml;base64,'.base64_encode((string) $svg);
    }

    public function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';
        if ($code === '' || $secret === '') {
            return false;
        }

        try {
            return (bool) $this->google2fa->verifyKey($secret, $code, 1);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return list<string> Plaintext recovery codes (show once).
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < self::RECOVERY_CODE_COUNT; $i++) {
            $codes[] = strtoupper(Str::random(4).'-'.Str::random(4));
        }

        return $codes;
    }

    /**
     * @param  list<string>  $plainCodes
     * @return list<string> Hashed codes for storage.
     */
    public function hashRecoveryCodes(array $plainCodes): array
    {
        return array_values(array_map(
            fn (string $code) => Hash::make(strtoupper(trim($code))),
            $plainCodes
        ));
    }

    /**
     * @param  list<string>|null  $hashedCodes
     * @return array{0: bool, 1: list<string>} [matched, remaining hashed codes]
     */
    public function consumeRecoveryCode(?array $hashedCodes, string $plainCode): array
    {
        $hashedCodes = array_values($hashedCodes ?? []);
        $plainCode = strtoupper(trim($plainCode));

        foreach ($hashedCodes as $index => $hashed) {
            if (is_string($hashed) && Hash::check($plainCode, $hashed)) {
                unset($hashedCodes[$index]);

                return [true, array_values($hashedCodes)];
            }
        }

        return [false, $hashedCodes];
    }

    public function markSessionPassed(): void
    {
        session([self::SESSION_PASSED_KEY => true]);
    }

    public function sessionPassed(): bool
    {
        return (bool) session(self::SESSION_PASSED_KEY, false);
    }

    public function clearSessionPassed(): void
    {
        session()->forget(self::SESSION_PASSED_KEY);
    }
}
