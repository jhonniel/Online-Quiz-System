<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserActivity;
use App\Models\UserSession;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Update or create user session
            $sessionId = session()->getId();
            $session = UserSession::createOrUpdateSession($user, $sessionId);

            // Log page view activity (but not for AJAX requests or API calls)
            if (!$request->ajax() && !$request->is('api/*')) {
                UserActivity::logActivity($user, 'page_view', $request->route()?->getName(), [
                    'method' => $request->method(),
                    'route' => $request->route()?->getName(),
                    'parameters' => $request->route()?->parameters()
                ]);
            }
        }

        $response = $next($request);

        // Update session activity after response
        if (Auth::check()) {
            $sessionId = session()->getId();
            $session = UserSession::where('session_id', $sessionId)->first();
            if ($session) {
                $session->updateActivity();
            }
        }

        return $response;
    }
}
