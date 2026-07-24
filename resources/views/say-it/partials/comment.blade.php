@php
    $replies = $allComments->where('parent_id', $comment->id);
@endphp
<div class="comment-block {{ $comment->parent_id ? 'pl-3 sm:pl-4 ml-2 sm:ml-4 border-l-2 border-gray-100' : '' }}">
    <div class="p-3 sm:p-4 flex gap-3 hover:bg-gray-50/50 transition">
        <div class="flex flex-col items-center gap-0 flex-shrink-0 vote-buttons select-none" data-type="comment" data-id="{{ $comment->id }}">
            <button type="button" class="vote-up p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-violet-600 transition touch-manipulation" title="Upvote" aria-label="Upvote">
                <i class="fas fa-thumbs-up text-xs"></i>
            </button>
            <span class="vote-score font-medium text-gray-600 text-xs min-w-[1.25rem] text-center py-0.5">{{ $comment->upvotes_count - $comment->downvotes_count }}</span>
            <button type="button" class="vote-down p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-violet-600 transition touch-manipulation" title="Downvote" aria-label="Downvote">
                <i class="fas fa-thumbs-down text-xs"></i>
            </button>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-x-2 text-gray-500 text-xs mb-0.5">
                <span class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 {{ \App\Helpers\SayItHelper::avatarColorClassesForCodename($comment->codename) }}" title="{{ $comment->codename }}">
                    <i class="{{ \App\Helpers\SayItHelper::animalIconForCodename($comment->codename) }} text-[10px]"></i>
                </span>
                <span class="font-medium text-gray-700">{{ $comment->codename }}</span>
                <span>·</span>
                <time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->diffForHumans() }}</time>
            </div>
            <p class="text-gray-900 text-sm whitespace-pre-wrap leading-relaxed break-words">{{ $comment->censored_content }}</p>
            @if(!$comment->parent_id && empty($hasCommented))
                <form action="{{ url('/Say-it/comment') }}" method="POST" class="say-it-comment-form mt-2">
                    @csrf
                    <input type="hidden" name="confession_post_id" value="{{ $comment->confession_post_id }}">
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    <div class="flex flex-col sm:flex-row gap-2 mt-2">
                        <input type="text" name="content" placeholder="Reply to {{ $comment->codename }}..." class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm placeholder-gray-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none touch-manipulation" required>
                        <button type="submit" class="px-3 py-2 text-sm font-semibold text-violet-600 hover:text-violet-700 hover:bg-violet-50 rounded-xl transition touch-manipulation whitespace-nowrap">Reply</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
    @foreach($replies as $reply)
        @include('say-it.partials.comment', ['comment' => $reply, 'allComments' => $allComments, 'hasCommented' => $hasCommented ?? false])
    @endforeach
</div>
