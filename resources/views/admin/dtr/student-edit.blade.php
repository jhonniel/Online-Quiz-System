@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-4">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Edit Student DTR Record</h1>
                    <p class="text-indigo-100 mt-1">Update an existing student time record</p>
                </div>
            </div>
            <a href="{{ route('admin.student-dtr.index') }}"
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
        <form action="{{ route('admin.student-dtr.update', $dtr) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Student Selection -->
                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Student <span class="text-red-500">*</span>
                                </label>
                                <select name="user_id" id="user_id" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}"
                                            {{ old('user_id', $dtr->user_id) == $student->id ? 'selected' : '' }}>
                                            {{ $student->name }} ({{ $student->email }})
                                        </option>
                                    @endforeach
                                </select>
                    @error('user_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date" id="date"
                           value="{{ old('date', $dtr->date->format('Y-m-d')) }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Added Time From Note (Extra Time) -->
            <div>
                <label for="added_time_from_note" class="block text-sm font-medium text-gray-700 mb-2">
                    Added Time From Note (HH:MM)
                </label>
                <input type="text" name="added_time_from_note" id="added_time_from_note"
                       value="{{ old('added_time_from_note', $addedFormatted) }}"
                       placeholder="00:00"
                       class="time-input w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <p class="mt-1 text-xs text-gray-500">
                    Extra time to add in <strong>HH:MM</strong> format (e.g., 01:00, 00:30, 02:15). No AM/PM.
                </p>
                @error('added_time_from_note')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Travel Checkbox -->
            <div class="flex items-center space-x-2 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <input type="checkbox" name="is_travel" id="is_travel" value="1"
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                       {{ old('is_travel', $dtr->status === 'travel') ? 'checked' : '' }}>
                <label for="is_travel" class="text-sm font-medium text-gray-700 cursor-pointer">
                    Mark as Travel (will auto-set Worked Hours to 08:00 and Status to Travel)
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Worked Hours (Total Hours) -->
                <div>
                    <label for="total_hours" class="block text-sm font-medium text-gray-700 mb-2">
                        Worked Hours (HH:MM) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="total_hours" id="total_hours"
                           value="{{ old('total_hours', $workedFormatted) }}"
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

                <!-- Read-only Overtime Info (calculated on save) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Overtime (HH:MM)
                    </label>
                    @php
                        $otMinutes = (int) round(($dtr->overtime_hours ?? 0) * 60);
                        $otH = intdiv($otMinutes, 60);
                        $otM = $otMinutes % 60;
                        $otFormatted = sprintf('%02d:%02d', $otH, $otM);
                    @endphp
                    <div class="px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-sm font-semibold text-orange-700">
                        {{ $otMinutes > 0 ? $otFormatted : '00:00' }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Overtime is automatically recalculated on save as
                        <strong>(Total Hours − 08:00)</strong> when Total Hours is more than 08:00.
                    </p>
                </div>

                <!-- Status (Auto-set based on Travel checkbox) -->
                <div>
                    <input type="hidden" name="status" id="status" value="{{ old('status', $dtr->status) }}">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    @php
                        $currentStatus = old('status', $dtr->status);
                        $statusDisplayText = $currentStatus === 'travel' ? 'Travel' : ucfirst(str_replace('_', ' ', $currentStatus));
                        $statusDisplayClass = $currentStatus === 'travel' 
                            ? 'px-4 py-3 border border-gray-200 rounded-lg bg-blue-50 text-sm font-medium text-blue-700'
                            : 'px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-sm font-medium text-gray-700';
                    @endphp
                    <div id="status_display" class="{{ $statusDisplayClass }}">
                        {{ $statusDisplayText }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Status is automatically set based on Travel checkbox. Time Records will show <strong>Under Time</strong> or <strong>Completed</strong> based on Total Hours.
                    </p>
                </div>
            </div>

            <!-- Remarks -->
            <div>
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">
                    Remarks
                </label>
                <textarea name="remarks" id="remarks" rows="3"
                          placeholder="Any additional notes or comments"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('remarks', $dtr->remarks) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Optional notes about this time record</p>
                @error('remarks')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.student-dtr.index') }}"
                   class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel
                </a>
                @if(auth()->check() && auth()->user()->isSuperAdmin())
                    <form action="{{ route('admin.student-dtr.destroy', $dtr) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this student DTR record? This action cannot be undone.');"
                          class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center px-6 py-3 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Record
                        </button>
                    </form>
                @endif
                <button type="submit"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Update DTR Record
                </button>
            </div>
        </form>
    </div>
</div>

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
            input.addEventListener('input', function () {
                input.value = formatTime(input.value);
            });
        });

        // Handle Travel checkbox
        const travelCheckbox = document.getElementById('is_travel');
        const totalHoursInput = document.getElementById('total_hours');
        const statusInput = document.getElementById('status');
        const statusDisplay = document.getElementById('status_display');

        if (travelCheckbox && totalHoursInput && statusInput && statusDisplay) {
            const isInitialLoad = true;
            let initialStatus = statusInput.value;
            
            function updateStatus(isInitial = false) {
                if (travelCheckbox.checked) {
                    // Set Status to Travel
                    statusInput.value = 'travel';
                    statusDisplay.textContent = 'Travel';
                    statusDisplay.className = 'px-4 py-3 border border-gray-200 rounded-lg bg-blue-50 text-sm font-medium text-blue-700';
                    
                    // Only set Worked Hours to 08:00 if not initial load (user is toggling checkbox)
                    if (!isInitial) {
                        totalHoursInput.value = '08:00';
                        totalHoursInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                } else {
                    // Reset to default if unchecked
                    statusInput.value = 'present';
                    statusDisplay.textContent = 'Present';
                    statusDisplay.className = 'px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-sm font-medium text-gray-700';
                    
                    // Only reset worked hours if not initial load and it was 08:00
                    if (!isInitial && totalHoursInput.value === '08:00') {
                        totalHoursInput.value = '';
                    }
                }
            }

            travelCheckbox.addEventListener('change', function() {
                updateStatus(false);
            });
            
            // Initialize on page load - just update display, don't change worked hours
            if (initialStatus === 'travel') {
                travelCheckbox.checked = true;
                updateStatus(true);
            } else {
                updateStatus(true);
            }
        }
    });
</script>

@endsection


