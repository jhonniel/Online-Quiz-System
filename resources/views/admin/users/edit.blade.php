@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Edit User</h2>
                <p class="mt-1 text-sm text-gray-600">Update user account information.</p>
            </div>

            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <div class="mt-1">
                            <input type="text" name="name" id="name" required
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                   value="{{ old('name', $user->name) }}">
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <div class="mt-1">
                            <input type="email" name="email" id="email" required
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                   value="{{ old('email', $user->email) }}">
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <div class="mt-1">
                            <select name="role" id="role" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrator</option>
                                <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="employee" {{ old('role', $user->role) == 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="applicant" {{ old('role', $user->role) == 'applicant' ? 'selected' : '' }}>Applicant</option>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User (Legacy)</option>
                            </select>
                        </div>
                        @error('role')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="mt-1">
                            <input type="password" name="password" id="password"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                   placeholder="Leave blank to keep current password">
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <div class="mt-1">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                   placeholder="Leave blank to keep current password">
                        </div>
                    </div>

                    <!-- University -->
                    <div>
                        <label for="university_select" class="block text-sm font-medium text-gray-700">University/School</label>
                        <div class="mt-1 relative">
                            <select name="university_id" id="university_select"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <option value="">No university selected</option>
                                @foreach($universities as $university)
                                    <option value="{{ $university->id }}" {{ old('university_id', $user->university_id) == $university->id ? 'selected' : '' }}>
                                        {{ $university->name }}
                                    </option>
                                @endforeach
                                <option value="new" class="text-blue-600 font-semibold">+ Add New University</option>
                            </select>

                            <!-- Hidden input for new university name -->
                            <input type="text" name="new_university_name" id="new_university_name"
                                   class="hidden shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md mt-2 transition-all duration-200"
                                   placeholder="Enter new university name"
                                   value="{{ old('new_university_name') }}">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Select from existing universities or choose "Add New University" to create one</p>
                        @error('university_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('new_university_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Required Training Hours (Time Needed to Acquire) -->
                    <div id="required_training_hours_wrapper"
                         @if(old('role', $user->role) === 'student') style="" @else style="display:none;" @endif>
                        <label for="required_training_hours" class="block text-sm font-medium text-gray-700">
                            Required Training Hours (Time Needed to Acquire)
                        </label>
                        <div class="mt-1 relative">
                            <input type="number"
                                   name="required_training_hours"
                                   id="required_training_hours"
                                   step="0.01"
                                   min="0"
                                   value="{{ old('required_training_hours', $user->required_training_hours) }}"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                   placeholder="e.g. 160 (for 160 hours)">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Optional. Primarily used for <span class="font-semibold">students</span> to indicate the total hours they need to complete via DTR.
                        </p>
                        @error('required_training_hours')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Leave Balances (Employees Only) -->
                    @php
                        $currentYear = now()->year;
                        $leaveBalance = \App\Models\LeaveBalance::where('user_id', $user->id)
                            ->where('year', $currentYear)
                            ->first();
                        $vacationAllowance = old('vacation_allowance', $leaveBalance ? $leaveBalance->vacation_allowance : '');
                        $sickAllowance = old('sick_allowance', $leaveBalance ? $leaveBalance->sick_allowance : '');
                    @endphp
                    <div id="leave_balances_wrapper"
                         @if(old('role', $user->role) === 'employee') style="" @else style="display:none;" @endif>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="vacation_allowance" class="block text-sm font-medium text-gray-700">
                                    Vacation Leave Balance (Days)
                                </label>
                                <div class="mt-1 relative">
                                    <input type="number"
                                           name="vacation_allowance"
                                           id="vacation_allowance"
                                           step="0.01"
                                           min="0"
                                           value="{{ $vacationAllowance }}"
                                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                           placeholder="e.g. 15">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Vacation leave balance for {{ $currentYear }}.
                                </p>
                                @error('vacation_allowance')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="sick_allowance" class="block text-sm font-medium text-gray-700">
                                    Sick Leave Balance (Days)
                                </label>
                                <div class="mt-1 relative">
                                    <input type="number"
                                           name="sick_allowance"
                                           id="sick_allowance"
                                           step="0.01"
                                           min="0"
                                           value="{{ $sickAllowance }}"
                                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                           placeholder="e.g. 10">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Sick leave balance for {{ $currentYear }}.
                                </p>
                                @error('sick_allowance')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Active (user can log in and take quizzes)
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('users.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const universitySelect = document.getElementById('university_select');
    const newUniversityInput = document.getElementById('new_university_name');
    const roleSelect = document.getElementById('role');
    const requiredHoursWrapper = document.getElementById('required_training_hours_wrapper');

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

    // Show Required Training Hours only for students
    if (roleSelect && requiredHoursWrapper) {
        function toggleRequiredHours() {
            if (roleSelect.value === 'student') {
                requiredHoursWrapper.style.display = '';
            } else {
                requiredHoursWrapper.style.display = 'none';
            }
        }

        roleSelect.addEventListener('change', toggleRequiredHours);
        // Initialize on load
        toggleRequiredHours();
    }

    // Show Leave Balances only for employees
    const leaveBalancesWrapper = document.getElementById('leave_balances_wrapper');
    if (roleSelect && leaveBalancesWrapper) {
        function toggleLeaveBalances() {
            if (roleSelect.value === 'employee') {
                leaveBalancesWrapper.style.display = '';
            } else {
                leaveBalancesWrapper.style.display = 'none';
            }
        }

        roleSelect.addEventListener('change', toggleLeaveBalances);
        // Initialize on load
        toggleLeaveBalances();
    }
});
</script>
@endsection
