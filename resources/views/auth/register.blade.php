@extends('layouts.landing')

@section('title', 'Register - ' . $settings['system_name'])
@section('description', 'Create your account to start your learning journey')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/login-page-themes.css') }}?v=5">
    <link rel="stylesheet" href="{{ asset('css/login-witch.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/login-ghosto.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/login-hohoho.css') }}?v=1">
@endsection

@section('content')
<div class="min-h-screen flex flex-col lg:flex-row">
    @include('partials.login-left-panel')

    <!-- Right Section - Register Form -->
    <div class="w-full lg:w-1/3 flex items-center justify-center p-8 bg-gray-50 overflow-y-auto">
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

            <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg mb-6">
                <a href="{{ url('/login') }}" class="flex-1 py-3 px-4 text-base font-bold rounded-md transition-colors duration-200 text-gray-600 hover:text-gray-800 text-center">
                    LOGIN
                </a>
                <span class="flex-1 py-3 px-4 text-base font-bold rounded-md bg-white text-gray-900 shadow-sm text-center">
                    REGISTER
                </span>
            </div>

            <div class="text-center mb-4">
                <a href="{{ url('/login') }}" class="text-sm text-teal-600 hover:text-teal-800 underline">
                    Already have an account? Login here
                </a>
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
                </style>

                <form method="POST" action="{{ url('/register') }}" class="space-y-6">
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
                                   class="block w-full pl-10 pr-3 py-4 border-2 border-gray-300 text-lg font-medium rounded-lg placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200"
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
                                   class="block w-full pl-10 pr-3 py-4 border-2 border-gray-300 text-lg font-medium rounded-lg placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200 {{ isset($application) && $application ? 'bg-gray-100' : '' }}"
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
                                    class="block w-full pl-10 pr-3 py-4 border-2 border-gray-300 text-lg font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200"
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

                    <!-- Course / Program -->
                    <div class="mb-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                </svg>
                            </div>
                            <input id="course" name="course" type="text" maxlength="255"
                                   value="{{ old('course') }}"
                                   placeholder="Course / Program (e.g. BS Information Technology)"
                                   class="block w-full pl-10 pr-3 py-4 border-2 border-gray-300 text-lg font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200">
                        </div>
                        @if($errors->has('course'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->first('course') }}</p>
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
                                   class="block w-full pl-10 pr-3 py-4 border-2 border-gray-300 text-lg font-medium rounded-lg placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200"
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
                                   class="block w-full pl-10 pr-10 py-4 border-2 border-gray-300 text-lg font-medium rounded-lg placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200"
                                   placeholder="Password">
                            <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg id="password-eye" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="password-eye-slash" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
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
                                   class="block w-full pl-10 pr-10 py-4 border-2 border-gray-300 text-lg font-medium rounded-lg placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200"
                                   placeholder="Confirm Password">
                            <button type="button" id="toggle-password-confirmation" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg id="password-confirmation-eye" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="password-confirmation-eye-slash" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
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
                    <button type="submit"
                            class="w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-lg shadow-lg text-lg font-bold text-white bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 transition-all duration-200">
                        REGISTER
                    </button>
                </form>
            </div>

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

        // Password visibility toggle
        const togglePasswordBtn = document.getElementById('toggle-password');
        const togglePasswordConfirmationBtn = document.getElementById('toggle-password-confirmation');
        const passwordEye = document.getElementById('password-eye');
        const passwordEyeSlash = document.getElementById('password-eye-slash');
        const passwordConfirmationEye = document.getElementById('password-confirmation-eye');
        const passwordConfirmationEyeSlash = document.getElementById('password-confirmation-eye-slash');

        if (togglePasswordBtn) {
            togglePasswordBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                passwordEye.classList.toggle('hidden');
                passwordEyeSlash.classList.toggle('hidden');
            });
        }

        if (togglePasswordConfirmationBtn) {
            togglePasswordConfirmationBtn.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                passwordConfirmationEye.classList.toggle('hidden');
                passwordConfirmationEyeSlash.classList.toggle('hidden');
            });
        }
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
                // Removed toast notification - validation still happens but no toast shown
                return false;
            }

            return true;
        }

        // Debounced email validation (removed toast notification)
        let emailValidationTimeout;
        function debouncedEmailValidation(email) {
            clearTimeout(emailValidationTimeout);
            emailValidationTimeout = setTimeout(() => {
                if (email.trim()) {
                    // Validate silently without showing toast
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

                    // Removed toast notification for password match
                } else if (confirmPassword.length > 0 && password !== confirmPassword) {
                    matchIcon.textContent = '✗';
                    matchIcon.className = 'text-red-500 mr-2';
                    matchCheck.className = 'flex items-center validation-check invalid';
                    matchText.textContent = 'Passwords do not match';
                    passwordMatchDiv.classList.add('error');

                    // Removed toast notification for password mismatch
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
