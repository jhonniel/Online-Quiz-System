@extends('layouts.say-it')

@section('title', 'Say it – Post')

@section('content')
<div class="max-w-2xl mx-auto lg:max-w-none">
    <a href="{{ url('/Say-it') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-violet-600 transition mb-4 min-h-[44px] items-center touch-manipulation">
        <i class="fas fa-arrow-left"></i>
        Back to feed
    </a>

    {{-- Post card --}}
    <article class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="p-4 sm:p-5">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mb-1.5">
                <span class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ \App\Helpers\SayItHelper::avatarColorClassesForCodename($post->codename) }}" title="{{ $post->codename }}">
                    <i class="{{ \App\Helpers\SayItHelper::animalIconForCodename($post->codename) }} text-sm"></i>
                </span>
                <span class="font-medium text-gray-800 text-sm">{{ $post->codename }}</span>
                <span class="text-gray-400">·</span>
                <time datetime="{{ $post->created_at->toIso8601String() }}" class="text-gray-500 text-xs">{{ $post->created_at->diffForHumans() }}</time>
                @if($post->relationLoaded('topic') && $post->topic)
                    <span class="text-gray-400">·</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-700">{{ $post->topic->name }}</span>
                @endif
            </div>
            @if($post->content)
                <p class="text-gray-900 whitespace-pre-wrap leading-relaxed break-words {{ $post->text_size_class }}">{{ $post->censored_content }}</p>
            @endif
            @if($post->image_url)
                <div class="mt-3 rounded-xl overflow-hidden bg-gray-50 border border-gray-100">
                    <img src="{{ $post->image_url }}" alt="Post image" class="w-full max-h-[28rem] object-contain">
                </div>
            @endif
        </div>
        {{-- Vote pill + comment pill (compact, slightly larger icons) --}}
        <div class="px-4 sm:px-5 py-2 border-t border-gray-100 flex flex-wrap items-center gap-1.5">
            <div class="inline-flex items-center rounded-full border border-gray-200 bg-gray-100 pl-2 pr-2 py-1 gap-1.5 vote-buttons select-none" data-type="post" data-id="{{ $post->id }}">
                <button type="button" class="vote-up p-0.5 text-gray-600 hover:text-violet-600 hover:bg-gray-200/80 rounded-full transition touch-manipulation" title="Upvote" aria-label="Upvote">
                    <i class="fas fa-thumbs-up text-sm"></i>
                </button>
                <span class="vote-score font-semibold text-gray-800 text-xs tabular-nums min-w-[1.5rem] text-center" data-raw="{{ $post->upvotes_count - $post->downvotes_count }}">{{ \App\Helpers\SayItHelper::formatCount($post->upvotes_count - $post->downvotes_count) }}</span>
                <button type="button" class="vote-down p-0.5 text-gray-600 hover:text-violet-600 hover:bg-gray-200/80 rounded-full transition touch-manipulation" title="Downvote" aria-label="Downvote">
                    <i class="fas fa-thumbs-down text-sm"></i>
                </button>
            </div>
            <div class="inline-flex items-center rounded-full border border-gray-200 bg-gray-100 pl-2 pr-2 py-1 gap-1.5 text-gray-800">
                <i class="far fa-comment text-sm text-gray-600"></i>
                <span class="font-semibold text-xs tabular-nums">{{ \App\Helpers\SayItHelper::formatCount($post->all_comments_count ?? 0) }}</span>
            </div>
            @php
                $sessionCodename = $sessionCodename ?? session('sayit_codename');
                $canDelete = $sessionCodename && $post->codename === $sessionCodename;
                $createdAt = $post->created_at;
                $secondsSinceCreation = now()->diffInSeconds($createdAt);
                $canDeleteWithinTime = $secondsSinceCreation <= 50;
                $timeRemaining = max(0, 50 - $secondsSinceCreation);
            @endphp
            @if($canDelete && $canDeleteWithinTime)
                <button type="button" 
                        class="delete-post-btn inline-flex items-center rounded-full border border-red-200 bg-red-50 pl-2 pr-2 py-1 gap-1.5 text-red-600 hover:bg-red-100 hover:border-red-300 transition touch-manipulation" 
                        data-post-id="{{ $post->id }}"
                        data-created-at="{{ $createdAt->timestamp }}"
                        data-time-remaining="{{ $timeRemaining }}"
                        title="Delete post ({{ $timeRemaining }}s remaining)"
                        aria-label="Delete post">
                    <i class="fas fa-trash text-sm"></i>
                    <span class="delete-timer font-semibold text-xs tabular-nums">{{ $timeRemaining }}s</span>
                </button>
            @endif
        </div>
    </article>

    {{-- Comments --}}
    <section id="comments" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Comments</h2>
        </div>
        <form action="{{ url('/Say-it/comment') }}" method="POST" class="p-4 sm:p-5 border-b border-gray-100 bg-gray-50/50">
            @csrf
            <input type="hidden" name="confession_post_id" value="{{ $post->id }}">
            <div class="flex gap-3">
                <div class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center shadow-sm {{ \App\Helpers\SayItHelper::avatarColorClassesForCodename('anon') }}" aria-hidden="true">
                    <i class="fas fa-paw text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <textarea name="content" rows="2" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-base sm:text-sm text-gray-900 placeholder-gray-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition resize-none touch-manipulation" placeholder="Write a comment..." required></textarea>
                    <button type="submit" class="mt-2 px-4 py-2.5 bg-violet-600 text-white rounded-full font-semibold text-sm hover:bg-violet-700 active:scale-[0.98] transition min-h-[44px] touch-manipulation">
                        Comment
                    </button>
                </div>
            </div>
        </form>

        <div class="divide-y divide-gray-100">
            @foreach($allComments->whereNull('parent_id') as $comment)
                @include('say-it.partials.comment', ['comment' => $comment, 'allComments' => $allComments])
            @endforeach
        </div>
        @if($allComments->whereNull('parent_id')->isEmpty())
            <p class="p-6 text-center text-gray-400 text-sm">No comments yet. Be the first to reply.</p>
        @endif
    </section>
</div>

@push('scripts')
<script>
document.querySelectorAll('.vote-buttons').forEach(function(el) {
    var type = el.dataset.type;
    var id = el.dataset.id;
    var scoreEl = el.querySelector('.vote-score');
    el.querySelector('.vote-up').addEventListener('click', function() { vote(type, id, 'up', scoreEl); });
    el.querySelector('.vote-down').addEventListener('click', function() { vote(type, id, 'down', scoreEl); });
});
function formatScore(n) {
    n = parseInt(n, 10);
    if (n >= 1000) return (n / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
    return String(n);
}
function vote(type, id, direction, scoreEl) {
    fetch('{{ url("/Say-it/vote") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ type: type, id: parseInt(id, 10), direction: direction })
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.score !== undefined) {
            scoreEl.textContent = formatScore(data.score);
            if (scoreEl.dataset) scoreEl.dataset.raw = data.score;
        }
    });
}

// Delete post functionality with 50-second timer
(function() {
    var deleteBtn = document.querySelector('.delete-post-btn');
    if (!deleteBtn) return;
    
    var postId = deleteBtn.dataset.postId;
    var createdAt = parseInt(deleteBtn.dataset.createdAt);
    var timerEl = deleteBtn.querySelector('.delete-timer');
    var postCreatedAt = createdAt;
    
    // Update timer every second
    var timerInterval = setInterval(function() {
        var remaining = Math.max(0, 50 - (Math.floor(Date.now() / 1000) - postCreatedAt));
        
        if (timerEl) {
            timerEl.textContent = remaining + 's';
        }
        
        if (remaining <= 0) {
            clearInterval(timerInterval);
            deleteBtn.style.display = 'none';
        }
    }, 1000);
    
    // Delete button click handler
    deleteBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
            return;
        }
        
        deleteBtn.disabled = true;
        deleteBtn.style.opacity = '0.5';
        
        fetch('{{ url("/Say-it/post") }}/' + postId, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.href = '{{ url("/Say-it") }}';
            } else {
                alert(data.message || 'Failed to delete post.');
                deleteBtn.disabled = false;
                deleteBtn.style.opacity = '1';
            }
        })
        .catch(function() {
            alert('An error occurred while deleting the post.');
            deleteBtn.disabled = false;
            deleteBtn.style.opacity = '1';
        });
    });
})();
</script>
@endpush
@endsection
