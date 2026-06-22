<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\UserActivity;
use App\Models\UserSession;
use Illuminate\Support\Facades\Log;

class LogUserActivity
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        try {
            UserActivity::logActivity($user, 'login', 'user_login', [
                'login_method' => 'web',
                'remember' => $event->remember ?? false,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log login activity', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $sessionId = session()->getId();
            UserSession::createOrUpdateSession($user, $sessionId, 'active');
        } catch (\Throwable $e) {
            Log::warning('Failed to create login session record', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle logout event
     */
    public function handleLogout(Logout $event): void
    {
        $user = $event->user;

        if ($user) {
            try {
                UserActivity::logActivity($user, 'logout', 'user_logout', [
                    'logout_method' => 'web',
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to log logout activity', [
                    'user_id' => $user->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                $sessionId = session()->getId();
                $session = UserSession::where('session_id', $sessionId)->first();
                if ($session) {
                    $session->markOffline();
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to mark session offline', [
                    'user_id' => $user->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
