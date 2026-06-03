<?php

namespace App\Http\Middleware;

use App\Http\Controllers\User\AccountTerminatedController;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                if ($user instanceof User && $user->isAdmin()) {
                    return redirect('/admin/dashboard');
                }

                if ($user instanceof User && $user->role === 'student' && (bool) $user->student_terminated) {
                    return redirect()->to(AccountTerminatedController::url());
                }

                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}

