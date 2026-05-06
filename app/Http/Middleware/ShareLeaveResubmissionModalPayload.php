<?php

namespace App\Http\Middleware;

use App\Services\LeaveRequestStaleResubmissionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareLeaveResubmissionModalPayload
{
    /**
     * Share leave resubmission modal data on every browser request so any layout can show the reminder
     * after refresh or navigation (not only via the layouts.user view composer lifecycle).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $payload = [];

        if (auth()->check()) {
            $user = auth()->user();
            if (in_array($user->role, ['employee', 'student'], true)) {
                $payload = app(LeaveRequestStaleResubmissionService::class)
                    ->pendingResubmissionModalPayloadForUser($user);
            }
        }

        View::share('leaveResubmissionModalPayload', $payload);

        return $next($request);
    }
}
