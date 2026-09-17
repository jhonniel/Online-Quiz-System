<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminTotp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SecurityController extends Controller
{
    public function __construct(private AdminTotp $totp) {}

    public function index()
    {
        $user = auth()->user();
        abort_unless($user && $user->isAdmin(), 403, 'Only administrators can manage authenticator 2FA.');

        $qrDataUri = null;
        $manualSecret = null;

        if ($user->hasAdminTotpPending() && filled($user->totp_secret)) {
            $manualSecret = (string) $user->totp_secret;
            $qrDataUri = $this->totp->qrCodeDataUri($user, $manualSecret);
        }

        $plainRecoveryCodes = session()->pull('totp_recovery_codes_plain');

        return view('admin.security.index', [
            'user' => $user,
            'enabled' => $user->hasAdminTotpEnabled(),
            'pending' => $user->hasAdminTotpPending(),
            'qrDataUri' => $qrDataUri,
            'manualSecret' => $manualSecret,
            'plainRecoveryCodes' => is_array($plainRecoveryCodes) ? $plainRecoveryCodes : null,
        ]);
    }

    public function start(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $user->isAdmin(), 403);

        if ($user->hasAdminTotpEnabled()) {
            return redirect('/admin/security')
                ->with('error', 'Authenticator 2FA is already enabled.');
        }

        $secret = $this->totp->generateSecret();
        $user->totp_secret = $secret;
        $user->totp_confirmed_at = null;
        $user->totp_recovery_codes = null;
        $user->save();

        $this->totp->clearSessionPassed();

        return redirect('/admin/security')
            ->with('success', 'Scan the QR code with Google Authenticator (or any TOTP app), then enter a code to confirm.');
    }

    public function confirm(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $user->isAdmin(), 403);

        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:12'],
        ], [
            'code.required' => 'Enter the 6-digit code from your authenticator app.',
        ]);

        if (! $user->hasAdminTotpPending() || ! filled($user->totp_secret)) {
            return redirect('/admin/security')
                ->with('error', 'Start authenticator setup first.');
        }

        if (! $this->totp->verify((string) $user->totp_secret, (string) $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => ['Invalid authenticator code. Try again.'],
            ]);
        }

        $plainCodes = $this->totp->generateRecoveryCodes();
        $user->totp_recovery_codes = $this->totp->hashRecoveryCodes($plainCodes);
        $user->totp_confirmed_at = now();
        $user->save();

        $this->totp->markSessionPassed();

        return redirect('/admin/security')
            ->with('success', 'Authenticator 2FA is now enabled for your admin account.')
            ->with('totp_recovery_codes_plain', $plainCodes);
    }

    public function cancelSetup(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $user->isAdmin(), 403);

        if ($user->hasAdminTotpEnabled()) {
            return redirect('/admin/security')
                ->with('error', 'Disable authenticator 2FA instead of canceling setup.');
        }

        $user->totp_secret = null;
        $user->totp_confirmed_at = null;
        $user->totp_recovery_codes = null;
        $user->save();

        return redirect('/admin/security')
            ->with('success', 'Authenticator setup canceled.');
    }

    public function disable(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $user->isAdmin(), 403);

        $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'string', 'min:6', 'max:64'],
        ], [
            'password.required' => 'Enter your account password to disable 2FA.',
            'code.required' => 'Enter an authenticator or recovery code.',
        ]);

        if (! $user->hasAdminTotpEnabled()) {
            return redirect('/admin/security')
                ->with('error', 'Authenticator 2FA is not enabled.');
        }

        if (! Hash::check((string) $request->input('password'), (string) $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Your password is incorrect.'],
            ]);
        }

        $code = (string) $request->input('code');
        $validTotp = $this->totp->verify((string) $user->totp_secret, $code);
        $recoveryOk = false;

        if (! $validTotp) {
            [$recoveryOk, $remaining] = $this->totp->consumeRecoveryCode(
                is_array($user->totp_recovery_codes) ? $user->totp_recovery_codes : [],
                $code
            );
            if ($recoveryOk) {
                $user->totp_recovery_codes = $remaining;
            }
        }

        if (! $validTotp && ! $recoveryOk) {
            throw ValidationException::withMessages([
                'code' => ['Invalid authenticator or recovery code.'],
            ]);
        }

        $user->totp_secret = null;
        $user->totp_confirmed_at = null;
        $user->totp_recovery_codes = null;
        $user->save();

        $this->totp->clearSessionPassed();

        return redirect('/admin/security')
            ->with('success', 'Authenticator 2FA has been disabled.');
    }

    public function regenerateRecoveryCodes(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $user->isAdmin(), 403);

        $request->validate([
            'password' => ['required', 'string'],
            'code' => ['required', 'string', 'min:6', 'max:12'],
        ]);

        if (! $user->hasAdminTotpEnabled()) {
            return redirect('/admin/security')
                ->with('error', 'Authenticator 2FA is not enabled.');
        }

        if (! Hash::check((string) $request->input('password'), (string) $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Your password is incorrect.'],
            ]);
        }

        if (! $this->totp->verify((string) $user->totp_secret, (string) $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => ['Invalid authenticator code.'],
            ]);
        }

        $plainCodes = $this->totp->generateRecoveryCodes();
        $user->totp_recovery_codes = $this->totp->hashRecoveryCodes($plainCodes);
        $user->save();

        return redirect('/admin/security')
            ->with('success', 'New recovery codes generated. Store them somewhere safe — they will not be shown again.')
            ->with('totp_recovery_codes_plain', $plainCodes);
    }
}
