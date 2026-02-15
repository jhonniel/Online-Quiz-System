@php
    $replies = $allComments->where('parent_id', $comment->id);
@endphp
<div class="comment-block {{ $comment->parent_id ? 'pl-4 ml-4 border-l-2 border-gray-100' : '' }}">
    <div class="p-4 flex gap-3 hover:bg-gray-50/50 transition">
        <div class="flex flex-col items-center gap-0 flex-shrink-0 vote-buttons select-none" data-type="comment" data-id="{{ $comment->id }}">
            <button type="button" class="vote-up p-0.5 rounded text-gray-400 hover:bg-green-50 hover:text-green-600 transition" title="Upvote" aria-label="Upvote">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
            </button>
            <span class="vote-score font-medium text-gray-600 text-xs min-w-[1rem] text-center">{{ $comment->upvotes_count - $comment->downvotes_count }}</span>
            <button type="button" class="vote-down p-0.5 rounded text-gray-400 hover:bg-red-50 hover:text-red-600 transition" title="Downvote" aria-label="Downvote">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 text-gray-500 text-xs mb-0.5">
                <span class="font-medium text-gray-700">{{ $comment->codename }}</span>
                <span>·</span>
                <time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->diffForHumans() }}</time>
            </div>
            <p class="text-gray-900 text-sm whitespace-pre-wrap leading-relaxed">{{ $comment->censored_content }}</p>
            @if(!$comment->parent_id)
                <form action="{{ url('/Say-it/comment') }}" method="POST" class="mt-2">
                    @csrf
                    <input type="hidden" name="confession_post_id" value="{{ $comment->confession_post_id }}">
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    <div class="flex gap-2 mt-1">
                        <input type="text" name="content" placeholder="Reply to {{ $comment->codename }}..." class="flex-1 rounded-lg border border-gray-200 px-3 py-1.5 text-sm placeholder-gray-400 focus:border-violet-400 focus:ring-1 focus:ring-violet-400/20 outline-none" required>
                        <button type="submit" class="px-3 py-1.5 text-sm font-semibold text-violet-600 hover:text-violet-700 hover:bg-violet-50 rounded-lg transition">Reply</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
    @foreach($replies as $reply)
        @include('say-it.partials.comment', ['comment' => $reply, 'allComments' => $allComments])
    @endforeach
</div>
