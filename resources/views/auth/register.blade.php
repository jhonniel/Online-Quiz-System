@extends('layouts.landing')

@section('title', 'Register - ' . $settings['system_name'])
@section('description', 'Create your account to start your learning journey')

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
                <h1 class="text-6xl font-black mb-6 text-white drop-shadow-lg animate-float-gentle">Join {{ $settings['system_name'] }}</h1>
                <p class="text-2xl font-semibold text-white drop-shadow-md animate-float-slow">Start your learning journey today</p>
            </div>
        </div>
    </div>

    <!-- Right Section - Register Form -->
    <div class="w-full lg:w-1/3 flex items-center justify-center p-8 bg-gray-50">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                @if($settings['system_logo'])
                    <img src="{{ Storage::url($settings['system_logo']) }}"
                         alt="{{ $settings['system_name'] }}"
                         class="h-16 w-auto mx-auto mb-4">
                @else
                    <div class="h-16 w-16 bg-gradient-to-r from-teal-500 to-cyan-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                @endif
                <h2 class="text-4xl font-black text-gray-900 mb-3 tracking-wide">REGISTER</h2>
                <p class="text-lg font-medium text-gray-700">Create your account to start the quest</p>
            </div>

            @if(isset($application) && $application)
                <!-- Hiring Application Notice -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                Welcome, {{ $application->first_name }}! Your application has been accepted. Please create your account to continue.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Back to Login Link -->
            <div class="text-center mb-6">
                <p class="text-sm text-gray-600">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-teal-600 hover:text-teal-800 font-medium underline">
                        Login here
                    </a>
                </p>
            </div>

            <!-- Register Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <style>
                    .validation-check {
                        transition: all 0.3s ease;
                    }
                    .validation-check.valid {
                        color: #10b981;
                    }
                    .validation-check.invalid {
                        color: #ef4444;
                    }
                    .password-requirements {
                        background-color: #f9fafb;
                        border: 1px solid #e5e7eb;
                        border-radius: 0.375rem;
                        padding: 0.75rem;
                        margin-top: 0.5rem;
                    }
                    .password-match {
                        background-color: #f0f9ff;
                        border: 1px solid #0ea5e9;
                        border-radius: 0.375rem;
                        padding: 0.75rem;
                        margin-top: 0.5rem;
                    }
                    .password-match.error {
                        background-color: #fef2f2;
                        border-color: #ef4444;
                    }

                    /* Floating Animations */
                    @keyframes float-slow {
                        0%, 100% { transform: translateY(0px) rotate(0deg); }
                        50% { transform: translateY(-20px) rotate(1deg); }
                    }
                    @keyframes float-medium {
                        0%, 100% { transform: translateY(0px) rotate(0deg); }
                        50% { transform: translateY(-15px) rotate(-1deg); }
                    }
                    @keyframes float-fast {
                        0%, 100% { transform: translateY(0px) rotate(0deg); }
                        50% { transform: translateY(-10px) rotate(0.5deg); }
                    }
                    @keyframes float-gentle {
                        0%, 100% { transform: translateY(0px); }
                        50% { transform: translateY(-5px); }
                    }
                    @keyframes twinkle {
                        0%, 100% { opacity: 0.3; transform: scale(1); }
                        50% { opacity: 1; transform: scale(1.1); }
                    }

                    .animate-float-slow { animation: float-slow 6s ease-in-out infinite; }
                    .animate-float-medium { animation: float-medium 4s ease-in-out infinite; }
                    .animate-float-fast { animation: float-fast 3s ease-in-out infinite; }
                    .animate-float-gentle { animation: float-gentle 8s ease-in-out infinite; }
                    .animate-twinkle { animation: twinkle 2s ease-in-out infinite; }
                </style>

                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf
                    @if(isset($token) && $token)
                        <input type="hidden" name="acceptance_token" value="{{ $token }}">
                    @endif

                    <!-- Name -->
                    <div class="mb-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input id="name" name="name" type="text" autocomplete="name" required
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200"
                                   placeholder="Full Name"
                                   value="{{ old('name') }}">
                        </div>
                        @if($errors->has('name'))
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $errors->first('name') }}
                            </p>
                        @endif
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="email" required
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200 {{ isset($application) && $application ? 'bg-gray-100' : '' }}"
                                   placeholder="Email Address"
                                   value="{{ old('email', $email ?? '') }}"
                                   {{ isset($application) && $application ? 'readonly' : '' }}>
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

                    <!-- University -->
                    <div class="mb-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <select name="university_id" id="university_id" required
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200"
                                    onchange="toggleUniversityInput()">
                                <option value="">Select University</option>
                                @foreach(\App\Models\University::active()->orderBy('name')->get() as $university)
                                    <option value="{{ $university->id }}" {{ old('university_id') == $university->id ? 'selected' : '' }}>
                                        {{ $university->name }}
                                    </option>
                                @endforeach
                                <option value="new" {{ old('university_id') == 'new' ? 'selected' : '' }}>+ Add New University</option>
                            </select>
                        </div>
                        @if($errors->has('university_id'))
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $errors->first('university_id') }}
                            </p>
                        @endif
                    </div>

                    <!-- New University Input (Hidden by default) -->
                    <div id="new-university-input" class="mb-4 hidden">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <input id="new_university_name" name="new_university_name" type="text"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200"
                                   placeholder="Enter University Name"
                                   value="{{ old('new_university_name') }}">
                        </div>
                        @if($errors->has('new_university_name'))
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $errors->first('new_university_name') }}
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
                            <input id="password" name="password" type="password" autocomplete="new-password" required
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200"
                                   placeholder="Password">
                        </div>

                        <div id="password-requirements" class="password-requirements">
                            <div class="text-sm font-medium text-gray-700 mb-2">Password Requirements:</div>
                            <div id="length-check" class="flex items-center validation-check">
                                <span id="length-icon" class="text-gray-400 mr-2">✗</span>
                                <span>At least 8 characters</span>
                            </div>
                            <div id="uppercase-check" class="flex items-center validation-check">
                                <span id="uppercase-icon" class="text-gray-400 mr-2">✗</span>
                                <span>One uppercase letter</span>
                            </div>
                            <div id="lowercase-check" class="flex items-center validation-check">
                                <span id="lowercase-icon" class="text-gray-400 mr-2">✗</span>
                                <span>One lowercase letter</span>
                            </div>
                            <div id="number-check" class="flex items-center validation-check">
                                <span id="number-icon" class="text-gray-400 mr-2">✗</span>
                                <span>One number</span>
                            </div>
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

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200"
                                   placeholder="Confirm Password">
                        </div>

                        <div id="password-match" class="password-match hidden">
                            <div id="match-check" class="flex items-center validation-check">
                                <span id="match-icon" class="text-gray-400 mr-2">✗</span>
                                <span id="match-text">Passwords match</span>
                            </div>
                        </div>

                        @if($errors->has('password_confirmation'))
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $errors->first('password_confirmation') }}
                            </p>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-gradient-to-r from-teal-500 to-cyan-600 text-white py-3 px-4 rounded-lg font-bold text-lg hover:from-teal-600 hover:to-cyan-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                        Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Include Toast Component -->
<x-toast />

<script>
    // University field toggle function
    function toggleUniversityInput() {
        const universitySelect = document.getElementById('university_id');
        const newUniversityField = document.getElementById('new-university-input');
        const newUniversityInput = document.getElementById('new_university_name');

        if (universitySelect.value === 'new') {
            newUniversityField.classList.remove('hidden');
            newUniversityInput.required = true;
        } else {
            newUniversityField.classList.add('hidden');
            newUniversityInput.required = false;
            newUniversityInput.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const universitySelect = document.getElementById('university_id');
        const newUniversityField = document.getElementById('new-university-input');
        const newUniversityInput = document.getElementById('new_university_name');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const passwordMatchDiv = document.getElementById('password-match');
        const emailInput = document.getElementById('email');

        // Email validation for dummy emails
        function validateEmail(email) {
            const dummyEmails = [
                'test@test.com', 'test@example.com', 'dummy@dummy.com',
                'fake@fake.com', 'sample@sample.com', 'demo@demo.com',
                'user@user.com', 'admin@admin.com', 'temp@temp.com',
                'example@example.com', 'sample@sample.com', 'demo@demo.com'
            ];

            const isDummy = dummyEmails.some(dummy =>
                email.toLowerCase().includes(dummy.toLowerCase())
            );

            if (isDummy) {
                ToastNotification.warning('Please use a real email address, not a dummy email.', 4000);
                return false;
            }

            return true;
        }

        // Debounced email validation
        let emailValidationTimeout;
        function debouncedEmailValidation(email) {
            clearTimeout(emailValidationTimeout);
            emailValidationTimeout = setTimeout(() => {
                if (email.trim()) {
                    validateEmail(email);
                }
            }, 1000); // Wait 1 second after user stops typing
        }

        // Password validation
        function validatePassword(password) {
            const checks = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password)
            };

            // Update visual indicators
            updateCheck('length-check', 'length-icon', checks.length);
            updateCheck('uppercase-check', 'uppercase-icon', checks.uppercase);
            updateCheck('lowercase-check', 'lowercase-icon', checks.lowercase);
            updateCheck('number-check', 'number-icon', checks.number);

            return Object.values(checks).every(check => check);
        }

        function updateCheck(checkId, iconId, isValid) {
            const checkElement = document.getElementById(checkId);
            const iconElement = document.getElementById(iconId);

            if (isValid) {
                iconElement.textContent = '✓';
                iconElement.className = 'text-green-500 mr-2';
                checkElement.className = 'flex items-center validation-check valid';
            } else {
                iconElement.textContent = '✗';
                iconElement.className = 'text-red-500 mr-2';
                checkElement.className = 'flex items-center validation-check invalid';
            }
        }

        // Password match validation
        function validatePasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (confirmPassword.length > 0) {
                passwordMatchDiv.classList.remove('hidden');
                const matchCheck = document.getElementById('match-check');
                const matchIcon = document.getElementById('match-icon');
                const matchText = document.getElementById('match-text');

                if (password === confirmPassword && password.length > 0) {
                    matchIcon.textContent = '✓';
                    matchIcon.className = 'text-green-500 mr-2';
                    matchCheck.className = 'flex items-center validation-check valid';
                    matchText.textContent = 'Passwords match';
                    passwordMatchDiv.classList.remove('error');

                    // Show success toast for password match
                    if (password.length >= 8) {
                        ToastNotification.success('Your passwords match perfectly!', 3000);
                    }
                } else if (confirmPassword.length > 0 && password !== confirmPassword) {
                    matchIcon.textContent = '✗';
                    matchIcon.className = 'text-red-500 mr-2';
                    matchCheck.className = 'flex items-center validation-check invalid';
                    matchText.textContent = 'Passwords do not match';
                    passwordMatchDiv.classList.add('error');

                    // Show error toast for password mismatch
                    ToastNotification.error('The passwords you entered do not match. Please try again.', 4000);
                }
            } else {
                passwordMatchDiv.classList.add('hidden');
            }
        }

        // Form validation before submit
        function validateForm() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const universityId = universitySelect.value;
            const newUniversityName = newUniversityInput.value;
            const email = emailInput.value;

            let isValid = true;

            // Email validation
            if (!validateEmail(email)) {
                isValid = false;
            }

            // Password validation
            if (!validatePassword(password)) {
                isValid = false;
                ToastNotification.error('Password must be at least 8 characters with uppercase, lowercase, and number.', 5000);
            }

            // Password match validation
            if (password !== confirmPassword) {
                isValid = false;
                ToastNotification.error('The passwords you entered do not match. Please try again.', 4000);
            }

            // University validation
            if (!universityId) {
                isValid = false;
                ToastNotification.warning('Please select a university/school.', 4000);
            }

            if (universityId === 'new' && !newUniversityName.trim()) {
                isValid = false;
                ToastNotification.warning('Please enter a university/school name.', 4000);
            }

            if (!isValid) {
                return false;
            }

            // Show success toast before submission
            ToastNotification.success('Creating your account...', 2000);
            return true;
        }

        // Event listeners
        universitySelect.addEventListener('change', toggleUniversityInput);
        emailInput.addEventListener('input', function() {
            debouncedEmailValidation(this.value);
        });
        emailInput.addEventListener('blur', function() {
            if (this.value.trim()) {
                validateEmail(this.value);
            }
        });
        passwordInput.addEventListener('input', function() {
            validatePassword(this.value);
            validatePasswordMatch();
        });
        confirmPasswordInput.addEventListener('input', validatePasswordMatch);

        // Form submission validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
            }
        });

        // Initial setup
        toggleUniversityInput();
    });
</script>

@endsection
