@extends('layouts.user')

@section('page-title', 'My Application')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h1 class="text-2xl font-bold text-white">My Application</h1>
                <p class="text-indigo-100 mt-1">View your application status and details</p>
            </div>
        </div>
    </div>

    @if(!$application)
        <!-- No Application Found -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No Application Found</h3>
                <p class="mt-2 text-sm text-gray-500">{{ $message ?? 'Please contact the administrator if you believe this is an error.' }}</p>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Application Status Card -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-medium text-gray-900">Application Status</h2>
                    </div>
                    <div class="px-6 py-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Current Status</h3>
                                <div class="mt-2">
                                    @if($application->status == 'pending')
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Pending Review
                                        </span>
                                    @elseif($application->status == 'accepted')
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Accepted
                                        </span>
                                    @elseif($application->status == 'rejected')
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Rejected
                                        </span>
                                    @elseif($application->status == 'interview_scheduled')
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            Interview Scheduled
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($application->interview_date)
                                <div class="text-right">
                                    <h3 class="text-sm font-medium text-gray-500">Interview Date & Time</h3>
                                    <p class="mt-2 text-lg font-semibold text-gray-900">
                                        {{ $application->interview_date->format('F j, Y g:i A') }}
                                    </p>
                                    @if(($application->interview_format ?? 'on_site') === 'online')
                                        <p class="mt-2 text-sm text-gray-600">Online interview</p>
                                        @if($application->interview_meeting_link)
                                            <p class="mt-1">
                                                <a href="{{ $application->interview_meeting_link }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Join meeting</a>
                                            </p>
                                        @endif
                                    @elseif(in_array($application->status, ['interview_scheduled', 'done_interview'], true))
                                        <p class="mt-2 text-sm text-gray-600">On-site interview</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-medium text-gray-900">Personal Information</h2>
                    </div>
                    <div class="px-6 py-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        @if($application->position_applied || $application->hiringPosition)
                            <div>
                                <label class="text-sm font-medium text-gray-500">Position Applied For</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $application->hiringPosition->title ?? $application->position_applied }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Cover Letter -->
                @if($application->cover_letter)
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-medium text-gray-900">Cover Letter</h2>
                        </div>
                        <div class="px-6 py-6">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $application->cover_letter }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Application Details -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
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

                @if($application->qualifiesForInternQuizPortal())
                    <div class="bg-indigo-50 shadow-sm rounded-lg border border-indigo-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-indigo-200 bg-indigo-100/50">
                            <h2 class="text-lg font-medium text-indigo-900">Internship Quizzes</h2>
                        </div>
                        <div class="px-6 py-6 space-y-4">
                            <p class="text-sm text-indigo-800">
                                You have access to the quiz portal for your internship. Only quizzes assigned by an administrator will appear in your list.
                            </p>
                            @if(($assignedQuizCount ?? 0) > 0)
                                <p class="text-sm text-indigo-900">
                                    <span class="font-semibold">{{ $assignedQuizCount }}</span>
                                    {{ $assignedQuizCount === 1 ? 'quiz is' : 'quizzes are' }} ready for you.
                                </p>
                                <a href="{{ url('/quizzes') }}"
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                    View My Quizzes
                                </a>
                            @else
                                <p class="text-sm text-indigo-800">
                                    No quizzes have been assigned yet. They will show on your dashboard and here once an administrator assigns them.
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Admin Notes -->
                @if($application->admin_notes)
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-medium text-gray-900">Admin Notes</h2>
                        </div>
                        <div class="px-6 py-6">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $application->admin_notes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

