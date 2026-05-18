<?php

namespace App\Exceptions;

use App\Models\ErrorLog;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Report exceptions without attaching huge context that can trigger a second OOM while logging.
     */
    public function report(Throwable $e): void
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        if ($this->isMemoryExhaustion($e)) {
            try {
                Log::error('Memory limit exceeded', [
                    'exception' => $e::class,
                    'message' => substr($e->getMessage(), 0, 500),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            } catch (Throwable) {
                // Avoid cascading failures while already out of memory.
            }

            return;
        }

        parent::report($e);
    }

    private function isMemoryExhaustion(Throwable $e): bool
    {
        return str_contains($e->getMessage(), 'Allowed memory size')
            || ($e instanceof \Error && str_contains($e->getMessage(), 'memory'));
    }

    /**
     * Handle TokenMismatchException (419): redirect back to form with message so user gets a fresh CSRF token.
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenMismatchException) {
            $path = $request->path();
            if ($request->isMethod('POST') && in_array($path, ['login', 'register', 'forgot-password', 'reset-password'], true)) {
                $redirectUrl = $path === 'login' ? '/login' : ($path === 'register' ? '/register' : ($path === 'forgot-password' ? '/forgot-password' : '/login'));
                return redirect($redirectUrl)
                    ->with('error', 'Your session has expired. Please try again.')
                    ->withInput($request->except('password', 'password_confirmation', '_token'));
            }
        }

        // Record 404/500 errors to DB for analytics page.
        // Keep this extremely defensive to avoid cascading failures if DB is unavailable.
        try {
            $statusCode = null;
            if ($e instanceof HttpExceptionInterface) {
                $statusCode = $e->getStatusCode();
            } else {
                $statusCode = 500;
            }

            if (in_array($statusCode, [404, 500], true)) {
                $path = '/' . ltrim((string) $request->path(), '/');

                ErrorLog::create([
                    'status_code' => $statusCode,
                    'exception_class' => get_class($e),
                    'message' => substr((string) $e->getMessage(), 0, 1000),
                    'method' => substr((string) $request->method(), 0, 10),
                    'path' => substr($path, 0, 2048),
                    'user_id' => Auth::id(),
                    'ip_address' => substr((string) $request->ip(), 0, 45),
                    'user_agent' => substr((string) $request->userAgent(), 0, 1024),
                ]);
            }
        } catch (\Throwable $t) {
            Log::warning('Failed to record error log', [
                'error' => $t->getMessage(),
            ]);
        }

        return parent::render($request, $e);
    }
}

