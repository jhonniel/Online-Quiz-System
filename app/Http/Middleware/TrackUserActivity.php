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
        $user = Auth::user();

        if ($user) {
            // Update or create user session
            $sessionId = session()->getId();
            UserSession::createOrUpdateSession($user, $sessionId);
        }

        // Log web traffic for both authenticated users and guests (excluding AJAX/API)
        if (!$request->ajax() && !$request->is('api/*')) {
            UserActivity::logActivity($user, 'page_view', $request->route()?->getName(), [
                'method' => $request->method(),
                'route' => $request->route()?->getName(),
                'parameters' => $request->route()?->parameters(),
                'is_guest' => !$user,
            ]);
        }

        $response = $next($request);

        // Update session activity after response
        if ($user) {
            $sessionId = session()->getId();
            $session = UserSession::where('session_id', $sessionId)->first();
            if ($session) {
                $session->updateActivity();
            }
        }

        return $response;
    }
}
