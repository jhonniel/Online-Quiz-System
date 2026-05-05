<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

class RequireStudentRulesRegulationsOnLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        if ($user instanceof User && $user->role === 'student') {
            session()->put('student_rules_regulations_pending', true);
        }
    }
}
