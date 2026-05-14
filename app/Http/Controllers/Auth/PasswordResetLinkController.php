<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MailConfigService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        // Get system settings for the view
        $settings = [
            'system_name' => \App\Models\Setting::get('system_name', 'System'),
            'system_logo' => \App\Models\Setting::get('system_logo'),
        ];

        return view('auth.forgot-password', [
            'settings' => $settings
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Check if the email exists in the database
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'This email address is not registered in our system.']);
        }

        try {
            // Ensure mail configuration is up to date from settings before sending reset link
            MailConfigService::configure();

            // We will send the password reset link to this user. Once we have attempted
            // to send the link, we will examine the response then see the message we
            // need to show to the user. Finally, we'll send out a proper response.
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset link', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            // Helpful fallback for local development: write email to logs instead of failing.
            if (app()->environment('local')) {
                config(['mail.default' => 'log']);

                try {
                    $status = Password::sendResetLink($request->only('email'));
                } catch (\Throwable $e2) {
                    Log::error('Failed to send password reset link even with log mailer', [
                        'email' => $request->email,
                        'error' => $e2->getMessage(),
                        'exception' => get_class($e2),
                    ]);

                    return back()->withInput($request->only('email'))
                        ->withErrors(['email' => 'Unable to send reset link right now. Please contact the administrator.']);
                }
            } else {
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => 'Unable to send reset link right now. Please contact the administrator.']);
            }
        }

        if ($status === Password::RESET_LINK_SENT) {
            $mailer = (string) config('mail.default', 'smtp');
            $response = back()->with('status', 'We have emailed your password reset link. Please check your inbox.');
            if (in_array($mailer, ['log', 'array'], true)) {
                $response->with(
                    'mail_driver_notice',
                    'Mail is configured to use the "'.$mailer.'" driver, so messages are not delivered to real inboxes. An administrator should set Admin → Settings → Mail Driver to SMTP (or your provider) and configure credentials. With the log driver, the message is written to storage/logs/laravel.log.'
                );
            }

            return $response;
        }

        // If user requests again too quickly, keep UX successful and ask them to check inbox.
        if ($status === Password::RESET_THROTTLED) {
            return back()->with('status', 'A reset link was already requested recently. Please check your email inbox (and spam).');
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
