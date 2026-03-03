<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function home()
    {
        if (auth()->check()) {
            // Always route to user dashboard after login; admin dashboard is not shown in user navigation
            return redirect('/dashboard');
        }

        return redirect('/login');
    }
}
