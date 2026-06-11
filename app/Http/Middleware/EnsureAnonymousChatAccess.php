<?php

namespace App\Http\Middleware;

use App\Models\AnonymousChatRoom;
use App\Support\AnonymousChatEligibility;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAnonymousChatAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $room = $request->route('anonymousChatRoom');

        if (! $room instanceof AnonymousChatRoom) {
            abort(404);
        }

        $user = $request->user();
        if (! $user || ! $room->includesUser($user->id)) {
            abort(404);
        }

        $peer = $room->peerUser($user);
        if ($peer && AnonymousChatEligibility::isBlockedBetween($user->id, $peer->id)) {
            abort(404);
        }

        return $next($request);
    }
}
