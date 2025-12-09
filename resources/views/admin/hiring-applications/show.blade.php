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
                                <label class="text-sm font-medium text-gray-500">Interview Date</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $application->interview_date->format('F j, Y') }}</p>
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
                    @if($application->admin_notes)
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $application->admin_notes }}</p>
                    @else
                        <p class="text-sm text-gray-500">No notes added yet.</p>
                    @endif
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
                                    Interview Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date"
                                       name="interview_date"
                                       id="interview_date"
                                       required
                                       min="{{ date('Y-m-d') }}"
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
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                Accept Application & Send Credentials
                            </button>
                            <p class="mt-2 text-xs text-gray-500">
                                A user account will be automatically created with role "Applicant" and credentials will be sent via email.
                            </p>
                        </form>
                        <form action="{{ route('admin.hiring-applications.reject', $application) }}" method="POST">
                            @csrf
                            <textarea name="admin_notes" rows="3" placeholder="Add notes (optional)"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm mb-3"></textarea>
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                                Reject Application
                            </button>
                        </form>
                    @elseif($application->status == 'accepted')
                        <form action="{{ route('admin.hiring-applications.schedule-interview', $application) }}" method="POST">
                            @csrf
                            <textarea name="admin_notes" rows="3" placeholder="Add interview notes (optional)"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm mb-3"></textarea>
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                Schedule Interview
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('admin.hiring-applications.destroy', $application) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this application?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Delete Application
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Link copied to clipboard!');
    });
}
</script>
@endsection

