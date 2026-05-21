@php
    $errors = $errors ?? new \Illuminate\Support\MessageBag();
@endphp

@extends('layouts.admin')

@section('page-title', 'Edit Quiz')

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
            <span class="ml-2 text-sm font-medium text-gray-500">Edit Quiz</span>
        </div>
    </li>
@endsection

@section('content')
<div class="h-full flex flex-col space-y-3">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Edit Quiz</h1>
                <p class="text-indigo-100 text-sm">Update quiz details and questions</p>
            </div>
        </div>
    </div>

    <!-- Quiz Edit Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden flex-1 flex flex-col mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="p-4 sm:p-6 flex-1 overflow-y-auto">

            <form action="{{ route('quizzes.update', $quiz) }}" method="POST" id="quiz-form">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Quiz Details -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Quiz Title</label>
                            <div class="mt-1">
                                <input type="text" name="title" id="title" required
                                       class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('title') ? 'border-red-500' : '' }}"
                                       value="{{ old('title', $quiz->title) }}">
                            </div>
                            @if($errors->has('title'))
                                <p class="mt-2 text-sm text-red-600">{{ $errors->first('title') }}</p>
                            @endif
                        </div>

                        <!-- Time Limit -->
                        <div>
                            <label for="time_limit" class="block text-sm font-medium text-gray-700">Time Limit (minutes)</label>
                            <div class="mt-1">
                                <input type="number" name="time_limit" id="time_limit" min="1"
                                       class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('time_limit') ? 'border-red-500' : '' }}"
                                       value="{{ old('time_limit', $quiz->time_limit) }}">
                            </div>
                            @if($errors->has('time_limit'))
                                <p class="mt-2 text-sm text-red-600">{{ $errors->first('time_limit') }}</p>
                            @endif
                        </div>

                        <!-- Questions to Show -->
                        <div>
                            <label for="questions_to_show" class="block text-sm font-medium text-gray-700">Questions to Show (Optional)</label>
                            <div class="mt-1">
                                <input type="number" name="questions_to_show" id="questions_to_show" min="1"
                                       class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('questions_to_show') ? 'border-red-500' : '' }}"
                                       value="{{ old('questions_to_show', $quiz->questions_to_show) }}"
                                       placeholder="Leave empty to show all questions">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Number of questions to randomly show to users. If empty, all questions will be shown.</p>
                            @if($errors->has('questions_to_show'))
                                <p class="mt-2 text-sm text-red-600">{{ $errors->first('questions_to_show') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <div class="mt-1">
                            <textarea name="description" id="description" rows="3"
                                      class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('description') ? 'border-red-500' : '' }}">{{ old('description', $quiz->description) }}</textarea>
                        </div>
                        @if($errors->has('description'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->first('description') }}</p>
                        @endif
                    </div>

                    <!-- Topic -->
                    <div>
                        <label for="topic" class="block text-sm font-medium text-gray-700">Topic/Subject</label>
                        <div class="mt-1 flex space-x-2">
                            <select name="topic" id="topic" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md {{ $errors->has('topic') ? 'border-red-500' : '' }}">
                                <option value="">Select a topic</option>
                                <option value="Laravel" {{ old('topic', $quiz->topic) == 'Laravel' ? 'selected' : '' }}>Laravel</option>
                                <option value="PHP" {{ old('topic', $quiz->topic) == 'PHP' ? 'selected' : '' }}>PHP</option>
                                <option value="JavaScript" {{ old('topic', $quiz->topic) == 'JavaScript' ? 'selected' : '' }}>JavaScript</option>
                                <option value="Vue.js" {{ old('topic', $quiz->topic) == 'Vue.js' ? 'selected' : '' }}>Vue.js</option>
                                <option value="React" {{ old('topic', $quiz->topic) == 'React' ? 'selected' : '' }}>React</option>
                                <option value="Git" {{ old('topic', $quiz->topic) == 'Git' ? 'selected' : '' }}>Git</option>
                                <option value="Networking" {{ old('topic', $quiz->topic) == 'Networking' ? 'selected' : '' }}>Networking</option>
                                <option value="Database" {{ old('topic', $quiz->topic) == 'Database' ? 'selected' : '' }}>Database</option>
                                <option value="HTML" {{ old('topic', $quiz->topic) == 'HTML' ? 'selected' : '' }}>HTML</option>
                                <option value="CSS" {{ old('topic', $quiz->topic) == 'CSS' ? 'selected' : '' }}>CSS</option>
                                <option value="Python" {{ old('topic', $quiz->topic) == 'Python' ? 'selected' : '' }}>Python</option>
                                <option value="Java" {{ old('topic', $quiz->topic) == 'Java' ? 'selected' : '' }}>Java</option>
                                <option value="Quality Assurance" {{ old('topic', $quiz->topic) == 'Quality Assurance' ? 'selected' : '' }}>Quality Assurance</option>
                                <option value="UI/UX Design" {{ old('topic', $quiz->topic) == 'UI/UX Design' ? 'selected' : '' }}>UI/UX Design</option>
                                <option value="General" {{ old('topic', $quiz->topic) == 'General' ? 'selected' : '' }}>General</option>
                                <option value="C#" {{ old('topic', $quiz->topic) == 'C#' ? 'selected' : '' }}>C#</option>
                                <option value="WordPress" {{ old('topic', $quiz->topic) == 'WordPress' ? 'selected' : '' }}>WordPress</option>
                                <option value="Mobile App Development" {{ old('topic', $quiz->topic) == 'Mobile App Development' ? 'selected' : '' }}>Mobile App Development</option>
                                <option value="Flutter" {{ old('topic', $quiz->topic) == 'Flutter' ? 'selected' : '' }}>Flutter</option>
                            </select>
                            <button type="button" id="add-topic-btn"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add New
                            </button>
                        </div>
                        @if($errors->has('topic'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->first('topic') }}</p>
                        @endif
                    </div>

                    <!-- Quiz Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Quiz Status</label>
                        <div class="flex items-center space-x-6">
                            <label class="inline-flex items-center">
                                <input type="radio" name="is_active" value="1"
                                       {{ old('is_active', $quiz->is_active) ? 'checked' : '' }}
                                       class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="is_active" value="0"
                                       {{ !old('is_active', $quiz->is_active) ? 'checked' : '' }}
                                       class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                <span class="ml-2 text-sm text-gray-700">Inactive</span>
                            </label>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Active quizzes are visible to users, inactive quizzes are hidden.</p>
                        @if($errors->has('is_active'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->first('is_active') }}</p>
                        @endif
                    </div>

                    <!-- Questions -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Questions</h3>
                        <div id="questions-container">
                            @foreach($quiz->questions as $index => $question)
                                <div class="border border-gray-200 rounded-lg p-4 mb-4" data-question-index="{{ $index }}">
                                    <div class="flex justify-between items-center mb-4">
                                        <h4 class="text-md font-medium text-gray-900">Question {{ $index + 1 }}</h4>
                                        <button type="button" class="text-red-600 hover:text-red-900" onclick="this.parentElement.parentElement.remove()">
                                            Remove
                                        </button>
                                    </div>

                                    <!-- Hidden field for question ID -->
                                    <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $question->id }}">

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Question Text</label>
                                            <textarea name="questions[{{ $index }}][question_text]" required rows="2"
                                                      class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('questions.'.$index.'.question_text', $question->question_text) }}</textarea>
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Question Type</label>
                                                <select name="questions[{{ $index }}][question_type]" required onchange="toggleAnswers(this)"
                                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                    <option value="multiple_choice" {{ old('questions.'.$index.'.question_type', $question->question_type) == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                                    <option value="true_false" {{ old('questions.'.$index.'.question_type', $question->question_type) == 'true_false' ? 'selected' : '' }}>True/False</option>
                                                    <option value="text" {{ old('questions.'.$index.'.question_type', $question->question_type) == 'text' ? 'selected' : '' }}>Text Answer</option>
                                                    <option value="fill_blank" {{ old('questions.'.$index.'.question_type', $question->question_type) == 'fill_blank' ? 'selected' : '' }}>Fill in the Blank</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Points</label>
                                                <input type="number" name="questions[{{ $index }}][points]" required min="1" value="{{ old('questions.'.$index.'.points', $question->points) }}"
                                                       class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                        </div>

                                        <div class="answers-container" style="{{ $question->question_type === 'text' ? '' : ($question->question_type === 'fill_blank' || $question->question_type === 'multiple_choice' || $question->question_type === 'true_false' ? '' : 'display: none;') }}">
                                            @if($question->question_type === 'text')
                                                @include('admin.quizzes.partials.reference-answers-fields', ['index' => $index, 'question' => $question])
                                            @elseif($question->question_type == 'fill_blank')
                                                <label class="block text-sm font-medium text-gray-700">Correct Answer(s)</label>
                                                <div class="mt-2 space-y-2">
                                                    <div class="flex items-center space-x-2">
                                                        <input type="text" name="questions[{{ $index }}][correct_answer]" required
                                                               value="{{ old('questions.'.$index.'.correct_answer', $question->correct_answer) }}"
                                                               placeholder="Enter the correct answer"
                                                               class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                                        <span class="text-sm text-gray-500">Correct Answer</span>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <input type="text" name="questions[{{ $index }}][alternative_answer_1]"
                                                               value="{{ old('questions.'.$index.'.alternative_answer_1', $question->alternative_answer_1) }}"
                                                               placeholder="Alternative answer 1 (optional)"
                                                               class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                                        <span class="text-sm text-gray-500">Alternative 1</span>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <input type="text" name="questions[{{ $index }}][alternative_answer_2]"
                                                               value="{{ old('questions.'.$index.'.alternative_answer_2', $question->alternative_answer_2) }}"
                                                               placeholder="Alternative answer 2 (optional)"
                                                               class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                                        <span class="text-sm text-gray-500">Alternative 2</span>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <input type="text" name="questions[{{ $index }}][alternative_answer_3]"
                                                               value="{{ old('questions.'.$index.'.alternative_answer_3', $question->alternative_answer_3) }}"
                                                               placeholder="Alternative answer 3 (optional)"
                                                               class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                                        <span class="text-sm text-gray-500">Alternative 3</span>
                                                    </div>
                                                </div>
                                                <p class="mt-2 text-sm text-gray-500">
                                                    <strong>Note:</strong> Fill-in-the-blank questions require manual grading.
                                                    Enter the main correct answer and up to 3 alternative acceptable answers.
                                                </p>
                                            @elseif($question->question_type == 'true_false')
                                                <label class="block text-sm font-medium text-gray-700">Answers</label>
                                                <div class="answers-list mt-2 space-y-2">
                                                    <!-- Option A - True -->
                                                    <div class="flex items-center space-x-2">
                                                        <input type="radio" name="questions[{{ $index }}][correct_answer]" value="A"
                                                               {{ old('questions.'.$index.'.correct_answer', $question->correct_answer) == 'A' ? 'checked' : '' }} required>
                                                        <label class="text-sm font-medium text-gray-700 w-8">A:</label>
                                                        <input type="text" name="questions[{{ $index }}][option_a]" required readonly
                                                               value="True" class="flex-1 shadow-sm bg-gray-100 block w-full sm:text-sm border-gray-300 rounded-md">
                                                    </div>
                                                    <!-- Option B - False -->
                                                    <div class="flex items-center space-x-2">
                                                        <input type="radio" name="questions[{{ $index }}][correct_answer]" value="B"
                                                               {{ old('questions.'.$index.'.correct_answer', $question->correct_answer) == 'B' ? 'checked' : '' }}>
                                                        <label class="text-sm font-medium text-gray-700 w-8">B:</label>
                                                        <input type="text" name="questions[{{ $index }}][option_b]" required readonly
                                                               value="False" class="flex-1 shadow-sm bg-gray-100 block w-full sm:text-sm border-gray-300 rounded-md">
                                                    </div>
                                                </div>
                                                <p class="mt-2 text-sm text-gray-500">
                                                    <strong>Note:</strong> True/False questions automatically have "True" and "False" options.
                                                    Select which one is the correct answer.
                                                </p>
                                            @else
                                                <label class="block text-sm font-medium text-gray-700">Answers</label>
                                                <div class="answers-list mt-2 space-y-2">
                                                    <!-- Option A -->
                                                    <div class="flex items-center space-x-2">
                                                        <input type="radio" name="questions[{{ $index }}][correct_answer]" value="A"
                                                               {{ old('questions.'.$index.'.correct_answer', $question->correct_answer) == 'A' ? 'checked' : '' }} required>
                                                        <label class="text-sm font-medium text-gray-700 w-8">A:</label>
                                                        <input type="text" name="questions[{{ $index }}][option_a]" required
                                                               value="{{ old('questions.'.$index.'.option_a', $question->option_a) }}"
                                                               class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                                    </div>
                                                    <!-- Option B -->
                                                    <div class="flex items-center space-x-2">
                                                        <input type="radio" name="questions[{{ $index }}][correct_answer]" value="B"
                                                               {{ old('questions.'.$index.'.correct_answer', $question->correct_answer) == 'B' ? 'checked' : '' }}>
                                                        <label class="text-sm font-medium text-gray-700 w-8">B:</label>
                                                        <input type="text" name="questions[{{ $index }}][option_b]" required
                                                               value="{{ old('questions.'.$index.'.option_b', $question->option_b) }}"
                                                               class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                                    </div>
                                                    <!-- Option C -->
                                                    <div class="flex items-center space-x-2">
                                                        <input type="radio" name="questions[{{ $index }}][correct_answer]" value="C"
                                                               {{ old('questions.'.$index.'.correct_answer', $question->correct_answer) == 'C' ? 'checked' : '' }}>
                                                        <label class="text-sm font-medium text-gray-700 w-8">C:</label>
                                                        <input type="text" name="questions[{{ $index }}][option_c]" required
                                                               value="{{ old('questions.'.$index.'.option_c', $question->option_c) }}"
                                                               class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                                    </div>
                                                    <!-- Option D -->
                                                    <div class="flex items-center space-x-2">
                                                        <input type="radio" name="questions[{{ $index }}][correct_answer]" value="D"
                                                               {{ old('questions.'.$index.'.correct_answer', $question->correct_answer) == 'D' ? 'checked' : '' }}>
                                                        <label class="text-sm font-medium text-gray-700 w-8">D:</label>
                                                        <input type="text" name="questions[{{ $index }}][option_d]" required
                                                               value="{{ old('questions.'.$index.'.option_d', $question->option_d) }}"
                                                               class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-question"
                                class="mt-4 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Add Question
                        </button>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('quizzes.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Update Quiz
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let questionCount = {{ $quiz->questions->count() }};

    // Initialize existing questions
    const existingQuestionTypes = document.querySelectorAll('select[name*="[question_type]"]');
    existingQuestionTypes.forEach(select => {
        toggleAnswers(select);
    });

    function addQuestion() {
        questionCount++;
        const container = document.getElementById('questions-container');
        const questionDiv = document.createElement('div');
        questionDiv.className = 'border border-gray-200 rounded-lg p-4 mb-4';
        questionDiv.innerHTML = `
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-md font-medium text-gray-900">Question ${questionCount}</h4>
                <button type="button" class="text-red-600 hover:text-red-900" onclick="this.parentElement.parentElement.remove()">
                    Remove
                </button>
            </div>

            <!-- Hidden field for question ID (null for new questions) -->
            <input type="hidden" name="questions[${questionCount}][id]" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Question Text</label>
                    <textarea name="questions[${questionCount}][question_text]" required rows="2"
                              class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Question Type</label>
                        <select name="questions[${questionCount}][question_type]" required onchange="toggleAnswers(this)"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="true_false">True/False</option>
                            <option value="text">Text Answer</option>
                            <option value="fill_blank">Fill in the Blank</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Points</label>
                        <input type="number" name="questions[${questionCount}][points]" required min="1" value="1"
                               class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>

                <div class="answers-container">
                    <label class="block text-sm font-medium text-gray-700">Answers</label>
                    <div class="answers-list mt-2 space-y-2">
                        <div class="flex items-center space-x-2">
                            <input type="radio" name="questions[${questionCount}][correct_answer]" value="A" required>
                            <label class="text-sm font-medium text-gray-700 w-8">A:</label>
                            <input type="text" name="questions[${questionCount}][option_a]" required
                                   class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="radio" name="questions[${questionCount}][correct_answer]" value="B">
                            <label class="text-sm font-medium text-gray-700 w-8">B:</label>
                            <input type="text" name="questions[${questionCount}][option_b]" required
                                   class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="radio" name="questions[${questionCount}][correct_answer]" value="C">
                            <label class="text-sm font-medium text-gray-700 w-8">C:</label>
                            <input type="text" name="questions[${questionCount}][option_c]" required
                                   class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="radio" name="questions[${questionCount}][correct_answer]" value="D">
                            <label class="text-sm font-medium text-gray-700 w-8">D:</label>
                            <input type="text" name="questions[${questionCount}][option_d]" required
                                   class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(questionDiv);
    }

    function toggleAnswers(select) {
        const answersContainer = select.closest('.space-y-4').querySelector('.answers-container');
        const questionType = select.value;

        if (questionType === 'text') {
            answersContainer.style.display = 'block';
            updateAnswersContainerForText(answersContainer, select);

            const questionDiv = select.closest('.space-y-4');
            let manualGradingInput = questionDiv.querySelector('input[name*="[requires_manual_grading]"]');
            if (!manualGradingInput) {
                manualGradingInput = document.createElement('input');
                manualGradingInput.type = 'hidden';
                manualGradingInput.name = select.name.replace('[question_type]', '[requires_manual_grading]');
                questionDiv.appendChild(manualGradingInput);
            }
            manualGradingInput.value = '1';
        } else if (questionType === 'fill_blank') {
            answersContainer.style.display = 'block';
            // Update the answers container for fill-in-the-blank
            updateAnswersContainerForFillBlank(answersContainer, select);

            // Set manual grading flag for fill-in-the-blank
            const questionDiv = select.closest('.space-y-4');
            let manualGradingInput = questionDiv.querySelector('input[name*="[requires_manual_grading]"]');
            if (!manualGradingInput) {
                manualGradingInput = document.createElement('input');
                manualGradingInput.type = 'hidden';
                manualGradingInput.name = select.name.replace('[question_type]', '[requires_manual_grading]');
                questionDiv.appendChild(manualGradingInput);
            }
            manualGradingInput.value = '1';
        } else if (questionType === 'true_false') {
            answersContainer.style.display = 'block';
            // Update the answers container for true/false
            updateAnswersContainerForTrueFalse(answersContainer, select);

            // Clear manual grading flag for true/false
            const questionDiv = select.closest('.space-y-4');
            const manualGradingInput = questionDiv.querySelector('input[name*="[requires_manual_grading]"]');
            if (manualGradingInput) {
                manualGradingInput.value = '0'; // True/false doesn't require manual grading
            }
        } else {
            answersContainer.style.display = 'block';

            // Check if this is a new question (no existing answers) or existing question
            const existingAnswers = answersContainer.querySelector('.answers-list');
            if (!existingAnswers || existingAnswers.children.length === 0) {
                // This is a new question, reset to default multiple choice format
                resetAnswersContainer(answersContainer, select);
            } else {
                // This is an existing question, just restore the required attributes
                restoreMultipleChoiceAnswers(answersContainer);
            }

            // Clear manual grading flag for multiple choice/true-false
            const questionDiv = select.closest('.space-y-4');
            const manualGradingInput = questionDiv.querySelector('input[name*="[requires_manual_grading]"]');
            if (manualGradingInput) {
                manualGradingInput.value = '0';
            }
        }
    }

    function updateAnswersContainerForText(container, select) {
        const questionIndex = select.name.match(/\[(\d+)\]/)[1];
        const existing = select.closest('.space-y-4')?.querySelector('textarea[name="questions[' + questionIndex + '][correct_answer]"]');
        const savedValue = existing ? existing.value.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;') : '';
        container.innerHTML =
            '<label for="reference_answer_' + questionIndex + '" class="block text-sm font-medium text-gray-700">Reference answer</label>' +
            '<p class="mt-1 text-sm text-gray-500"><strong>Admin only.</strong> Not shown to quiz takers. Shown in manual grading when reviewing this text question.</p>' +
            '<textarea name="questions[' + questionIndex + '][correct_answer]" id="reference_answer_' + questionIndex + '" rows="3" placeholder="Optional reference for graders (e.g. key points or sample answer)" class="mt-2 block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md">' + savedValue + '</textarea>';
    }

    function updateAnswersContainerForFillBlank(container, select) {
        const questionIndex = select.name.match(/\[(\d+)\]/)[1];
        container.innerHTML =
            '<label class="block text-sm font-medium text-gray-700">Correct Answer(s)</label>' +
            '<div class="mt-2 space-y-2">' +
                '<div class="flex items-center space-x-2">' +
                    '<input type="text" name="questions[' + questionIndex + '][correct_answer]" required' +
                           ' placeholder="Enter the correct answer"' +
                           ' class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">' +
                    '<span class="text-sm text-gray-500">Correct Answer</span>' +
                '</div>' +
                '<div class="flex items-center space-x-2">' +
                    '<input type="text" name="questions[' + questionIndex + '][alternative_answer_1]"' +
                           ' placeholder="Alternative answer 1 (optional)"' +
                           ' class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">' +
                    '<span class="text-sm text-gray-500">Alternative 1</span>' +
                '</div>' +
                '<div class="flex items-center space-x-2">' +
                    '<input type="text" name="questions[' + questionIndex + '][alternative_answer_2]"' +
                           ' placeholder="Alternative answer 2 (optional)"' +
                           ' class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">' +
                    '<span class="text-sm text-gray-500">Alternative 2</span>' +
                '</div>' +
                '<div class="flex items-center space-x-2">' +
                    '<input type="text" name="questions[' + questionIndex + '][alternative_answer_3]"' +
                           ' placeholder="Alternative answer 3 (optional)"' +
                           ' class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">' +
                    '<span class="text-sm text-gray-500">Alternative 3</span>' +
                '</div>' +
            '</div>' +
            '<p class="mt-2 text-sm text-gray-500">' +
                '<strong>Note:</strong> Fill-in-the-blank questions require manual grading. ' +
                'Enter the main correct answer and up to 3 alternative acceptable answers.' +
            '</p>';
    }

    function updateAnswersContainerForTrueFalse(container, select) {
        const questionIndex = select.name.match(/\[(\d+)\]/)[1];
        container.innerHTML =
            '<label class="block text-sm font-medium text-gray-700">Answers</label>' +
            '<div class="answers-list mt-2 space-y-2">' +
                '<div class="flex items-center space-x-2">' +
                    '<input type="radio" name="questions[' + questionIndex + '][correct_answer]" value="A" required>' +
                    '<label class="text-sm font-medium text-gray-700 w-8">A:</label>' +
                    '<input type="text" name="questions[' + questionIndex + '][option_a]" required readonly' +
                           ' value="True" class="flex-1 shadow-sm bg-gray-100 block w-full sm:text-sm border-gray-300 rounded-md">' +
                '</div>' +
                '<div class="flex items-center space-x-2">' +
                    '<input type="radio" name="questions[' + questionIndex + '][correct_answer]" value="B">' +
                    '<label class="text-sm font-medium text-gray-700 w-8">B:</label>' +
                    '<input type="text" name="questions[' + questionIndex + '][option_b]" required readonly' +
                           ' value="False" class="flex-1 shadow-sm bg-gray-100 block w-full sm:text-sm border-gray-300 rounded-md">' +
                '</div>' +
            '</div>' +
            '<p class="mt-2 text-sm text-gray-500">' +
                '<strong>Note:</strong> True/False questions automatically have "True" and "False" options. ' +
                'Select which one is the correct answer.' +
            '</p>';
    }

    function restoreMultipleChoiceAnswers(container) {
        // Restore required attributes for existing multiple choice answers
        const radioButtons = container.querySelectorAll('input[type="radio"]');
        const textInputs = container.querySelectorAll('input[type="text"]');

        // Add required attribute to all radio buttons and text inputs
        radioButtons.forEach(radio => {
            radio.setAttribute('required', 'required');
        });

        textInputs.forEach(input => {
            input.setAttribute('required', 'required');
        });

        // Ensure the first radio button has the required attribute
        const firstRadio = container.querySelector('input[type="radio"]');
        if (firstRadio) {
            firstRadio.setAttribute('required', 'required');
        }
    }

    function resetAnswersContainer(container, select) {
        const questionIndex = select.name.match(/\[(\d+)\]/)[1];
        container.innerHTML =
            '<label class="block text-sm font-medium text-gray-700">Answers</label>' +
            '<div class="answers-list mt-2 space-y-2">' +
                '<div class="flex items-center space-x-2">' +
                    '<input type="radio" name="questions[' + questionIndex + '][correct_answer]" value="A" required>' +
                    '<label class="text-sm font-medium text-gray-700 w-8">A:</label>' +
                    '<input type="text" name="questions[' + questionIndex + '][option_a]" required' +
                           ' class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">' +
                '</div>' +
                '<div class="flex items-center space-x-2">' +
                    '<input type="radio" name="questions[' + questionIndex + '][correct_answer]" value="B">' +
                    '<label class="text-sm font-medium text-gray-700 w-8">B:</label>' +
                    '<input type="text" name="questions[' + questionIndex + '][option_b]" required' +
                           ' class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">' +
                '</div>' +
                '<div class="flex items-center space-x-2">' +
                    '<input type="radio" name="questions[' + questionIndex + '][correct_answer]" value="C">' +
                    '<label class="text-sm font-medium text-gray-700 w-8">C:</label>' +
                    '<input type="text" name="questions[' + questionIndex + '][option_c]" required' +
                           ' class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">' +
                '</div>' +
                '<div class="flex items-center space-x-2">' +
                    '<input type="radio" name="questions[' + questionIndex + '][correct_answer]" value="D">' +
                    '<label class="text-sm font-medium text-gray-700 w-8">D:</label>' +
                    '<input type="text" name="questions[' + questionIndex + '][option_d]" required' +
                           ' class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">' +
                '</div>' +
            '</div>';

        // Ensure the first radio button has the required attribute
        const firstRadio = container.querySelector('input[type="radio"]');
        if (firstRadio) {
            firstRadio.setAttribute('required', 'required');
        }
    }


    document.getElementById('add-question').addEventListener('click', addQuestion);

    // Event delegation for question type changes
    document.addEventListener('change', function(e) {
        if (e.target.name && e.target.name.includes('[question_type]')) {
            toggleAnswers(e.target);
        }
    });

    // Add new topic functionality
    document.getElementById('add-topic-btn').addEventListener('click', function() {
        const newTopic = prompt('Enter the new topic/subject name:');
        if (newTopic && newTopic.trim() !== '') {
            const topicSelect = document.getElementById('topic');
            const option = document.createElement('option');
            option.value = newTopic.trim();
            option.textContent = newTopic.trim();
            option.selected = true;
            topicSelect.appendChild(option);

            // Show success message
            ToastNotification.success('New topic "' + newTopic.trim() + '" added successfully!');
        }
    });

    // Form submission
    document.getElementById('quiz-form').addEventListener('submit', function(e) {
        const questions = document.querySelectorAll('[id^="questions-container"] > div');
        if (questions.length === 0) {
            e.preventDefault();
            ToastNotification.warning('Please add at least one question.');
            return;
        }
    });
});
</script>
@endsection
