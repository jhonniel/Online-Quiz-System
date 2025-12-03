@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col min-h-0">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-4 flex-shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">New Leave Request</h1>
                    <p class="text-indigo-100 text-sm">Submit a new leave or work request</p>
                </div>
            </div>
            <a href="{{ route('user.leave-requests.index') }}"
               class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white hover:bg-white/30 transition duration-200">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="flex-1 overflow-y-auto p-4">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <form action="{{ route('user.leave-requests.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Request Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Request Type <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="type" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Request Type</option>
                            <option value="vacation_leave" {{ old('type') == 'vacation_leave' ? 'selected' : '' }}>Vacation Leave</option>
                            <option value="sick_leave" {{ old('type') == 'sick_leave' ? 'selected' : '' }}>Sick Leave</option>
                            <option value="work_from_home" {{ old('type') == 'work_from_home' ? 'selected' : '' }}>Work From Home</option>
                            <option value="absent" {{ old('type') == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="overtime" {{ old('type') == 'overtime' ? 'selected' : '' }}>Overtime</option>
                            <option value="offset" {{ old('type') == 'offset' ? 'selected' : '' }}>Offset</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date" 
                                   value="{{ old('start_date') }}" 
                                   min="{{ date('Y-m-d') }}"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                End Date <span class="text-gray-400">(Optional)</span>
                            </label>
                            <input type="date" name="end_date" id="end_date" 
                                   value="{{ old('end_date') }}" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Leave blank for single day requests</p>
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Reason -->
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason <span class="text-gray-400">(Optional)</span>
                        </label>
                        <textarea name="reason" id="reason" rows="4"
                                  placeholder="Please provide a reason for this request..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('reason') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            Provide additional details about your request. For <strong>Overtime</strong>, if provided, this will appear as the explanation for extra hours. If not provided, it will be left blank.
                        </p>
                        @error('reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Overtime Details (visible only when Request Type = Overtime) -->
                    <div id="overtime-section" class="space-y-4 hidden">
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <h2 class="text-sm font-semibold text-gray-900 mb-2">Overtime Details</h2>
                            <p class="text-xs text-gray-500 mb-3">
                                When requesting <strong>Overtime</strong>, please provide the total hours, the dates covered,
                                and strictly list down the tasks (e.g., ClickUp links) that will be done during the overtime.
                            </p>
                        </div>

                        <!-- Total Overtime Hours -->
                        <div>
                            <label for="overtime_hours" class="block text-sm font-medium text-gray-700 mb-2">
                                Total Overtime Hours (HH:MM) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="overtime_hours" id="overtime_hours"
                                   value="{{ old('overtime_hours') }}"
                                   placeholder="01:20"
                                   class="time-input w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">
                                Enter the total overtime time in <strong>HH:MM</strong> (e.g., 01:00, 02:30). No AM/PM.
                            </p>
                            @error('overtime_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Overtime Dates Text -->
                        <div>
                            <label for="overtime_dates" class="block text-sm font-medium text-gray-700 mb-2">
                                Overtime Dates <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="overtime_dates" id="overtime_dates"
                                   value="{{ old('overtime_dates') }}"
                                   placeholder="e.g., May 10–11, 2025 or specific dates"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">
                                Clearly state the date(s) when the overtime will be rendered.
                            </p>
                            @error('overtime_dates')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Overtime Tasks / ClickUp Link -->
                        <div>
                            <label for="overtime_tasks" class="block text-sm font-medium text-gray-700 mb-2">
                                Tasks / ClickUp Links <span class="text-red-500">*</span>
                            </label>
                            <textarea name="overtime_tasks" id="overtime_tasks" rows="4"
                                      placeholder="Strictly list down tasks listed in ClickUp for Devs via link..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('overtime_tasks') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                List the tasks that justify this overtime, including any ClickUp or ticket links.
                            </p>
                            @error('overtime_tasks')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Offset Details (visible only when Request Type = Offset) -->
                    <div id="offset-section" class="space-y-4 hidden">
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <h2 class="text-sm font-semibold text-gray-900 mb-2">Offset Details</h2>
                            <p class="text-xs text-gray-500 mb-3">
                                When requesting <strong>Offset</strong>, you can specify custom hours to be deducted from your overtime balance.
                                If not specified, it will automatically calculate based on duration (1 day = 08:00).
                            </p>
                        </div>

                        <!-- Offset Hours (Optional) -->
                        <div>
                            <label for="offset_hours" class="block text-sm font-medium text-gray-700 mb-2">
                                Hours to Deduct (HH:MM) <span class="text-gray-400">(Optional)</span>
                            </label>
                            <input type="text" name="offset_hours" id="offset_hours"
                                   value="{{ old('offset_hours') }}"
                                   placeholder="08:00"
                                   class="time-input w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">
                                Enter custom hours to deduct in <strong>HH:MM</strong> format (e.g., 08:00, 04:30). 
                                If left blank, it will automatically calculate as <strong>1 day = 08:00</strong> based on your request duration.
                            </p>
                            @error('offset_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Work From Home Details (visible only when Request Type = Work From Home) -->
                    <div id="wfh-section" class="space-y-4 hidden">
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <h2 class="text-sm font-semibold text-gray-900 mb-2">Work From Home Details</h2>
                            <p class="text-xs text-gray-500 mb-3">
                                When requesting <strong>Work From Home</strong>, please specify your remote setup and list the tasks
                                you will be working on (e.g., ClickUp links).
                            </p>
                        </div>

                        <!-- Work Mode -->
                        <div>
                            <label for="wfh_mode" class="block text-sm font-medium text-gray-700 mb-2">
                                Work Mode <span class="text-red-500">*</span>
                            </label>
                            <select name="wfh_mode" id="wfh_mode"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select Mode</option>
                                <option value="working_remotely" {{ old('wfh_mode') == 'working_remotely' ? 'selected' : '' }}>
                                    Working remotely
                                </option>
                                <option value="request_to_be_excused" {{ old('wfh_mode') == 'request_to_be_excused' ? 'selected' : '' }}>
                                    Request to be excused
                                </option>
                            </select>
                            @error('wfh_mode')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remote Address -->
                        <div>
                            <label for="wfh_address" class="block text-sm font-medium text-gray-700 mb-2">
                                Remote Address <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="wfh_address" id="wfh_address"
                                   value="{{ old('wfh_address') }}"
                                   placeholder="e.g., Home address or remote work location"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('wfh_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Work From Home Tasks / ClickUp Link -->
                        <div>
                            <label for="wfh_tasks" class="block text-sm font-medium text-gray-700 mb-2">
                                Tasks / ClickUp Links <span class="text-red-500">*</span>
                            </label>
                            <textarea name="wfh_tasks" id="wfh_tasks" rows="4"
                                      placeholder="Strictly list down tasks listed in ClickUp for Devs via link..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('wfh_tasks') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                List the tasks you will work on while remote, including any ClickUp or ticket links.
                            </p>
                            @error('wfh_tasks')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('user.leave-requests.index') }}"
                           class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-set end_date to start_date if not provided
    document.getElementById('start_date').addEventListener('change', function() {
        const endDateInput = document.getElementById('end_date');
        if (!endDateInput.value) {
            endDateInput.min = this.value;
        }
    });

    document.getElementById('end_date').addEventListener('focus', function() {
        const startDate = document.getElementById('start_date').value;
        if (startDate) {
            this.min = startDate;
        }
    });

    // Toggle overtime section based on request type
    const typeSelect = document.getElementById('type');
    const overtimeSection = document.getElementById('overtime-section');
    const wfhSection = document.getElementById('wfh-section');
    const offsetSection = document.getElementById('offset-section');

    function updateRequestTypeSections() {
        if (typeSelect.value === 'overtime') {
            overtimeSection.classList.remove('hidden');
        } else {
            overtimeSection.classList.add('hidden');
        }

        if (typeSelect.value === 'work_from_home') {
            wfhSection.classList.remove('hidden');
        } else {
            wfhSection.classList.add('hidden');
        }

        if (typeSelect.value === 'offset') {
            offsetSection.classList.remove('hidden');
        } else {
            offsetSection.classList.add('hidden');
        }
    }

    typeSelect.addEventListener('change', updateRequestTypeSections);
    // Initialize on page load (for validation errors / old input)
    updateRequestTypeSections();

    // Simple time input formatter (HH:MM), max 4 digits, no AM/PM
    document.querySelectorAll('.time-input').forEach(function (input) {
        input.addEventListener('input', function () {
            let digits = this.value.replace(/\D/g, '').slice(0, 4);
            if (digits.length <= 2) {
                this.value = digits;
            } else {
                const h = digits.slice(0, 2);
                const m = digits.slice(2);
                this.value = m ? h + ':' + m : h;
            }
        });
    });
</script>
@endsection

