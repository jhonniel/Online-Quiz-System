@extends('layouts.user')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <!-- Result Header -->
            <div class="text-center mb-8">
                @if($isPartialScore)
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold text-gray-900">Quiz Submitted - Partial Score</h1>
                    <p class="mt-2 text-gray-600">{{ $quiz->title }}</p>
                    <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Pending Manual Review
                    </div>
                @else
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold text-gray-900">Quiz Completed!</h1>
                    <p class="mt-2 text-gray-600">{{ $quiz->title }}</p>
                @endif
            </div>

            <!-- Score Summary -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
                <div class="bg-gray-50 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Correct Answers</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $correctAnswers }}/{{ $totalQuestions }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Points Earned</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $totalPoints }}/{{ $maxPoints }}
                                        @if($isPartialScore)
                                            <span class="text-sm text-yellow-600 ml-2">(Partial)</span>
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Percentage</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $maxPoints > 0 ? round(($totalPoints / $maxPoints) * 100, 1) : 0 }}%</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Results -->
            <div class="border-t border-gray-200 pt-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Question Review</h3>
                <div class="space-y-6">
                    @foreach($attempts as $attempt)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $attempt->question->question_text }}</h4>
                                    <div class="mt-2">
                                        @if($attempt->question->question_type === 'multiple_choice' || $attempt->question->question_type === 'true_false')
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">Your answer:</span>
                                                @if($attempt->user_answer)
                                                    @php
                                                        $answerText = '';
                                                        switch($attempt->user_answer) {
                                                            case 'A': $answerText = $attempt->question->option_a; break;
                                                            case 'B': $answerText = $attempt->question->option_b; break;
                                                            case 'C': $answerText = $attempt->question->option_c; break;
                                                            case 'D': $answerText = $attempt->question->option_d; break;
                                                            default: $answerText = $attempt->user_answer; break;
                                                        }
                                                    @endphp
                                                    {{ $answerText ?: 'No answer selected' }}
                                                @else
                                                    No answer selected
                                                @endif
                                            </p>
                                        @else
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">Your answer:</span>
                                                {{ $attempt->user_answer ?: 'No answer provided' }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    @if(($attempt->question->question_type === 'text' || $attempt->question->question_type === 'fill_blank') && is_null($attempt->graded_at))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                            Pending Review
                                        </span>
                                    @elseif($attempt->is_correct)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Correct
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                            Incorrect
                                        </span>
                                    @endif
                                    <div class="mt-1 text-sm text-gray-500">
                                        {{ $attempt->points_earned }}/{{ $attempt->question->points }} points
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($isPartialScore)
                <!-- Partial Score Notice -->
                <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                Partial Score - Manual Review Required
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>This quiz contains text answer questions that require manual grading by an administrator. Your current score reflects only the automatically graded questions. The final score will be updated once the text answers are reviewed and graded.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="mt-8 flex justify-center space-x-4">
                <a href="{{ url('/dashboard') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Back to Dashboard
                </a>
                <a href="{{ url('/quizzes/enter-code') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Take Another Quiz
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
