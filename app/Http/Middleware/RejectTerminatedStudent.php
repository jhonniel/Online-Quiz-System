<?php

namespace App\Http\Middleware;

use App\Http\Controllers\User\AccountTerminatedController;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectTerminatedStudent
{
    /**
     * Block navigation for terminated students; show restricted access screen instead.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User
            || $user->role !== 'student'
            || ! (bool) $user->student_terminated) {
            return $next($request);
        }

        if ($request->routeIs('user.account-terminated', 'logout')) {
            return $next($request);
        }

        if ($request->is('access') || $request->is('logout')) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Your student account has been terminated. You cannot access the system.',
            ], 403);
        }

        return redirect()->to(AccountTerminatedController::url());
    }
}
