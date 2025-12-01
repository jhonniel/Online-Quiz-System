@extends('layouts.landing')

@section('content')
@php
    // Ensure settings are available from session, view data, or use defaults
    $settings = $settings ?? session('settings', [
        'hiring_process_description' => '',
        'hiring_instructions' => '',
        'hiring_stages' => '',
        'hiring_application_url' => 'hiring/apply',
    ]);
    
    // Ensure all required keys exist
    $settings = array_merge([
        'hiring_process_description' => '',
        'hiring_instructions' => '',
        'hiring_stages' => '',
        'hiring_application_url' => 'hiring/apply',
    ], $settings);
    
    // Get errors - Laravel should automatically share $errors with all views via ShareErrorsFromSession middleware
    // But we'll also check session as fallback
    if (!isset($errors)) {
        $errors = session()->get('errors');
    }
    
    // Ensure $errors is always a ViewErrorBag instance
    if (!$errors || !($errors instanceof \Illuminate\Support\ViewErrorBag)) {
        $errors = new \Illuminate\Support\ViewErrorBag();
    }
    
    // Set formErrors for use in the form
    $formErrors = $errors;
    
    // Debug: Log if we have errors
    if ($formErrors->any()) {
        \Log::info('Form errors found in view', [
            'errors' => $formErrors->all(), 
            'has_email_error' => $formErrors->has('email'),
            'email_error' => $formErrors->has('email') ? $formErrors->first('email') : null
        ]);
    }
@endphp
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        @if(session('success'))
            <!-- Success Message (shown first when form is submitted) -->
            <div id="success-message" class="mb-6 bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl p-8 shadow-lg animate-fade-in">
                <div class="text-center">
                    <!-- Animated Checkmark Icon -->
                    <div class="flex justify-center mb-6">
                        <div class="relative">
                            <div class="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-75"></div>
                            <div class="relative bg-green-500 rounded-full p-4">
                                <svg class="h-12 w-12 text-white animate-bounce" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Success Title -->
                    <h2 class="text-3xl font-bold text-green-800 mb-4 animate-slide-up">
                        Application Submitted Successfully! 🎉
                    </h2>

                    <!-- Main Message -->
                    <div class="bg-white rounded-lg p-6 mb-6 shadow-sm border border-green-200">
                        <p class="text-lg text-gray-800 mb-4 leading-relaxed">
                            Your application has been successfully submitted and received by our team.
                        </p>
                        
                        <!-- Next Steps -->
                        <div class="text-left space-y-4 mt-6">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-1">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">Wait for Email Notification</h3>
                                    <p class="text-gray-700 text-sm">
                                        Please check your email inbox. You will receive an email notification once your application has been reviewed.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-1">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">Application Review Process</h3>
                                    <p class="text-gray-700 text-sm">
                                        Our HR team will carefully review your application. If your application is approved, you will be contacted to proceed to the interview stage.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-1">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 mb-1">Interview Scheduling</h3>
                                    <p class="text-gray-700 text-sm">
                                        When your application is approved, HR will contact you via email to schedule your interview. Please keep an eye on your inbox!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center justify-center space-x-2 text-blue-800">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm font-medium">Thank you for your interest in joining our team!</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Confetti Canvas -->
            <canvas id="confetti-canvas" class="fixed top-0 left-0 w-full h-full pointer-events-none z-50" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999;"></canvas>
        @else
            <!-- Header (only shown when form is visible) -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Join Our Team</h1>
                <p class="text-xl text-gray-600">Submit your application to start your journey with us</p>
            </div>
        @endif


        @if(isset($position) && $position)
            <!-- Position Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $position->title }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    @if($position->department)
                        <div>
                            <span class="text-sm font-medium text-gray-500">Department:</span>
                            <span class="text-sm text-gray-900 ml-2">{{ $position->department }}</span>
                        </div>
                    @endif
                    @if($position->location)
                        <div>
                            <span class="text-sm font-medium text-gray-500">Location:</span>
                            <span class="text-sm text-gray-900 ml-2">{{ $position->location }}</span>
                        </div>
                    @endif
                    @if($position->employment_type)
                        <div>
                            <span class="text-sm font-medium text-gray-500">Employment Type:</span>
                            <span class="text-sm text-gray-900 ml-2">{{ $position->employment_type }}</span>
                        </div>
                    @endif
                    @if($position->salary_min || $position->salary_max)
                        <div>
                            <span class="text-sm font-medium text-gray-500">Salary:</span>
                            <span class="text-sm text-gray-900 ml-2">
                                @if($position->salary_min && $position->salary_max)
                                    ${{ number_format($position->salary_min) }} - ${{ number_format($position->salary_max) }}
                                @elseif($position->salary_min)
                                    From ${{ number_format($position->salary_min) }}
                                @elseif($position->salary_max)
                                    Up to ${{ number_format($position->salary_max) }}
                                @endif
                            </span>
                        </div>
                    @endif
                </div>

                @if($position->description)
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Description</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $position->description }}</p>
                    </div>
                @endif

                @if($position->requirements)
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Requirements</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $position->requirements }}</p>
                    </div>
                @endif

                @if($position->responsibilities)
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Responsibilities</h3>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $position->responsibilities }}</p>
                    </div>
                @endif

                @if($position->application_deadline)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600">
                            <strong>Application Deadline:</strong> {{ $position->application_deadline->format('F j, Y') }}
                        </p>
                    </div>
                @endif
            </div>
        @endif

        <!-- Hiring Process Info -->
        @if(($settings['hiring_process_description'] ?? '') || ($settings['hiring_stages'] ?? ''))
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                @if(!empty($settings['hiring_process_description'] ?? ''))
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Our Hiring Process</h2>
                    <p class="text-gray-600 mb-4">{{ $settings['hiring_process_description'] ?? '' }}</p>
                @endif

                @if(!empty($settings['hiring_stages'] ?? ''))
                    <h3 class="text-md font-semibold text-gray-900 mb-3">Process Stages</h3>
                    <div class="space-y-2">
                        @foreach(explode("\n", $settings['hiring_stages'] ?? '') as $index => $stage)
                            @if(trim($stage))
                                <div class="flex items-center text-gray-700">
                                    <span class="flex-shrink-0 w-6 h-6 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xs font-medium mr-3">
                                        {{ $index + 1 }}
                                    </span>
                                    <span>{{ trim($stage) }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                @if(!empty($settings['hiring_instructions'] ?? ''))
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <h3 class="text-md font-semibold text-gray-900 mb-2">Instructions</h3>
                        <p class="text-gray-600">{{ $settings['hiring_instructions'] ?? '' }}</p>
                    </div>
                @endif
            </div>
        @endif

        @if(!session('success'))
        <!-- Application Form -->
        <div id="application-form-container" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form action="{{ isset($position) && $position ? route('hiring.apply.position.store', $position->slug) : route('hiring.apply') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Name Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                            First Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="first_name" id="first_name" required
                               value="{{ old('first_name') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Last Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="last_name" id="last_name" required
                               value="{{ old('last_name') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" required
                               value="{{ old('email', '') }}"
                               class="w-full px-4 py-2 border {{ $formErrors && $formErrors->has('email') ? 'border-red-500' : 'border-gray-300' }} rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @if($formErrors && $formErrors->has('email'))
                            <p class="mt-1 text-sm text-red-600 font-medium">{{ $formErrors->first('email') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="phone" id="phone" required
                               value="{{ old('phone') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @if($formErrors && $formErrors->has('phone'))
                            <p class="mt-1 text-sm text-red-600">{{ $formErrors->first('phone') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Birth Date -->
                <div>
                    <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Date of Birth
                    </label>
                    <input type="date" name="birth_date" id="birth_date"
                           value="{{ old('birth_date') }}"
                           max="{{ date('Y-m-d', strtotime('-18 years')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-sm text-gray-500">Optional: Your date of birth</p>
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Address
                    </label>
                    <textarea name="address" id="address" rows="3"
                              placeholder="Enter your full address..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('address') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Optional: Your complete address</p>
                </div>

                @if(!isset($position) || !$position)
                    <!-- Position -->
                    <div>
                        <label for="position_applied" class="block text-sm font-medium text-gray-700 mb-2">
                            Position Applied For
                        </label>
                        <input type="text" name="position_applied" id="position_applied"
                               value="{{ old('position_applied') }}"
                               placeholder="e.g., Software Developer, Data Analyst, etc."
                               class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                @else
                    <input type="hidden" name="hiring_position_id" value="{{ $position->id }}">
                @endif

                <!-- Cover Letter -->
                <div>
                    <label for="cover_letter" class="block text-sm font-medium text-gray-700 mb-2">
                        Cover Letter
                    </label>
                    <textarea name="cover_letter" id="cover_letter" rows="6"
                              placeholder="Tell us why you're interested in joining our team..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('cover_letter') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Optional: Share your motivation and why you'd be a great fit.</p>
                </div>

                <!-- Resume Link -->
                <div>
                    <label for="resume_link" class="block text-sm font-medium text-gray-700 mb-2">
                        Resume/CV (Link) <span class="text-red-500">*</span>
                    </label>
                    <input type="url" name="resume_link" id="resume_link" required
                           value="{{ old('resume_link') }}"
                           placeholder="https://example.com/resume.pdf or https://drive.google.com/..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-sm text-gray-500">Provide a link to your resume (Google Drive, Dropbox, personal website, etc.)</p>
                    @if($formErrors && $formErrors->has('resume_link'))
                        <p class="mt-1 text-sm text-red-600">{{ $formErrors->first('resume_link') }}</p>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-500">
                        By submitting this form, you agree to our privacy policy and terms of service.
                    </p>
                    <button type="submit"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Submit Application
                    </button>
                </div>

                <!-- Error Messages (shown below submit button) -->
                @if(isset($errors) && $errors && $errors->any())
                    <div class="mt-4 bg-red-50 border-2 border-red-300 rounded-lg p-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-bold text-red-800 mb-2">Please correct the following errors:</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
            
            <!-- Error Messages (shown below form, outside form tag) -->
            @php
                // Get errors from multiple sources - Laravel should share $errors automatically
                $errorBag = null;
                
                // Try to get errors from Laravel's shared $errors variable first
                if (isset($errors) && $errors instanceof \Illuminate\Support\ViewErrorBag) {
                    $errorBag = $errors;
                } 
                // Fallback to session errors
                elseif (session()->has('errors')) {
                    $errorBag = session()->get('errors');
                }
                
                // Debug logging
                if ($errorBag && method_exists($errorBag, 'any') && $errorBag->any()) {
                    \Log::info('Displaying errors in view', [
                        'error_count' => count($errorBag->all()),
                        'all_errors' => $errorBag->all(),
                        'has_email' => $errorBag->has('email')
                    ]);
                }
            @endphp
            
            @if($errorBag && method_exists($errorBag, 'any') && $errorBag->any())
                <div class="mt-4 bg-red-50 border-2 border-red-300 rounded-lg p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-bold text-red-800 mb-2">Please correct the following errors:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                @foreach($errorBag->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @endif

        @if(!session('success'))
        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Note:</strong> You don't need to create an account to apply. If your application is accepted, you'll receive a link to create your account and proceed to the interview stage.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
<!-- Canvas Confetti Library -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if form was successfully submitted
    @if(session('success'))
        // Trigger confetti animation
        const duration = 3000;
        const animationEnd = Date.now() + duration;
        const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 9999 };

        function randomInRange(min, max) {
            return Math.random() * (max - min) + min;
        }

        const interval = setInterval(function() {
            const timeLeft = animationEnd - Date.now();

            if (timeLeft <= 0) {
                return clearInterval(interval);
            }

            const particleCount = 50 * (timeLeft / duration);
            
            // Launch confetti from left
            confetti({
                ...defaults,
                particleCount,
                origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
            });
            
            // Launch confetti from right
            confetti({
                ...defaults,
                particleCount,
                origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
            });
        }, 250);

        // Big burst at the start
        setTimeout(() => {
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 },
                colors: ['#10b981', '#059669', '#34d399', '#6ee7b7', '#a7f3d0']
            });
        }, 100);

        // Success message animation
        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            successMessage.style.opacity = '0';
            successMessage.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                successMessage.style.transition = 'all 0.6s ease-out';
                successMessage.style.opacity = '1';
                successMessage.style.transform = 'translateY(0)';
            }, 100);
        }
    @endif
});
</script>

<style>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.6s ease-out;
}

.animate-slide-up {
    animation: slide-up 0.8s ease-out;
}
</style>
@endsection

