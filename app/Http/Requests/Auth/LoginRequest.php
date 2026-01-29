<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // First, try to find the user by email
        $user = \App\Models\User::where('email', $this->email)->first();

        if ($user) {
            // Check if user has a hiring application
            $hiringApplication = \App\Models\HiringApplication::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->latest()
                ->first();

            // If user has a hiring application, only allow login when status is accepted, interview_scheduled, done_interview, or hired
            if (
                $hiringApplication &&
                !in_array($hiringApplication->status, ['accepted', 'interview_scheduled', 'done_interview', 'hired'], true)
            ) {
                throw ValidationException::withMessages([
                    'email' => 'Your application is still under review. You will be able to login once your application is accepted or your interview is scheduled.',
                ]);
            }

            if (!$user->is_approved) {
                // User exists but account is not approved
                throw ValidationException::withMessages([
                    'email' => 'Your account is not yet activated. Please wait for admin approval.',
                ]);
            }
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Check if user exists but password is wrong
            if ($user) {
                throw ValidationException::withMessages([
                    'password' => 'The password you entered is incorrect. Please try again.',
                ]);
            } else {
                throw ValidationException::withMessages([
                    'email' => 'No account found with this email address. Please check your email or register a new account.',
                ]);
            }
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
