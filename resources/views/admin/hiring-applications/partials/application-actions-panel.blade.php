            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Actions</h2>
                </div>
                <div class="px-6 py-6 space-y-3">
                    @if($application->status == 'pending')
                        <form action="{{ url('/admin/hiring-applications/' . $application->id . '/accept') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="interview_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Interview Date &amp; Time <span class="text-gray-500 font-normal">(optional)</span>
                                </label>
                                <input type="datetime-local"
                                       name="interview_date"
                                       id="interview_date"
                                       min="{{ date('Y-m-d\TH:i') }}"
                                       value="{{ old('interview_date') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm @error('interview_date') border-red-500 @enderror">
                                @error('interview_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Applicants are notified of date, time, and on-site or online details when you use <strong>Schedule Interview</strong> after acceptance.</p>
                            </div>
                            <div class="mb-3">
                                <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-1">
                                    Notes (Optional)
                                </label>
                                <textarea name="admin_notes"
                                          id="admin_notes"
                                          rows="3"
                                          placeholder="Add notes (optional)"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"></textarea>
                            </div>
                            <button type="submit" class="action-button w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Processing...">
                                <span class="button-text">Accept Application & Send Credentials</span>
                                <span class="button-spinner hidden ml-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                            <p class="mt-2 text-xs text-gray-500">
                                A user account will be automatically created with role "Applicant" and credentials will be sent via email.
                            </p>
                        </form>
                        <form action="{{ url('/admin/hiring-applications/' . $application->id . '/reject') }}" method="POST">
                            @csrf
                            <textarea name="admin_notes" rows="3" placeholder="Add notes (optional)"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm mb-3"></textarea>
                            <button type="submit" class="action-button w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Processing...">
                                <span class="button-text">Reject Application</span>
                                <span class="button-spinner hidden ml-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </form>
                    @elseif($application->status == 'rejected' && auth()->user()->isAdmin())
                        <form action="{{ url('/admin/hiring-applications/' . $application->id . '/reconsider') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="interview_date_reconsider" class="block text-sm font-medium text-gray-700 mb-1">
                                    Interview Date &amp; Time <span class="text-gray-500 font-normal">(optional)</span>
                                </label>
                                <input type="datetime-local"
                                       name="interview_date"
                                       id="interview_date_reconsider"
                                       min="{{ date('Y-m-d\TH:i') }}"
                                       value="{{ old('interview_date') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm @error('interview_date') border-red-500 @enderror">
                                @error('interview_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Official interview details are sent when you use <strong>Schedule Interview</strong>.</p>
                            </div>
                            <div class="mb-3">
                                <label for="admin_notes_reconsider" class="block text-sm font-medium text-gray-700 mb-1">
                                    Notes (Optional)
                                </label>
                                <textarea name="admin_notes"
                                          id="admin_notes_reconsider"
                                          rows="3"
                                          placeholder="Add notes (optional)"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"></textarea>
                            </div>
                            <button type="submit" class="action-button w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Processing...">
                                <span class="button-text">Accept as Reconsideration & Send Credentials</span>
                                <span class="button-spinner hidden ml-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                            <p class="mt-2 text-xs text-gray-500">
                                A user account will be automatically created with role "Applicant" and credentials will be sent via email with reconsideration message.
                            </p>
                        </form>
                    @elseif($application->status == 'accepted')
                        @php
                            $scheduleInterviewFormat = old('interview_format', $application->interview_format ?? 'on_site');
                        @endphp
                        <form action="{{ url('/admin/hiring-applications/' . $application->id . '/schedule-interview') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="interview_date_schedule" class="block text-sm font-medium text-gray-700 mb-1">
                                    Interview Date & Time <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local"
                                       name="interview_date"
                                       id="interview_date_schedule"
                                       required
                                       min="{{ date('Y-m-d\TH:i') }}"
                                       value="{{ old('interview_date', $application->interview_date ? $application->interview_date->format('Y-m-d\TH:i') : '') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm @error('interview_date') border-red-500 @enderror">
                                @error('interview_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <fieldset class="mb-3">
                                <legend class="block text-sm font-medium text-gray-700 mb-2">Interview format <span class="text-red-500">*</span></legend>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 text-sm text-gray-800 cursor-pointer">
                                        <input type="radio" name="interview_format" value="on_site" class="rounded-full border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ $scheduleInterviewFormat === 'on_site' ? 'checked' : '' }}>
                                        <span>On-site</span>
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-800 cursor-pointer">
                                        <input type="radio" name="interview_format" value="online" class="rounded-full border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ $scheduleInterviewFormat === 'online' ? 'checked' : '' }}>
                                        <span>Online (Zoom, Google Meet, etc.)</span>
                                    </label>
                                </div>
                                @error('interview_format')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </fieldset>
                            <div id="interview_meeting_link_wrap" class="mb-3 {{ $scheduleInterviewFormat === 'online' ? '' : 'hidden' }}">
                                <label for="interview_meeting_link_schedule" class="block text-sm font-medium text-gray-700 mb-1">
                                    Meeting link <span class="text-red-500">*</span>
                                </label>
                                <input type="url"
                                       name="interview_meeting_link"
                                       id="interview_meeting_link_schedule"
                                       value="{{ old('interview_meeting_link', $application->interview_meeting_link) }}"
                                       placeholder="https://zoom.us/j/... or https://meet.google.com/..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm @error('interview_meeting_link') border-red-500 @enderror">
                                @error('interview_meeting_link')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="admin_notes_schedule" class="block text-sm font-medium text-gray-700 mb-1">
                                    Notes (Optional)
                                </label>
                                <textarea name="admin_notes"
                                          id="admin_notes_schedule"
                                          rows="3"
                                          placeholder="Add interview notes (optional)"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">{{ old('admin_notes', $application->admin_notes) }}</textarea>
                            </div>
                            <button type="submit" class="action-button w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Processing...">
                                <span class="button-text">Schedule Interview</span>
                                <span class="button-spinner hidden ml-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </form>
                    @elseif($application->status == 'interview_scheduled')
                        @php
                            $rescheduleInterviewFormat = old('interview_format', $application->interview_format ?? 'on_site');
                        @endphp
                        <form action="{{ url('/admin/hiring-applications/' . $application->id . '/schedule-interview') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="interview_date_reschedule" class="block text-sm font-medium text-gray-700 mb-1">
                                    Interview Date & Time <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local"
                                       name="interview_date"
                                       id="interview_date_reschedule"
                                       required
                                       min="{{ date('Y-m-d\TH:i') }}"
                                       value="{{ old('interview_date', $application->interview_date ? $application->interview_date->format('Y-m-d\TH:i') : '') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm @error('interview_date') border-red-500 @enderror">
                                @error('interview_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <fieldset class="mb-3">
                                <legend class="block text-sm font-medium text-gray-700 mb-2">Interview format <span class="text-red-500">*</span></legend>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 text-sm text-gray-800 cursor-pointer">
                                        <input type="radio" name="interview_format" value="on_site" class="rounded-full border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ $rescheduleInterviewFormat === 'on_site' ? 'checked' : '' }}>
                                        <span>On-site</span>
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-800 cursor-pointer">
                                        <input type="radio" name="interview_format" value="online" class="rounded-full border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ $rescheduleInterviewFormat === 'online' ? 'checked' : '' }}>
                                        <span>Online (Zoom, Google Meet, etc.)</span>
                                    </label>
                                </div>
                                @error('interview_format')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </fieldset>
                            <div id="interview_meeting_link_wrap" class="mb-3 {{ $rescheduleInterviewFormat === 'online' ? '' : 'hidden' }}">
                                <label for="interview_meeting_link_reschedule" class="block text-sm font-medium text-gray-700 mb-1">
                                    Meeting link <span class="text-red-500">*</span>
                                </label>
                                <input type="url"
                                       name="interview_meeting_link"
                                       id="interview_meeting_link_reschedule"
                                       value="{{ old('interview_meeting_link', $application->interview_meeting_link) }}"
                                       placeholder="https://zoom.us/j/... or https://meet.google.com/..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm @error('interview_meeting_link') border-red-500 @enderror">
                                @error('interview_meeting_link')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="admin_notes_reschedule" class="block text-sm font-medium text-gray-700 mb-1">
                                    Notes (Optional)
                                </label>
                                <textarea name="admin_notes"
                                          id="admin_notes_reschedule"
                                          rows="3"
                                          placeholder="Add interview notes (optional)"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">{{ old('admin_notes', $application->admin_notes) }}</textarea>
                            </div>
                            <button type="submit" class="action-button w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Processing...">
                                <span class="button-text">Reschedule Interview</span>
                                <span class="button-spinner hidden ml-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </form>
                        @if($application->interview_date && $application->interview_date->lt(now()))
                            <form action="{{ url('/admin/hiring-applications/' . $application->id . '/send-follow-up') }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit" class="action-button w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Sending...">
                                    <span class="button-text">Send Follow-Up Email</span>
                                    <span class="button-spinner hidden ml-2">
                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                                <p class="mt-2 text-xs text-gray-500">
                                    Send a follow-up email with interview details and rescheduling information.
                                </p>
                            </form>
                        @endif
                        @if($application->user_id)
                            <form action="{{ url('/admin/hiring-applications/' . $application->id . '/mark-interview-done') }}" method="POST" class="mt-3">
                                @csrf
                                <div class="mb-3">
                                    <label for="admin_notes_done" class="block text-sm font-medium text-gray-700 mb-1">
                                        Notes (Optional)
                                    </label>
                                    <textarea name="admin_notes"
                                              id="admin_notes_done"
                                              rows="3"
                                              placeholder="Add interview completion notes (optional)"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">{{ old('admin_notes', $application->admin_notes) }}</textarea>
                                </div>
                                <button type="submit" class="action-button w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Processing...">
                                    <span class="button-text">Mark Interview as Done</span>
                                    <span class="button-spinner hidden ml-2">
                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                            </form>
                        @endif
                    @elseif($application->status == 'done_interview' && $application->user_id && auth()->user()->isSuperAdmin())
                        @php
                            $isInternship = $application->hiringPosition && strcasecmp($application->hiringPosition->employment_type ?? '', 'Internship') === 0;
                        @endphp
                        @if($isInternship)
                            <form action="{{ url('/admin/hiring-applications/' . $application->id . '/accept-intern') }}" method="POST" onsubmit="return confirm('Are you sure you want to accept this intern? Their role will change from applicant to student and they will be able to login.');">
                                @csrf
                                <div class="mb-3">
                                    <label for="admin_notes_intern_done" class="block text-sm font-medium text-gray-700 mb-1">
                                        Notes (Optional)
                                    </label>
                                    <textarea name="admin_notes"
                                              id="admin_notes_intern_done"
                                              rows="3"
                                              placeholder="Add notes about accepting intern (optional)"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">{{ old('admin_notes', $application->admin_notes) }}</textarea>
                                </div>
                                <button type="submit" class="action-button w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Processing...">
                                    <span class="button-text">Accept Intern</span>
                                    <span class="button-spinner hidden ml-2">
                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                                <p class="mt-2 text-xs text-gray-500">
                                    This will change the user role from applicant to student and activate their account.
                                </p>
                            </form>
                        @else
                            <form action="{{ url('/admin/hiring-applications/' . $application->id . '/mark-hired') }}" method="POST" onsubmit="return confirm('Are you sure you want to mark this applicant as hired? Their role will change from applicant to employee and they will be able to login.');">
                                @csrf
                                <div class="mb-3">
                                    <label for="admin_notes_hired_done" class="block text-sm font-medium text-gray-700 mb-1">
                                        Notes (Optional)
                                    </label>
                                    <textarea name="admin_notes"
                                              id="admin_notes_hired_done"
                                              rows="3"
                                              placeholder="Add notes about hiring (optional)"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">{{ old('admin_notes', $application->admin_notes) }}</textarea>
                                </div>
                                <button type="submit" class="action-button w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Processing...">
                                    <span class="button-text">Mark as Hired</span>
                                    <span class="button-spinner hidden ml-2">
                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                                <p class="mt-2 text-xs text-gray-500">
                                    This will change the user role from applicant to employee and activate their account.
                                </p>
                            </form>
                        @endif
                    @endif

                    @if(($application->status == 'interview_scheduled' || $application->status == 'accepted') && $application->user_id && auth()->user()->isSuperAdmin())
                        @php
                            $isInternship = $application->hiringPosition && strcasecmp($application->hiringPosition->employment_type ?? '', 'Internship') === 0;
                        @endphp
                        @if($isInternship)
                            <form action="{{ url('/admin/hiring-applications/' . $application->id . '/accept-intern') }}" method="POST" onsubmit="return confirm('Are you sure you want to accept this intern? Their role will change from applicant to student and they will be able to login.');">
                                @csrf
                                <div class="mb-3">
                                    <label for="admin_notes_intern" class="block text-sm font-medium text-gray-700 mb-1">
                                        Notes (Optional)
                                    </label>
                                    <textarea name="admin_notes"
                                              id="admin_notes_intern"
                                              rows="3"
                                              placeholder="Add notes about accepting intern (optional)"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">{{ old('admin_notes', $application->admin_notes) }}</textarea>
                                </div>
                                <button type="submit" class="action-button w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Processing...">
                                    <span class="button-text">Accept Intern</span>
                                    <span class="button-spinner hidden ml-2">
                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                                <p class="mt-2 text-xs text-gray-500">
                                    This will change the user role from applicant to student and activate their account.
                                </p>
                            </form>
                        @else
                            <form action="{{ url('/admin/hiring-applications/' . $application->id . '/mark-hired') }}" method="POST" onsubmit="return confirm('Are you sure you want to mark this applicant as hired? They will be able to login to their account.');">
                                @csrf
                                <div class="mb-3">
                                    <label for="admin_notes_hired" class="block text-sm font-medium text-gray-700 mb-1">
                                        Notes (Optional)
                                    </label>
                                    <textarea name="admin_notes"
                                              id="admin_notes_hired"
                                              rows="3"
                                              placeholder="Add notes about hiring (optional)"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">{{ old('admin_notes', $application->admin_notes) }}</textarea>
                                </div>
                                <button type="submit" class="action-button w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Processing...">
                                    <span class="button-text">Mark as Hired</span>
                                    <span class="button-spinner hidden ml-2">
                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                                <p class="mt-2 text-xs text-gray-500">
                                    This will activate the user account and allow them to login.
                                </p>
                            </form>
                        @endif
                    @endif
                    @if($application->status == 'hired' && $application->user_id)
                        <form action="{{ url('/admin/hiring-applications/' . $application->id . '/cancel-hired') }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel the hired status? The user account will be deactivated and they will not be able to login.');">
                            @csrf
                            <div class="mb-3">
                                <label for="admin_notes_cancel" class="block text-sm font-medium text-gray-700 mb-1">
                                    Notes (Optional)
                                </label>
                                <textarea name="admin_notes"
                                          id="admin_notes_cancel"
                                          rows="3"
                                          placeholder="Add notes about cancellation (optional)"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">{{ old('admin_notes', $application->admin_notes) }}</textarea>
                            </div>
                            <button type="submit" class="action-button w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Processing...">
                                <span class="button-text">Cancel Hired Status</span>
                                <span class="button-spinner hidden ml-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                            <p class="mt-2 text-xs text-gray-500">
                                This will deactivate the user account and change status back to {{ $application->interview_date ? 'Interview Scheduled' : 'Accepted' }}.
                            </p>
                        </form>
                    @endif
                    @if(auth()->user()->isAdmin())
                        <form action="{{ url('/admin/hiring-applications/' . $application->id) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-button w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Deleting...">
                            <span class="button-text">Delete Application</span>
                            <span class="button-spinner hidden ml-2">
                                <svg class="animate-spin h-5 w-5 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
