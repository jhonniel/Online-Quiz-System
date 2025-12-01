@extends('layouts.admin')

@section('page-title', 'Create New User')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Users</span>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Create</span>
        </div>
    </li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create New User</h1>
                <p class="mt-2 text-gray-600">Create a new user account for the quiz system with proper permissions and settings.</p>
            </div>
            <a href="{{ route('users.index') }}">
                <x-formal-button variant="outline" size="md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Users
                </x-formal-button>
            </a>
        </div>
    </div>

    <!-- Main Form Card -->
    <x-formal-card
        title="User Information"
        subtitle="Please provide the required information to create a new user account."
        class="mb-6"
    >
        <form action="{{ route('users.store') }}" method="POST" class="space-y-8">
            @csrf

            <!-- Basic Information Section -->
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Basic Information</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <x-formal-input
                        label="Full Name"
                        name="name"
                        type="text"
                        :required="true"
                        placeholder="Enter the user's full name"
                        :value="old('name')"
                        :error="$errors->first('name') ?? null"
                        help="The complete name of the user"
                        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>'
                    />

                    <!-- Email -->
                    <x-formal-input
                        label="Email Address"
                        name="email"
                        type="email"
                        :required="true"
                        placeholder="Enter the user's email address"
                        :value="old('email')"
                        :error="$errors->first('email') ?? null"
                        help="This will be used for login and notifications"
                        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>'
                    />

                    <!-- Role -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            User Role <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="role" id="role" required
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 appearance-none bg-white">
                                <option value="">Select a role</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                                <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="applicant" {{ old('role') == 'applicant' ? 'selected' : '' }}>Applicant</option>
                                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User (Legacy)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500">Select the role that best describes this user's function</p>
                        @if($errors && $errors->has('role'))
                            <p class="text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $errors->first('role') }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Password -->
                <x-formal-input
                    label="Password"
                    name="password"
                    type="password"
                    :required="true"
                    placeholder="Enter a secure password"
                    :error="$errors->first('password') ?? null"
                    help="Minimum 8 characters with letters and numbers"
                    icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>'
                />
            </div>

            <!-- Account Settings Section -->
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Account Settings</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Password Confirmation -->
                    <x-formal-input
                        label="Confirm Password"
                        name="password_confirmation"
                        type="password"
                        :required="true"
                        placeholder="Confirm the password"
                        :error="$errors->first('password_confirmation') ?? null"
                        help="Re-enter the password to confirm"
                        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>'
                    />

                    <!-- University -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            University/School
                        </label>
                        <div class="relative">
                            <select name="university_id" id="university_select"
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 appearance-none bg-white">
                                <option value="">Select or add new university (optional)</option>
                                @foreach($universities as $university)
                                    <option value="{{ $university->id }}" {{ old('university_id') == $university->id ? 'selected' : '' }}>
                                        {{ $university->name }}
                                    </option>
                                @endforeach
                                <option value="new" class="text-blue-600 font-semibold">+ Add New University</option>
                            </select>

                            <!-- Custom dropdown arrow -->
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Hidden input for new university name -->
                        <input type="text" name="new_university_name" id="new_university_name"
                               class="hidden block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                               placeholder="Enter new university name">

                        <p class="text-sm text-gray-500">Select from existing universities or choose "Add New University" to create one</p>
                        @if($errors && $errors->has('university_id'))
                            <p class="text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $errors->first('university_id') }}
                            </p>
                        @endif
                        @if($errors && $errors->has('new_university_name'))
                            <p class="text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $errors->first('new_university_name') }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Active Status -->
                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-3 block text-sm text-gray-900">
                        <span class="font-medium">Active Account</span>
                        <span class="text-gray-500 block">User can log in and take quizzes</span>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('users.index') }}">
                    <x-formal-button variant="outline" size="md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel
                    </x-formal-button>
                </a>
                <x-formal-button
                    type="submit"
                    variant="primary"
                    size="md"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create User
                </x-formal-button>
            </div>
        </form>
    </x-formal-card>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const universitySelect = document.getElementById('university_select');
    const newUniversityInput = document.getElementById('new_university_name');

    if (universitySelect && newUniversityInput) {
        universitySelect.addEventListener('change', function() {
            console.log('Dropdown changed to:', this.value); // Debug log

            if (this.value === 'new') {
                // Show the text input for new university
                newUniversityInput.classList.remove('hidden');
                newUniversityInput.style.display = 'block';
                newUniversityInput.focus();
                newUniversityInput.required = true;
                console.log('Showing new university input'); // Debug log
            } else {
                // Hide the text input
                newUniversityInput.classList.add('hidden');
                newUniversityInput.style.display = 'none';
                newUniversityInput.value = '';
                newUniversityInput.required = false;
                console.log('Hiding new university input'); // Debug log
            }
        });

        // Handle form submission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (universitySelect.value === 'new' && !newUniversityInput.value.trim()) {
                    e.preventDefault();
                    ToastNotification.warning('Please enter a university name when selecting "Add New University"');
                    newUniversityInput.focus();
                    return false;
                }
            });
        }
    } else {
        console.error('University select or input not found');
    }
});
</script>
@endsection
