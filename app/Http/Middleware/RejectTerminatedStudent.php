<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RejectTerminatedStudent
{
    /**
     * Log out students whose account has been marked terminated and block access.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user instanceof User
            && $user->role === 'student'
            && (bool) $user->student_terminated) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Your student account has been terminated. You can no longer access the system. Contact the administration if you need assistance.');
        }

        return $next($request);
    }
}
