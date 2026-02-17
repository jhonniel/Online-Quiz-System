<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ url('/login') }}">
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

            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pr-10"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
                <button type="button" id="toggle-password-login" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg id="password-login-eye" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="password-login-eye-slash" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>

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
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ url('/forgot-password') }}">
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

            // Password visibility toggle
            const togglePasswordBtn = document.getElementById('toggle-password-login');
            const passwordEye = document.getElementById('password-login-eye');
            const passwordEyeSlash = document.getElementById('password-login-eye-slash');

            // Define toggle function globally as fallback
            window.togglePassword = function() {
                if (passwordInput) {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    if (passwordEye) passwordEye.classList.toggle('hidden');
                    if (passwordEyeSlash) passwordEyeSlash.classList.toggle('hidden');
                }
            };

            if (togglePasswordBtn && passwordEye && passwordEyeSlash) {
                togglePasswordBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.togglePassword();
                });
            }

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
