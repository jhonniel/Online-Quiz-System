@extends('layouts.landing')

@section('title', 'Login - ' . $settings['system_name'])
@section('description', 'Login to access your quiz dashboard')

@section('content')
<div class="min-h-screen flex">
    <!-- Left Section - Illustrative Background -->
    <div class="hidden lg:flex lg:w-2/3 bg-gradient-to-br from-teal-500 to-cyan-600 relative overflow-hidden">
        <!-- Decorative Shapes with Floating Animation -->
        <div class="absolute top-0 left-0 w-96 h-96 bg-yellow-300 rounded-full -translate-x-48 -translate-y-24 opacity-80 animate-float-slow"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-teal-300 rounded-full translate-x-40 translate-y-20 opacity-60 animate-float-medium"></div>

        <!-- Additional Floating Elements -->
        <div class="absolute top-1/4 left-1/4 w-4 h-4 bg-white rounded-full opacity-60 animate-float-fast animate-twinkle"></div>
        <div class="absolute top-3/4 right-1/3 w-6 h-6 bg-pink-200 rounded-full opacity-50 animate-float-slow"></div>
        <div class="absolute top-1/2 left-1/2 w-3 h-3 bg-yellow-200 rounded-full opacity-70 animate-float-medium animate-twinkle"></div>
        <div class="absolute bottom-1/4 left-1/3 w-5 h-5 bg-white rounded-full opacity-40 animate-float-fast"></div>

        <!-- Twinkling Stars -->
        <div class="absolute top-1/6 right-1/4 w-2 h-2 bg-white rounded-full opacity-80 animate-twinkle"></div>
        <div class="absolute top-2/3 left-1/6 w-1 h-1 bg-yellow-300 rounded-full opacity-90 animate-twinkle"></div>
        <div class="absolute bottom-1/3 right-1/6 w-2 h-2 bg-white rounded-full opacity-70 animate-twinkle"></div>
        <div class="absolute top-1/3 right-2/3 w-1 h-1 bg-pink-300 rounded-full opacity-85 animate-twinkle"></div>
        <div class="absolute bottom-2/3 left-2/3 w-1 h-1 bg-yellow-200 rounded-full opacity-75 animate-twinkle"></div>

        <!-- Illustration -->
        <div class="relative z-10 flex items-center justify-center w-full">
            <div class="text-center text-white">
                <!-- Person Illustration -->
                <div class="relative mb-8 animate-float-gentle">
                    <!-- Person Group - All elements move together -->
                    <div class="w-32 h-32 mx-auto relative animate-float-slow">
                        <!-- Head -->
                        <div class="w-16 h-16 bg-pink-200 rounded-full mx-auto mb-2"></div>
                        <!-- Body -->
                        <div class="w-20 h-16 bg-green-300 rounded-t-2xl mx-auto"></div>
                        <!-- Legs -->
                        <div class="flex justify-center space-x-2 mt-1">
                            <div class="w-8 h-12 bg-gray-700 rounded-b-lg"></div>
                            <div class="w-8 h-12 bg-gray-700 rounded-b-lg"></div>
                        </div>

                        <!-- Laptop - Part of the person group -->
                        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
                            <div class="w-20 h-12 bg-gray-800 rounded-lg">
                                <div class="w-16 h-8 bg-white rounded mx-auto mt-1 flex items-center justify-center">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Decorative Elements - Move separately from person -->
                    <div class="absolute -left-8 top-12 animate-float-slow">
                        <div class="w-8 h-8 bg-pink-300 rounded-full"></div>
                        <div class="w-4 h-4 bg-teal-400 rounded-full ml-4 -mt-2 animate-float-fast"></div>
                    </div>

                    <div class="absolute -right-8 top-16 animate-float-medium">
                        <div class="w-6 h-12 bg-gray-600 rounded-full"></div>
                    </div>
                </div>

                <!-- Welcome Text -->
                <h1 class="text-6xl font-black mb-6 text-white drop-shadow-lg animate-float-gentle">Welcome to {{ $settings['system_name'] }}</h1>
                <p class="text-2xl font-semibold text-white drop-shadow-md animate-float-slow">Your learning journey starts here</p>
            </div>
        </div>
    </div>

    <!-- Right Section - Login Form -->
    <div class="w-full lg:w-1/3 flex items-center justify-center p-8 bg-gray-50">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                @if($settings['system_logo'])
                    <img src="{{ $settings['system_logo_url'] ?? '' }}"
                         alt="{{ $settings['system_name'] }}"
                         class="h-16 w-auto mx-auto mb-4">
                @else
                    <div class="h-16 w-16 bg-gradient-to-r from-teal-500 to-cyan-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                @endif
                <h2 id="main-heading" class="text-4xl font-black text-gray-900 mb-3 tracking-wide">LOGIN</h2>
                <p id="main-subtitle" class="text-lg font-medium text-gray-700">Sign in to your account</p>
            </div>

                <!-- Tab Navigation -->
                <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg mb-6">
                    <button type="button" id="login-tab" class="flex-1 py-3 px-4 text-base font-bold rounded-md transition-colors duration-200 bg-white text-gray-900 shadow-sm">
                        LOGIN
                    </button>
                    <a href="{{ url('/register') }}" id="register-tab" class="flex-1 py-3 px-4 text-base font-bold rounded-md transition-colors duration-200 text-gray-600 hover:text-gray-800 text-center">
                        REGISTER
                    </a>
                </div>

                <!-- Fallback register link in case JavaScript fails -->
                <div class="text-center mb-4">
                    <a href="{{ url('/register') }}" class="text-sm text-teal-600 hover:text-teal-800 underline">
                        Don't have an account? Register here
                    </a>
                </div>


                <!-- Login Form -->
                <div id="login-form" class="bg-white rounded-2xl shadow-xl p-8">
                @if(session('error'))
                    <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                        {{ session('error') }}
                    </div>
                @endif
                <form id="login-form-element" class="space-y-6" action="{{ url('/login') }}" method="POST">
                    @csrf

                    <!-- Error Hint Area -->
                    <div id="login-error-hint" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span id="login-error-text" class="text-sm text-red-700 font-medium"></span>
                        </div>
                    </div>

                <!-- Email -->
                <div class="mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required
                               class="block w-full pl-10 pr-3 py-4 border-2 border-gray-300 rounded-lg placeholder-gray-500 text-lg font-medium focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200"
                               placeholder="Enter your email address"
                               value="{{ old('email') }}">
                    </div>
                    @if($errors->has('email'))
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $errors->first('email') }}
                        </p>
                    @endif
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="block w-full pl-10 pr-10 py-4 border-2 border-gray-300 rounded-lg placeholder-gray-500 text-lg font-medium focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200"
                               placeholder="Enter your password">
                        <button type="button" onclick="return togglePassword(event);" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors duration-200" aria-label="Toggle password visibility">
                            <svg id="eye-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @if($errors->has('password'))
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $errors->first('password') }}
                        </p>
                    @endif
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                               class="h-5 w-5 text-teal-600 focus:ring-teal-500 border-gray-300 rounded transition-colors">
                        <label for="remember" class="ml-3 block text-base font-bold text-gray-800 cursor-pointer">
                            Remember me
                        </label>
                    </div>

                    <div class="text-base">
                        <a href="{{ url('/forgot-password') }}" class="font-bold text-teal-600 hover:text-teal-500 transition-colors duration-200">
                            Forgot password?
                        </a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mb-4">
                    <button type="submit"
                            class="w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-lg shadow-lg text-lg font-bold text-white bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 transition-all duration-200">
                        LOGIN
                    </button>
                </div>

                <!-- Error Message Below Login Button -->
                <div id="login-error-below-button" class="mb-6">
                    @if($errors->has('login'))
                        <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm text-red-700 font-medium">{{ $errors->first('login') }}</span>
                            </div>
                        </div>
                    @elseif(session('errors') && session('errors')->has('login'))
                        <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm text-red-700 font-medium">{{ session('errors')->first('login') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
                </form>
                </div>



            <!-- Back to Home -->
            <div class="mt-6 text-center">
                <a href="{{ url('/') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Password toggle functionality for login - defined globally
function togglePassword(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');

    if (!passwordInput) {
        console.error('Password input not found');
        return false;
    }

    if (!eyeIcon) {
        console.error('Eye icon not found');
        return false;
    }

    // Store current value to preserve it
    const currentValue = passwordInput.value;

    // Toggle password visibility
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        // Show eye-slash icon (password is visible)
        eyeIcon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
        `;
    } else {
        passwordInput.type = 'password';
        // Show normal eye icon (password is hidden)
        eyeIcon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        `;
    }

    // Restore value (in case browser cleared it)
    passwordInput.value = currentValue;

    // Focus back on input
    passwordInput.focus();

    console.log('Password type changed to:', passwordInput.type, 'Value length:', passwordInput.value.length);

    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    // Add loading state to submit button
    const form = document.querySelector('form');
    const submitButton = document.querySelector('button[type="submit"]');

    if (form && submitButton) {
        form.addEventListener('submit', function() {
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Signing in...
            `;
        });
    }

    // Also add event listener for password toggle button as fallback
    const togglePasswordBtn = document.querySelector('button[onclick="togglePassword()"]');
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Toggle button clicked via event listener');
            togglePassword();
        });
    } else {
        // Try alternative selector
        const passwordField = document.getElementById('password');
        if (passwordField) {
            const parentDiv = passwordField.closest('.relative');
            if (parentDiv) {
                const toggleBtn = parentDiv.querySelector('button[type="button"]');
                if (toggleBtn) {
                    toggleBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Toggle button clicked via alternative selector');
                        togglePassword();
                    });
                }
            }
        }
    }
});

    // Simple Error Hint System
    function showErrorHint(errorText, isLogin = true) {
        if (isLogin) {
            // Show error below the LOGIN button
            const errorBelowButton = document.getElementById('login-error-below-button');
            if (errorBelowButton && errorText) {
                errorBelowButton.innerHTML = `
                    <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm text-red-700 font-medium">${errorText}</span>
                        </div>
                    </div>
                `;
            }
        } else {
            // For register form, use the existing error hint
            const errorHint = document.getElementById('register-error-hint');
            const errorTextElement = document.getElementById('register-error-text');
            if (errorHint && errorTextElement && errorText) {
                errorTextElement.textContent = errorText;
                errorHint.classList.remove('hidden');
            }
        }
    }

    function hideErrorHint(isLogin = true) {
        if (isLogin) {
            // Clear error below the LOGIN button
            const errorBelowButton = document.getElementById('login-error-below-button');
            if (errorBelowButton) {
                errorBelowButton.innerHTML = '';
            }
        } else {
            const errorHint = document.getElementById('register-error-hint');
            if (errorHint) {
                errorHint.classList.add('hidden');
            }
        }
    }

    function checkForErrors() {
        // Check for login error specifically (below login button)
        const loginErrorBelowButton = document.querySelector('#login-error-below-button .text-red-700');
        if (loginErrorBelowButton) {
            const errorText = loginErrorBelowButton.textContent.trim();
            if (errorText) {
                // Error already displayed below button, no need to show again
                return;
            }
        }

        // Check for login error in the form (from server-side validation)
        const loginErrorElement = document.querySelector('#login-form .text-red-700');
        if (loginErrorElement) {
            const errorText = loginErrorElement.textContent.trim();
            if (errorText) {
                showErrorHint(errorText, true);
                return;
            }
        }

        // Find all error messages on the page
        const allErrorElements = document.querySelectorAll('.text-red-600, .text-red-500');

        allErrorElements.forEach((errorElement) => {
            const errorText = errorElement.textContent.trim();

            if (errorText) {
                // Determine if this is a login or register error based on context
                const isLoginError = errorElement.closest('#login-form') !== null;
                const isRegisterError = errorElement.closest('#register-form') !== null;

                if (isLoginError) {
                    showErrorHint(errorText, true);
                } else if (isRegisterError) {
                    showErrorHint(errorText, false);
                } else {
                    // Default to login if we can't determine context
                    showErrorHint(errorText, true);
                }
            }
        });
    }

    // Check for errors immediately and after delays
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

    // Observe the forms for changes
    const loginFormElement = document.getElementById('login-form');
    const registerFormElement = document.getElementById('register-form');
    if (loginFormElement) {
        observer.observe(loginFormElement, {
            childList: true,
            subtree: true,
            characterData: true
        });
    }
    if (registerFormElement) {
        observer.observe(registerFormElement, {
            childList: true,
            subtree: true,
            characterData: true
        });
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

    // Real-time validation for login form
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const loginFormElement = document.getElementById('login-form-element');

    if (emailInput) {
        // Email validation function
        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Real-time email validation and hide error hint when typing
        let emailValidationTimeout;
        emailInput.addEventListener('input', function() {
            // Hide error hint when user starts typing
            hideErrorHint(true);

            clearTimeout(emailValidationTimeout);
            emailValidationTimeout = setTimeout(() => {
                const email = this.value.trim();
                if (email.length > 0 && !validateEmail(email)) {
                    ToastNotification.warning('Please enter a valid email address format.', 3000);
                }
            }, 1000);
        });
    }

    if (passwordInput) {
        // Real-time password validation and hide error hint when typing
        passwordInput.addEventListener('input', function() {
            // Hide error hint when user starts typing
            hideErrorHint(true);

            const password = this.value;
            if (password.length > 0 && password.length < 6) {
                ToastNotification.warning('Password should be at least 6 characters long.', 3000);
            }
        });
    }

    if (loginFormElement) {
        // Form submission validation
        loginFormElement.addEventListener('submit', function(e) {
            const email = emailInput ? emailInput.value.trim() : '';
            const password = passwordInput ? passwordInput.value : '';

            // Validate email format
            if (email && !validateEmail(email)) {
                e.preventDefault();
                ToastNotification.error('Please enter a valid email address.', 4000);
                return false;
            }

            // Validate password length
            if (password && password.length < 6) {
                e.preventDefault();
                ToastNotification.error('Password must be at least 6 characters long.', 4000);
                return false;
            }

            // Show loading toast
            ToastNotification.info('Logging in...', 2000);

            // Store form data for error detection after page reload
            sessionStorage.setItem('loginAttempt', JSON.stringify({
                email: email,
                timestamp: Date.now()
            }));
        });
    }

    // Check if we just submitted a login form (for error detection after page reload)
    const loginAttempt = sessionStorage.getItem('loginAttempt');
    if (loginAttempt) {
        const attempt = JSON.parse(loginAttempt);
        const timeDiff = Date.now() - attempt.timestamp;

        // If the page was loaded within 5 seconds of a login attempt, check for errors
        if (timeDiff < 5000) {
            console.log('Login attempt detected, checking for errors...');
            setTimeout(checkForErrors, 100);
            setTimeout(checkForErrors, 500);
            setTimeout(checkForErrors, 1000);
        }

        // Clear the stored attempt
        sessionStorage.removeItem('loginAttempt');
    }

    // Tab switching functionality
    console.log('Login page JavaScript loaded');
    const loginTab = document.getElementById('login-tab');
    const registerTab = document.getElementById('register-tab');

    console.log('Login tab found:', !!loginTab);
    console.log('Register tab found:', !!registerTab);

    if (loginTab) {
        loginTab.addEventListener('click', function() {
            console.log('Login tab clicked'); // Debug log
            // Keep login tab active (it's already the default)
            loginTab.className = 'flex-1 py-3 px-4 text-base font-bold rounded-md transition-colors duration-200 bg-white text-gray-900 shadow-sm';
            registerTab.className = 'flex-1 py-3 px-4 text-base font-bold rounded-md transition-colors duration-200 text-gray-600 hover:text-gray-800';
        });
    }

    if (registerTab) {
        registerTab.addEventListener('click', function(e) {
            console.log('Register tab clicked - navigating to register page'); // Debug log
            // No need to prevent default since it's now a proper link
            // The link will handle the navigation automatically
        });
    } else {
        console.error('Register tab element not found!');
    }


    // Password toggle functionality for registration
    function toggleRegPassword() {
        const passwordInput = document.getElementById('reg_password');
        const eyeIcon = document.getElementById('reg-eye-icon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
            `;
        } else {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            `;
        }
    }

    // University input toggle functionality
    function toggleUniversityInput() {
        const universitySelect = document.getElementById('reg_university_id');
        const newUniversityInput = document.getElementById('new-university-input');
        const newUniversityNameInput = document.getElementById('new_university_name');

        if (universitySelect.value === 'new') {
            newUniversityInput.classList.remove('hidden');
            newUniversityNameInput.required = true;
        } else {
            newUniversityInput.classList.add('hidden');
            newUniversityNameInput.required = false;
            newUniversityNameInput.value = '';
        }
    }

    // Initialize university input state on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleUniversityInput();

        // Add event listeners to register form inputs to hide error hints
        const regNameInput = document.getElementById('reg_name');
        const regEmailInput = document.getElementById('reg_email');
        const regPasswordInput = document.getElementById('reg_password');
        const regConfirmPasswordInput = document.getElementById('reg_password_confirmation');

        if (regNameInput) {
            regNameInput.addEventListener('input', function() {
                hideErrorHint(false);
            });
        }

        if (regEmailInput) {
            regEmailInput.addEventListener('input', function() {
                hideErrorHint(false);
            });
        }

        if (regPasswordInput) {
            regPasswordInput.addEventListener('input', function() {
                hideErrorHint(false);
            });
        }

        if (regConfirmPasswordInput) {
            regConfirmPasswordInput.addEventListener('input', function() {
                hideErrorHint(false);
            });
        }

        // Handle login form submission
        const loginForm = document.getElementById('login-form-element');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                // For now, let the form submit normally to avoid CSRF issues
                // TODO: Fix AJAX submission later
                return true;

                // Try AJAX first, fallback to regular form submission
                e.preventDefault();

                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.innerHTML;

                // Show loading state
                submitButton.disabled = true;
                submitButton.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Signing in...
                `;

                // Ensure CSRF token is in FormData
                const csrfToken = this.querySelector('input[name="_token"]')?.value;
                if (csrfToken) {
                    formData.set('_token', csrfToken);
                }

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(async response => {
                    const data = await response.json();

                    if (!response.ok) {
                        // Show error message below login button
                        const errorMessage = data.message || 'An error occurred during login. Please try again.';
                        showErrorHint(errorMessage, true);

                        // Reset button state
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                        return;
                    }

                    if (data.success) {
                        ToastNotification.success(data.message);
                        // Redirect after successful login
                        setTimeout(() => {
                            window.location.href = data.redirect_url || '/dashboard';
                        }, 1000);
                    } else {
                        // Show error message below login button
                        showErrorHint(data.message, true);

                        // Reset button state
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Login error:', error);

                    // If it's a CSRF token issue, try regular form submission
                    if (error.message.includes('419') || error.message.includes('CSRF')) {
                        console.log('CSRF token issue detected, falling back to regular form submission');

                        // Reset button state
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;

                        // Remove the event listener and submit normally
                        this.removeEventListener('submit', arguments.callee);
                        this.submit();
                        return;
                    }

                    let errorMessage = 'An error occurred during login. Please try again.';
                    showErrorHint(errorMessage, true);

                    // Reset button state
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                });
            });
        }
    });
</script>

<style>
    /* Floating Space Animations */
    @keyframes float-slow {
        0%, 100% {
            transform: translateY(0px) translateX(0px) rotate(0deg);
        }
        25% {
            transform: translateY(-20px) translateX(10px) rotate(1deg);
        }
        50% {
            transform: translateY(-10px) translateX(-5px) rotate(-1deg);
        }
        75% {
            transform: translateY(-15px) translateX(8px) rotate(0.5deg);
        }
    }

    @keyframes float-medium {
        0%, 100% {
            transform: translateY(0px) translateX(0px) rotate(0deg);
        }
        33% {
            transform: translateY(-15px) translateX(-8px) rotate(-0.5deg);
        }
        66% {
            transform: translateY(-25px) translateX(12px) rotate(1deg);
        }
    }

    @keyframes float-fast {
        0%, 100% {
            transform: translateY(0px) translateX(0px) rotate(0deg);
        }
        20% {
            transform: translateY(-12px) translateX(6px) rotate(0.8deg);
        }
        40% {
            transform: translateY(-8px) translateX(-4px) rotate(-0.6deg);
        }
        60% {
            transform: translateY(-18px) translateX(10px) rotate(1.2deg);
        }
        80% {
            transform: translateY(-6px) translateX(-8px) rotate(-0.4deg);
        }
    }

    @keyframes float-gentle {
        0%, 100% {
            transform: translateY(0px) rotate(0deg);
        }
        50% {
            transform: translateY(-8px) rotate(0.3deg);
        }
    }

    @keyframes space-drift {
        0%, 100% {
            transform: translateX(0px) translateY(0px) rotate(0deg);
        }
        25% {
            transform: translateX(15px) translateY(-10px) rotate(2deg);
        }
        50% {
            transform: translateX(-10px) translateY(-20px) rotate(-1deg);
        }
        75% {
            transform: translateX(20px) translateY(-5px) rotate(1.5deg);
        }
    }

    @keyframes twinkle {
        0%, 100% {
            opacity: 0.3;
            transform: scale(1);
        }
        50% {
            opacity: 1;
            transform: scale(1.2);
        }
    }

    /* Animation Classes */
    .animate-float-slow {
        animation: float-slow 8s ease-in-out infinite;
    }

    .animate-float-medium {
        animation: float-medium 6s ease-in-out infinite;
    }

    .animate-float-fast {
        animation: float-fast 4s ease-in-out infinite;
    }

    .animate-float-gentle {
        animation: float-gentle 3s ease-in-out infinite;
    }

    .animate-space-drift {
        animation: space-drift 10s ease-in-out infinite;
    }

    .animate-twinkle {
        animation: twinkle 2s ease-in-out infinite;
    }

    /* Staggered animations for multiple elements */
    .animate-float-slow:nth-child(1) { animation-delay: 0s; }
    .animate-float-slow:nth-child(2) { animation-delay: 1s; }
    .animate-float-slow:nth-child(3) { animation-delay: 2s; }

    .animate-float-medium:nth-child(1) { animation-delay: 0.5s; }
    .animate-float-medium:nth-child(2) { animation-delay: 1.5s; }
    .animate-float-medium:nth-child(3) { animation-delay: 2.5s; }

    .animate-float-fast:nth-child(1) { animation-delay: 0.2s; }
    .animate-float-fast:nth-child(2) { animation-delay: 0.8s; }
    .animate-float-fast:nth-child(3) { animation-delay: 1.4s; }
</style>
@endsection
