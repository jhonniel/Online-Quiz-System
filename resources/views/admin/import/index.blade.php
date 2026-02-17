<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Questions from Excel') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- Instructions -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Import Instructions</h3>
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">
                                        Excel Format Requirements
                                    </h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>First row must contain headers: Question Text, Question Type, Points, Answer 1, Answer 2, Answer 3, Answer 4, Correct Answer</li>
                                            <li>Question Type: multiple_choice, true_false, or text</li>
                                            <li>Points: Number of points for the question (default: 1)</li>
                                            <li>Answer 1-4: Answer options (leave empty for unused options)</li>
                                            <li>Correct Answer: Number indicating which answer is correct (1-4)</li>
                                            <li>For True/False: Use Answer 1="True", Answer 2="False", Correct Answer=1 or 2</li>
                                            <li>For Text questions: Leave answers empty, Correct Answer can be empty</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Download Template -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Download Template</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                            <p class="text-sm text-gray-600 mb-3">
                                Download our Excel template to see the correct format and get started quickly.
                            </p>
                            <a href="{{ url('/admin/import/template') }}"
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download Excel Template
                            </a>
                        </div>
                    </div>

                    <!-- Import Form -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Import Questions</h3>

                        @if ($errors->any())
                            <div class="mb-4 bg-red-50 border border-red-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">
                                            There were errors with your submission
                                        </h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <ul class="list-disc list-inside space-y-1">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form action="{{ url('/admin/import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf

                            <!-- Quiz Details -->
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="quiz_title" class="block text-sm font-medium text-gray-700">
                                        Quiz Title <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           name="quiz_title"
                                           id="quiz_title"
                                           value="{{ old('quiz_title') }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                           placeholder="Enter quiz title"
                                           required>
                                </div>

                                <div>
                                    <label for="time_limit" class="block text-sm font-medium text-gray-700">
                                        Time Limit (minutes)
                                    </label>
                                    <input type="number"
                                           name="time_limit"
                                           id="time_limit"
                                           value="{{ old('time_limit') }}"
                                           min="1"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                           placeholder="Optional time limit">
                                </div>
                            </div>

                            <div>
                                <label for="quiz_description" class="block text-sm font-medium text-gray-700">
                                    Quiz Description
                                </label>
                                <textarea name="quiz_description"
                                          id="quiz_description"
                                          rows="3"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                          placeholder="Enter quiz description (optional)">{{ old('quiz_description') }}</textarea>
                            </div>

                            <!-- File Upload -->
                            <div>
                                <label for="excel_file" class="block text-sm font-medium text-gray-700">
                                    Excel File <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="excel_file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                <span>Upload Excel file</span>
                                                <input id="excel_file"
                                                       name="excel_file"
                                                       type="file"
                                                       accept=".xlsx,.xls,.csv"
                                                       class="sr-only"
                                                       required>
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            Excel files (.xlsx, .xls, .csv) up to 10MB
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end space-x-3">
                                <a href="{{ url('/admin/quizzes') }}"
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Cancel
                                </a>
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

                    <!-- Example Format -->
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Example Format</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Question Text</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Question Type</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Answer 1</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Answer 2</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Answer 3</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Answer 4</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correct Answer</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-gray-900">What is the capital of France?</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">multiple_choice</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">10</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">London</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">Berlin</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">Paris</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">Madrid</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">3</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-gray-900">PHP is a server-side language.</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">true_false</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">5</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">True</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">False</td>
                                        <td class="px-3 py-2 text-sm text-gray-900"></td>
                                        <td class="px-3 py-2 text-sm text-gray-900"></td>
                                        <td class="px-3 py-2 text-sm text-gray-900">1</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-gray-900">Explain the concept of OOP.</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">text</td>
                                        <td class="px-3 py-2 text-sm text-gray-900">15</td>
                                        <td class="px-3 py-2 text-sm text-gray-900"></td>
                                        <td class="px-3 py-2 text-sm text-gray-900"></td>
                                        <td class="px-3 py-2 text-sm text-gray-900"></td>
                                        <td class="px-3 py-2 text-sm text-gray-900"></td>
                                        <td class="px-3 py-2 text-sm text-gray-900"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
