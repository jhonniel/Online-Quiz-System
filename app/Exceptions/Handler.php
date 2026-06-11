<?php

namespace App\Exceptions;

use App\Models\ErrorLog;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
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
                    'message' => substr($this->publicSafeErrorMessage($e), 0, 1000),
                    'method' => substr((string) $request->method(), 0, 10),
                    'path' => substr($path, 0, 2048),
                    'user_id' => Auth::id(),
                    'ip_address' => substr((string) $request->ip(), 0, 45),
                    'user_agent' => substr((string) $request->userAgent(), 0, 1024),
                ]);

                try {
                    app(\App\Services\NetworkGraph\NetworkGraphRecorder::class)->recordError([
                        'status_code' => $statusCode,
                        'path' => $path,
                        'method' => (string) $request->method(),
                        'user_id' => Auth::id(),
                    ]);
                } catch (\Throwable) {
                    // ignore graph failures
                }
            }
        } catch (\Throwable $t) {
            Log::warning('Failed to record error log', [
                'error' => $t->getMessage(),
            ]);
        }

        if (! config('app.debug') && $this->shouldSanitizeJsonResponse($request, $e)) {
            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            return response()->json([
                'message' => $status >= 500 ? 'Server error.' : 'Request could not be completed.',
            ], $status);
        }

        $response = parent::render($request, $e);

        if (! config('app.debug') && $request->expectsJson() && $response->getStatusCode() >= 500) {
            return response()->json(['message' => 'Server error.'], 500);
        }

        return $response;
    }

    private function shouldSanitizeJsonResponse($request, Throwable $e): bool
    {
        if (! $request->expectsJson()) {
            return false;
        }

        if ($e instanceof ValidationException || $e instanceof TokenMismatchException) {
            return false;
        }

        return $e instanceof QueryException
            || ! $e instanceof HttpExceptionInterface
            || $e->getStatusCode() >= 500;
    }

    private function publicSafeErrorMessage(Throwable $e): string
    {
        if ($e instanceof QueryException) {
            return 'Database error';
        }

        $message = (string) $e->getMessage();

        if (preg_match('/SQLSTATE\\[/i', $message)) {
            return 'Database error';
        }

        return $message;
    }
}

