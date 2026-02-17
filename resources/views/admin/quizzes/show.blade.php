@extends('layouts.admin')

@section('page-title', 'Quiz Details')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Quizzes</span>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Quiz Details</span>
        </div>
    </li>
@endsection

@section('content')
<div class="h-full flex flex-col space-y-3">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">{{ $quiz->title }}</h1>
                    <p class="text-indigo-100 text-sm">Quiz details and questions</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ url('admin/quizzes/' . $quiz->id . '/export-history/pdf') }}"
                   class="inline-flex items-center px-4 py-2 border border-white text-sm font-medium rounded-md text-white bg-transparent hover:bg-white hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Quiz History (PDF)
                </a>
                <a href="{{ route('quizzes.edit', $quiz) }}"
                   class="inline-flex items-center px-4 py-2 border border-white text-sm font-medium rounded-md text-white bg-transparent hover:bg-white hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Quiz
                </a>
                <a href="{{ route('quizzes.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-white text-sm font-medium rounded-md text-white bg-transparent hover:bg-white hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Quizzes
                </a>
            </div>
        </div>
    </div>

    <!-- Quiz Details -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden flex-1 flex flex-col mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="p-4 sm:p-6 flex-1 overflow-y-auto">

            <!-- Quiz Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Total Questions</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $quiz->total_questions }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Time Limit</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $quiz->time_limit ? $quiz->time_limit . ' minutes' : 'No limit' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Quiz Code</p>
                            <p class="text-lg font-semibold text-gray-900 font-mono">{{ $quiz->quiz_code }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Section -->
            <div class="mb-8">
                <div class="border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Assign Quiz to Users</h3>
                        <button onclick="openAssignModal({{ $quiz->id }}, '{{ $quiz->title }}')"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Assign to Users
                        </button>
                    </div>

                    <div class="text-sm text-gray-600">
                        <p>Assign this quiz to specific users. Users will be able to access the quiz using the quiz code: <span class="font-mono font-medium">{{ $quiz->quiz_code }}</span></p>
                    </div>
                </div>
            </div>

            <!-- Import Questions Section -->
            <div class="mb-8">
                <div class="border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Import Questions from Excel</h3>
                        <a href="{{ url('admin/quizzes/template/download') }}"
                           class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download Template
                        </a>
                    </div>

                    <form action="{{ url('admin/quizzes/' . $quiz->id . '/import-questions') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label for="excel_file" class="block text-sm font-medium text-gray-700">Excel File</label>
                            <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <p class="mt-1 text-sm text-gray-500">Upload an Excel file with questions. Maximum file size: 10MB</p>
                            @error('excel_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                </svg>
                                Import Questions
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Questions List -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Questions ({{ $quiz->questions->count() }})</h3>
                    <a href="{{ route('quizzes.edit', $quiz) }}"
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Questions
                    </a>
                </div>

                @if($quiz->questions->count() > 0)
                    <div class="space-y-4">
                        @foreach($quiz->questions as $question)
                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Question {{ $loop->iteration }}
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ ucfirst(str_replace('_', ' ', $question->question_type)) }}
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $question->points }} points
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-900 mb-3">{{ $question->question_text }}</p>

                                        @if($question->question_type === 'multiple_choice' && $question->option_a)
                                            <div class="space-y-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-xs text-gray-500">A.</span>
                                                    <span class="text-sm text-gray-700 {{ $question->correct_answer === 'A' ? 'font-semibold text-green-700' : '' }}">
                                                        {{ $question->option_a }}
                                                    </span>
                                                    @if($question->correct_answer === 'A')
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Correct
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-xs text-gray-500">B.</span>
                                                    <span class="text-sm text-gray-700 {{ $question->correct_answer === 'B' ? 'font-semibold text-green-700' : '' }}">
                                                        {{ $question->option_b }}
                                                    </span>
                                                    @if($question->correct_answer === 'B')
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Correct
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-xs text-gray-500">C.</span>
                                                    <span class="text-sm text-gray-700 {{ $question->correct_answer === 'C' ? 'font-semibold text-green-700' : '' }}">
                                                        {{ $question->option_c }}
                                                    </span>
                                                    @if($question->correct_answer === 'C')
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Correct
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-xs text-gray-500">D.</span>
                                                    <span class="text-sm text-gray-700 {{ $question->correct_answer === 'D' ? 'font-semibold text-green-700' : '' }}">
                                                        {{ $question->option_d }}
                                                    </span>
                                                    @if($question->correct_answer === 'D')
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Correct
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No questions yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by importing questions from Excel or adding them manually.</p>
                        <div class="mt-6">
                            <a href="{{ route('quizzes.edit', $quiz) }}"
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Questions
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Assigned Users Section -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Assigned Users</h3>
                <button onclick="openAssignModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                    Assign to Users
                </button>
            </div>
        </div>

        <div class="p-6">
            @if($quiz->assignments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attempts</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Best Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Attempt</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($quiz->assignments as $assignment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($assignment->user->profile_picture)
                                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ $assignment->user->getProfilePictureUrl() }}" alt="{{ $assignment->user->name }}">
                                                @else
                                                    <div class="h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                                        <span class="text-indigo-600 font-semibold text-sm">{{ $assignment->user->getInitials() }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $assignment->user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $assignment->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $assignment->getStatusBadgeClass() }}">
                                            {{ $assignment->getStatusText() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $assignment->attempt_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($assignment->best_score)
                                            <div class="flex items-center">
                                                <span class="font-medium">{{ $assignment->best_score }}/{{ $quiz->total_questions }}</span>
                                                <span class="ml-2 text-xs text-gray-500">({{ $assignment->getBestScorePercentage() }}%)</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($assignment->attempt_count > 0)
                                            {{ $assignment->getAverageScore() }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($assignment->last_attempt_at)
                                            {{ \Carbon\Carbon::parse($assignment->last_attempt_at)->format('M j, Y g:i A') }}
                                        @else
                                            <span class="text-gray-400">Never</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ url('admin/quiz-assignments/' . $assignment->id . '/history') }}"
                                               class="text-indigo-600 hover:text-indigo-900">
                                                View History
                                            </a>
                                            @if($assignment->canRetake())
                                                <form action="{{ url('admin/quiz-assignments/' . $assignment->id . '/reset') }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900"
                                                            onclick="return confirm('Are you sure you want to reset this quiz assignment? The user will be able to retake the quiz.')">
                                                        Reset
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ url('admin/quiz-assignments/' . $assignment->id . '/allow-retake') }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900">
                                                        Allow Retake
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No users assigned</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by assigning this quiz to users.</p>
                    <div class="mt-6">
                        <button onclick="openAssignModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                            Assign to Users
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Assignment Modal -->
<div id="assignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Assign Quiz to Users</h3>
                <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600">Assigning: <span id="quizTitle" class="font-medium"></span></p>
            </div>

            <form id="assignForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label for="user_ids" class="block text-sm font-medium text-gray-700">
                            Select Users
                        </label>
                        <div class="flex space-x-2">
                            <button type="button" id="selectAllBtn" class="text-xs px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200">
                                Select All
                            </button>
                            <button type="button" id="selectNoneBtn" class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                Select None
                            </button>
                        </div>
                    </div>

                    <!-- Search Users -->
                    <div class="mb-3">
                        <input type="text"
                               id="userSearch"
                               placeholder="Search users by name or email..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div id="usersList" class="max-h-60 overflow-y-auto border border-gray-300 rounded-md p-2 space-y-2">
                        <!-- Users will be loaded here -->
                    </div>

                    <!-- Selected Count -->
                    <div class="mt-2 text-sm text-gray-600">
                        <span id="selectedCount">0</span> users selected
                    </div>

                    <div id="user_ids_error" class="mt-1 text-sm text-red-600 hidden"></div>
                </div>

                <div class="mb-4">
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Due Date (Optional)
                    </label>
                    <input type="datetime-local"
                           id="due_date"
                           name="due_date"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                            onclick="closeAssignModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </button>
                    <button type="submit"
                            id="submitAssign"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="submitText">Assign Quiz</span>
                        <span id="loadingText" class="hidden">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Assigning...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentQuizId = null;

function openAssignModal(quizId, quizTitle) {
    currentQuizId = quizId;
    document.getElementById('quizTitle').textContent = quizTitle;
    document.getElementById('assignForm').action = `/admin/quizzes/${quizId}/assign`;
    document.getElementById('assignModal').classList.remove('hidden');

    // Load users
    loadUsers();
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.add('hidden');
    document.getElementById('usersList').innerHTML = '';
    document.getElementById('due_date').value = '';
    document.getElementById('user_ids_error').classList.add('hidden');
    currentQuizId = null;
}

// Close modal when clicking outside
document.getElementById('assignModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAssignModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAssignModal();
    }
});

function loadUsers() {
    const quizId = currentQuizId;
    const url = quizId ? `/admin/users/api?quiz_id=${quizId}` : '/admin/users/api';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            const usersList = document.getElementById('usersList');
            usersList.innerHTML = '';

            data.users.forEach(user => {
                const userDiv = document.createElement('div');
                userDiv.className = 'flex items-center space-x-2 p-2 hover:bg-gray-50 rounded';

                // Check if user is already assigned
                const isAssigned = user.is_assigned || false;
                const checkedAttribute = isAssigned ? 'checked' : '';
                const assignedBadge = isAssigned ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">Assigned</span>' : '';

                userDiv.innerHTML = `
                    <input type="checkbox"
                           id="user_${user.id}"
                           name="user_ids[]"
                           value="${user.id}"
                           ${checkedAttribute}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="user_${user.id}" class="text-sm text-gray-700 cursor-pointer flex-1">
                        <div class="font-medium flex items-center">
                            ${user.name}
                            ${assignedBadge}
                        </div>
                        <div class="text-gray-500">${user.email}</div>
                        ${user.university ? `<div class="text-xs text-gray-400">${user.university.name}</div>` : ''}
                    </label>
                `;
                usersList.appendChild(userDiv);
            });
        })
        .catch(error => {
            console.error('Error loading users:', error);
            document.getElementById('usersList').innerHTML = '<p class="text-red-600 text-sm">Error loading users</p>';
        });
}

// Handle form submission
document.getElementById('assignForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const submitButton = document.getElementById('submitAssign');
    const submitText = document.getElementById('submitText');
    const loadingText = document.getElementById('loadingText');
    const errorDiv = document.getElementById('user_ids_error');

    // Clear previous errors
    errorDiv.classList.add('hidden');

    // Get selected users (allow zero for removing all assignments)
    const selectedUsers = form.querySelectorAll('input[name="user_ids[]"]:checked');

    // Show confirmation if removing all assignments
    if (selectedUsers.length === 0) {
        if (!confirm('Are you sure you want to remove all assignments for this quiz?')) {
            return;
        }
    }

    // Show loading state
    submitButton.disabled = true;
    submitText.classList.add('hidden');
    loadingText.classList.remove('hidden');

    // Prepare form data
    const formData = new FormData(form);

    // Add AJAX header
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert('Quiz assigned successfully!');

            // Close modal
            closeAssignModal();

            // Reload page to show updated data
            window.location.reload();
        } else {
            // Show error message
            errorDiv.textContent = data.message || 'An error occurred while assigning the quiz.';
            errorDiv.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    })
    .finally(() => {
        // Reset button state
        submitButton.disabled = false;
        submitText.classList.remove('hidden');
        loadingText.classList.add('hidden');
    });
});
</script>
@endsection
