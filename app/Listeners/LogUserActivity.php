<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\UserActivity;
use App\Models\UserSession;

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

        // Log login activity
        UserActivity::logActivity($user, 'login', 'user_login', [
            'login_method' => 'web',
            'remember' => $event->remember ?? false
        ]);

        // Create user session
        $sessionId = session()->getId();
        UserSession::createOrUpdateSession($user, $sessionId, 'active');
    }

    /**
     * Handle logout event
     */
    public function handleLogout(Logout $event): void
    {
        $user = $event->user;

        if ($user) {
            // Log logout activity
            UserActivity::logActivity($user, 'logout', 'user_logout', [
                'logout_method' => 'web'
            ]);

            // Mark session as offline
            $sessionId = session()->getId();
            $session = UserSession::where('session_id', $sessionId)->first();
            if ($session) {
                $session->markOffline();
            }
        }
    }
}
