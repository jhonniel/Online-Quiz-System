@extends('layouts.admin')

@section('page-title', 'Quiz Management')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Quizzes</span>
        </div>
    </li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Search and Filter Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Search -->
            <div class="flex-1 max-w-md">
                <form id="quizzes-search-form" method="GET" action="{{ route('quizzes.index') }}">
                    @if(request()->has('per_page'))
                        <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                    @endif
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text"
                               id="search-input"
                               name="search"
                               value="{{ request('search', $search ?? '') }}"
                               placeholder="Search quizzes (title, code, topic, creator, ID)..."
                               autocomplete="off"
                               class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @if(request('search'))
                            <button type="button"
                                    id="clear-search-btn"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                    title="Clear search">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-3">
                <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filter
                </button>
                @if(auth()->user()->isAdmin())
                <form id="export-quizzes-form" method="POST" action="{{ route('admin.quizzes.export-csv') }}" style="display: none;">
                    @csrf
                    <input type="hidden" name="quiz_ids" id="export-quiz-ids" value="">
                </form>
                <button type="button" id="export-quizzes-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span id="export-btn-text">Export CSV</span>
                    <span id="export-count-badge" class="hidden ml-2 bg-indigo-600 text-white text-xs rounded-full px-2 py-0.5">0</span>
                </button>
                @endif
                <a href="{{ route('admin.quizzes.import-form') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                    </svg>
                    Import
                </a>
                <a href="{{ route('quizzes.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Quiz
                </a>
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h1 class="text-2xl font-bold text-white">Quizzes</h1>
                <p class="text-indigo-100">Manage quiz content and assignments</p>
            </div>
        </div>
    </div>

    <!-- Quizzes Table -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
        @if($quizzes->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            @if(auth()->user()->isAdmin())
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="select-all-quizzes" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            @endif
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quiz ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Title
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Code
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Topic
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Questions
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Time Limit
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Created By
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Assigned Users
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($quizzes as $quiz)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                @if(auth()->user()->isAdmin())
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" 
                                           name="quiz_ids[]" 
                                           value="{{ $quiz->id }}" 
                                           class="quiz-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ str_pad($quiz->id, 4, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $quiz->title }}</div>
                                    @if($quiz->description)
                                        <div class="text-sm text-gray-500 truncate max-w-xs">{{ $quiz->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 font-mono">
                                        {{ $quiz->quiz_code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($quiz->topic)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $quiz->topic }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic">Not specified</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ $quiz->total_questions }} questions
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ $quiz->time_limit }} min
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $quiz->creator->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @php
                                        $assignedCount = $quiz->assignments_count ?? $quiz->assignments()->count();
                                        $completedCount = $quiz->completed_assignments_count ?? $quiz->assignments()->where('status', 'completed')->count();
                                    @endphp
                                    <div class="flex items-center space-x-2">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                            </svg>
                                            <span class="text-sm font-medium">{{ $assignedCount }}</span>
                                        </div>
                                        @if($assignedCount > 0)
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span class="text-xs text-green-600">{{ $completedCount }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    @if($assignedCount > 0)
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $completedCount }}/{{ $assignedCount }} completed
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-400 italic">No assignments</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $quiz->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $quiz->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open"
                                                class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                            </svg>
                                        </button>

                                        <!-- Simple Dropdown -->
                                        <div x-show="open"
                                             @click.away="open = false"
                                             @keydown.escape.window="open = false"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">

                                            <a href="{{ route('quizzes.show', $quiz) }}" class="w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                View
                                            </a>

                                            <a href="{{ route('quizzes.edit', $quiz) }}" class="w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </a>

                                            <button onclick="openAssignModal({{ $quiz->id }}, '{{ $quiz->title }}')" class="w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                                </svg>
                                                Assign
                                            </button>

                                            @if($assignedCount > 0)
                                                <button onclick="openAssignedUsersModal({{ $quiz->id }}, '{{ $quiz->title }}')" class="w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-50 flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                    </svg>
                                                    View Assigned Users
                                                </button>
                                            @endif

                                            <a href="{{ route('admin.quizzes.results', $quiz) }}" class="w-full px-4 py-2 text-sm text-green-700 hover:bg-green-50 flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                                Results
                                            </a>

                                            <div class="border-t border-gray-100"></div>

                                            <form method="POST" action="{{ route('quizzes.destroy', $quiz) }}" class="block" onsubmit="return confirmQuizAction('delete', this)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Close dropdown portal -->
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    {{ $quizzes->appends(request()->query())->links() }}
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div class="flex items-center">
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ $quizzes->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $quizzes->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $quizzes->total() }}</span>
                            results
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-700">Rows per page:</span>
                        <select id="quizzes-per-page-select" class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div>
                        {{ $quizzes->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No quizzes</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new quiz.</p>
                    <div class="mt-6">
                        <a href="{{ route('quizzes.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Create Quiz
                        </a>
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

<!-- Assigned Users Modal -->
<div id="assignedUsersModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Assigned Users</h3>
                <button onclick="closeAssignedUsersModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600">Quiz: <span id="assignedQuizTitle" class="font-medium"></span></p>
            </div>

            <!-- Search Assigned Users -->
            <div class="mb-4">
                <input type="text"
                       id="assignedUserSearch"
                       placeholder="Search assigned users..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Assigned Users List -->
            <div id="assignedUsersList" class="max-h-96 overflow-y-auto border border-gray-300 rounded-md">
                <!-- Assigned users will be loaded here -->
            </div>

            <div class="mt-4 flex justify-end">
                <button onclick="closeAssignedUsersModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Close
                </button>
            </div>
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
    document.getElementById('userSearch').value = '';
    document.getElementById('selectedCount').textContent = '0';
    document.getElementById('user_ids_error').classList.add('hidden');
    allUsers = [];
    filteredUsers = [];
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

let allUsers = [];
let filteredUsers = [];

function loadUsers() {
    const quizId = currentQuizId;
    const url = quizId ? `/admin/users/api?quiz_id=${quizId}` : '/admin/users/api';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            allUsers = data.users;
            filteredUsers = [...allUsers];
            renderUsers();
            setupEventListeners();
        })
        .catch(error => {
            console.error('Error loading users:', error);
            document.getElementById('usersList').innerHTML = '<p class="text-red-600 text-sm">Error loading users</p>';
        });
}

function renderUsers() {
    const usersList = document.getElementById('usersList');
    usersList.innerHTML = '';

    if (filteredUsers.length === 0) {
        usersList.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">No users found</p>';
        return;
    }

    filteredUsers.forEach(user => {
        const userDiv = document.createElement('div');
        userDiv.className = 'flex items-center space-x-2 p-2 hover:bg-gray-50 rounded user-item';
        userDiv.setAttribute('data-user-id', user.id);

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
                   class="user-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
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

    updateSelectedCount();
}

function setupEventListeners() {
    // Select All button
    document.getElementById('selectAllBtn').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        updateSelectedCount();
    });

    // Select None button
    document.getElementById('selectNoneBtn').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectedCount();
    });

    // User search
    document.getElementById('userSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        filteredUsers = allUsers.filter(user =>
            user.name.toLowerCase().includes(searchTerm) ||
            user.email.toLowerCase().includes(searchTerm) ||
            (user.university && user.university.name.toLowerCase().includes(searchTerm))
        );
        renderUsers();
    });

    // Checkbox change events
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('user-checkbox')) {
            updateSelectedCount();
        }
    });
}

function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.user-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selectedCount;
}

// Assigned Users Modal Functions
function openAssignedUsersModal(quizId, quizTitle) {
    document.getElementById('assignedQuizTitle').textContent = quizTitle;
    document.getElementById('assignedUsersModal').classList.remove('hidden');
    loadAssignedUsers(quizId);
}

function closeAssignedUsersModal() {
    document.getElementById('assignedUsersModal').classList.add('hidden');
    document.getElementById('assignedUserSearch').value = '';
    document.getElementById('assignedUsersList').innerHTML = '';
}

function loadAssignedUsers(quizId) {
    fetch(`/admin/quizzes/${quizId}/assigned-users`)
        .then(response => response.json())
        .then(data => {
            const assignedUsersList = document.getElementById('assignedUsersList');
            assignedUsersList.innerHTML = '';

            if (data.assignments.length === 0) {
                assignedUsersList.innerHTML = '<p class="text-gray-500 text-center py-8">No users assigned to this quiz</p>';
                return;
            }

            data.assignments.forEach(assignment => {
                const userDiv = document.createElement('div');
                userDiv.className = 'flex items-center justify-between p-4 border-b border-gray-200 hover:bg-gray-50 assigned-user-item';
                userDiv.setAttribute('data-user-name', assignment.user.name.toLowerCase());
                userDiv.setAttribute('data-user-email', assignment.user.email.toLowerCase());

                const statusBadge = getStatusBadge(assignment.status);
                const completionInfo = assignment.status === 'completed' ?
                    `<div class="text-xs text-gray-500 mt-1">Completed: ${assignment.completed_at}</div>` : '';

                userDiv.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            ${assignment.user.profile_picture ?
                                `<img src="${assignment.user.profile_picture_url}" alt="${assignment.user.name}" class="w-10 h-10 rounded-full object-cover">` :
                                `<div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <span class="text-indigo-600 font-semibold text-sm">${assignment.user.name.charAt(0).toUpperCase()}</span>
                                </div>`
                            }
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900">${assignment.user.name}</div>
                            <div class="text-sm text-gray-500">${assignment.user.email}</div>
                            ${assignment.user.university ? `<div class="text-xs text-gray-400">${assignment.user.university.name}</div>` : ''}
                            ${completionInfo}
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        ${statusBadge}
                        ${assignment.due_date ? `<div class="text-xs text-gray-500">Due: ${assignment.due_date}</div>` : ''}
                    </div>
                `;
                assignedUsersList.appendChild(userDiv);
            });

            // Setup search functionality
            setupAssignedUsersSearch();
        })
        .catch(error => {
            console.error('Error loading assigned users:', error);
            document.getElementById('assignedUsersList').innerHTML = '<p class="text-red-600 text-center py-8">Error loading assigned users</p>';
        });
}

function getStatusBadge(status) {
    const badges = {
        'assigned': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Assigned</span>',
        'in_progress': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">In Progress</span>',
        'completed': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>',
        'cancelled': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Cancelled</span>'
    };
    return badges[status] || '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>';
}

function setupAssignedUsersSearch() {
    const searchInput = document.getElementById('assignedUserSearch');
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const userItems = document.querySelectorAll('.assigned-user-item');

        userItems.forEach(item => {
            const userName = item.getAttribute('data-user-name');
            const userEmail = item.getAttribute('data-user-email');

            if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
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

    function confirmQuizAction(action, button) {
        event.preventDefault();

        if (confirm('Are you sure you want to delete this quiz? This action cannot be undone.')) {
            button.closest('form').submit();
        }

        return false;
    }
</script>

<script>
    // Quizzes search + per-page (server-side)
    document.addEventListener('DOMContentLoaded', function () {
        const searchForm = document.getElementById('quizzes-search-form');
        const searchInput = document.getElementById('search-input');
        const clearBtn = document.getElementById('clear-search-btn');
        const perPageSelect = document.getElementById('quizzes-per-page-select');

        let t = null;
        if (searchForm && searchInput) {
            searchInput.addEventListener('input', function () {
                if (t) clearTimeout(t);
                t = setTimeout(() => searchForm.submit(), 350);
            });
        }

        if (clearBtn && searchInput && searchForm) {
            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                searchForm.submit();
            });
        }

        if (perPageSelect) {
            perPageSelect.addEventListener('change', function () {
                const url = new URL(window.location.href);
                const params = new URLSearchParams(url.search);
                params.set('per_page', this.value);
                params.delete('page');
                url.search = params.toString();
                window.location.href = url.toString();
            });
        }

        // Export quizzes to CSV with selection
        const exportBtn = document.getElementById('export-quizzes-btn');
        const exportForm = document.getElementById('export-quizzes-form');
        const selectAllCheckbox = document.getElementById('select-all-quizzes');
        const quizCheckboxes = document.querySelectorAll('.quiz-checkbox');
        const exportCountBadge = document.getElementById('export-count-badge');
        const exportBtnText = document.getElementById('export-btn-text');

        function updateExportButton() {
            const selectedQuizzes = Array.from(quizCheckboxes).filter(cb => cb.checked);
            const count = selectedQuizzes.length;
            
            if (exportBtn) {
                exportBtn.disabled = count === 0;
            }
            
            if (exportCountBadge) {
                if (count > 0) {
                    exportCountBadge.textContent = count;
                    exportCountBadge.classList.remove('hidden');
                } else {
                    exportCountBadge.classList.add('hidden');
                }
            }
        }

        // Select all functionality
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                quizCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateExportButton();
            });
        }

        // Individual checkbox change
        quizCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                updateExportButton();
                
                // Update select all checkbox state
                if (selectAllCheckbox) {
                    const allChecked = Array.from(quizCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(quizCheckboxes).some(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = someChecked && !allChecked;
                }
            });
        });

        // Export button click
        if (exportBtn && exportForm) {
            exportBtn.addEventListener('click', function () {
                const selectedQuizzes = Array.from(quizCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);
                
                if (selectedQuizzes.length === 0) {
                    alert('Please select at least one quiz to export.');
                    return;
                }
                
                document.getElementById('export-quiz-ids').value = JSON.stringify(selectedQuizzes);
                exportForm.submit();
            });
        }

        // Initial state
        updateExportButton();
    });
</script>
@endsection
