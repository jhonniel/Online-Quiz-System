@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col space-y-2 min-h-0">
    <!-- Search and Filter Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-2 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <!-- Search -->
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text"
                           id="search-input"
                           placeholder="Search feedback..."
                           class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-2">
                <button class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <span class="hidden sm:inline">Filter</span>
                </button>
                <a href="{{ route('user.feedback.create') }}" class="inline-flex items-center justify-center px-3 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 touch-manipulation">
                    <svg class="w-4 h-4 sm:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="hidden sm:inline">Submit Feedback</span>
                    <span class="sm:hidden">New</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-3 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Feedback & Suggestions</h1>
                <p class="text-indigo-100 text-sm">Help us improve the system by sharing your feedback</p>
            </div>
        </div>
    </div>

    <!-- Feedback Table -->
    <div class="bg-white shadow-sm border-t border-b border-gray-200 overflow-visible flex-1 flex flex-col mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        @if($userFeedbacks->count() > 0)
            <x-responsive-data-panel class="border-0 shadow-none rounded-none flex-1 flex flex-col">
                <x-slot:mobile>
                    @foreach($userFeedbacks as $feedback)
                        <div class="mobile-card">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-gray-500">#{{ str_pad($feedback->id, 4, '0', STR_PAD_LEFT) }}</p>
                                    <p class="mobile-card-title">{{ $feedback->title }}</p>
                                    <p class="mobile-card-subtitle">{{ Str::limit($feedback->description, 100) }}</p>
                                </div>
                            </div>
                            <div class="mobile-card-meta">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $feedback->type_badge_class }}">{{ $feedback->type_icon }} {{ ucfirst(str_replace('_', ' ', $feedback->type)) }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $feedback->status_badge_class }}">{{ ucfirst(str_replace('_', ' ', $feedback->status)) }}</span>
                            </div>
                            <div class="mobile-card-actions">
                                <span class="text-xs text-gray-500">{{ $feedback->created_at->format('M d, Y') }}</span>
                                <a href="{{ route('user.feedback.show', $feedback) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 touch-manipulation py-1 ml-auto">
                                    View details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </x-slot:mobile>
                <x-slot:desktop>
            <div class="overflow-x-auto overflow-y-visible flex-1 mobile-table-scroll">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden sm:inline">Feedback ID</span>
                                <span class="sm:hidden">ID</span>
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Title
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden md:inline">Type</span>
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden md:inline">Priority</span>
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden md:inline">Status</span>
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden lg:inline">Created</span>
                            </th>
                            <th scope="col" class="relative px-3 sm:px-4 lg:px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($userFeedbacks as $feedback)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-3 sm:px-4 lg:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ str_pad($feedback->id, 4, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-3 sm:px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 flex items-center">
                                        {{ Str::limit($feedback->title, 30) }}
                                        @if($feedback->hasImage())
                                            <div class="flex items-center ml-2 text-blue-600" title="Has {{ $feedback->image_count }} attached image(s)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <span class="text-xs font-medium ml-1">{{ $feedback->image_count }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500 truncate max-w-xs">{{ Str::limit($feedback->description, 50) }}</div>
                                </td>
                                <td class="px-3 sm:px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $feedback->type_badge_class }}">
                                        {{ $feedback->type_icon }} {{ ucfirst(str_replace('_', ' ', $feedback->type)) }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $feedback->priority_badge_class }}">
                                        {{ $feedback->priority_icon }} {{ ucfirst($feedback->priority) }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $feedback->status_badge_class }}">
                                        {{ ucfirst(str_replace('_', ' ', $feedback->status)) }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $feedback->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-3 sm:px-4 lg:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="relative inline-block text-left" x-data="{ open: false }">
                                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 rounded-full p-1">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                            </svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20">
                                            <div class="py-1">
                                                <a href="{{ route('user.feedback.show', $feedback) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
                </x-slot:desktop>
            </x-responsive-data-panel>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    @if($userFeedbacks->onFirstPage())
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-not-allowed">
                            Previous
                        </span>
                    @else
                        <a href="{{ $userFeedbacks->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                    @endif

                    @if($userFeedbacks->hasMorePages())
                        <a href="{{ $userFeedbacks->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </a>
                    @else
                        <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-not-allowed">
                            Next
                        </span>
                    @endif
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ $userFeedbacks->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $userFeedbacks->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $userFeedbacks->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            @if($userFeedbacks->onFirstPage())
                                <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-not-allowed">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                            @else
                                <a href="{{ $userFeedbacks->previousPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            @endif

                            @foreach($userFeedbacks->getUrlRange(1, $userFeedbacks->lastPage()) as $page => $url)
                                @if($page == $userFeedbacks->currentPage())
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-indigo-50 text-sm font-medium text-indigo-600">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            @if($userFeedbacks->hasMorePages())
                                <a href="{{ $userFeedbacks->nextPageUrl() }}" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            @else
                                <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-not-allowed">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                            @endif
                        </nav>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No feedback submitted yet</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by submitting your first feedback to help us improve the system.</p>
                <div class="mt-6">
                    <a href="{{ route('user.feedback.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Submit Feedback
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const tableRows = document.querySelectorAll('tbody tr');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            const title = row.querySelector('td:nth-child(2) .text-sm.font-medium').textContent.toLowerCase();
            const description = row.querySelector('td:nth-child(2) .text-sm.text-gray-500').textContent.toLowerCase();
            const type = row.querySelector('td:nth-child(3) span').textContent.toLowerCase();
            const priority = row.querySelector('td:nth-child(4) span').textContent.toLowerCase();
            const status = row.querySelector('td:nth-child(5) span').textContent.toLowerCase();

            if (title.includes(searchTerm) || description.includes(searchTerm) ||
                type.includes(searchTerm) || priority.includes(searchTerm) || status.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>
