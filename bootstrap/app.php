<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Models\ErrorLog;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'admin.permission' => \App\Http\Middleware\CheckAdminPermission::class,
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
        // Custom 404 for route not found (still redirect to landing for now)
        $exceptions->render(function (\Symfony\Component\Routing\Exception\RouteNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Route not found'], 404);
            }

            return redirect()->route('landing.index');
        });

        // Render 500 errors with custom error page
        $exceptions->render(function (\Throwable $e, $request) {
            // Skip HTTP exceptions that are not 500 (like 403, 404, etc.)
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $statusCode = $e->getStatusCode();
                // Don't handle 403, 404, etc. - let them show normally
                if ($statusCode !== 500) {
                    return null;
                }
            }
            
            // For non-HTTP exceptions (which are treated as 500), show custom 500 error page
            // This catches all server errors (500) and unhandled exceptions
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Internal server error',
                    'message' => 'An unexpected error occurred. Please contact the administrator for assistance.'
                ], 500);
            }
            
            // Return 500 error page
            return response()->view('errors.500', [
                'exception' => $e,
                'code' => 500,
                'title' => 'Internal Server Error',
                'message' => 'An unexpected error occurred. Please contact the administrator for assistance.'
            ], 500);
        });

        // Log HTTP and server errors into error_logs table
        $exceptions->report(function (\Throwable $e) {
            try {
                // Only log once per request and only for HTTP / server style errors
                $request = request();
                if (!$request) {
                    return;
                }

                $status = null;
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    $status = $e->getStatusCode();
                } else {
                    $status = 500;
                }

                ErrorLog::create([
                    'status_code' => $status,
                    'exception_class' => get_class($e),
                    'message' => mb_substr($e->getMessage(), 0, 1000),
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'user_id' => optional($request->user())->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => mb_substr($request->userAgent() ?? '', 0, 1024),
                ]);
            } catch (\Throwable $inner) {
                // Never let logging failures break the app
            }
        });
    })
    ->withProviders([
        \App\Providers\SettingsServiceProvider::class,
    ])
    ->create();
