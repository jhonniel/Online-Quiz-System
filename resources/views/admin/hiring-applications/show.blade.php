@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ url('/admin/hiring-applications') }}" class="mr-4 text-white hover:text-indigo-100">
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
                    @php
                        $isInternship = $application->hiringPosition && strcasecmp((string) ($application->hiringPosition->employment_type ?? ''), 'Internship') === 0;
                        $statusLabel = $isInternship ? 'Internship Accepted' : 'Hired';
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        {{ $statusLabel }}
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

    @if($errors->has('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-sm font-medium text-red-800">{{ $errors->first('error') }}</p>
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

            @php
                $__resumeExt = '';
                $__resumeCanEmbed = false;
                if ($application->resume_path) {
                    $__resumeExt = strtolower(pathinfo($application->resume_path, PATHINFO_EXTENSION));
                    $__resumeCanEmbed = in_array($__resumeExt, ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp'], true);
                }
            @endphp

            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-medium text-gray-900">Resume</h2>
                    @if($application->resume_path)
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ url('/admin/hiring-applications/' . $application->id . '/view-resume') }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                View
                            </a>
                            <a href="{{ url('/admin/hiring-applications/' . $application->id . '/download-resume') }}"
                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Download
                            </a>
                        </div>
                    @endif
                </div>
                <div class="px-6 py-6 space-y-4">
                    @if($application->resume_path)
                        <p class="text-sm text-gray-900">
                            Uploaded file
                            @if($__resumeExt)
                                <span class="text-gray-500">(.{{ $__resumeExt }})</span>
                            @endif
                        </p>
                        @if($__resumeCanEmbed)
                            <div class="border border-gray-300 rounded-lg overflow-hidden bg-gray-50">
                                <iframe src="{{ url('/admin/hiring-applications/' . $application->id . '/view-resume') }}"
                                        class="w-full border-0"
                                        style="min-height: 280px; height: 50vh; max-height: 640px;"
                                        title="Resume preview">
                                </iframe>
                            </div>
                            <p class="text-xs text-gray-500">
                                @if($application->cover_letter)
                                    Placed under your cover letter. If the preview is blank, use <strong>View</strong> or <strong>Download</strong> above.
                                @else
                                    If the preview is blank, use <strong>View</strong> or <strong>Download</strong> above.
                                @endif
                            </p>
                        @else
                            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                <p class="font-medium">No inline preview for .{{ $__resumeExt ?: 'file' }}</p>
                                <p class="mt-1 text-amber-800">Open the file with <strong>View</strong> or <strong>Download</strong> above.</p>
                            </div>
                        @endif
                    @endif
                    @if($application->resume_link)
                        <div>
                            <label class="text-sm font-medium text-gray-500 block mb-2">External link</label>
                            <a href="{{ $application->resume_link }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 break-all">
                                Open resume link
                                <svg class="w-4 h-4 ml-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                            <p class="mt-2 text-xs text-gray-500 break-all">{{ $application->resume_link }}</p>
                        </div>
                    @endif
                    @if(! $application->resume_path && ! $application->resume_link)
                        <p class="text-sm text-gray-500">No resume or link on file.</p>
                    @endif
                </div>
            </div>
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
                        @if($application->hiringPosition && strcasecmp((string) ($application->hiringPosition->employment_type ?? ''), 'Internship') === 0)
                            <div class="mt-3 text-sm text-gray-600">
                                <p>Use <strong>Intern quiz</strong> below to assign quizzes (no interview required first).</p>
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
                    @if(($application->interview_format ?? 'on_site') === 'online')
                        <div class="mt-3">
                            <label class="text-sm font-medium text-gray-500">Interview format</label>
                            <p class="mt-1 text-sm text-gray-900">Online</p>
                            @if($application->interview_meeting_link)
                                <p class="mt-1 text-xs text-gray-600 break-all">
                                    <a href="{{ $application->interview_meeting_link }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-800">Open meeting link</a>
                                </p>
                            @endif
                        </div>
                    @elseif($application->interview_date)
                        <div class="mt-3">
                            <label class="text-sm font-medium text-gray-500">Interview format</label>
                            <p class="mt-1 text-sm text-gray-900">On-site</p>
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
                        @if(($application->interview_format ?? 'on_site') === 'online')
                            <div class="mt-3">
                                <label class="text-sm font-medium text-gray-500">Interview format</label>
                                <p class="mt-1 text-sm text-gray-900">Online</p>
                                @if($application->interview_meeting_link)
                                    <p class="mt-1 text-xs text-gray-600 break-all">
                                        <a href="{{ $application->interview_meeting_link }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-800">Open meeting link</a>
                                    </p>
                                @endif
                            </div>
                        @elseif($application->interview_date)
                            <div class="mt-3">
                                <label class="text-sm font-medium text-gray-500">Interview format</label>
                                <p class="mt-1 text-sm text-gray-900">On-site</p>
                            </div>
                        @endif
                        <div class="mt-3">
                            <p class="text-sm text-gray-600">User can login and take quizzes. Ready to mark as hired.</p>
                        </div>
                @elseif($application->status == 'hired')
                        @php
                            $isInternship = $application->hiringPosition && strcasecmp((string) ($application->hiringPosition->employment_type ?? ''), 'Internship') === 0;
                            $statusLabel = $isInternship ? 'Internship Accepted' : 'Hired';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            {{ $statusLabel }}
                        </span>
                        <div class="mt-3">
                            <p class="text-sm text-gray-600">User account is active and can login.</p>
                        </div>
                    @endif

                    @if($application->acceptance_token && $application->isTokenValid())
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <label class="text-sm font-medium text-gray-500">Acceptance Link</label>
                            <div class="mt-2 flex">
                                <input type="text" readonly value="{{ url('/hiring/accept/' . $application->acceptance_token) }}"
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

            @if(!empty($showInternQuizPanel))
            @php
                $noInternQuizAssignmentsYet = ! isset($internQuizAssignments) || $internQuizAssignments->isEmpty();
            @endphp
            <details
                class="bg-white shadow-sm rounded-lg overflow-hidden {{ $noInternQuizAssignmentsYet ? 'border-2 border-emerald-400 shadow-[0_0_0_1px_rgba(52,211,153,0.5),0_0_20px_rgba(16,185,129,0.45),0_0_40px_rgba(5,150,105,0.2)]' : 'border border-indigo-200' }}"
                @if($errors->has('quiz_ids') || $errors->has('quiz_ids.*') || $errors->has('due_date') || $errors->has('resend_quiz_ids') || $errors->has('resend_quiz_ids.*')) open @endif
            >
                <summary class="px-6 py-4 cursor-pointer list-none bg-indigo-50/60 border-b border-gray-200 [&::-webkit-details-marker]:hidden flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-lg font-medium text-gray-900">Intern quiz</h2>
                        <p class="mt-1 text-sm text-gray-600">After <strong>acceptance</strong>, select quizzes below (interview optional). Optional due date applies to this batch only.</p>
                    </div>
                    <svg class="w-5 h-5 shrink-0 text-gray-500 mt-1 opacity-70" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </summary>
                <div class="px-6 py-6 space-y-4">
                    @if(isset($assignableQuizzes) && $assignableQuizzes->isNotEmpty())
                        <form method="post" action="{{ url('/admin/hiring-applications/'.$application->id.'/assign-intern-quiz') }}" class="space-y-3">
                            @csrf
                            <fieldset>
                                <legend class="block text-sm font-medium text-gray-700 mb-2">Quizzes</legend>
                                @php $oldQuizIds = array_map('intval', (array) old('quiz_ids', [])); @endphp
                                <div class="max-h-64 overflow-y-auto rounded-md border border-gray-300 bg-white divide-y divide-gray-100">
                                    @foreach($assignableQuizzes as $q)
                                        <label class="flex items-start gap-3 px-3 py-2.5 hover:bg-gray-50 cursor-pointer text-left">
                                            <input type="checkbox"
                                                   name="quiz_ids[]"
                                                   value="{{ $q->id }}"
                                                   class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                   @checked(in_array((int) $q->id, $oldQuizIds, true))>
                                            <span class="text-sm text-gray-900 leading-snug">
                                                <span class="font-medium">{{ $q->title }}</span>
                                                @if($q->quiz_code)
                                                    <span class="text-gray-500"> · Code <span class="font-mono text-gray-800">{{ $q->quiz_code }}</span></span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('quiz_ids')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                @error('quiz_ids.*')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Re-assigning the same quiz only updates the due date.</p>
                            </fieldset>
                            <div>
                                <label for="intern_quiz_due" class="block text-sm font-medium text-gray-700 mb-1">Due date <span class="text-gray-400 font-normal">(optional, applies to all selected)</span></label>
                                <input type="date" name="due_date" id="intern_quiz_due" value="{{ old('due_date') }}" min="{{ now()->format('Y-m-d') }}"
                                    class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @error('due_date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit"
                                class="action-button w-full inline-flex justify-center items-center px-4 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                data-loading-text="Sending emails…">
                                <span class="button-text">Assign selected quiz(es) &amp; send email(s)</span>
                                <span class="button-spinner hidden ml-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </form>
                    @else
                        <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-md px-3 py-2">No active quizzes are available. Create and activate a quiz under <strong>Admin → Quizzes</strong> first.</p>
                    @endif

                    @if(isset($internQuizAssignments) && $internQuizAssignments->isNotEmpty())
                        <div class="pt-2 border-t border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Quiz assignments</h3>
                            <ul class="space-y-3 text-sm text-gray-700">
                                @foreach($internQuizAssignments as $asg)
                                    @php
                                        $rankInfo = $internQuizRankMeta[$asg->id] ?? null;
                                        $totalQ = (int) ($asg->quiz->total_questions ?? 0);
                                        $best = (int) ($asg->best_score ?? 0);
                                    @endphp
                                    <li class="flex flex-col gap-2 border border-gray-100 rounded-md px-3 py-3 bg-gray-50">
                                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                            <div class="min-w-0">
                                                <span class="font-medium text-gray-900">{{ $asg->quiz->title ?? 'Quiz' }}</span>
                                                @if($asg->quiz && $asg->quiz->quiz_code)
                                                    <span class="text-xs text-gray-500"> · Code <span class="font-mono text-gray-800">{{ $asg->quiz->quiz_code }}</span></span>
                                                @endif
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2 sm:justify-end sm:shrink-0">
                                                <span class="text-sm tabular-nums">
                                                    <span class="text-gray-500">Score</span>
                                                    <span class="ml-1 font-semibold text-gray-900">
                                                        @if($totalQ > 0)
                                                            @if($asg->status === 'assigned' && ! $asg->started_at)
                                                                <span class="text-gray-400 font-normal">—</span><span class="text-gray-400 font-medium">/</span>{{ $totalQ }}
                                                            @else
                                                                {{ $best }}<span class="text-gray-400 font-medium">/</span>{{ $totalQ }}
                                                            @endif
                                                        @else
                                                            {{ $best > 0 ? $best : '—' }}
                                                        @endif
                                                    </span>
                                                </span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $asg->getStatusBadgeClass() }}">{{ $asg->getStatusText() }}</span>
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-600 space-y-1">
                                            @if($asg->due_date)
                                                <div><span class="font-medium text-gray-700">Due:</span> {{ $asg->due_date->format('M j, Y') }}</div>
                                            @endif
                                            @if($asg->started_at)
                                                <div><span class="font-medium text-gray-700">Started:</span> {{ $asg->started_at->format('M j, Y g:i A') }}</div>
                                            @endif
                                            @if($asg->is_completed && $asg->last_attempt_at)
                                                <div><span class="font-medium text-gray-700">Last submitted:</span> {{ $asg->last_attempt_at->format('M j, Y g:i A') }}</div>
                                            @endif
                                            @if($asg->is_completed && $rankInfo)
                                                <div>
                                                    <span class="font-medium text-gray-700">Rank:</span>
                                                    {{ $rankInfo['rank'] }} of {{ $rankInfo['of'] }} (this quiz)
                                                </div>
                                            @endif
                                        </div>
                                        @php
                                            $recentAttempts = ($internQuizRecentAttempts ?? [])[$asg->id] ?? collect();
                                        @endphp
                                        @if($recentAttempts->isNotEmpty())
                                            <div class="mt-2 overflow-x-auto rounded border border-gray-200 bg-white">
                                                <table class="min-w-full text-xs">
                                                    <thead class="bg-gray-100 text-gray-600">
                                                        <tr>
                                                            <th class="px-2 py-1.5 text-left font-medium">#</th>
                                                            <th class="px-2 py-1.5 text-left font-medium">Score</th>
                                                            <th class="px-2 py-1.5 text-left font-medium">%</th>
                                                            <th class="px-2 py-1.5 text-left font-medium">Status</th>
                                                            <th class="px-2 py-1.5 text-left font-medium">Completed</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-100">
                                                        @foreach($recentAttempts as $att)
                                                            <tr>
                                                                <td class="px-2 py-1.5 text-gray-900">{{ $att->attempt_number }}</td>
                                                                <td class="px-2 py-1.5 text-gray-800">{{ (int) $att->correct_answers }}/{{ (int) $att->total_questions }}</td>
                                                                <td class="px-2 py-1.5 text-gray-700">{{ $att->percentage }}%</td>
                                                                <td class="px-2 py-1.5"><span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium {{ $att->getStatusBadgeClass() }}">{{ $att->getStatusText() }}</span></td>
                                                                <td class="px-2 py-1.5 text-gray-600 whitespace-nowrap">{{ $att->completed_at ? $att->completed_at->format('M j, g:i A') : '—' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <p class="text-[10px] text-gray-500 mt-1">Showing up to 5 most recent attempts.</p>
                                        @endif
                                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-2">
                                            <a href="{{ url('/admin/hiring-applications/' . $application->getKey() . '/quiz-assignments/' . $asg->getKey() . '/attempts') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Open full attempt list</a>
                                            @if(auth()->user()->canAccessContentManagement())
                                                <a href="{{ url('/admin/quiz-assignments/' . $asg->getKey() . '/history') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Q&amp;A detail (quizzes admin)</a>
                                            @endif
                                            @if($asg->quiz)
                                                <form method="post" action="{{ url('/admin/hiring-applications/'.$application->id.'/resend-intern-quiz-email') }}" class="inline-flex items-center">
                                                    @csrf
                                                    <input type="hidden" name="resend_quiz_ids[]" value="{{ $asg->quiz_id }}">
                                                    <button type="submit"
                                                        class="action-button inline-flex items-center px-2.5 py-1 rounded-md border text-xs font-medium focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed {{ $asg->quiz->is_active ? 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' : 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed' }}"
                                                        data-loading-text="Sending…"
                                                        @if(! $asg->quiz->is_active) disabled title="Activate this quiz under Admin → Quizzes to resend" @endif>
                                                        <span class="button-text">Resend assignment email</span>
                                                        <span class="button-spinner hidden ml-1.5">
                                                            <svg class="animate-spin h-3.5 w-3.5 {{ $asg->quiz->is_active ? 'text-emerald-700' : 'text-gray-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            @php
                                $defaultResendQuizIds = $internQuizAssignments
                                    ->filter(fn ($a) => $a->quiz && $a->quiz->is_active)
                                    ->pluck('quiz_id')
                                    ->map(fn ($id) => (int) $id)
                                    ->values()
                                    ->all();
                                $resendOldIds = array_map('intval', (array) old('resend_quiz_ids', $defaultResendQuizIds));
                                $hasActiveAssignedQuiz = $internQuizAssignments->contains(fn ($a) => $a->quiz && $a->quiz->is_active);
                            @endphp
                            @if($hasActiveAssignedQuiz)
                            <form method="post" action="{{ url('/admin/hiring-applications/'.$application->id.'/resend-intern-quiz-email') }}" class="mt-4 pt-4 border-t border-gray-200 space-y-3">
                                @csrf
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Resend assignment email</p>
                                    <p class="mt-1 text-xs text-gray-600">Sends the same quiz list email to <strong>{{ $application->email }}</strong>. Uncheck any quiz you do not want included.</p>
                                </div>
                                <div class="flex flex-wrap gap-x-4 gap-y-2">
                                    @foreach($internQuizAssignments as $asg)
                                        @if($asg->quiz)
                                            <label class="inline-flex items-center gap-2 text-sm text-gray-800 {{ $asg->quiz->is_active ? '' : 'opacity-60' }}">
                                                <input type="checkbox"
                                                       name="resend_quiz_ids[]"
                                                       value="{{ $asg->quiz_id }}"
                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                       @checked(in_array((int) $asg->quiz_id, $resendOldIds, true))
                                                       @if(! $asg->quiz->is_active) disabled title="Quiz is inactive — enable it to include in email" @endif>
                                                <span class="max-w-[220px] truncate" title="{{ $asg->quiz->title }}">{{ $asg->quiz->title }}</span>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                                @error('resend_quiz_ids')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                @error('resend_quiz_ids.*')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <button type="submit"
                                    class="action-button inline-flex justify-center items-center px-4 py-2 border border-indigo-300 rounded-md shadow-sm text-sm font-medium text-indigo-700 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    data-loading-text="Sending…">
                                    <span class="button-text">Resend quiz email to applicant</span>
                                    <span class="button-spinner hidden ml-2">
                                        <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                            </form>
                            @else
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <p class="text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-md px-3 py-2">There are no <strong>active</strong> quizzes in the assignments above. Activate the quiz in <strong>Admin → Quizzes</strong> to resend the assignment email.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </details>
            @endif

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
                    <form action="{{ url('/admin/hiring-applications/' . $application->id . '/admin-notes') }}" method="POST">
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

            @include('admin.hiring-applications.partials.application-actions-panel')
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
                            'hiring_application_follow_up_sent' => 'Follow-Up Email Sent',
                            'hiring_application_interview_done' => 'Interview Done',
                            'hiring_application_hired' => 'Marked as Hired',
                            'hiring_application_intern_accepted' => 'Intern Accepted',
                            'hiring_application_intern_quiz_assigned' => 'Intern Quiz Assigned',
                            'hiring_application_hired_cancelled' => 'Hired Status Cancelled',
                            'hiring_application_deleted' => 'Application Deleted',
                            'hiring_application_updated' => 'Record Updated',
                            default => ucfirst(str_replace('_', ' ', str_replace('hiring_application_', '', $log->action))),
                        };
                        $borderColor = match($log->action) {
                            'hiring_application_accepted', 'hiring_application_reconsidered', 'hiring_application_hired', 'hiring_application_intern_accepted', 'hiring_application_intern_quiz_assigned' => 'border-green-500',
                            'hiring_application_rejected', 'hiring_application_hired_cancelled' => 'border-red-500',
                            'hiring_application_interview_scheduled', 'hiring_application_interview_rescheduled', 'hiring_application_follow_up_sent' => 'border-blue-500',
                            'hiring_application_interview_done' => 'border-purple-500',
                            'hiring_application_deleted' => 'border-gray-400',
                            'hiring_application_updated' => 'border-slate-400',
                            default => 'border-gray-400',
                        };
                        $badgeColor = match($log->action) {
                            'hiring_application_accepted', 'hiring_application_reconsidered', 'hiring_application_hired', 'hiring_application_intern_accepted', 'hiring_application_intern_quiz_assigned' => 'bg-green-100 text-green-800',
                            'hiring_application_rejected', 'hiring_application_hired_cancelled' => 'bg-red-100 text-red-800',
                            'hiring_application_interview_scheduled', 'hiring_application_interview_rescheduled', 'hiring_application_follow_up_sent' => 'bg-blue-100 text-blue-800',
                            'hiring_application_interview_done' => 'bg-purple-100 text-purple-800',
                            'hiring_application_deleted' => 'bg-gray-100 text-gray-800',
                            'hiring_application_updated' => 'bg-slate-100 text-slate-800',
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
                                        @if(isset($log->metadata['interview_format']))
                                            <div class="text-sm text-gray-600">
                                                <span class="font-medium">Format:</span> {{ $log->metadata['interview_format'] === 'online' ? 'Online' : 'On-site' }}
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
                                        @if(!empty($log->metadata['changes']) && is_array($log->metadata['changes']))
                                            @php
                                                $changeLabels = [
                                                    'admin_notes' => 'Admin notes',
                                                    'status' => 'Status',
                                                    'interview_date' => 'Interview date',
                                                    'interview_format' => 'Interview format',
                                                    'interview_meeting_link' => 'Meeting link',
                                                    'user_id' => 'Linked user',
                                                    'reviewed_by' => 'Reviewed by (user id)',
                                                    'reviewed_at' => 'Reviewed at',
                                                    'hiring_position_id' => 'Position',
                                                ];
                                            @endphp
                                            <div class="mt-2 space-y-2">
                                                <p class="text-xs font-medium text-gray-600 uppercase tracking-wide">Fields changed</p>
                                                @foreach($log->metadata['changes'] as $field => $pair)
                                                    @if(is_array($pair) && (array_key_exists('old', $pair) || array_key_exists('new', $pair)))
                                                        <div class="text-sm border border-gray-100 rounded-md p-2 bg-gray-50/90">
                                                            <div class="font-medium text-gray-800">{{ $changeLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}</div>
                                                            <div class="mt-1 text-xs text-gray-600 break-words">
                                                                <span class="text-gray-500">From:</span>
                                                                @php
                                                                    $fromVal = $pair['old'] ?? null;
                                                                    $fromStr = $fromVal === null || $fromVal === '' ? '—' : (is_scalar($fromVal) ? (string) $fromVal : json_encode($fromVal));
                                                                @endphp
                                                                {{ $fromStr }}
                                                            </div>
                                                            <div class="mt-0.5 text-xs text-gray-800 break-words">
                                                                <span class="text-gray-500">To:</span>
                                                                @php
                                                                    $toVal = $pair['new'] ?? null;
                                                                    $toStr = $toVal === null || $toVal === '' ? '—' : (is_scalar($toVal) ? (string) $toVal : json_encode($toVal));
                                                                @endphp
                                                                {{ $toStr }}
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
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
    const meetingLinkWrap = document.getElementById('interview_meeting_link_wrap');
    function syncInterviewMeetingLinkRow() {
        if (!meetingLinkWrap) return;
        const onlineSelected = document.querySelector('input[name="interview_format"][value="online"]:checked');
        meetingLinkWrap.classList.toggle('hidden', !onlineSelected);
    }
    document.querySelectorAll('input[name="interview_format"]').forEach(function (radio) {
        radio.addEventListener('change', syncInterviewMeetingLinkRow);
    });
    syncInterviewMeetingLinkRow();

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

        window.setTimeout(function () {
            button.disabled = true;

            if (buttonText) {
                buttonText.textContent = loadingText;
            }
            if (buttonSpinner) {
                buttonSpinner.classList.remove('hidden');
            }
        }, 0);
    }
});
</script>
@endsection

