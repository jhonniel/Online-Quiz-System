@extends('layouts.user')

@section('page-title', 'Forum')

@section('content')
<x-user-page-shell fluid class="user-portal-viewport-pane">
    <x-user-page-header title="Forum" subtitle="Discover discussions and share your thoughts" class="shrink-0">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
            </svg>
        </x-slot:icon>
        <x-slot:actions>
            <div class="rounded-lg bg-white/15 px-3 py-2 text-center ring-1 ring-white/20">
                <div class="text-xl font-bold text-white leading-none">{{ $threads->total() }}</div>
                <div class="text-[11px] font-medium text-indigo-100">Threads</div>
            </div>
            <x-user-header-btn-primary href="{{ url('/forum/saved') }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                Saved
            </x-user-header-btn-primary>
        </x-slot:actions>
    </x-user-page-header>

    <div class="shrink-0 rounded-xl border border-slate-200 bg-white p-3 shadow-sm ring-1 ring-slate-900/5">
        <div class="flex flex-col gap-2 sm:flex-row">
            <div class="flex-1">
                <label for="search-input" class="sr-only">Search forum threads</label>
                <input type="text" id="search-input" placeholder="Search forum threads..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
            </div>
            <button type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
            </button>
        </div>
    </div>

    <!-- Forum Threads -->
    <div class="min-h-0 flex-1 overflow-y-auto">
        <div class="space-y-4 pb-4">
            @forelse($threads as $thread)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2 mb-2">
                                @if($thread->is_pinned)
                                    <svg class="h-5 w-5 text-yellow-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2L3 7v11h4v-6h6v6h4V7l-7-5z"></path>
                                    </svg>
                                @endif
                                <h3 class="text-lg font-semibold text-gray-900 truncate">
                                    <a href="{{ url('/forum/' . $thread->id) }}" class="hover:text-indigo-600 transition-colors">
                                        {{ $thread->title }}
                                    </a>
                                </h3>
                            </div>

                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                {{ Str::limit($thread->content, 150) }}
                            </p>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <div class="flex items-center">
                                        @if($thread->admin->profile_picture)
                                            <img class="h-5 w-5 rounded-full object-cover mr-2" src="{{ $thread->admin->getProfilePictureUrl() }}" alt="{{ $thread->admin->name }}">
                                        @else
                                            <div class="h-5 w-5 bg-indigo-100 rounded-full flex items-center justify-center mr-2">
                                                <span class="text-indigo-600 font-semibold text-xs">{{ $thread->admin->getInitials() }}</span>
                                            </div>
                                        @endif
                                        <span><x-user-name :user="$thread->admin" :size="14" /></span>
                                    </div>
                                    <span>•</span>
                                    <span>{{ $thread->created_at->diffForHumans() }}</span>
                                    <span>•</span>
                                    <span>{{ $thread->views_count }} views</span>
                                </div>

                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <span class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $thread->likes_count }}
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                        {{ $thread->comments_count }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if($thread->hasImage())
                        <div class="ml-4 flex-shrink-0">
                            <img src="{{ $thread->image_url }}" alt="{{ $thread->title }}" class="h-20 w-20 object-cover rounded-lg">
                        </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <button onclick="toggleLike({{ $thread->id }})"
                                    class="flex items-center space-x-1 px-3 py-2 text-sm font-medium rounded-md transition-colors {{ in_array($thread->id, $likedThreadIds) ? 'text-red-600 bg-red-50' : 'text-gray-600 hover:text-red-600 hover:bg-red-50' }}">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Like</span>
                            </button>

                            <button onclick="toggleSave({{ $thread->id }})"
                                    class="flex items-center space-x-1 px-3 py-2 text-sm font-medium rounded-md transition-colors {{ in_array($thread->id, $savedThreadIds) ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }}">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                                <span>Save</span>
                            </button>

                            <button onclick="shareThread({{ $thread->id }})"
                                    class="flex items-center space-x-1 px-3 py-2 text-sm font-medium rounded-md transition-colors text-gray-600 hover:text-green-600 hover:bg-green-50">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                </svg>
                                <span>Share</span>
                            </button>

                            <a href="{{ url('/forum/' . $thread->id) }}#comments"
                               class="flex items-center space-x-1 px-3 py-2 text-sm font-medium rounded-md transition-colors text-gray-600 hover:text-indigo-600 hover:bg-indigo-50">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <span>Comment</span>
                            </a>
                        </div>

                        <a href="{{ url('/forum/' . $thread->id) }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-600 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Read More
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No forum threads yet</h3>
                <p class="text-gray-500">Check back later for new discussions and updates.</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($threads->hasPages())
        <div class="mt-6">
            {{ $threads->links() }}
        </div>
        @endif
    </div>
</x-user-page-shell>

<!-- Share Modal -->
<div id="shareModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Share Thread</h3>
            <form id="shareForm">
                @csrf
                <input type="hidden" id="shareThreadId" name="thread_id">
                <div class="mb-4">
                    <label for="shareMessage" class="block text-sm font-medium text-gray-700 mb-2">Add a message (optional)</label>
                    <textarea id="shareMessage" name="message" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="Share your thoughts about this thread..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeShareModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Share
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('search-input').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const threads = document.querySelectorAll('.space-y-4 > div');

    threads.forEach(thread => {
        const title = thread.querySelector('h3 a').textContent.toLowerCase();
        const content = thread.querySelector('p').textContent.toLowerCase();

        if (title.includes(searchTerm) || content.includes(searchTerm)) {
            thread.style.display = '';
        } else {
            thread.style.display = 'none';
        }
    });
});

// Like functionality
function toggleLike(threadId) {
    fetch('/forum/like', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ thread_id: threadId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Save functionality
function toggleSave(threadId) {
    fetch('/forum/save', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ thread_id: threadId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Share functionality
function shareThread(threadId) {
    document.getElementById('shareThreadId').value = threadId;
    document.getElementById('shareModal').classList.remove('hidden');
}

function closeShareModal() {
    document.getElementById('shareModal').classList.add('hidden');
    document.getElementById('shareForm').reset();
}

document.getElementById('shareForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/forum/share', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeShareModal();
            // Show success message
            alert('Thread shared successfully!');
        }
    })
    .catch(error => console.error('Error:', error));
});

// Real-time notification polling
let notificationPollingInterval;

function startNotificationPolling() {
    // Poll for new notifications every 10 seconds
    notificationPollingInterval = setInterval(function() {
        fetch('{{ url("/notifications/unread-count") }}')
            .then(response => response.json())
            .then(data => {
                // Update notification bell count if it exists
                const bellCount = document.querySelector('.notification-bell-count');
                if (bellCount) {
                    if (data.unread_count > 0) {
                        bellCount.textContent = data.unread_count;
                        bellCount.classList.remove('hidden');
                    } else {
                        bellCount.classList.add('hidden');
                    }
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }, 10000); // Poll every 10 seconds
}

function stopNotificationPolling() {
    if (notificationPollingInterval) {
        clearInterval(notificationPollingInterval);
    }
}

// Polling disabled to reduce server load
// Start notification polling when page loads
document.addEventListener('DOMContentLoaded', function() {
    // startNotificationPolling();

    // Stop polling when page is unloaded
    // window.addEventListener('beforeunload', stopNotificationPolling);
});
</script>
@endsection
