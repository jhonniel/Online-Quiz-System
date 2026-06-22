<?php

namespace App\Http\Middleware;

use App\Jobs\LogUserActivityJob;
use App\Models\User;
use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /** Minimum seconds between session row updates per user/session. */
    private const SESSION_TOUCH_SECONDS = 120;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $this->touchUserSessionThrottled($user, $request);
        }

        if ($this->shouldLogPageView($request, $user)) {
            $this->dispatchPageViewLog($request, $user);
        }

        return $next($request);
    }

    private function shouldLogPageView(Request $request, mixed $user): bool
    {
        if ($request->ajax() || $request->is('api/*')) {
            return false;
        }

        if ($user instanceof User && $user->isAdmin()) {
            return false;
        }

        return true;
    }

    private function dispatchPageViewLog(Request $request, mixed $user): void
    {
        $url = (string) $request->fullUrl();
        $ua = (string) ($request->userAgent() ?? '');
        $ip = $request->ip() !== null ? (string) $request->ip() : null;

        try {
            LogUserActivityJob::dispatch(
                $user instanceof User ? (int) $user->id : null,
                'page_view',
                $request->route()?->getName(),
                [
                    'method' => $request->method(),
                    'route' => $request->route()?->getName(),
                    'parameters' => $request->route()?->parameters(),
                    'is_guest' => ! $user,
                ],
                $url !== '' ? $url : null,
                $ip,
                $ua !== '' ? $ua : null,
            )->afterResponse();
        } catch (\Throwable) {
            // Page views should not break when the queue is unavailable.
        }
    }

    private function touchUserSessionThrottled(User $user, Request $request): void
    {
        $sessionId = session()->getId();
        $cacheKey = 'user_session_touch:'.$user->id.':'.md5($sessionId);

        if (! Cache::add($cacheKey, 1, now()->addSeconds(self::SESSION_TOUCH_SECONDS))) {
            return;
        }

        UserSession::createOrUpdateSession($user, $sessionId);
    }
}
