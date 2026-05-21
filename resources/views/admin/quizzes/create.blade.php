@extends('layouts.admin')

@section('page-title', 'Create New Quiz')

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
            <span class="ml-2 text-sm font-medium text-gray-500">Create</span>
        </div>
    </li>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create New Quiz</h1>
                <p class="mt-2 text-gray-600">Create a comprehensive quiz with questions, answers, and proper settings.</p>
            </div>
            <a href="{{ route('quizzes.index') }}">
                <x-formal-button variant="outline" size="md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Quizzes
                </x-formal-button>
            </a>
        </div>
    </div>

    <!-- Main Form Card -->
    <x-formal-card
        title="Quiz Information"
        subtitle="Please provide the basic information for your quiz."
        class="mb-6"
    >
        <form action="{{ route('quizzes.store') }}" method="POST" id="quiz-form" class="space-y-8">
            @csrf

            <!-- Quiz Details Section -->
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Quiz Details</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Title -->
                    <x-formal-input
                        label="Quiz Title"
                        name="title"
                        type="text"
                        :required="true"
                        placeholder="Enter the quiz title"
                        :value="old('title')"
                        :error="$errors->first('title')"
                        help="A clear and descriptive title for the quiz"
                        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>'
                    />

                    <!-- Time Limit -->
                    <x-formal-input
                        label="Time Limit (minutes)"
                        name="time_limit"
                        type="number"
                        :required="true"
                        placeholder="Enter time limit in minutes"
                        :value="old('time_limit')"
                        :error="$errors->first('time_limit')"
                        help="Maximum time allowed to complete the quiz"
                        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
                    />
                </div>

                <!-- Questions to Show -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-formal-input
                        label="Questions to Show (Optional)"
                        name="questions_to_show"
                        type="number"
                        :required="false"
                        placeholder="Leave empty to show all questions"
                        :value="old('questions_to_show')"
                        :error="$errors->first('questions_to_show')"
                        help="Number of questions to randomly show to users. If empty, all questions will be shown."
                        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>'
                    />
                </div>

                <!-- Description -->
                <x-formal-textarea
                    label="Description"
                    name="description"
                    :rows="3"
                    placeholder="Provide a brief description of the quiz content and objectives"
                    :value="old('description')"
                    :error="$errors->first('description')"
                    help="Optional description to help students understand what the quiz covers"
                />

                    <!-- Topic -->
                    <div>
                        <label for="topic" class="block text-sm font-medium text-gray-700">Topic/Subject</label>
                        <div class="mt-1 flex space-x-2">
                            <select name="topic" id="topic" required
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <option value="">Select a topic</option>
                                <option value="Laravel" {{ old('topic') == 'Laravel' ? 'selected' : '' }}>Laravel</option>
                                <option value="PHP" {{ old('topic') == 'PHP' ? 'selected' : '' }}>PHP</option>
                                <option value="JavaScript" {{ old('topic') == 'JavaScript' ? 'selected' : '' }}>JavaScript</option>
                                <option value="Vue.js" {{ old('topic') == 'Vue.js' ? 'selected' : '' }}>Vue.js</option>
                                <option value="React" {{ old('topic') == 'React' ? 'selected' : '' }}>React</option>
                                <option value="Git" {{ old('topic') == 'Git' ? 'selected' : '' }}>Git</option>
                                <option value="Networking" {{ old('topic') == 'Networking' ? 'selected' : '' }}>Networking</option>
                                <option value="Database" {{ old('topic') == 'Database' ? 'selected' : '' }}>Database</option>
                                <option value="HTML" {{ old('topic') == 'HTML' ? 'selected' : '' }}>HTML</option>
                                <option value="CSS" {{ old('topic') == 'CSS' ? 'selected' : '' }}>CSS</option>
                                <option value="Python" {{ old('topic') == 'Python' ? 'selected' : '' }}>Python</option>
                                <option value="Java" {{ old('topic') == 'Java' ? 'selected' : '' }}>Java</option>
                                <option value="Quality Assurance" {{ old('topic') == 'Quality Assurance' ? 'selected' : '' }}>Quality Assurance</option>
                                <option value="UI/UX Design" {{ old('topic') == 'UI/UX Design' ? 'selected' : '' }}>UI/UX Design</option>
                                <option value="General" {{ old('topic') == 'General' ? 'selected' : '' }}>General</option>
                                <option value="C#" {{ old('topic') == 'C#' ? 'selected' : '' }}>C#</option>
                                <option value="WordPress" {{ old('topic') == 'WordPress' ? 'selected' : '' }}>WordPress</option>
                                <option value="Mobile App Development" {{ old('topic') == 'Mobile App Development' ? 'selected' : '' }}>Mobile App Development</option>
                                <option value="Flutter" {{ old('topic') == 'Flutter' ? 'selected' : '' }}>Flutter</option>
                            </select>
                            <button type="button" id="add-topic-btn"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add New
                            </button>
                        </div>
                        @if(isset($errors) && $errors->has('topic'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->first('topic') }}</p>
                        @endif
                    </div>

                    <!-- Quiz Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Quiz Status</label>
                        <div class="flex items-center space-x-6">
                            <label class="inline-flex items-center">
                                <input type="radio" name="is_active" value="1"
                                       {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                                       class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="is_active" value="0"
                                       {{ old('is_active') == '0' ? 'checked' : '' }}
                                       class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                <span class="ml-2 text-sm text-gray-700">Inactive</span>
                            </label>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Active quizzes are visible to users, inactive quizzes are hidden.</p>
                        @if(isset($errors) && $errors->has('is_active'))
                            <p class="mt-2 text-sm text-red-600">{{ $errors->first('is_active') }}</p>
                        @endif
                    </div>

                    <!-- Questions -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Questions</h3>
                        <div id="questions-container">
                            <!-- Question template will be added here -->
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
                        Create Quiz
                    </button>
                </div>
            </form>
        </x-formal-card>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let questionCount = 0;

    function addQuestion() {
        questionCount++;
        const container = document.getElementById('questions-container');
        const questionDiv = document.createElement('div');
        questionDiv.className = 'border border-gray-200 rounded-lg p-4 mb-4';
        questionDiv.innerHTML =
            '<div class="flex justify-between items-center mb-4">' +
                '<h4 class="text-md font-medium text-gray-900">Question ' + questionCount + '</h4>' +
                '<button type="button" class="text-red-600 hover:text-red-900" onclick="this.parentElement.parentElement.remove()">' +
                    'Remove' +
                '</button>' +
            '</div>' +
            '<div class="space-y-4">' +
                '<div>' +
                    '<label class="block text-sm font-medium text-gray-700">Question Text</label>' +
                    '<textarea name="questions[' + questionCount + '][question_text]" required rows="2"' +
                              ' class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>' +
                '</div>' +
                '<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">' +
                    '<div>' +
                        '<label class="block text-sm font-medium text-gray-700">Question Type</label>' +
                        '<select name="questions[' + questionCount + '][question_type]" required' +
                                ' class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">' +
                            '<option value="multiple_choice">Multiple Choice</option>' +
                            '<option value="true_false">True/False</option>' +
                            '<option value="text">Text Answer</option>' +
                            '<option value="fill_blank">Fill in the Blank</option>' +
                        '</select>' +
                    '</div>' +
                    '<div>' +
                        '<label class="block text-sm font-medium text-gray-700">Points</label>' +
                        '<input type="number" name="questions[' + questionCount + '][points]" required min="1" value="1"' +
                               ' class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">' +
                    '</div>' +
                '</div>' +
                '<div class="answers-container">' +
                    '<label class="block text-sm font-medium text-gray-700">Answers</label>' +
                    '<div class="answers-list mt-2 space-y-2">' +
                        '<div class="flex items-center space-x-2">' +
                            '<input type="radio" name="questions[' + questionCount + '][correct_answer]" value="A" required>' +
                            '<label class="text-sm font-medium text-gray-700 w-8">A:</label>' +
                            '<input type="text" name="questions[' + questionCount + '][option_a]" required' +
                                   ' class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">' +
                        '</div>' +
                        '<div class="flex items-center space-x-2">' +
                            '<input type="radio" name="questions[' + questionCount + '][correct_answer]" value="B">' +
                            '<label class="text-sm font-medium text-gray-700 w-8">B:</label>' +
                            '<input type="text" name="questions[' + questionCount + '][option_b]" required' +
                                   ' class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">' +
                        '</div>' +
                        '<div class="flex items-center space-x-2">' +
                            '<input type="radio" name="questions[' + questionCount + '][correct_answer]" value="C">' +
                            '<label class="text-sm font-medium text-gray-700 w-8">C:</label>' +
                            '<input type="text" name="questions[' + questionCount + '][option_c]" required' +
                                   ' class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">' +
                        '</div>' +
                        '<div class="flex items-center space-x-2">' +
                            '<input type="radio" name="questions[' + questionCount + '][correct_answer]" value="D">' +
                            '<label class="text-sm font-medium text-gray-700 w-8">D:</label>' +
                            '<input type="text" name="questions[' + questionCount + '][option_d]" required' +
                                   ' class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" onclick="addAnswer(this)" class="mt-2 text-sm text-blue-600 hover:text-blue-800">' +
                        'Add Answer' +
                    '</button>' +
                '</div>' +
            '</div>';
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

            // Clear manual grading flag for fill-in-the-blank (handled separately)
            const questionDiv = select.closest('.space-y-4');
            const manualGradingInput = questionDiv.querySelector('input[name*="[requires_manual_grading]"]');
            if (manualGradingInput) {
                manualGradingInput.value = '1'; // Fill-in-the-blank also requires manual grading
            }
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
                manualGradingInput.value = '0'; // Multiple choice/true-false don't require manual grading
            }
        }
    }

    function updateAnswersContainerForText(container, select) {
        const questionIndex = select.name.match(/\[(\d+)\]/)[1];
        container.innerHTML =
            '<label for="reference_answer_' + questionIndex + '" class="block text-sm font-medium text-gray-700">Reference answer</label>' +
            '<p class="mt-1 text-sm text-gray-500"><strong>Admin only.</strong> Not shown to quiz takers. Shown in manual grading when reviewing this text question.</p>' +
            '<textarea name="questions[' + questionIndex + '][correct_answer]" id="reference_answer_' + questionIndex + '" rows="3" placeholder="Optional reference for graders (e.g. key points or sample answer)" class="mt-2 block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md"></textarea>';
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
            '</div>' +
            '<button type="button" onclick="addAnswer(this)" class="mt-2 text-sm text-blue-600 hover:text-blue-800">' +
                'Add Answer' +
            '</button>';

        // Ensure the first radio button has the required attribute
        const firstRadio = container.querySelector('input[type="radio"]');
        if (firstRadio) {
            firstRadio.setAttribute('required', 'required');
        }
    }

    function addAnswer(button) {
        const answersList = button.previousElementSibling;
        const answerCount = answersList.children.length;
        const answerDiv = document.createElement('div');
        answerDiv.className = 'flex items-center space-x-2';
        answerDiv.innerHTML = `
            <input type="radio" name="${button.previousElementSibling.querySelector('input[type="radio"]').name}" value="${answerCount}">
            <input type="text" name="${button.previousElementSibling.querySelector('input[type="text"]').name.replace('[0]', `[${answerCount}]`)}" required
                   class="flex-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
            <span class="text-sm text-gray-500">Correct</span>
        `;
        answersList.appendChild(answerDiv);
    }

    // Add first question
    addQuestion();

    document.getElementById('add-question').addEventListener('click', addQuestion);

    // Add event delegation for question type changes
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
