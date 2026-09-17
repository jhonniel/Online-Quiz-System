<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminTotp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TotpChallengeController extends Controller
{
    public function __construct(private AdminTotp $totp) {}

    public function show(Request $request)
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isAdmin()) {
            return redirect('/home');
        }

        if (! $user->hasAdminTotpEnabled()) {
            return redirect('/admin/dashboard');
        }

        if ($this->totp->sessionPassed()) {
            return redirect()->intended('/admin/dashboard');
        }

        return view('auth.totp-challenge');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isAdmin() || ! $user->hasAdminTotpEnabled()) {
            return redirect('/login');
        }

        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:64'],
        ], [
            'code.required' => 'Enter your authenticator or recovery code.',
        ]);

        $code = (string) $request->input('code');
        $validTotp = $this->totp->verify((string) $user->totp_secret, $code);

        if ($validTotp) {
            $this->totp->markSessionPassed();

            return redirect()->intended('/admin/dashboard');
        }

        [$recoveryOk, $remaining] = $this->totp->consumeRecoveryCode(
            is_array($user->totp_recovery_codes) ? $user->totp_recovery_codes : [],
            $code
        );

        if ($recoveryOk) {
            $user->totp_recovery_codes = $remaining;
            $user->save();
            $this->totp->markSessionPassed();

            return redirect()->intended('/admin/dashboard')
                ->with('warning', 'You signed in with a recovery code. Consider regenerating recovery codes from Security.');
        }

        throw ValidationException::withMessages([
            'code' => ['Invalid authenticator or recovery code.'],
        ]);
    }

    public function cancel(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('info', 'Signed out. Sign in again when you have your authenticator code.');
    }
}
