<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\AdminTotp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminTotpVerified
{
    public function __construct(private AdminTotp $totp) {}

    /**
     * Require confirmed TOTP for admins who enabled it (after password login).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isAdmin() || ! $user->hasAdminTotpEnabled()) {
            return $next($request);
        }

        if ($this->totp->sessionPassed()) {
            return $next($request);
        }

        if ($request->routeIs('admin.totp.challenge*', 'logout')) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Two-factor authentication is required.',
                'redirect' => url('/admin/two-factor-challenge'),
            ], 423);
        }

        return redirect()->guest(url('/admin/two-factor-challenge'));
    }
}
