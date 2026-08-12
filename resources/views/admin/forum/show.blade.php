@extends('layouts.admin')

@section('page-title', 'View Forum Thread')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <a href="{{ route('admin.forum.index') }}" class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700">Forum Management</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">View Thread</span>
        </div>
    </li>
@endsection

@section('content')
<div class="h-screen flex flex-col space-y-2 overflow-hidden">
    <div class="flex-1 overflow-y-auto">
        <div class="max-w-4xl mx-auto space-y-6 pb-4">
    <!-- Thread Header -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                        @if($forum->is_pinned)
                            <svg class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2L3 7v11h4v-6h6v6h4V7l-7-5z"></path>
                            </svg>
                        @endif
                        <h1 class="text-2xl font-bold text-gray-900">{{ $forum->title }}</h1>
                    </div>

                    <div class="flex items-center space-x-4 text-sm text-gray-500 mb-4">
                        <div class="flex items-center">
                            @if($forum->admin->profile_picture)
                                <img class="h-6 w-6 rounded-full object-cover mr-2" src="{{ $forum->admin->getProfilePictureUrl() }}" alt="{{ $forum->admin->name }}">
                            @else
                                <div class="h-6 w-6 bg-indigo-100 rounded-full flex items-center justify-center mr-2">
                                    <span class="text-indigo-600 font-semibold text-xs">{{ $forum->admin->getInitials() }}</span>
                                </div>
                            @endif
                            <span>By <x-user-name :user="$forum->admin" :size="14" class="inline" /></span>
                        </div>
                        <span>•</span>
                        <span>{{ $forum->created_at->format('M j, Y \a\t g:i A') }}</span>
                        <span>•</span>
                        <span>{{ $forum->views_count }} views</span>
                    </div>

                    <!-- Status Badges -->
                    <div class="flex items-center space-x-2 mb-4">
                        @if($forum->is_published)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Published
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Draft
                            </span>
                        @endif
                        @if($forum->is_pinned)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Pinned
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.forum.edit', $forum) }}"
                       class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                    <a href="{{ route('admin.forum.index') }}"
                       class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back
                    </a>
                </div>
            </div>

            <!-- Featured Image -->
            @if($forum->hasImage())
            <div class="mb-6">
                <img src="{{ $forum->image_url }}" alt="{{ $forum->title }}" class="w-full h-64 object-cover rounded-lg shadow-sm">
            </div>
            @endif

            <!-- Content -->
            <div class="prose max-w-none">
                {!! nl2br(e($forum->content)) !!}
            </div>
        </div>
    </div>

    <!-- Thread Stats -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Thread Statistics</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-red-50 rounded-lg">
                    <div class="text-3xl font-bold text-red-600">{{ $forum->likes_count }}</div>
                    <div class="text-sm text-red-600 font-medium">Likes</div>
                </div>
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-3xl font-bold text-blue-600">{{ $forum->comments_count }}</div>
                    <div class="text-sm text-blue-600 font-medium">Comments</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-3xl font-bold text-gray-600">{{ $forum->views_count }}</div>
                    <div class="text-sm text-gray-600 font-medium">Views</div>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-3xl font-bold text-green-600">{{ $forum->saves_count }}</div>
                    <div class="text-sm text-green-600 font-medium">Saves</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Comments ({{ $forum->comments_count }})</h3>

            <!-- Add Comment Form -->
            <div class="mb-6">
                <form id="adminCommentForm" class="space-y-4">
                    @csrf
                    <input type="hidden" name="thread_id" value="{{ $forum->id }}">
                    <div>
                        <label for="adminComment" class="block text-sm font-medium text-gray-700 mb-2">Add a comment as admin</label>
                        <textarea id="adminComment" name="content" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                  placeholder="Share your thoughts as an admin..."></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Post Comment
                        </button>
                    </div>
                </form>
            </div>

            @if($forum->topLevelComments->count() > 0)
                <div class="space-y-4">
                    @foreach($forum->topLevelComments as $comment)
                        <div class="border-l-4 border-indigo-200 pl-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    @if($comment->user->profile_picture)
                                        <img class="h-8 w-8 rounded-full object-cover" src="{{ $comment->user->getProfilePictureUrl() }}" alt="{{ $comment->user->name }}">
                                    @else
                                        <div class="h-8 w-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <span class="text-indigo-600 font-semibold text-xs">{{ $comment->user->getInitials() }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <p class="text-sm font-medium text-gray-900"><x-user-name :user="$comment->user" /></p>
                                        <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 mt-1">{{ $comment->content }}</p>
                                    <div class="flex items-center space-x-4 mt-2">
                                        <button onclick="toggleCommentLike({{ $comment->id }})"
                                                class="flex items-center space-x-1 text-xs text-gray-500 hover:text-red-600 transition-colors">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span>{{ $comment->likes_count }}</span>
                                        </button>
                                        <button onclick="showReplyForm({{ $comment->id }})"
                                                class="text-xs text-gray-500 hover:text-indigo-600 transition-colors">
                                            Reply
                                        </button>
                                        @if($comment->replies_count > 0)
                                            <span class="text-xs text-gray-500">{{ $comment->replies_count }} replies</span>
                                        @endif
                                    </div>

                                    <!-- Reply Form (Hidden by default) -->
                                    <div id="replyForm-{{ $comment->id }}" class="mt-3 hidden">
                                        <form class="admin-reply-form" data-parent-id="{{ $comment->id }}">
                                            @csrf
                                            <input type="hidden" name="thread_id" value="{{ $forum->id }}">
                                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                            <div class="flex space-x-2">
                                                <textarea name="content" rows="2" required
                                                          class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                                                          placeholder="Write a reply as admin..."></textarea>
                                                <button type="submit"
                                                        class="px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    Reply
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Replies -->
                            @if($comment->replies->count() > 0)
                                <div class="mt-4 ml-8 space-y-3">
                                    @foreach($comment->replies as $reply)
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                @if($reply->user->profile_picture)
                                                    <img class="h-6 w-6 rounded-full object-cover" src="{{ $reply->user->getProfilePictureUrl() }}" alt="{{ $reply->user->name }}">
                                                @else
                                                    <div class="h-6 w-6 bg-gray-100 rounded-full flex items-center justify-center">
                                                        <span class="text-gray-600 font-semibold text-xs">{{ $reply->user->getInitials() }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center space-x-2">
                                                    <p class="text-sm font-medium text-gray-900"><x-user-name :user="$reply->user" /></p>
                                                    <span class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-sm text-gray-700 mt-1">{{ $reply->content }}</p>
                                                <div class="flex items-center space-x-4 mt-2">
                                                    <button onclick="toggleCommentLike({{ $reply->id }})"
                                                            class="flex items-center space-x-1 text-xs text-gray-500 hover:text-red-600 transition-colors">
                                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        <span>{{ $reply->likes_count }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No comments yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Be the first to comment on this thread.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Admin Comment functionality
document.getElementById('adminCommentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('{{ route("admin.forum.comment") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
});

// Admin Reply functionality
document.querySelectorAll('.admin-reply-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('{{ route("admin.forum.comment") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

// Show reply form
function showReplyForm(commentId) {
    const replyForm = document.getElementById('replyForm-' + commentId);
    replyForm.classList.toggle('hidden');
}

// Comment like functionality
function toggleCommentLike(commentId) {
    fetch('{{ route("admin.forum.comment.like") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ comment_id: commentId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
        </div>
    </div>
</div>
@endsection
