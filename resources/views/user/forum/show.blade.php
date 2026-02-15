@extends('layouts.user')

@section('page-title', 'Forum')

@php
function parseMentions($content) {
    // Parse @username mentions and make them clickable
    return preg_replace('/@(\w+)/', '<span class="text-indigo-600 font-medium hover:text-indigo-800 cursor-pointer">@$1</span>', e($content));
}
@endphp

@section('content')
<div class="h-screen flex flex-col space-y-2 overflow-hidden">
    <!-- Back Button -->
    <div class="flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <a href="{{ url('/forum') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Forum
        </a>
    </div>

    <!-- Thread Content -->
    <div class="flex-1 overflow-y-auto mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="max-w-4xl mx-auto space-y-6 pb-4">
            <!-- Thread Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                @if($forum->is_pinned)
                                    <svg class="h-5 w-5 text-yellow-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
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
                                    <span>{{ $forum->admin->name }}</span>
                                </div>
                                <span>•</span>
                                <span>{{ $forum->created_at->format('M j, Y \a\t g:i A') }}</span>
                                <span>•</span>
                                <span>{{ $forum->views_count }} views</span>
                            </div>
                        </div>
                    </div>

                    <!-- Featured Image -->
                    @if($forum->hasImage())
                    <div class="mb-6">
                        <img src="{{ $forum->image_url }}" alt="{{ $forum->title }}" class="w-full h-64 object-cover rounded-lg shadow-sm">
                    </div>
                    @endif

                    <!-- Content -->
                    <div class="prose max-w-none mb-6">
                        {!! nl2br(e($forum->content)) !!}
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <div class="flex items-center space-x-4">
                            <button onclick="toggleLike({{ $forum->id }})"
                                    class="flex items-center space-x-2 px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $isLiked ? 'text-red-600 bg-red-50' : 'text-gray-600 hover:text-red-600 hover:bg-red-50' }}">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Like ({{ $forum->likes_count }})</span>
                            </button>

                            <button onclick="toggleSave({{ $forum->id }})"
                                    class="flex items-center space-x-2 px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $isSaved ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                                <span>{{ $isSaved ? 'Saved' : 'Save' }}</span>
                            </button>

                            <button onclick="shareThread({{ $forum->id }})"
                                    class="flex items-center space-x-2 px-4 py-2 text-sm font-medium rounded-md transition-colors text-gray-600 hover:text-green-600 hover:bg-green-50">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                </svg>
                                <span>Share</span>
                            </button>
                        </div>

                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                            <span class="flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                {{ $forum->comments_count }} comments
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div id="comments" class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Comments ({{ $forum->comments_count }})</h3>

                    <!-- Add Comment Form -->
                    <div class="mb-6">
                        <form id="commentForm" class="space-y-4" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="thread_id" value="{{ $forum->id }}">
                            <div>
                                <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Add a comment</label>
                                <textarea id="comment" name="content" rows="3" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                          placeholder="Share your thoughts..."></textarea>
                                <div class="mt-1 text-xs text-gray-500">
                                    Tip: Use @username to mention other users (e.g., @{{ auth()->user()->name }}) • Press Enter to send
                                </div>
                            </div>

                            <!-- Image Upload Section -->
                            <div>
                                <label for="commentImage" class="block text-sm font-medium text-gray-700 mb-2">Add an image (optional)</label>
                                <div class="flex items-center space-x-4">
                                    <input type="file" id="commentImage" name="image" accept="image/*"
                                           class="hidden" onchange="previewCommentImage(this)">
                                    <button type="button" onclick="document.getElementById('commentImage').click()"
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Choose Image
                                    </button>
                                    <div id="commentImagePreview" class="hidden">
                                        <img id="commentImagePreviewImg" src="" alt="Preview" class="h-16 w-16 object-cover rounded-lg">
                                        <button type="button" onclick="removeCommentImage()" class="ml-2 text-red-600 hover:text-red-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    Supported formats: JPEG, PNG, JPG, GIF, WebP (max 5MB)
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Post Comment
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Comments List -->
                    <div id="commentsList" class="space-y-4">
                        @forelse($forum->topLevelComments as $comment)
                            <div class="border-l-4 border-indigo-200 pl-4" data-comment-id="{{ $comment->id }}">
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
                                        <div class="flex items-center space-x-2 mb-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</p>
                                            <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-gray-700 mb-2">{!! parseMentions($comment->content) !!}</p>
                                        @if($comment->hasImage())
                                            <div class="mt-2">
                                                <img src="{{ $comment->image_url }}" alt="Comment image" class="max-w-xs rounded-lg shadow-sm cursor-pointer" onclick="openImageModal('{{ $comment->image_url }}')">
                                            </div>
                                        @endif
                                        <div class="flex items-center space-x-4">
                                            <button onclick="toggleCommentLike({{ $comment->id }})"
                                                    class="flex items-center space-x-1 text-xs text-gray-500 hover:text-red-600 transition-colors">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span>{{ $comment->likes_count }}</span>
                                            </button>
                                            <button onclick="showReplyForm({{ $comment->id }}, '{{ $comment->user->name }}')"
                                                    class="text-xs text-gray-500 hover:text-indigo-600 transition-colors">
                                                Reply
                                            </button>
                                        </div>

                                        <!-- Reply Form (Hidden by default) -->
                                        <div id="replyForm-{{ $comment->id }}" class="mt-3 hidden">
                                            <form class="reply-form" data-parent-id="{{ $comment->id }}" data-reply-to="{{ $comment->user->name }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="thread_id" value="{{ $forum->id }}">
                                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                                <div class="mb-2">
                                                    <span class="text-xs text-gray-500">Replying to <span class="font-medium text-indigo-600">@{{ $comment->user->name }}</span></span>
                                                </div>
                                                <div class="space-y-2">
                                                    <div class="flex space-x-2">
                                                        <div class="flex-1">
                                                            <textarea name="content" rows="2" required
                                                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                                                                      placeholder="Write a reply to @{{ $comment->user->name }}..."></textarea>
                                                            <div class="mt-1 text-xs text-gray-500">
                                                                Tip: Use @username to mention other users • Press Enter to send
                                                            </div>
                                                        </div>
                                                        <button type="submit"
                                                                class="px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            Reply
                                                        </button>
                                                    </div>

                                                    <!-- Image Upload for Reply -->
                                                    <div class="flex items-center space-x-2">
                                                        <input type="file" id="replyImage-{{ $comment->id }}" name="image" accept="image/*"
                                                               class="hidden" onchange="previewReplyImage(this, {{ $comment->id }})">
                                                        <button type="button" onclick="document.getElementById('replyImage-{{ $comment->id }}').click()"
                                                                class="inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                            Add Image
                                                        </button>
                                                        <div id="replyImagePreview-{{ $comment->id }}" class="hidden flex items-center space-x-2">
                                                            <img id="replyImagePreviewImg-{{ $comment->id }}" src="" alt="Preview" class="h-8 w-8 object-cover rounded">
                                                            <button type="button" onclick="removeReplyImage({{ $comment->id }})" class="text-red-600 hover:text-red-800">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- Replies -->
                                        @if($comment->replies->count() > 0)
                                            <div class="mt-4 ml-8 space-y-3">
                                                @foreach($comment->replies as $reply)
                                                    <div class="flex items-start space-x-3" data-comment-id="{{ $reply->id }}">
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
                                                            <div class="flex items-center space-x-2 mb-1">
                                                                <p class="text-sm font-medium text-gray-900">{{ $reply->user->name }}</p>
                                                                <span class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</span>
                                                            </div>
                                                            <p class="text-sm text-gray-700 mb-2">{!! parseMentions($reply->content) !!}</p>
                                                            @if($reply->hasImage())
                                                                <div class="mt-2">
                                                                    <img src="{{ $reply->image_url }}" alt="Reply image" class="max-w-xs rounded-lg shadow-sm cursor-pointer" onclick="openImageModal('{{ $reply->image_url }}')">
                                                                </div>
                                                            @endif
                                                            <div class="flex items-center space-x-4">
                                                                <button onclick="toggleCommentLike({{ $reply->id }})"
                                                                        class="flex items-center space-x-1 text-xs text-gray-500 hover:text-red-600 transition-colors">
                                                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    <span>{{ $reply->likes_count }}</span>
                                                                </button>
                                                                <button onclick="showReplyForm({{ $reply->id }}, '{{ $reply->user->name }}')"
                                                                        class="text-xs text-gray-500 hover:text-indigo-600 transition-colors">
                                                                    Reply
                                                                </button>
                                                            </div>

                                                            <!-- Reply to Reply Form (Hidden by default) -->
                                                            <div id="replyForm-{{ $reply->id }}" class="mt-3 hidden">
                                                                <form class="reply-form" data-parent-id="{{ $reply->id }}" data-reply-to="{{ $reply->user->name }}" enctype="multipart/form-data">
                                                                    @csrf
                                                                    <input type="hidden" name="thread_id" value="{{ $forum->id }}">
                                                                    <input type="hidden" name="parent_id" value="{{ $reply->id }}">
                                                                    <div class="mb-2">
                                                                        <span class="text-xs text-gray-500">Replying to <span class="font-medium text-indigo-600">@{{ $reply->user->name }}</span></span>
                                                                    </div>
                                                                    <div class="space-y-2">
                                                                        <div class="flex space-x-2">
                                                                            <div class="flex-1">
                                                                                <textarea name="content" rows="2" required
                                                                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                                                                                          placeholder="Write a reply to @{{ $reply->user->name }}..."></textarea>
                                                                                <div class="mt-1 text-xs text-gray-500">
                                                                                    Tip: Use @username to mention other users • Press Enter to send
                                                                                </div>
                                                                            </div>
                                                                            <button type="submit"
                                                                                    class="px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                                Reply
                                                                            </button>
                                                                        </div>

                                                                        <!-- Image Upload for Reply to Reply -->
                                                                        <div class="flex items-center space-x-2">
                                                                            <input type="file" id="replyImage-{{ $reply->id }}" name="image" accept="image/*"
                                                                                   class="hidden" onchange="previewReplyImage(this, {{ $reply->id }})">
                                                                            <button type="button" onclick="document.getElementById('replyImage-{{ $reply->id }}').click()"
                                                                                    class="inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                                </svg>
                                                                                Add Image
                                                                            </button>
                                                                            <div id="replyImagePreview-{{ $reply->id }}" class="hidden flex items-center space-x-2">
                                                                                <img id="replyImagePreviewImg-{{ $reply->id }}" src="" alt="Preview" class="h-8 w-8 object-cover rounded">
                                                                                <button type="button" onclick="removeReplyImage({{ $reply->id }})" class="text-red-600 hover:text-red-800">
                                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                                    </svg>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No comments yet</h3>
                                <p class="mt-1 text-sm text-gray-500">Be the first to share your thoughts!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
// Like functionality
function toggleLike(threadId) {
    fetch('{{ url("/forum/like") }}', {
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
    fetch('{{ url("/forum/save") }}', {
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

    fetch('{{ url("/forum/share") }}', {
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
            alert('Thread shared successfully!');
        }
    })
    .catch(error => console.error('Error:', error));
});

// Comment functionality
document.getElementById('commentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('{{ url("/forum/comment") }}', {
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

// Reply functionality
function initializeReplyForms() {
    document.querySelectorAll('.reply-form').forEach(form => {
        // Remove existing event listeners to prevent duplicates
        form.removeEventListener('submit', handleReplySubmit);
        form.addEventListener('submit', handleReplySubmit);
    });
}

function handleReplySubmit(e) {
    e.preventDefault();
    console.log('Reply form submitted:', this);

    const formData = new FormData(this);
    console.log('Form data:', Object.fromEntries(formData));

    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Posting...';
    submitButton.disabled = true;

    fetch('{{ url("/forum/comment") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Reply submission response:', data);
        if (data.success) {
            // Show success message
            alert('Reply posted successfully!');
            location.reload();
        } else {
            console.error('Error submitting reply:', data.message);
            alert('Error: ' + (data.message || 'Failed to post reply'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the reply.');
    })
    .finally(() => {
        // Reset button state
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    });
}

// Initialize reply forms when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeReplyForms();
});

// Show reply form
function showReplyForm(commentId, userName) {
    console.log('showReplyForm called with commentId:', commentId, 'userName:', userName);
    const replyForm = document.getElementById('replyForm-' + commentId);

    if (!replyForm) {
        console.error('Reply form not found for commentId:', commentId);
        return;
    }

    replyForm.classList.toggle('hidden');

    // If showing the form, focus on the textarea
    if (!replyForm.classList.contains('hidden')) {
        const textarea = replyForm.querySelector('textarea[name="content"]');
        if (textarea) {
            // Auto-fill with mention if not already present
            if (!textarea.value.includes('@' + userName)) {
                textarea.value = '@' + userName + ' ';
            }
            textarea.focus();
            // Move cursor to end
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
            console.log('Reply form shown and focused for commentId:', commentId);

            // Reinitialize form event listeners
            initializeReplyForms();
        } else {
            console.error('Textarea not found in reply form for commentId:', commentId);
        }
    }
}

// Comment like functionality
function toggleCommentLike(commentId) {
    fetch('{{ url("/forum/comment/like") }}', {
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

// User mention functionality and Enter key support
document.addEventListener('DOMContentLoaded', function() {
    // Polling disabled to reduce server load
    // Start notification polling when page loads
    // startNotificationPolling();

    // Stop polling when page is unloaded
    window.addEventListener('beforeunload', stopNotificationPolling);

    // Add mention functionality to all textareas
    const textareas = document.querySelectorAll('textarea[name="content"]');

    textareas.forEach(textarea => {
        textarea.addEventListener('input', function(e) {
            const cursorPos = e.target.selectionStart;
            const text = e.target.value;

            // Check if user is typing @
            if (text[cursorPos - 1] === '@') {
                // Could add autocomplete dropdown here in the future
                console.log('User mention detected');
            }
        });

        // Handle @ key press for mentions
        textarea.addEventListener('keydown', function(e) {
            if (e.key === '@') {
                // Could trigger mention dropdown here
                console.log('@ key pressed - mention mode');
            }

            // Handle Enter key to submit (Ctrl+Enter or Cmd+Enter)
            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                const form = textarea.closest('form');
                if (form) {
                    form.dispatchEvent(new Event('submit'));
                }
            }
        });
    });
});

// Image preview functionality
function previewCommentImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('commentImagePreviewImg').src = e.target.result;
            document.getElementById('commentImagePreview').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeCommentImage() {
    document.getElementById('commentImage').value = '';
    document.getElementById('commentImagePreview').classList.add('hidden');
    document.getElementById('commentImagePreviewImg').src = '';
}

function previewReplyImage(input, commentId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('replyImagePreviewImg-' + commentId).src = e.target.result;
            document.getElementById('replyImagePreview-' + commentId).classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeReplyImage(commentId) {
    document.getElementById('replyImage-' + commentId).value = '';
    document.getElementById('replyImagePreview-' + commentId).classList.add('hidden');
    document.getElementById('replyImagePreviewImg-' + commentId).src = '';
}

// Image modal functionality
function openImageModal(imageUrl) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('imageModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'imageModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden';
        modal.innerHTML = `
            <div class="relative max-w-4xl max-h-full p-4">
                <button onclick="closeImageModal()" class="absolute top-2 right-2 text-white hover:text-gray-300 z-10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <img id="modalImage" src="" alt="Full size image" class="max-w-full max-h-full object-contain rounded-lg">
            </div>
        `;
        document.body.appendChild(modal);
    }

    document.getElementById('modalImage').src = imageUrl;
    modal.classList.remove('hidden');
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}
</script>
@endsection
