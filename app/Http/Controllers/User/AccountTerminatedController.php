<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AccountTerminatedController extends Controller
{
    /** Fragment shown in the browser URL (not sent to the server). */
    public const URL_FRAGMENT = 'restricted';

    public static function url(): string
    {
        return url('/access#'.self::URL_FRAGMENT);
    }

    public function show(Request $request)
    {
        $user = $request->user();
        if (! $user instanceof User || $user->role !== 'student') {
            return redirect('/home');
        }

        if (! (bool) $user->student_terminated) {
            return redirect()->route('user.dashboard');
        }

        return response()
            ->view('user.account-terminated', [
                'targetUrl' => self::url(),
                'urlFragment' => self::URL_FRAGMENT,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
