@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-4">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Add DTR Record</h1>
                    <p class="text-indigo-100 mt-1">Manually add a new employee time record</p>
                </div>
            </div>
            <a href="{{ url('/admin/dtr') }}"
               class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white hover:bg-white/20 transition duration-200">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to DTR List
            </a>
        </div>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8">
        <form action="{{ url('/admin/dtr') }}" method="POST" class="space-y-6">
            @csrf


            <!-- Basic Information Section -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button type="button"
                        class="w-full flex items-center justify-between px-6 py-4 bg-gray-50 hover:bg-gray-100 transition text-left"
                        data-toggle="section"
                        data-target="basic-info">
                    <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                    <span class="ml-3 inline-flex items-center justify-center rounded-full bg-white/70 text-gray-700 text-xs px-2 py-0.5">
                        <span class="mr-1" data-chevron="basic-info">{{ $collapseByDefault ? '+' : '−' }}</span>
                        Toggle
                    </span>
                </button>

                <div class="border-t border-gray-200 {{ $collapseByDefault ? 'hidden' : '' }}" data-section="basic-info" style="{{ $collapseByDefault ? 'display: none;' : '' }}">
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Employee Selection (Bulk) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Select Employees <span class="text-red-500">*</span>
                                </label>
                                <div class="border border-gray-300 rounded-lg p-4 max-h-64 overflow-y-auto bg-white">
                                    <div class="mb-3 pb-3 border-b border-gray-200">
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" id="select_all_employees" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <span class="text-sm font-medium text-gray-700">Select All</span>
                                        </label>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($employees as $employee)
                                            <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                                                <input type="checkbox" name="user_ids[]" value="{{ $employee->id }}"
                                                       class="employee-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                       {{ (is_array(old('user_ids')) && in_array($employee->id, old('user_ids'))) ? 'checked' : '' }}>
                                                <span class="text-sm text-gray-700">
                                                    {{ $employee->name }}
                                                    <span class="text-gray-500">({{ $employee->email }})</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Select one or more employees to add DTR records for</p>
                                @error('user_ids')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('user_ids.*')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Date Range -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Date Range <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="date_from" class="block text-xs font-medium text-gray-600 mb-1">
                                            From Date
                                        </label>
                                        <input type="date" name="date_from" id="date_from" value="{{ old('date_from', date('Y-m-d')) }}" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('date_from')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="date_to" class="block text-xs font-medium text-gray-600 mb-1">
                                            To Date
                                        </label>
                                        <input type="date" name="date_to" id="date_to" value="{{ old('date_to', date('Y-m-d')) }}" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('date_to')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    Select a date range to create or update DTR records for multiple dates. Records will be processed for all dates in the range (excluding weekends). If a record already exists, the new hours will be added to the existing total hours.
                                </p>
                                <div id="date_range_info" class="mt-2 text-sm text-gray-600 hidden">
                                    <span id="date_range_count">0</span> date(s) will be processed
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Time Details Section -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button type="button"
                        class="w-full flex items-center justify-between px-6 py-4 bg-gray-50 hover:bg-gray-100 transition text-left"
                        data-toggle="section"
                        data-target="time-details">
                    <h3 class="text-lg font-semibold text-gray-900">Time Details</h3>
                    <span class="ml-3 inline-flex items-center justify-center rounded-full bg-white/70 text-gray-700 text-xs px-2 py-0.5">
                        <span class="mr-1" data-chevron="time-details">{{ $collapseByDefault ? '+' : '−' }}</span>
                        Toggle
                    </span>
                </button>

                <div class="border-t border-gray-200 {{ $collapseByDefault ? 'hidden' : '' }}" data-section="time-details" style="{{ $collapseByDefault ? 'display: none;' : '' }}">
                    <div class="p-6 space-y-6">
                        <!-- Added Time From Note (Extra Time) -->
                        <div>
                            <label for="added_time_from_note" class="block text-sm font-medium text-gray-700 mb-2">
                                Added Time From Note (HH:MM)
                            </label>
                            <input type="text" name="added_time_from_note" id="added_time_from_note"
                                   value="{{ old('added_time_from_note') }}"
                                   placeholder="00:00"
                                   class="time-input w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">
                                Extra time to add in <strong>HH:MM</strong> format (e.g., 01:00, 00:30, 02:15). No AM/PM.
                            </p>
                            @error('added_time_from_note')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Worked Hours (Total Hours) -->
                            <div>
                                <label for="total_hours" class="block text-sm font-medium text-gray-700 mb-2">
                                    Worked Hours (HH:MM) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="total_hours" id="total_hours"
                                       value="{{ old('total_hours') }}"
                                       placeholder="08:00"
                                       class="time-input w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       required>
                                <p class="mt-1 text-xs text-gray-500">
                                    Worked hours for this day in <strong>HH:MM</strong> format (e.g., 08:00, 07:30). No AM/PM.
                                    Total Hours = Worked Hours + Added Time From Note.
                                </p>
                                @error('total_hours')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Overtime Hours -->
                            <div>
                                <label for="overtime_hours" class="block text-sm font-medium text-gray-700 mb-2">
                                    Overtime (HH:MM)
                                </label>
                                <input type="text" name="overtime_hours" id="overtime_hours"
                                       value="{{ old('overtime_hours') }}"
                                       placeholder="00:00"
                                       class="time-input w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="mt-1 text-xs text-gray-500">Overtime in <strong>HH:MM</strong> (e.g., 01:00, 00:45). No AM/PM.</p>
                                @error('overtime_hours')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status (Auto-set based on Travel checkbox) -->
                            <div>
                                <input type="hidden" name="status" id="status" value="present">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <div id="status_display" class="px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-sm font-medium text-gray-700">
                                    Present
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Status is automatically set based on Travel checkbox. Time Records will show <strong>Under Time</strong> or <strong>Completed</strong> based on Total Hours.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information Section -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button type="button"
                        class="w-full flex items-center justify-between px-6 py-4 bg-gray-50 hover:bg-gray-100 transition text-left"
                        data-toggle="section"
                        data-target="additional-info">
                    <h3 class="text-lg font-semibold text-gray-900">Additional Information</h3>
                    <span class="ml-3 inline-flex items-center justify-center rounded-full bg-white/70 text-gray-700 text-xs px-2 py-0.5">
                        <span class="mr-1" data-chevron="additional-info">{{ $collapseByDefault ? '+' : '−' }}</span>
                        Toggle
                    </span>
                </button>

                <div class="border-t border-gray-200 {{ $collapseByDefault ? 'hidden' : '' }}" data-section="additional-info" style="{{ $collapseByDefault ? 'display: none;' : '' }}">
                    <div class="p-6 space-y-6">
                        <!-- Travel Checkbox -->
                        <div class="flex items-center space-x-2 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <input type="checkbox" name="is_travel" id="is_travel" value="1"
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                   {{ old('is_travel') ? 'checked' : '' }}>
                            <label for="is_travel" class="text-sm font-medium text-gray-700 cursor-pointer">
                                Mark as Travel (will auto-set Worked Hours to 08:00 and Status to Travel)
                            </label>
                        </div>

                        <!-- Remarks -->
                        <div>
                            <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">
                                Remarks
                            </label>
                            <textarea name="remarks" id="remarks" rows="3"
                                      placeholder="Any additional notes or comments"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('remarks') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Optional notes about this time record</p>
                            @error('remarks')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ url('/admin/dtr') }}"
                   class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Add DTR Record
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Select All functionality
    document.addEventListener('DOMContentLoaded', function () {
        const selectAllCheckbox = document.getElementById('select_all_employees');
        const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');

        if (selectAllCheckbox && employeeCheckboxes.length > 0) {
            selectAllCheckbox.addEventListener('change', function() {
                employeeCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });

            // Update select all when individual checkboxes change
            employeeCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(employeeCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                });
            });
        }

        // Date range validation and info
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');
        const dateRangeInfo = document.getElementById('date_range_info');
        const dateRangeCount = document.getElementById('date_range_count');

        function updateDateRangeInfo() {
            if (dateFromInput && dateToInput && dateRangeInfo && dateRangeCount) {
                const dateFrom = new Date(dateFromInput.value);
                const dateTo = new Date(dateToInput.value);

                if (dateFromInput.value && dateToInput.value && dateFrom <= dateTo) {
                    // Calculate number of weekdays in range
                    let count = 0;
                    const currentDate = new Date(dateFrom);
                    while (currentDate <= dateTo) {
                        const dayOfWeek = currentDate.getDay();
                        // Exclude weekends (Saturday = 6, Sunday = 0)
                        if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                            count++;
                        }
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                    dateRangeCount.textContent = count;
                    dateRangeInfo.classList.remove('hidden');
                } else {
                    dateRangeInfo.classList.add('hidden');
                }
            }
        }

        if (dateFromInput && dateToInput) {
            dateFromInput.addEventListener('change', function() {
                // Ensure date_to is not before date_from
                if (dateToInput.value && dateToInput.value < dateFromInput.value) {
                    dateToInput.value = dateFromInput.value;
                }
                updateDateRangeInfo();
            });

            dateToInput.addEventListener('change', function() {
                // Ensure date_to is not before date_from
                if (dateToInput.value < dateFromInput.value) {
                    alert('To Date must be on or after From Date.');
                    dateToInput.value = dateFromInput.value;
                }
                updateDateRangeInfo();
            });

            // Initial update
            updateDateRangeInfo();
        }

        // Form validation - ensure at least one employee is selected
        const form = document.querySelector('form[action="{{ url('/admin/dtr') }}"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                const checkedEmployees = document.querySelectorAll('.employee-checkbox:checked');
                if (checkedEmployees.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one employee.');
                    return false;
                }

                // Validate date range
                if (dateFromInput && dateToInput) {
                    const dateFrom = new Date(dateFromInput.value);
                    const dateTo = new Date(dateToInput.value);

                    if (!dateFromInput.value || !dateToInput.value) {
                        e.preventDefault();
                        alert('Please select both From Date and To Date.');
                        return false;
                    }

                    if (dateTo < dateFrom) {
                        e.preventDefault();
                        alert('To Date must be on or after From Date.');
                        return false;
                    }
                }
            });
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('.time-input');

        function formatTime(value) {
            // Keep only digits, max 4
            let digits = value.replace(/\D/g, '').slice(0, 4);
            if (digits.length <= 2) {
                return digits;
            }
            const h = digits.slice(0, 2);
            const m = digits.slice(2);
            return m ? h + ':' + m : h;
        }

        inputs.forEach(function (input) {
            input.addEventListener('input', function (e) {
                const cursorPos = input.selectionStart;
                const before = input.value;
                input.value = formatTime(input.value);
                // Best-effort keep cursor near end
                if (document.activeElement === input) {
                    input.selectionStart = input.selectionEnd = input.value.length;
                }
            });
        });

        // Handle Travel checkbox
        const travelCheckbox = document.getElementById('is_travel');
        const totalHoursInput = document.getElementById('total_hours');
        const statusInput = document.getElementById('status');
        const statusDisplay = document.getElementById('status_display');

        if (travelCheckbox && totalHoursInput && statusInput && statusDisplay) {
            function updateStatus() {
                if (travelCheckbox.checked) {
                    // Set Worked Hours to 08:00
                    totalHoursInput.value = '08:00';
                    // Set Status to Travel
                    statusInput.value = 'travel';
                    statusDisplay.textContent = 'Travel';
                    statusDisplay.className = 'px-4 py-3 border border-gray-200 rounded-lg bg-blue-50 text-sm font-medium text-blue-700';
                } else {
                    // Reset to default if unchecked
                    if (totalHoursInput.value === '08:00') {
                        totalHoursInput.value = '';
                    }
                    statusInput.value = 'present';
                    statusDisplay.textContent = 'Present';
                    statusDisplay.className = 'px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-sm font-medium text-gray-700';
                }
                // Trigger input event to ensure formatting is applied
                totalHoursInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            travelCheckbox.addEventListener('change', updateStatus);

            // Initialize on page load
            updateStatus();
        }

        // Handle collapsible sections
        function toggleVisibility(element, chevron) {
            if (!element) return;

            const isHidden = element.classList.contains('hidden');
            if (isHidden) {
                element.classList.remove('hidden');
                element.style.display = '';
                if (chevron) chevron.textContent = '−';
            } else {
                element.classList.add('hidden');
                element.style.display = 'none';
                if (chevron) chevron.textContent = '+';
            }
        }

        // Section toggles
        document.querySelectorAll('[data-toggle="section"]').forEach(function (button) {
            const targetId = button.getAttribute('data-target');
            const content = document.querySelector(`[data-section="${targetId}"]`);
            const chevron = document.querySelector(`[data-chevron="${targetId}"]`);

            if (!button || !content) {
                console.warn('Section toggle elements not found', { button: !!button, content: !!content });
                return;
            }

            button.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggleVisibility(content, chevron);
            });
        });
    });
</script>

@endsection
