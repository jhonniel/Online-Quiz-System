@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col min-h-0">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-4 flex-shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Edit Leave Request</h1>
                    <p class="text-indigo-100 text-sm">Update your leave request based on admin feedback</p>
                </div>
            </div>
            <a href="{{ url('/leave-requests/' . $leaveRequest->id) }}"
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
            @if($leaveRequest->admin_notes)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Admin Feedback</h3>
                            <div class="mt-2 text-sm text-yellow-700 whitespace-pre-line">
                                {{ $leaveRequest->admin_notes }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <form action="{{ url('/leave-requests/' . $leaveRequest->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @php
                        $selectedType = old('type', $editData['type']);
                        $isVacationLeaveType = in_array($selectedType, ['leave', 'vacation_leave'], true);
                    @endphp

                    <!-- Request Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Request Type <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="type" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Request Type</option>
                            @if(auth()->user()->role === 'student')
                                <option value="additional_time" {{ old('type', $editData['type']) == 'additional_time' ? 'selected' : '' }}>Additional Time</option>
                                <option value="absent" {{ old('type', $editData['type']) == 'absent' ? 'selected' : '' }}>Absent</option>
                                <option value="other" {{ old('type', $editData['type']) == 'other' ? 'selected' : '' }}>Other</option>
                            @else
                                <option value="vacation_leave" {{ $isVacationLeaveType ? 'selected' : '' }}>Vacation Leave</option>
                                <option value="sick_leave" {{ old('type', $editData['type']) == 'sick_leave' ? 'selected' : '' }}>Sick Leave</option>
                                <option value="work_from_home" {{ old('type', $editData['type']) == 'work_from_home' ? 'selected' : '' }}>Work From Home</option>
                                @if(auth()->user()->role === 'employee')
                                    <option value="travel" {{ old('type', $editData['type']) == 'travel' ? 'selected' : '' }}>Travel</option>
                                @endif
                                <option value="absent" {{ old('type', $editData['type']) == 'absent' ? 'selected' : '' }}>Absent</option>
                                <option value="overtime" {{ old('type', $editData['type']) == 'overtime' ? 'selected' : '' }}>Overtime</option>
                                <option value="offset" {{ old('type', $editData['type']) == 'offset' ? 'selected' : '' }}>Offset</option>
                            @endif
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
                                   value="{{ old('start_date', $editData['start_date']) }}"
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
                                   value="{{ old('end_date', $editData['end_date']) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Leave blank for single day requests</p>
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Student Additional Time Input Mode -->
                    @if(auth()->user()->role === 'student')
                    <div id="additional-time-section" class="space-y-4 {{ old('type', $editData['type']) == 'additional_time' ? '' : 'hidden' }}">
                        <div class="border-t border-gray-200 pt-4 mt-2">
                            <h2 class="text-sm font-semibold text-gray-900 mb-2">Additional Time Details</h2>
                            <p class="text-xs text-gray-500 mb-3">
                                Choose how to submit Additional Time:
                                <strong>Total Hours</strong>, or <strong>Fixed Date(s)</strong> where each day is counted as <strong>8 hours</strong>.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Input Mode <span class="text-red-500">*</span></label>
                            <div class="space-y-2">
                                <label class="flex items-start gap-2 rounded-lg border border-gray-200 p-3 cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="additional_time_mode" value="fixed_date"
                                           class="mt-0.5 text-indigo-600 focus:ring-indigo-500"
                                           {{ old('additional_time_mode', $editData['additional_time_mode']) === 'fixed_date' ? 'checked' : '' }}>
                                    <span>
                                        <span class="block text-sm font-medium text-gray-900">Fixed Date(s)</span>
                                        <span class="block text-xs text-gray-500">Use Start/End Date. Each day is credited as 8 hours when approved.</span>
                                    </span>
                                </label>
                                <label class="flex items-start gap-2 rounded-lg border border-gray-200 p-3 cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="additional_time_mode" value="total_hours"
                                           class="mt-0.5 text-indigo-600 focus:ring-indigo-500"
                                           {{ old('additional_time_mode', $editData['additional_time_mode']) === 'total_hours' ? 'checked' : '' }}>
                                    <span>
                                        <span class="block text-sm font-medium text-gray-900">Total Hours</span>
                                        <span class="block text-xs text-gray-500">Enter exact hours in HH:MM format. Example: 12:30</span>
                                    </span>
                                </label>
                            </div>
                            @error('additional_time_mode')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="additional-time-hours-wrap" class="{{ old('additional_time_mode', $editData['additional_time_mode']) === 'total_hours' ? '' : 'hidden' }}">
                            <label for="additional_time_total_hours" class="block text-sm font-medium text-gray-700 mb-2">
                                Total Additional Time Hours (HH:MM) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="additional_time_total_hours" id="additional_time_total_hours"
                                   value="{{ old('additional_time_total_hours', $editData['additional_time_total_hours']) }}"
                                   placeholder="08:00"
                                   class="time-input w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">
                                Accepted format: <strong>HH:MM</strong>. Minutes must be 00-59.
                            </p>
                            @error('additional_time_total_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    @endif

                    <!-- Reason (label/required change to "Location of travel" when type = Travel) -->
                    <div id="reason-field">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            <span id="reason-label-text">Reason</span> <span id="reason-required-span" class="text-gray-400">(Optional)</span>
                        </label>
                        <textarea name="reason" id="reason" rows="4"
                                  placeholder="Please provide a reason for this request..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('reason', $editData['reason']) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500" id="reason-help">
                            Provide additional details about your request. For <strong>Overtime</strong>, if provided, this will appear as the explanation for extra hours. If not provided, it will be left blank.
                        </p>
                        <p class="mt-1 text-xs text-gray-500 hidden" id="reason-travel-help">
                            Enter the location or destination of your travel.
                        </p>
                        @error('reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Supporting Document (Optional; hidden for Travel) -->
                    <div id="supporting-section">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Supporting Document (e.g., Medical Certificate, Proof, Attachments)
                        </label>

                        @if($leaveRequest->supporting_document_path)
                            @php
                                $docUrl = null;
                                try {
                                    $docUrl = \Illuminate\Support\Facades\Storage::disk('digitalocean')
                                        ->temporaryUrl(
                                            $leaveRequest->supporting_document_path,
                                            now()->addMinutes(30),
                                            ['ResponseContentDisposition' => 'inline']
                                        );
                                } catch (\Throwable $e) {
                                    try {
                                        $docUrl = \Illuminate\Support\Facades\Storage::url($leaveRequest->supporting_document_path);
                                    } catch (\Throwable $e) {
                                        $docUrl = null;
                                    }
                                }
                            @endphp
                            @if($docUrl)
                                <div class="mb-3 p-3 bg-indigo-50 border border-indigo-100 rounded-lg">
                                    <p class="text-xs font-medium text-gray-700 mb-1">Current attachment</p>
                                    <a href="{{ $docUrl }}" target="_blank" rel="noopener"
                                       class="text-sm text-indigo-700 underline break-words">View / Download current file</a>
                                </div>
                            @endif
                        @endif

                        <input type="file" name="supporting_document" accept=".pdf,.jpg,.jpeg,.png"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500">Optional, PDF/JPG/PNG up to 5MB. Uploading a new file replaces the current attachment.</p>
                        @error('supporting_document')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Overtime Details (visible only when Request Type = Overtime) -->
                    <div id="overtime-section" class="space-y-4 {{ old('type', $editData['type']) == 'overtime' ? '' : 'hidden' }}">
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
                                   value="{{ old('overtime_hours', $editData['overtime_hours']) }}"
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
                                   value="{{ old('overtime_dates', $editData['overtime_dates']) }}"
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
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('overtime_tasks', $editData['overtime_tasks']) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                List the tasks that justify this overtime, including any ClickUp or ticket links.
                            </p>
                            @error('overtime_tasks')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Travel Details (visible only when Request Type = Travel, employees only) -->
                    @if(auth()->user()->role === 'employee')
                    <div id="travel-section" class="space-y-4 {{ old('type', $editData['type']) == 'travel' ? '' : 'hidden' }}">
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <h2 class="text-sm font-semibold text-gray-900 mb-2">Travel Details</h2>
                            <p class="text-xs text-gray-500 mb-3">
                                Travel requests are subject to approval. Upon approval, hours are added to your DTR. Past dates are allowed.
                            </p>
                            <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-3 py-2 mb-3">
                                <strong>Note:</strong> Only travel <strong>outside Davao</strong> will be approved for this request.
                            </p>
                            <p class="text-xs text-gray-600 mb-3">
                                You can only file TRAVEL for <strong>today or past dates</strong>. Travel for future dates can only be filed by an admin with full access.
                            </p>
                        </div>
                        <div>
                            <label for="travel_hours" class="block text-sm font-medium text-gray-700 mb-2">
                                Hours per Day <span class="text-gray-400">(Default 8:00)</span>
                            </label>
                            <input type="number" name="travel_hours" id="travel_hours" min="0" max="24" step="0.5" value="{{ old('travel_hours', '8') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="8">
                            <p class="mt-1 text-xs text-gray-500">Default is 8 hours per day if left blank.</p>
                            @error('travel_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    @endif

                    <!-- Offset Details (visible only when Request Type = Offset) -->
                    <div id="offset-section" class="space-y-4 {{ old('type', $editData['type']) == 'offset' ? '' : 'hidden' }}">
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
                                   value="{{ old('offset_hours', $editData['offset_hours']) }}"
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
                    <div id="wfh-section" class="space-y-4 {{ old('type', $editData['type']) == 'work_from_home' ? '' : 'hidden' }}">
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
                                <option value="working_remotely" {{ old('wfh_mode', $editData['wfh_mode']) == 'working_remotely' ? 'selected' : '' }}>
                                    Working remotely
                                </option>
                                <option value="request_to_be_excused" {{ old('wfh_mode', $editData['wfh_mode']) == 'request_to_be_excused' ? 'selected' : '' }}>
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
                                   value="{{ old('wfh_address', $editData['wfh_address']) }}"
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
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('wfh_tasks', $editData['wfh_tasks']) }}</textarea>
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
                        <a href="{{ url('/leave-requests/' . $leaveRequest->id) }}"
                           class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                            Cancel
                        </a>
                        <button type="submit" id="submit-btn"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="submit-icon" class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span id="submit-text">Update Request</span>
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
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const today = new Date().toISOString().split('T')[0];
    const overtimeSection = document.getElementById('overtime-section');
    const wfhSection = document.getElementById('wfh-section');
    const offsetSection = document.getElementById('offset-section');
    const travelSection = document.getElementById('travel-section');
    const supportingSection = document.getElementById('supporting-section');
    const additionalTimeSection = document.getElementById('additional-time-section');
    const additionalTimeHoursWrap = document.getElementById('additional-time-hours-wrap');
    const additionalTimeHoursInput = document.getElementById('additional_time_total_hours');
    const additionalTimeModeInputs = document.querySelectorAll('input[name="additional_time_mode"]');

    function getAdditionalTimeMode() {
        const selected = document.querySelector('input[name="additional_time_mode"]:checked');
        return selected ? selected.value : 'fixed_date';
    }

    function updateAdditionalTimeModeUI() {
        const isAdditionalTime = typeSelect.value === 'additional_time';
        if (additionalTimeSection) {
            additionalTimeSection.classList.toggle('hidden', !isAdditionalTime);
        }

        const mode = getAdditionalTimeMode();
        const useTotalHours = isAdditionalTime && mode === 'total_hours';
        if (additionalTimeHoursWrap) {
            additionalTimeHoursWrap.classList.toggle('hidden', !useTotalHours);
        }
        if (additionalTimeHoursInput) {
            additionalTimeHoursInput.required = useTotalHours;
        }
        if (endDateInput) {
            if (useTotalHours) {
                endDateInput.value = '';
                endDateInput.disabled = true;
            } else {
                endDateInput.disabled = false;
            }
        }
    }

    function updateRequestTypeSections() {
        // Travel: only today or past dates (no future for employees)
        if (typeSelect.value === 'travel') {
            if (startDateInput) { startDateInput.removeAttribute('min'); startDateInput.setAttribute('max', today); }
            if (endDateInput) endDateInput.setAttribute('max', today);
        } else {
            if (startDateInput) startDateInput.removeAttribute('max');
            if (endDateInput) endDateInput.removeAttribute('max');
        }
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

        if (travelSection) {
            if (typeSelect.value === 'travel') {
                travelSection.classList.remove('hidden');
            } else {
                travelSection.classList.add('hidden');
            }
        }

        updateAdditionalTimeModeUI();

        // For Travel: "Location of travel" required
        const reasonLabelText = document.getElementById('reason-label-text');
        const reasonRequiredSpan = document.getElementById('reason-required-span');
        const reasonInput = document.getElementById('reason');
        const reasonHelp = document.getElementById('reason-help');
        const reasonTravelHelp = document.getElementById('reason-travel-help');
        if (typeSelect.value === 'travel') {
            if (reasonLabelText) reasonLabelText.textContent = 'Location of travel ';
            if (reasonRequiredSpan) { reasonRequiredSpan.classList.remove('text-gray-400'); reasonRequiredSpan.classList.add('text-red-500'); reasonRequiredSpan.textContent = '*'; }
            if (reasonInput) reasonInput.required = true;
            if (reasonHelp) reasonHelp.classList.add('hidden');
            if (reasonTravelHelp) reasonTravelHelp.classList.remove('hidden');
            if (reasonInput) reasonInput.placeholder = 'Enter location or destination of travel...';
            if (supportingSection) supportingSection.classList.add('hidden');
        } else {
            if (reasonLabelText) reasonLabelText.textContent = 'Reason ';
            if (reasonRequiredSpan) { reasonRequiredSpan.classList.add('text-gray-400'); reasonRequiredSpan.classList.remove('text-red-500'); reasonRequiredSpan.textContent = '(Optional)'; }
            if (reasonInput) reasonInput.required = false;
            if (reasonHelp) reasonHelp.classList.remove('hidden');
            if (reasonTravelHelp) reasonTravelHelp.classList.add('hidden');
            if (reasonInput) reasonInput.placeholder = 'Please provide a reason for this request...';
            if (supportingSection) supportingSection.classList.remove('hidden');
        }
    }

    typeSelect.addEventListener('change', updateRequestTypeSections);
    additionalTimeModeInputs.forEach((input) => {
        input.addEventListener('change', updateAdditionalTimeModeUI);
    });
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

    // Form submission with loading animation
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submit-btn');
    const submitIcon = document.getElementById('submit-icon');
    const submitText = document.getElementById('submit-text');

    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            // Disable submit button and show loading state
            submitBtn.disabled = true;
            submitIcon.outerHTML = '<svg id="submit-icon" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            submitText.textContent = 'Updating...';
        });
    }
</script>
@endsection

