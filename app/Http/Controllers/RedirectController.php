<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function home()
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Any admin role user should always land on admin dashboard.
            if ($user instanceof User && $user->isAdmin()) {
                return redirect('/admin/dashboard');
            }

            // Everyone else goes to the user dashboard.
            return redirect('/dashboard');
        }

        return redirect('/login');
    }
}
