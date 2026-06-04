@php
    $errors = $errors ?? new \Illuminate\Support\MessageBag();
@endphp

@extends('layouts.admin')

@section('page-title', 'Import Quiz')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Import Quiz from Excel
            </h2>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="{{ route('quizzes.index') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Quizzes
            </a>
        </div>
    </div>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Instructions -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">How to Import Quiz Questions</h3>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-blue-800">Instructions</h4>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <ol class="list-decimal list-inside space-y-1">
                                            <li>Download the Excel template below to see the correct format</li>
                                            <li>Fill in your quiz questions following the template structure</li>
                                            <li>Upload the completed Excel file using the form below</li>
                                            <li>Provide quiz details (title, description, time limit)</li>
                                            <li>Click "Import Quiz" to create your quiz with all questions</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Template Download -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Download Template</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">Excel Template</h4>
                                    <p class="text-sm text-gray-600">Download the template to see the correct format for importing questions</p>
                                </div>
                                <a href="{{ route('admin.quizzes.download-template') }}"
                                   class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download Template
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Import Form -->
                    <form action="{{ route('admin.quizzes.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Quiz Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="quiz_title" class="block text-sm font-medium text-gray-700 mb-2">
                                    Quiz Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="quiz_title"
                                       name="quiz_title"
                                       value="{{ old('quiz_title') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('quiz_title') ? 'border-red-500' : '' }}"
                                       placeholder="Enter quiz title"
                                       required>
                                @if($errors->has('quiz_title'))
                                    <p class="mt-1 text-sm text-red-600">{{ $errors->first('quiz_title') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="time_limit" class="block text-sm font-medium text-gray-700 mb-2">
                                    Time Limit (minutes)
                                </label>
                                <input type="number"
                                       id="time_limit"
                                       name="time_limit"
                                       value="{{ old('time_limit') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('time_limit') ? 'border-red-500' : '' }}"
                                       placeholder="Enter time limit in minutes"
                                       min="1">
                                @if($errors->has('time_limit'))
                                    <p class="mt-1 text-sm text-red-600">{{ $errors->first('time_limit') }}</p>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label for="quiz_description" class="block text-sm font-medium text-gray-700 mb-2">
                                Quiz Description
                            </label>
                            <textarea id="quiz_description"
                                      name="quiz_description"
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('quiz_description') ? 'border-red-500' : '' }}"
                                      placeholder="Enter quiz description">{{ old('quiz_description') }}</textarea>
                            @if($errors->has('quiz_description'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->first('quiz_description') }}</p>
                            @endif
                        </div>

                        <!-- File Upload -->
                        <div>
                            <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-2">
                                Excel File <span class="text-red-500">*</span>
                            </label>

                            <!-- File Input -->
                            <div class="mt-1">
                                <input id="excel_file"
                                       name="excel_file"
                                       type="file"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 {{ $errors->has('excel_file') ? 'border-red-500' : '' }}"
                                       accept=".xlsx,.xls,.csv"
                                       required>
                            </div>

                            <!-- Drag and Drop Area -->
                            <div id="drop-area" class="mt-3 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors cursor-pointer">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium text-indigo-600">Click to browse</span>
                                        <span class="text-gray-500"> or drag and drop</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Excel files (.xlsx, .xls) or CSV files up to 10MB</p>
                                </div>
                            </div>

                            <!-- File Preview -->
                            <div id="file-preview" class="mt-3 hidden">
                                <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div>
                                            <p id="file-name" class="text-sm font-medium text-gray-900"></p>
                                            <p id="file-size" class="text-xs text-gray-500"></p>
                                        </div>
                                    </div>
                                    <button type="button" id="remove-file" class="text-sm text-red-600 hover:text-red-500">
                                        Remove
                                    </button>
                                </div>
                            </div>

                            @if($errors->has('excel_file'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->first('excel_file') }}</p>
                            @endif
                        </div>

                        <!-- Excel Format Info -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-yellow-800">Excel Format Requirements</h4>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li><strong>Column A:</strong> Question Text</li>
                                            <li><strong>Column B:</strong> Question Type (multiple_choice, true_false, text)</li>
                                            <li><strong>Column C:</strong> Points (number)</li>
                                            <li><strong>Columns D-G:</strong> Answer options (Answer 1, Answer 2, Answer 3, Answer 4)</li>
                                            <li><strong>Column H:</strong> Correct Answer (1, 2, 3, 4 for multiple choice; 1, 2 for true/false; actual answer for text)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('quizzes.index') }}"
                               class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                </svg>
                                Import Quiz
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('excel_file');
            const dropArea = document.getElementById('drop-area');
            const filePreview = document.getElementById('file-preview');
            const fileName = document.getElementById('file-name');
            const fileSize = document.getElementById('file-size');
            const removeFileBtn = document.getElementById('remove-file');

            // File input change handler
            fileInput.addEventListener('change', function(e) {
                handleFileSelect(e.target.files[0]);
            });

            // Drag and drop handlers
            dropArea.addEventListener('click', function() {
                fileInput.click();
            });

            dropArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropArea.classList.add('border-indigo-400', 'bg-indigo-50');
            });

            dropArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                dropArea.classList.remove('border-indigo-400', 'bg-indigo-50');
            });

            dropArea.addEventListener('drop', function(e) {
                e.preventDefault();
                dropArea.classList.remove('border-indigo-400', 'bg-indigo-50');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    handleFileSelect(files[0]);
                }
            });

            // Remove file handler
            removeFileBtn.addEventListener('click', function() {
                fileInput.value = '';
                filePreview.classList.add('hidden');
                dropArea.classList.remove('hidden');
            });

            function handleFileSelect(file) {
                if (file) {
                    const fileNameText = file.name;
                    const fileSizeText = (file.size / 1024 / 1024).toFixed(2) + ' MB';

                    fileName.textContent = fileNameText;
                    fileSize.textContent = fileSizeText;

                    filePreview.classList.remove('hidden');
                    dropArea.classList.add('hidden');
                }
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('excel_file');
            const quizTitle = document.getElementById('quiz_title');

            if (!fileInput.files.length) {
                e.preventDefault();
                ToastNotification.warning('Please select an Excel file to upload.');
                return;
            }

            if (!quizTitle.value.trim()) {
                e.preventDefault();
                ToastNotification.warning('Please enter a quiz title.');
                quizTitle.focus();
                return;
            }

            // Show loading state
            const submitButton = document.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Importing...
            `;
        });
    </script>
@endsection
