@extends('layouts.user')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-xl font-bold leading-7 text-gray-900 sm:text-2xl lg:text-3xl">
                My Quizzes
            </h2>
            <p class="mt-1 text-sm text-gray-600">View and take your assigned quizzes.</p>
        </div>
        <div class="shrink-0">
            <a href="{{ url('/quizzes/enter-code') }}"
               class="inline-flex w-full sm:w-auto justify-center items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 touch-manipulation">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Enter Quiz Code
            </a>
        </div>
    </div>

    <!-- Assigned Quizzes -->
    @if($assignedQuizzes->count() > 0)
        <x-responsive-data-panel>
            <x-slot:mobile>
                @foreach($assignedQuizzes as $assignment)
                    <div class="mobile-card">
                        <div class="mobile-card-row">
                            <div class="min-w-0 flex-1">
                                <p class="mobile-card-title">{{ $assignment->quiz->title }}</p>
                                @if($assignment->quiz->description)
                                    <p class="mobile-card-subtitle">{{ Str::limit($assignment->quiz->description, 80) }}</p>
                                @endif
                                <div class="mobile-card-meta">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $assignment->getStatusBadgeClass() }}">
                                        {{ $assignment->getStatusText() }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ $assignment->quiz->total_questions }} questions
                                    </span>
                                    @if($assignment->quiz->time_limit)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                                            {{ $assignment->quiz->time_limit }} min
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mobile-card-actions">
                            @if(!$assignment->is_completed)
                                <a href="{{ url('/quizzes/' . $assignment->quiz->id . '/take') }}"
                                   class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-800 touch-manipulation py-1">
                                    {{ ($assignment->status === 'in_progress' || $assignment->status === 'cancelled') ? 'Resume Quiz' : 'Take Quiz' }}
                                </a>
                            @else
                                <a href="{{ url('/quizzes/' . $assignment->quiz->id . '/result') }}"
                                   class="inline-flex items-center text-sm font-semibold text-green-600 hover:text-green-800 touch-manipulation py-1">
                                    View Result
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </x-slot:mobile>

            <x-slot:desktop>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Questions</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Limit</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($assignedQuizzes as $assignment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $assignment->quiz->title }}</div>
                                    @if($assignment->quiz->description)
                                        <div class="text-sm text-gray-500">{{ Str::limit($assignment->quiz->description, 50) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $assignment->quiz->total_questions }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $assignment->quiz->time_limit ? $assignment->quiz->time_limit . ' min' : 'No limit' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $assignment->getStatusBadgeClass() }}">
                                        {{ $assignment->getStatusText() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if(!$assignment->is_completed)
                                        <a href="{{ url('/quizzes/' . $assignment->quiz->id . '/take') }}"
                                           class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            {{ ($assignment->status === 'in_progress' || $assignment->status === 'cancelled') ? 'Resume Quiz' : 'Take Quiz' }}
                                        </a>
                                    @else
                                        <a href="{{ url('/quizzes/' . $assignment->quiz->id . '/result') }}"
                                           class="text-green-600 hover:text-green-900 font-medium">
                                            View Result
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-slot:desktop>
        </x-responsive-data-panel>
    @else
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="text-center py-12 px-4">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No quizzes assigned</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(auth()->user()->isApplicant())
                        No internship quizzes have been assigned to you yet. They will appear here after an administrator assigns them.
                    @else
                        You don't have any quizzes assigned to you yet.
                    @endif
                </p>
                @if(!auth()->user()->isApplicant())
                <div class="mt-6">
                    <a href="{{ url('/quizzes/enter-code') }}"
                       class="inline-flex items-center px-4 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 touch-manipulation">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Enter Quiz Code
                    </a>
                </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
