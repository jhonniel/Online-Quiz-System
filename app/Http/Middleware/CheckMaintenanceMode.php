<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if maintenance mode is enabled
        $maintenanceMode = Setting::get('maintenance_mode', 'disabled');

        if ($maintenanceMode === 'enabled') {
            // Allow admin users to access the system during maintenance
            if (auth()->check() && auth()->user()->role === 'admin') {
                return $next($request);
            }

            // Allow admin login page during maintenance
            if ($request->is('login') && $request->isMethod('post')) {
                // Check if the user trying to login is an admin
                $credentials = $request->only('email', 'password');
                if (auth()->attempt($credentials)) {
                    if (auth()->user()->role === 'admin') {
                        return $next($request);
                    } else {
                        auth()->logout();
                    }
                }
            }

            // Show maintenance page for all other requests
            $settings = Setting::all()->keyBy('key');
            return response()->view('maintenance', compact('settings'), 503);
        }

        return $next($request);
    }
}
