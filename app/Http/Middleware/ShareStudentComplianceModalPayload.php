<?php

namespace App\Http\Middleware;

use App\Support\StudentComplianceRequirements;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareStudentComplianceModalPayload
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $payload = [];

        if (auth()->check()) {
            $user = auth()->user();

            if ($user->isStudent()
                && $request->session()->get('student_rules_regulations_pending') !== true
                && ! $this->shouldSuppressForRoute($request)) {
                $payload = StudentComplianceRequirements::incompleteItemsFor($user);
            }
        }

        View::share('studentComplianceModalPayload', $payload);

        return $next($request);
    }

    private function shouldSuppressForRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        return in_array($routeName, [
            'user.nda.index',
            'user.nda.store',
            'user.nda.preview',
            'user.nda.generate-pdf',
            'user.tor',
        ], true);
    }
}
