@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('admin.hiring-applications.index') }}" class="mr-4 text-white hover:text-indigo-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $application->full_name }}</h1>
                    <p class="text-indigo-100">Application Details</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                @if($application->status == 'pending')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        Pending Review
                    </span>
                @elseif($application->status == 'accepted')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        Accepted
                    </span>
                @elseif($application->status == 'rejected')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        Rejected
                    </span>
                @elseif($application->status == 'interview_scheduled')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        Interview Scheduled
                    </span>
                @elseif($application->status == 'done_interview')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                        Interview Done
                    </span>
                @elseif($application->status == 'hired')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        Hired
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Personal Information</h2>
                </div>
                <div class="px-6 py-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">First Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->first_name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Last Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->last_name }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Email</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $application->email }}</p>
                    </div>
                    @if($application->phone)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Phone</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->phone }}</p>
                        </div>
                    @endif
                    @if($application->birth_date)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->birth_date->format('F j, Y') }}</p>
                        </div>
                    @endif
                    @if($application->address)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Address</label>
                            <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $application->address }}</p>
                        </div>
                    @endif
                    @if($application->hiringPosition && strcasecmp($application->hiringPosition->employment_type ?? '', 'Internship') === 0 && $application->school)
                        <div>
                            <label class="text-sm font-medium text-gray-500">School</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->school }}</p>
                        </div>
                    @endif
                    @if($application->position_applied)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Position Applied For</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->position_applied }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cover Letter -->
            @if($application->cover_letter)
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Cover Letter</h2>
                    </div>
                    <div class="px-6 py-6">
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $application->cover_letter }}</p>
                    </div>
                </div>
            @endif

            <!-- Resume -->
            @if($application->resume_path || $application->resume_link)
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900">Resume</h2>
                        @if($application->resume_path)
                            <a href="{{ route('admin.hiring-applications.download-resume', $application) }}"
                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download
                            </a>
                        @endif
                    </div>
                    <div class="px-6 py-6 space-y-4">
                        @if($application->resume_path)
                            <div>
                                <label class="text-sm font-medium text-gray-500 block mb-2">Uploaded Resume</label>
                                <div class="border border-gray-300 rounded-lg overflow-hidden bg-gray-50">
                                    <iframe src="{{ route('admin.hiring-applications.view-resume', $application) }}"
                                            class="w-full h-[600px] border-0"
                                            title="Resume Preview">
                                    </iframe>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">If the resume doesn't display, you can download it using the button above.</p>
                            </div>
                        @endif
                        @if($application->resume_link)
                            <div>
                                <label class="text-sm font-medium text-gray-500 block mb-2">Resume Link</label>
                                <a href="{{ $application->resume_link }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    Open Resume Link
                                </a>
                                <p class="mt-2 text-xs text-gray-500 break-all">{{ $application->resume_link }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Status</h2>
                </div>
                <div class="px-6 py-6">
                    @if($application->status == 'pending')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            Pending Review
                        </span>
                    @elseif($application->status == 'accepted')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            Accepted
                        </span>
                        @if($application->interview_date)
                            <div class="mt-3">
                                <label class="text-sm font-medium text-gray-500">Interview Date & Time</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $application->interview_date->format('F j, Y g:i A') }}</p>
                            </div>
                        @endif
                    @elseif($application->status == 'rejected')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            Rejected
                        </span>
                @elseif($application->status == 'interview_scheduled')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        Interview Scheduled
                    </span>
                    @if($application->interview_date)
                        <div class="mt-3">
                            <label class="text-sm font-medium text-gray-500">Interview Date & Time</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->interview_date->format('F j, Y g:i A') }}</p>
                        </div>
                    @endif
                    <div class="mt-3">
                        <p class="text-sm text-gray-600">User can login and take quizzes.</p>
                    </div>
                    @elseif($application->status == 'done_interview')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            Interview Done
                        </span>
                        @if($application->interview_date)
                            <div class="mt-3">
                                <label class="text-sm font-medium text-gray-500">Interview Date & Time</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $application->interview_date->format('F j, Y g:i A') }}</p>
                            </div>
                        @endif
                        <div class="mt-3">
                            <p class="text-sm text-gray-600">User can login and take quizzes. Ready to mark as hired.</p>
                        </div>
                @elseif($application->status == 'hired')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            Hired
                        </span>
                        <div class="mt-3">
                            <p class="text-sm text-gray-600">User account is active and can login.</p>
                        </div>
                    @endif

                    @if($application->acceptance_token && $application->isTokenValid())
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <label class="text-sm font-medium text-gray-500">Acceptance Link</label>
                            <div class="mt-2 flex">
                                <input type="text" readonly value="{{ route('hiring.accept', $application->acceptance_token) }}"
                                       class="flex-1 px-3 py-2 text-xs border border-gray-300 rounded-l-md bg-gray-50">
                                <button onclick="copyToClipboard(this.previousElementSibling.value)"
                                        class="px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 hover:bg-gray-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Share this link with the applicant to create their account</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Application Details -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Application Details</h2>
                </div>
                <div class="px-6 py-6 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Applied Date</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $application->created_at->format('M j, Y g:i A') }}</p>
                    </div>
                    @if($application->reviewed_at)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Reviewed Date</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $application->reviewed_at->format('M j, Y g:i A') }}</p>
                        </div>
                        @if($application->reviewer)
                            <div>
                                <label class="text-sm font-medium text-gray-500">Reviewed By</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $application->reviewer->name }}</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Admin Notes -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Admin Notes</h2>
                </div>
                <div class="px-6 py-6">
                    <form action="{{ route('admin.hiring-applications.update-admin-notes', $application) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <textarea name="admin_notes"
                                  id="admin_notes"
                                  rows="4"
                                  placeholder="Add notes about this application..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('admin_notes', $application->admin_notes) }}</textarea>
                        @error('admin_notes')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-3">
                            <button type="submit" class="action-button inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Saving...">
                                <span class="button-text">Save Notes</span>
                                <span class="button-spinner hidden ml-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Actions</h2>
                </div>
                <div class="px-6 py-6 space-y-3">
                    @if($application->status == 'pending')
                        <form action="{{ route('admin.hiring-applications.accept', $application) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="interview_date" class="block text-sm font-medium text-gray-700 mb-1">
                                    Interview Date & Time <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local"
                                       name="interview_date"
                                       id="interview_date"
                                       required
                                       min="{{ date('Y-m-d\TH:i') }}"
                                       value="{{ old('interview_date') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm @error('interview_date') border-red-500 @enderror">
                                @error('interview_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
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
                        <form action="{{ route('admin.hiring-applications.reject', $application) }}" method="POST">
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
                        <form action="{{ route('admin.hiring-applications.reconsider', $application) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="interview_date_reconsider" class="block text-sm font-medium text-gray-700 mb-1">
                                    Interview Date & Time <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local"
                                       name="interview_date"
                                       id="interview_date_reconsider"
                                       required
                                       min="{{ date('Y-m-d\TH:i') }}"
                                       value="{{ old('interview_date') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm @error('interview_date') border-red-500 @enderror">
                                @error('interview_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
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
                        <form action="{{ route('admin.hiring-applications.schedule-interview', $application) }}" method="POST">
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
                        <form action="{{ route('admin.hiring-applications.schedule-interview', $application) }}" method="POST">
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
                        @if($application->user_id)
                            <form action="{{ route('admin.hiring-applications.mark-interview-done', $application) }}" method="POST" class="mt-3">
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
                    @elseif($application->status == 'done_interview' && $application->user_id)
                        <form action="{{ route('admin.hiring-applications.mark-hired', $application) }}" method="POST" onsubmit="return confirm('Are you sure you want to mark this applicant as hired? Their role will change from applicant to employee and they will be able to login.');">
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

                    @if(($application->status == 'interview_scheduled' || $application->status == 'accepted') && $application->user_id)
                        <form action="{{ route('admin.hiring-applications.mark-hired', $application) }}" method="POST" onsubmit="return confirm('Are you sure you want to mark this applicant as hired? They will be able to login to their account.');">
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
                    @if($application->status == 'hired' && $application->user_id)
                        <form action="{{ route('admin.hiring-applications.cancel-hired', $application) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel the hired status? The user account will be deactivated and they will not be able to login.');">
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
                        <form action="{{ route('admin.hiring-applications.destroy', $application) }}" method="POST" class="delete-form">
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
        </div>
    </div>

    <!-- Activity Logs -->
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6 mt-6">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4">Activity Log</h2>
        <p class="text-sm text-gray-500 mb-4">Track all updates and actions performed on this application.</p>
        @if($activityLogs && $activityLogs->count() > 0)
            <div class="space-y-4">
                @foreach($activityLogs as $log)
                    @php
                        $actionLabel = match($log->action) {
                            'hiring_application_accepted' => 'Application Accepted',
                            'hiring_application_rejected' => 'Application Rejected',
                            'hiring_application_reconsidered' => 'Application Reconsidered',
                            'hiring_application_interview_scheduled' => 'Interview Scheduled',
                            'hiring_application_interview_rescheduled' => 'Interview Rescheduled',
                            'hiring_application_interview_done' => 'Interview Done',
                            'hiring_application_hired' => 'Marked as Hired',
                            'hiring_application_hired_cancelled' => 'Hired Status Cancelled',
                            'hiring_application_deleted' => 'Application Deleted',
                            default => ucfirst(str_replace('_', ' ', str_replace('hiring_application_', '', $log->action))),
                        };
                        $borderColor = match($log->action) {
                            'hiring_application_accepted', 'hiring_application_reconsidered', 'hiring_application_hired' => 'border-green-500',
                            'hiring_application_rejected', 'hiring_application_hired_cancelled' => 'border-red-500',
                            'hiring_application_interview_scheduled', 'hiring_application_interview_rescheduled' => 'border-blue-500',
                            'hiring_application_interview_done' => 'border-purple-500',
                            'hiring_application_deleted' => 'border-gray-400',
                            default => 'border-gray-400',
                        };
                        $badgeColor = match($log->action) {
                            'hiring_application_accepted', 'hiring_application_reconsidered', 'hiring_application_hired' => 'bg-green-100 text-green-800',
                            'hiring_application_rejected', 'hiring_application_hired_cancelled' => 'bg-red-100 text-red-800',
                            'hiring_application_interview_scheduled', 'hiring_application_interview_rescheduled' => 'bg-blue-100 text-blue-800',
                            'hiring_application_interview_done' => 'bg-purple-100 text-purple-800',
                            'hiring_application_deleted' => 'bg-gray-100 text-gray-800',
                            default => 'bg-gray-100 text-gray-800',
                        };
                    @endphp
                    <div class="border-l-4 {{ $borderColor }} pl-4 py-2">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badgeColor }}">
                                        {{ $actionLabel }}
                                    </span>
                                </div>
                                <div class="mt-2 text-sm text-gray-700">
                                    @if($log->user)
                                        <span class="font-medium">{{ $log->user->name }}</span>
                                        <span class="text-gray-500">performed this action</span>
                                    @else
                                        <span class="text-gray-500">Action performed by unknown user</span>
                                    @endif
                                </div>
                                @if($log->metadata)
                                    <div class="mt-2 space-y-1">
                                        @if(isset($log->metadata['interview_date']))
                                            <div class="text-sm text-gray-600">
                                                <span class="font-medium">Interview Date:</span> {{ \Carbon\Carbon::parse($log->metadata['interview_date'])->format('F j, Y g:i A') }}
                                            </div>
                                        @endif
                                        @if(isset($log->metadata['admin_notes']) && !empty($log->metadata['admin_notes']))
                                            <div class="text-sm text-gray-600 bg-gray-50 rounded p-2 mt-2">
                                                {{ $log->metadata['admin_notes'] }}
                                            </div>
                                        @endif
                                        @if(isset($log->metadata['is_reschedule']) && $log->metadata['is_reschedule'])
                                            <div class="text-sm text-blue-600">
                                                <span class="font-medium">Type:</span> Rescheduled
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 ml-4">
                                {{ $log->created_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="mt-2 text-sm">No activity log entries yet.</p>
            </div>
        @endif
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Link copied to clipboard!');
    });
}

// Loading animation for action buttons
document.addEventListener('DOMContentLoaded', function() {
    // Get all action buttons
    const actionButtons = document.querySelectorAll('button[type="submit"].action-button');

    actionButtons.forEach(button => {
        const form = button.closest('form');

        if (form) {
            // Check if this is a delete form
            const isDeleteForm = form.classList.contains('delete-form') ||
                                 form.action.includes('/destroy') ||
                                 form.querySelector('input[name="_method"][value="DELETE"]');

            if (isDeleteForm) {
                // Handle delete forms with confirm dialog
                form.addEventListener('submit', function(e) {
                    const confirmed = confirm('Are you sure you want to delete this application?');
                    if (confirmed) {
                        showLoadingState(button);
                    } else {
                        e.preventDefault();
                        return false;
                    }
                });
            } else {
                // For other forms, show loading on submit
                form.addEventListener('submit', function(e) {
                    if (!button.disabled) {
                        showLoadingState(button);
                    }
                });
            }
        }
    });

    function showLoadingState(button) {
        const buttonText = button.querySelector('.button-text');
        const buttonSpinner = button.querySelector('.button-spinner');
        const loadingText = button.getAttribute('data-loading-text') || 'Processing...';

        // Disable button
        button.disabled = true;

        // Show spinner and update text
        if (buttonText) {
            buttonText.textContent = loadingText;
        }
        if (buttonSpinner) {
            buttonSpinner.classList.remove('hidden');
        }
    }
});
</script>
@endsection

