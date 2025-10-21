<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'track.activity' => \App\Http\Middleware\TrackUserActivity::class,
            'maintenance' => \App\Http\Middleware\CheckMaintenanceMode::class,
        ]);

        // Add maintenance mode check first, then activity tracking to web middleware group
        $middleware->web(prepend: [
            \App\Http\Middleware\CheckMaintenanceMode::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\TrackUserActivity::class,
        ]);

        // Configure authentication redirect
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\Routing\Exception\RouteNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Route not found'], 404);
            }

            // Redirect to landing page for route not found errors
            return redirect()->route('landing.index');
        });
    })
    ->withProviders([
        \App\Providers\SettingsServiceProvider::class,
    ])
    ->create();
