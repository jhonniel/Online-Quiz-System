<?php

namespace App\Http\Middleware;

use App\Models\LeaveRequest;
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

                $payload = $this->excludeCurrentLeaveRequestPage($request, $payload);
            }
        }

        View::share('leaveResubmissionModalPayload', $payload);

        return $next($request);
    }

    /**
     * Do not show the modal while the user is already viewing or editing that request.
     *
     * @param  list<array<string, mixed>>  $payload
     * @return list<array<string, mixed>>
     */
    private function excludeCurrentLeaveRequestPage(Request $request, array $payload): array
    {
        if ($payload === []) {
            return [];
        }

        $routeName = $request->route()?->getName();
        if (! in_array($routeName, ['user.leave-requests.show', 'user.leave-requests.edit'], true)) {
            return $payload;
        }

        $leaveRequest = $request->route('leave_request');
        $currentId = $leaveRequest instanceof LeaveRequest
            ? (int) $leaveRequest->id
            : (int) $leaveRequest;

        if ($currentId <= 0) {
            return $payload;
        }

        return array_values(array_filter(
            $payload,
            fn (array $row): bool => (int) ($row['id'] ?? 0) !== $currentId
        ));
    }
}
