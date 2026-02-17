<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
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

        return parent::render($request, $e);
    }
}

