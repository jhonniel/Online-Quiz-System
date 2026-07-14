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
            <a href="{{ url('/leave-requests') }}"
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
                <form id="leave-request-form" action="{{ url('/leave-requests') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    @if($errors->any())
                    @php
                        $noBalanceMsg = 'No balance to file for that type of request.';
                        $otherErrors = array_filter($errors->all(), fn($err) => $err !== $noBalanceMsg && !str_contains((string)$err, 'No balance'));
                    @endphp
                    @if(count($otherErrors) > 0)
                    <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                        <p class="text-sm font-medium text-red-800">The request could not be saved. Please fix the errors below and try again.</p>
                        <ul class="mt-2 list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach($otherErrors as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    @endif

                    <!-- Request Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Request Type <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="type" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Request Type</option>
                            @if(auth()->user()->role === 'student')
                                <option value="additional_time" {{ in_array(old('type'), ['additional_time', 'overtime'], true) ? 'selected' : '' }}>Additional Time</option>
                                <option value="absent" {{ old('type') == 'absent' ? 'selected' : '' }}>Absent</option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                            @else
                                <option value="vacation_leave" {{ old('type') == 'vacation_leave' ? 'selected' : '' }}>Vacation Leave</option>
                                <option value="sick_leave" {{ old('type') == 'sick_leave' ? 'selected' : '' }}>Sick Leave</option>
                                <option value="work_from_home" {{ old('type') == 'work_from_home' ? 'selected' : '' }}>Work From Home</option>
                                @if(auth()->user()->isStaffMember())
                                    <option value="travel" {{ old('type') == 'travel' ? 'selected' : '' }}>Travel</option>
                                @endif
                                <option value="absent" {{ old('type') == 'absent' ? 'selected' : '' }}>Absent</option>
                                <option value="overtime" {{ old('type') == 'overtime' ? 'selected' : '' }}>Overtime</option>
                                <option value="offset" {{ old('type') == 'offset' ? 'selected' : '' }}>Offset</option>
                            @endif
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if(auth()->user()->role !== 'student')
                            <p id="travel-additional-time-notice"
                               class="mt-2 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-md px-3 py-2 {{ old('type') === 'travel' ? '' : 'hidden' }}">
                                <strong>Travel</strong> will only record <strong>8 hours per day</strong>. If you have overtime, file it as a separate <strong>Overtime</strong> request and notify your supervisor or team lead.
                            </p>
                        @endif
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date"
                                   value="{{ old('start_date') }}"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                End Date <span id="end-date-required-span" class="text-gray-400">(Optional)</span>
                            </label>
                            <input type="date" name="end_date" id="end_date"
                                   value="{{ old('end_date') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p id="end-date-hint" class="mt-1 text-xs text-gray-500">Leave blank for single day requests</p>
                            <p id="overtime-date-hint" class="mt-1 text-xs text-amber-700 hidden">
                                Additional Time dates must be from the last 7 days through today only (no future dates).
                            </p>
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div id="overtime-specific-dates-container" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select specific date(s) from your chosen range <span class="text-red-500">*</span>
                        </label>
                        <div id="overtime-specific-dates-wrap"
                             data-old-selected='@json(old("overtime_specific_dates", []))'
                             class="rounded-lg border border-gray-200 bg-gray-50 p-3 min-h-[3rem]">
                            <p class="text-xs text-gray-500">Pick Start Date and End Date above first (Additional Time only).</p>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Additional Time filed within the past 7 days is eligible for approval.
                        </p>
                        @error('overtime_specific_dates')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('overtime_specific_dates.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Reason (label/required change to "Location of travel" when type = Travel) -->
                    <div id="reason-field">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            <span id="reason-label-text">Reason</span> <span id="reason-required-span" class="text-gray-400">(Optional)</span>
                        </label>
                        <textarea name="reason" id="reason" rows="4"
                                  placeholder="Please provide a reason for this request..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('reason') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500" id="reason-help">
                            Provide additional details about your request. For <strong>Additional Time</strong>, if provided, this will appear as additional explanation. If not provided, it will be left blank.
                        </p>
                        <p class="mt-1 text-xs text-gray-500 hidden" id="reason-travel-help">
                            Enter the location or destination of your travel.
                        </p>
                        @error('reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Supporting Document (required for Overtime; hidden for Travel) -->
                    <div id="supporting-section">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Supporting Document (e.g., Hubstaff screenshots, ClickUp links/screenshots)
                            <span id="supporting-required-span" class="text-gray-400">(Optional)</span>
                        </label>
                        <input type="file" name="supporting_documents[]" id="supporting_documents_input" accept=".pdf,.jpg,.jpeg,.png" multiple
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p id="supporting-help" class="mt-1 text-xs text-gray-500">Optional, upload up to 5 files (PDF/JPG/PNG), 5MB max per file.</p>
                        @error('supporting_documents')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('supporting_documents.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Additional Time / Overtime details (students: Additional Time only) -->
                    <div id="overtime-section" class="space-y-4 hidden">
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <h2 class="text-sm font-semibold text-gray-900 mb-2" id="structured-hours-section-title">Additional Time Details</h2>
                            <p class="text-xs text-gray-500 mb-3" id="structured-hours-section-help">
                                @if(auth()->user()->role === 'student')
                                    When requesting <strong>Additional Time</strong>, provide the total hours, the dates covered,
                                    and list the tasks (e.g., ClickUp links) completed during that time.
                                    When approved, these hours are added to your DTR and count toward your required training hours.
                                @else
                                    <strong>Overtime</strong> requests should be filed by your supervisor or team lead on your behalf.
                                    If you need to record additional time, please ask them to submit an <strong>Overtime</strong> request for you.
                                @endif
                            </p>
                        </div>

                        <div>
                            <label for="overtime_hours" class="block text-sm font-medium text-gray-700 mb-2" id="structured-hours-input-label">
                                Total Additional Time Hours (HH:MM) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="overtime_hours" id="overtime_hours"
                                   value="{{ old('overtime_hours') }}"
                                   placeholder="01:20"
                                   class="time-input w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">
                                Enter the total time in <strong>HH:MM</strong> (e.g., 01:00, 02:30). No AM/PM.
                            </p>
                            @error('overtime_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Overtime Tasks / ClickUp Link -->
                        <div>
                            <label for="overtime_tasks" class="block text-sm font-medium text-gray-700 mb-2">
                                Tasks / ClickUp Links <span class="text-red-500">*</span>
                            </label>
                            <textarea name="overtime_tasks" id="overtime_tasks" rows="4"
                                      placeholder="https://app.clickup.com/... (URLs only; add more on separate lines or separated by spaces)"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('overtime_tasks') }}</textarea>
                            @error('overtime_tasks')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Travel Details (visible only when Request Type = Travel, employees only) -->
                    @if(auth()->user()->isStaffMember())
                    <div id="travel-section" class="space-y-4 hidden">
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <h2 class="text-sm font-semibold text-gray-900 mb-2">Travel Details</h2>
                            <p class="text-xs text-gray-500 mb-3">
                                Travel requests are subject to approval. Upon approval, hours are added to your DTR. Past dates are allowed.
                            </p>
                            <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-3 py-2 mb-3">
                                <strong>Note:</strong> Only travel <strong>outside Davao</strong> will be approved for this request.
                            </p>
                            <p class="text-xs text-gray-600 mb-3">
                                You can only file TRAVEL for <strong>today or past dates</strong>. Travel for future dates can only be filed by your team lead or supervisor.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Hours per Day <span class="text-gray-400">(Default 8:00)</span>
                            </label>
                            <input type="hidden" name="travel_hours" value="8">
                            <div class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-sm font-medium text-gray-900" aria-hidden="true">
                                8
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Fixed at 8 hours per day for travel requests (shown for reference only).</p>
                            @error('travel_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    @endif

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
                                      placeholder="https://app.clickup.com/... (URLs only; add more on separate lines or separated by spaces)"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('wfh_tasks') }}</textarea>
                            @error('wfh_tasks')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ url('/leave-requests') }}"
                           class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                            Cancel
                        </a>
                        <button type="submit" id="submit-btn"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="submit-icon" class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span id="submit-text">Submit Request</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@php
    $noBalanceModalMessage = $errors->has('type') ? $errors->first('type') : null;
    $showNoBalanceModalOnLoad = ($showNoBalanceModalOnLoad ?? false)
        || ($noBalanceModalMessage && (
            str_contains($noBalanceModalMessage, 'No balance')
            || str_contains($noBalanceModalMessage, 'Work From Home balance')
            || str_contains($noBalanceModalMessage, 'do not have any Work From Home balance')
        ));
@endphp
@if((isset($balances) && $balances) || $showNoBalanceModalOnLoad)
<div id="no-balance-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" aria-modal="true" role="dialog" onclick="if (event.target === this) closeNoBalanceModal();">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 mx-auto" onclick="event.stopPropagation();">
            <div class="flex items-center justify-center w-12 h-12 mx-auto rounded-full bg-amber-100 flex-shrink-0">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-gray-900 text-center">No balance to file for that type of request</h3>
            <p id="no-balance-modal-message" class="mt-2 text-sm text-gray-600 text-center">
                @if($noBalanceModalMessage)
                    {{ $noBalanceModalMessage }}
                @else
                    You have no remaining balance for the selected request type. Choose another request type or contact HR if you believe your balance should be updated.
                @endif
            </p>
            <div class="mt-6">
                <button type="button" onclick="closeNoBalanceModal()"
                        class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    OK
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<div id="leave-request-page-data" data-show-no-balance="{{ ($showNoBalanceModalOnLoad ?? false) ? '1' : '0' }}" hidden></div>
<script id="leave-request-balances-json" type="application/json">{!! json_encode($balances ?? null) !!}</script>
<script>
    window.showNoBalanceModalOnLoad =
        (document.getElementById('leave-request-page-data')?.dataset.showNoBalance === '1');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const typeSelect = document.getElementById('type');
    const overtimeSection = document.getElementById('overtime-section');
    const wfhSection = document.getElementById('wfh-section');
    const offsetSection = document.getElementById('offset-section');
    const travelSection = document.getElementById('travel-section');
    const travelAdditionalTimeNotice = document.getElementById('travel-additional-time-notice');
    const isStudent = @json(auth()->user()->role === 'student');
    const overtimeSpecificDatesContainer = document.getElementById('overtime-specific-dates-container');
    const overtimeSpecificDatesWrap = document.getElementById('overtime-specific-dates-wrap');
    const submitBtn = document.getElementById('submit-btn');
    const noBalanceModal = document.getElementById('no-balance-modal');
    const leaveDateBounds = {
        today: @json(now()->toDateString()),
        overtimeMin: @json(now()->subDays(7)->toDateString()),
    };
    const today = leaveDateBounds.today;
    const endDateRequiredSpan = document.getElementById('end-date-required-span');
    const endDateHint = document.getElementById('end-date-hint');
    const overtimeDateHint = document.getElementById('overtime-date-hint');

    function isStructuredHoursType(type) {
        return type === 'overtime' || (isStudent && type === 'additional_time');
    }

    function clampOvertimeDateInputs() {
        if (!startDateInput) return;
        const minD = leaveDateBounds.overtimeMin;
        const maxD = leaveDateBounds.today;
        if (startDateInput.value) {
            if (startDateInput.value > maxD) startDateInput.value = maxD;
            if (startDateInput.value < minD) startDateInput.value = minD;
        }
        if (endDateInput && endDateInput.value) {
            if (endDateInput.value > maxD) endDateInput.value = maxD;
            if (endDateInput.value < minD) endDateInput.value = minD;
            if (startDateInput.value && endDateInput.value < startDateInput.value) {
                endDateInput.value = startDateInput.value;
            }
        }
    }

    const balances = JSON.parse(document.getElementById('leave-request-balances-json').textContent || 'null');
    const balanceCheckTypes = ['vacation_leave', 'sick_leave', 'offset', 'work_from_home'];
    @if(isset($balances['work_from_home']))
    const wfhBalanceUrl = @json(route('user.leave-requests.wfh-balance'));

    async function refreshWfhBalance() {
        if (typeSelect.value !== 'work_from_home' || !startDateInput?.value) {
            return;
        }

        try {
            const params = new URLSearchParams({ start_date: startDateInput.value });
            const response = await fetch(`${wfhBalanceUrl}?${params.toString()}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) {
                return;
            }

            const data = await response.json();
            if (balances) {
                balances.work_from_home = data;
                balances.work_from_home_remaining = data.remaining;
            }

            updateNoBalancePrompt();
        } catch (error) {
            // ignore network errors for balance preview
        }
    }
    @endif
    const offsetHoursInput = document.getElementById('offset_hours');
    function parseHoursValue(val) {
        if (val === null || val === undefined) return 0;
        if (typeof val === 'number') return Number.isFinite(val) ? val : 0;
        const txt = String(val).trim();
        const m = txt.match(/^(\d{1,3}):(\d{2})$/);
        if (m) return parseInt(m[1], 10) + (parseInt(m[2], 10) / 60);
        const n = Number(txt);
        return Number.isFinite(n) ? n : 0;
    }
    function hasNoBalanceForType(type) {
        if (!balances || !balanceCheckTypes.includes(type)) return false;
        if (type === 'vacation_leave' || type === 'sick_leave') return (balances.leave_remaining || 0) <= 0;
        if (type === 'work_from_home') {
            const remaining = parseFloat(balances.work_from_home_remaining ?? balances.work_from_home?.remaining ?? 0);
            if (remaining <= 0) {
                return true;
            }
            const start = startDateInput?.value;
            const end = endDateInput?.value || start;
            if (!start) {
                return false;
            }
            const startDate = new Date(start + 'T00:00:00');
            const endDate = new Date(end + 'T00:00:00');
            const daysRequested = Math.floor((endDate - startDate) / (24 * 3600 * 1000)) + 1;
            return daysRequested > remaining;
        }
        if (type === 'offset') {
            const overtimeBal = parseHoursValue(balances.overtime_hours);
            if (overtimeBal <= 0) return true;

            // Requested offset hours:
            // - if Hours to Deduct is provided, use that (HH:MM)
            // - else fall back to duration days * 8 hours
            let requestedHours = 0;
            const txt = (offsetHoursInput?.value || '').trim();
            if (txt) {
                const m = txt.match(/^(\d{1,3}):(\d{2})$/);
                if (!m) return false; // incomplete/invalid input: don't show "no balance" modal
                requestedHours = parseInt(m[1], 10) + (parseInt(m[2], 10) / 60);
            } else {
                const s = startDateInput?.value;
                const e = endDateInput?.value || s;
                if (!s) return false;
                const start = new Date(s + 'T00:00:00');
                const end = new Date(e + 'T00:00:00');
                const days = Math.floor((end - start) / (24 * 3600 * 1000)) + 1;
                requestedHours = Math.max(1, days) * 8;
            }

            return requestedHours > overtimeBal;
        }
        return false;
    }
    function closeNoBalanceModal() {
        if (noBalanceModal) noBalanceModal.classList.add('hidden');
    }
    function noBalanceMessageForType(type) {
        if (type === 'work_from_home') {
            const month = balances?.work_from_home?.month_label ?? 'this month';
            const remaining = parseFloat(balances?.work_from_home_remaining ?? balances?.work_from_home?.remaining ?? 0);
            if (remaining <= 0) {
                const carryover = parseFloat(balances?.work_from_home?.carryover_debt ?? 0);
                const carryoverNote = carryover > 0
                    ? ` This includes ${carryover} day(s) carried over from admin-filed Work From Home in the previous month.`
                    : '';
                return `No balance: You do not have any Work From Home balance remaining for ${month}.${carryoverNote} Contact an administrator if you need additional WFH days.`;
            }
            const start = startDateInput?.value;
            const end = endDateInput?.value || start;
            if (start) {
                const startDate = new Date(start + 'T00:00:00');
                const endDate = new Date(end + 'T00:00:00');
                const daysRequested = Math.floor((endDate - startDate) / (24 * 3600 * 1000)) + 1;
                if (daysRequested > remaining) {
                    const dayLabel = remaining === 1 ? 'day' : 'days';
                    const needLabel = daysRequested === 1 ? 'day' : 'days';
                    return `Work From Home is limited to 2 days per month. You have ${remaining} ${dayLabel} remaining for ${month}, but this request needs ${daysRequested} ${needLabel}.`;
                }
            }
        }
        if (type === 'vacation_leave' || type === 'sick_leave') {
            return 'No balance: You do not have any Leave Credits balance remaining to file this request.';
        }
        if (type === 'offset') {
            return 'No balance: You do not have enough overtime balance to file this offset request.';
        }
        return 'No balance: You do not have remaining balance for the selected request type.';
    }

    function updateNoBalancePrompt() {
        const type = typeSelect.value;
        const noBalance = type && hasNoBalanceForType(type);
        const messageEl = document.getElementById('no-balance-modal-message');
        if (noBalanceModal) {
            if (noBalance) {
                noBalanceModal.classList.remove('hidden');
                if (messageEl) {
                    messageEl.textContent = noBalanceMessageForType(type);
                }
            } else {
                noBalanceModal.classList.add('hidden');
            }
        }
        if (submitBtn) submitBtn.disabled = !!noBalance;
    }

    function renderOvertimeSpecificDates() {
        if (!overtimeSpecificDatesWrap) return;
        if (!isStructuredHoursType(typeSelect.value)) {
            overtimeSpecificDatesWrap.innerHTML = '<p class="text-xs text-gray-500">Visible only when Request Type is Additional Time.</p>';
            return;
        }

        const start = startDateInput.value;
        let end = endDateInput.value || start;
        if (end > leaveDateBounds.today) {
            end = leaveDateBounds.today;
        }
        if (!start || !end) {
            overtimeSpecificDatesWrap.innerHTML = '<p class="text-xs text-gray-500">Pick Start Date and End Date first.</p>';
            return;
        }

        const startDate = new Date(start + 'T00:00:00');
        const endDate = new Date(end + 'T00:00:00');
        if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime()) || endDate < startDate) {
            overtimeSpecificDatesWrap.innerHTML = '<p class="text-xs text-red-600">Invalid date range. End Date must be same or after Start Date.</p>';
            return;
        }

        const currentChecked = Array.from(
            overtimeSpecificDatesWrap.querySelectorAll('input[name="overtime_specific_dates[]"]:checked')
        ).map((el) => el.value);
        const oldSelected = (() => {
            try {
                return JSON.parse(overtimeSpecificDatesWrap.dataset.oldSelected || '[]');
            } catch (e) {
                return [];
            }
        })();
        const selectedSet = new Set(currentChecked.length ? currentChecked : oldSelected);

        const rows = [];
        let cursor = new Date(startDate);
        while (cursor <= endDate) {
            const y = cursor.getFullYear();
            const m = String(cursor.getMonth() + 1).padStart(2, '0');
            const d = String(cursor.getDate()).padStart(2, '0');
            const value = `${y}-${m}-${d}`;
            if (value > leaveDateBounds.today || value < leaveDateBounds.overtimeMin) {
                cursor.setDate(cursor.getDate() + 1);
                continue;
            }
            rows.push(`
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 mr-4 mb-2">
                    <input type="checkbox" name="overtime_specific_dates[]" value="${value}" ${selectedSet.has(value) ? 'checked' : ''} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span>${value}</span>
                </label>
            `);
            cursor.setDate(cursor.getDate() + 1);
        }

        overtimeSpecificDatesWrap.innerHTML = rows.length > 0
            ? `<div class="flex flex-wrap">${rows.join('')}</div>`
            : '<p class="text-xs text-gray-500">No selectable dates in range.</p>';
    }

    // Auto-set end_date min/max when relevant
    function syncEndDateMin() {
        if (!endDateInput) return;
        const startDate = startDateInput.value;
        if (typeSelect.value === 'travel') {
            endDateInput.removeAttribute('min');
            endDateInput.setAttribute('max', today);
        } else if (isStructuredHoursType(typeSelect.value)) {
            const endMin = startDate && startDate >= leaveDateBounds.overtimeMin
                ? startDate
                : leaveDateBounds.overtimeMin;
            endDateInput.min = endMin;
            endDateInput.setAttribute('max', today);
        } else if (typeSelect.value === 'sick_leave') {
            endDateInput.removeAttribute('min');
            endDateInput.removeAttribute('max');
        } else if (startDate) {
            endDateInput.min = startDate;
            endDateInput.removeAttribute('max');
        } else {
            endDateInput.min = today;
            endDateInput.removeAttribute('max');
        }
    }

    startDateInput.addEventListener('change', function() {
        if (!endDateInput.value) {
            syncEndDateMin();
        }
        renderOvertimeSpecificDates();
        updateNoBalancePrompt();
    });

    endDateInput.addEventListener('focus', syncEndDateMin);
    endDateInput.addEventListener('change', renderOvertimeSpecificDates);

    function updateRequestTypeSections() {
        if (typeSelect.value === 'travel') {
            // Travel: only today or past (no future dates for employees)
            startDateInput.removeAttribute('min');
            startDateInput.setAttribute('max', today);
            if (endDateInput) {
                endDateInput.setAttribute('max', today);
                endDateInput.required = false;
            }
            if (overtimeDateHint) overtimeDateHint.classList.add('hidden');
            if (endDateHint) endDateHint.classList.remove('hidden');
            if (endDateRequiredSpan) {
                endDateRequiredSpan.classList.add('text-gray-400');
                endDateRequiredSpan.classList.remove('text-red-500');
                endDateRequiredSpan.textContent = '(Optional)';
            }
        } else if (isStructuredHoursType(typeSelect.value)) {
            startDateInput.setAttribute('min', leaveDateBounds.overtimeMin);
            startDateInput.setAttribute('max', today);
            if (endDateInput) {
                endDateInput.setAttribute('max', today);
                endDateInput.required = true;
                endDateInput.disabled = false;
            }
            clampOvertimeDateInputs();
            if (overtimeDateHint) overtimeDateHint.classList.remove('hidden');
            if (endDateHint) endDateHint.classList.add('hidden');
            if (endDateRequiredSpan) {
                endDateRequiredSpan.classList.remove('text-gray-400');
                endDateRequiredSpan.classList.add('text-red-500');
                endDateRequiredSpan.textContent = '*';
            }
        } else if (typeSelect.value === 'sick_leave') {
            if (endDateInput) {
                endDateInput.required = false;
                endDateInput.disabled = false;
            }
            if (overtimeDateHint) overtimeDateHint.classList.add('hidden');
            if (endDateHint) endDateHint.classList.remove('hidden');
            if (endDateRequiredSpan) {
                endDateRequiredSpan.classList.add('text-gray-400');
                endDateRequiredSpan.classList.remove('text-red-500');
                endDateRequiredSpan.textContent = '(Optional)';
            }
            startDateInput.removeAttribute('min');
            startDateInput.removeAttribute('max');
            if (endDateInput) {
                endDateInput.removeAttribute('min');
                endDateInput.removeAttribute('max');
            }
        } else {
            startDateInput.setAttribute('min', today);
            startDateInput.removeAttribute('max');
            if (endDateInput) {
                endDateInput.removeAttribute('max');
                endDateInput.required = false;
            }
            if (overtimeDateHint) overtimeDateHint.classList.add('hidden');
            if (endDateHint) endDateHint.classList.remove('hidden');
            if (endDateRequiredSpan) {
                endDateRequiredSpan.classList.add('text-gray-400');
                endDateRequiredSpan.classList.remove('text-red-500');
                endDateRequiredSpan.textContent = '(Optional)';
            }
        }

        if (isStructuredHoursType(typeSelect.value)) {
            overtimeSection.classList.remove('hidden');
            if (overtimeSpecificDatesContainer) overtimeSpecificDatesContainer.classList.remove('hidden');
        } else {
            overtimeSection.classList.add('hidden');
            if (overtimeSpecificDatesContainer) overtimeSpecificDatesContainer.classList.add('hidden');
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

        if (travelSection) {
            if (typeSelect.value === 'travel') {
                travelSection.classList.remove('hidden');
            } else {
                travelSection.classList.add('hidden');
            }
        }

        if (travelAdditionalTimeNotice) {
            if (typeSelect.value === 'travel') {
                travelAdditionalTimeNotice.classList.remove('hidden');
            } else {
                travelAdditionalTimeNotice.classList.add('hidden');
            }
        }

        updateNoBalancePrompt();

        // For Travel: "Location of travel" required; hide Supporting Document
        const reasonLabelText = document.getElementById('reason-label-text');
        const reasonRequiredSpan = document.getElementById('reason-required-span');
        const reasonInput = document.getElementById('reason');
        const reasonHelp = document.getElementById('reason-help');
        const reasonTravelHelp = document.getElementById('reason-travel-help');
        const supportingSection = document.getElementById('supporting-section');
        const supportingRequiredSpan = document.getElementById('supporting-required-span');
        const supportingHelp = document.getElementById('supporting-help');
        const supportingInput = document.getElementById('supporting_documents_input');
        if (typeSelect.value === 'travel') {
            if (reasonLabelText) reasonLabelText.textContent = 'Location of travel ';
            if (reasonRequiredSpan) { reasonRequiredSpan.classList.remove('text-gray-400'); reasonRequiredSpan.classList.add('text-red-500'); reasonRequiredSpan.textContent = '*'; }
            if (reasonInput) reasonInput.required = true;
            if (reasonHelp) reasonHelp.classList.add('hidden');
            if (reasonTravelHelp) reasonTravelHelp.classList.remove('hidden');
            if (reasonInput) reasonInput.placeholder = 'Enter location or destination of travel...';
            if (supportingSection) supportingSection.classList.add('hidden');
            if (supportingInput) supportingInput.required = false;
        } else {
            if (reasonLabelText) reasonLabelText.textContent = 'Reason ';
            if (reasonRequiredSpan) { reasonRequiredSpan.classList.add('text-gray-400'); reasonRequiredSpan.classList.remove('text-red-500'); reasonRequiredSpan.textContent = '(Optional)'; }
            if (reasonInput) reasonInput.required = false;
            if (reasonHelp) reasonHelp.classList.remove('hidden');
            if (reasonTravelHelp) reasonTravelHelp.classList.add('hidden');
            if (reasonInput) reasonInput.placeholder = 'Please provide a reason for this request...';
            if (supportingSection) supportingSection.classList.remove('hidden');
            if (isStructuredHoursType(typeSelect.value)) {
                if (supportingRequiredSpan) { supportingRequiredSpan.classList.remove('text-gray-400'); supportingRequiredSpan.classList.add('text-red-500'); supportingRequiredSpan.textContent = '*'; }
                if (supportingHelp) supportingHelp.textContent = 'Required for Additional Time. Upload up to 5 files (PDF/JPG/PNG), 5MB max per file.';
                if (supportingInput) supportingInput.required = true;
            } else {
                if (supportingRequiredSpan) { supportingRequiredSpan.classList.remove('text-red-500'); supportingRequiredSpan.classList.add('text-gray-400'); supportingRequiredSpan.textContent = '(Optional)'; }
                if (supportingHelp) supportingHelp.textContent = 'Optional, upload up to 5 files (PDF/JPG/PNG), 5MB max per file.';
                if (supportingInput) supportingInput.required = false;
            }
        }

        syncEndDateMin();
        renderOvertimeSpecificDates();

        @if(isset($balances['work_from_home']))
        if (typeSelect.value === 'work_from_home') {
            refreshWfhBalance();
        }
        @endif
    }

    typeSelect.addEventListener('change', updateRequestTypeSections);
    @if(isset($balances['work_from_home']))
    if (startDateInput) {
        startDateInput.addEventListener('change', refreshWfhBalance);
    }
    if (endDateInput) {
        endDateInput.addEventListener('change', refreshWfhBalance);
    }
    @endif
    if (offsetHoursInput) {
        offsetHoursInput.addEventListener('input', updateNoBalancePrompt);
    }
    // Initialize on page load (for validation errors / old input)
    const oldType = '{{ old("type") }}';
    if (!isStructuredHoursType(oldType) && oldType !== 'travel' && oldType !== 'sick_leave') {
        startDateInput.setAttribute('min', today);
    }
    updateRequestTypeSections();

    if (window.showNoBalanceModalOnLoad && noBalanceModal) {
        noBalanceModal.classList.remove('hidden');
    }

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

    // Form submission with loading animation
    const form = document.getElementById('leave-request-form');
    const submitIcon = document.getElementById('submit-icon');
    const submitText = document.getElementById('submit-text');

    form.addEventListener('submit', function(e) {
        const type = typeSelect.value;
        if (type && hasNoBalanceForType(type)) {
            e.preventDefault();
            updateNoBalancePrompt();
            return;
        }

        if (isStructuredHoursType(type)) {
            clampOvertimeDateInputs();
            const maxD = leaveDateBounds.today;
            const minD = leaveDateBounds.overtimeMin;
            if (!startDateInput.value || !endDateInput?.value
                || startDateInput.value > maxD || endDateInput.value > maxD
                || startDateInput.value < minD || endDateInput.value < minD) {
                e.preventDefault();
                alert('Additional Time can only be filed for dates within the last 7 days through today.');
                return;
            }
        }

        // Disable submit button and show loading state
        submitBtn.disabled = true;
        submitIcon.outerHTML = '<svg id="submit-icon" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        submitText.textContent = 'Submitting...';

        // Allow form to submit normally
        // The form will continue with its default submission behavior
    });
</script>
@endsection

