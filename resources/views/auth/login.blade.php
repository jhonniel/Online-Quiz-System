<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <!-- Include Toast Component -->
    <x-toast />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const loginForm = document.querySelector('form');

            // Email validation function
            function validateEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            // Real-time email validation
            let emailValidationTimeout;
            emailInput.addEventListener('input', function() {
                clearTimeout(emailValidationTimeout);
                emailValidationTimeout = setTimeout(() => {
                    const email = this.value.trim();
                    if (email.length > 0 && !validateEmail(email)) {
                        ToastNotification.warning('Please enter a valid email address format.', 3000);
                    }
                }, 1000);
            });

            // Real-time password validation
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                if (password.length > 0 && password.length < 6) {
                    ToastNotification.warning('Password should be at least 6 characters long.', 3000);
                }
            });

            // Form submission validation
            loginForm.addEventListener('submit', function(e) {
                const email = emailInput.value.trim();
                const password = passwordInput.value;

                // Validate email format
                if (!validateEmail(email)) {
                    e.preventDefault();
                    ToastNotification.error('Please enter a valid email address.', 4000);
                    return false;
                }

                // Validate password length
                if (password.length < 6) {
                    e.preventDefault();
                    ToastNotification.error('Password must be at least 6 characters long.', 4000);
                    return false;
                }

                // Show loading toast
                ToastNotification.info('Logging in...', 2000);
            });

            // Check for validation errors and show appropriate toasts
            function checkForErrors() {
                // Try multiple selectors to find error messages
                const emailErrorSelectors = [
                    '#email + .mt-2 .text-red-600 li',
                    '#email + .mt-2 .text-red-600',
                    '#email + .mt-2 li',
                    '#email + .mt-2'
                ];

                const passwordErrorSelectors = [
                    '#password + .mt-2 .text-red-600 li',
                    '#password + .mt-2 .text-red-600',
                    '#password + .mt-2 li',
                    '#password + .mt-2'
                ];

                let emailError = null;
                let passwordError = null;

                // Find email error
                for (const selector of emailErrorSelectors) {
                    emailError = document.querySelector(selector);
                    if (emailError && emailError.textContent.trim()) {
                        console.log('Found email error with selector:', selector, 'Text:', emailError.textContent.trim());
                        break;
                    }
                }

                // Find password error
                for (const selector of passwordErrorSelectors) {
                    passwordError = document.querySelector(selector);
                    if (passwordError && passwordError.textContent.trim()) {
                        console.log('Found password error with selector:', selector, 'Text:', passwordError.textContent.trim());
                        break;
                    }
                }

                if (emailError) {
                    const errorText = emailError.textContent.trim();
                    if (errorText.includes('not yet activated') || errorText.includes('admin approval')) {
                        ToastNotification.warning('Your account is not yet activated. Please wait for admin approval.', 6000);
                    } else if (errorText.includes('No account found') || errorText.includes('email address')) {
                        ToastNotification.error('No account found with this email address. Please check your email or register a new account.', 6000);
                    } else if (errorText.includes('failed') || errorText.includes('credentials')) {
                        ToastNotification.error('Invalid email or password. Please check your credentials.', 5000);
                    } else {
                        ToastNotification.error(errorText, 5000);
                    }
                }

                if (passwordError) {
                    const errorText = passwordError.textContent.trim();
                    if (errorText.includes('incorrect') || errorText.includes('wrong password')) {
                        ToastNotification.error('The password you entered is incorrect. Please try again.', 5000);
                    } else {
                        ToastNotification.error(errorText, 5000);
                    }
                }
            }

            // Check for errors immediately and after a short delay
            checkForErrors();
            setTimeout(checkForErrors, 100);
            setTimeout(checkForErrors, 500);

            // Watch for dynamically added error messages
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' || mutation.type === 'characterData') {
                        checkForErrors();
                    }
                });
            });

            // Observe the form for changes
            const form = document.querySelector('form');
            if (form) {
                observer.observe(form, {
                    childList: true,
                    subtree: true,
                    characterData: true
                });
            }

            // Check for success messages
            const sessionStatus = document.querySelector('.mb-4 .text-green-600');
            if (sessionStatus) {
                ToastNotification.success(sessionStatus.textContent.trim(), 4000);
            }

            // Check for errors in URL parameters (fallback)
            const urlParams = new URLSearchParams(window.location.search);
            const errorParam = urlParams.get('error');
            if (errorParam) {
                if (errorParam === 'activation') {
                    ToastNotification.warning('Your account is not yet activated. Please wait for admin approval.', 6000);
                } else if (errorParam === 'credentials') {
                    ToastNotification.error('Invalid email or password. Please check your credentials.', 5000);
                } else if (errorParam === 'notfound') {
                    ToastNotification.error('No account found with this email address. Please check your email or register a new account.', 6000);
                }
            }

        });
    </script>
</x-guest-layout>
