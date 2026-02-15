@extends('layouts.say-it')

@section('title', 'Say it – Confession')

@section('content')
<div class="space-y-4">
    <a href="{{ url('/Say-it') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-violet-600 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to feed
    </a>

    {{-- Post --}}
    <article class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="p-4">
            <div class="flex gap-3">
                <div class="flex flex-col items-center gap-0 flex-shrink-0 vote-buttons select-none" data-type="post" data-id="{{ $post->id }}">
                    <button type="button" class="vote-up p-1 rounded-lg text-gray-400 hover:bg-green-50 hover:text-green-600 transition" title="Upvote" aria-label="Upvote">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                    </button>
                    <span class="vote-score font-semibold text-gray-700 text-sm min-w-[1.25rem] text-center">{{ $post->upvotes_count - $post->downvotes_count }}</span>
                    <button type="button" class="vote-down p-1 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 transition" title="Downvote" aria-label="Downvote">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 text-gray-500 text-xs mb-1">
                        <span class="font-medium text-gray-700">{{ $post->codename }}</span>
                        <span>·</span>
                        <time datetime="{{ $post->created_at->toIso8601String() }}">{{ $post->created_at->diffForHumans() }}</time>
                    </div>
                    @if($post->content)
                        <p class="text-gray-900 whitespace-pre-wrap text-[15px] leading-relaxed">{{ $post->censored_content }}</p>
                    @endif
                    @if($post->image_url)
                        <div class="mt-3 rounded-xl overflow-hidden bg-gray-50 border border-gray-100">
                            <img src="{{ $post->image_url }}" alt="Post image" class="w-full max-h-[28rem] object-contain">
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </article>

    {{-- Comments --}}
    <section id="comments" class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Comments</h2>
        </div>
        <form action="{{ url('/Say-it/comment') }}" method="POST" class="p-4 border-b border-gray-100 bg-gray-50/50">
            @csrf
            <input type="hidden" name="confession_post_id" value="{{ $post->id }}">
            <div class="flex gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-violet-400 to-fuchsia-400 flex-shrink-0 flex items-center justify-center text-white text-xs font-semibold shadow-inner" aria-hidden="true">?</div>
                <div class="flex-1 min-w-0">
                    <textarea name="content" rows="2" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-gray-900 placeholder-gray-400 focus:border-violet-400 focus:ring-2 focus:ring-violet-400/20 outline-none transition resize-none text-sm" placeholder="Write a comment..." required></textarea>
                    <button type="submit" class="mt-2 px-4 py-2 bg-violet-600 text-white rounded-lg font-semibold text-sm hover:bg-violet-700 active:scale-[0.98] transition">Comment</button>
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
document.querySelectorAll('.vote-buttons').forEach(el => {
    const type = el.dataset.type;
    const id = el.dataset.id;
    const scoreEl = el.querySelector('.vote-score');
    el.querySelector('.vote-up').addEventListener('click', () => vote(type, id, 'up', scoreEl));
    el.querySelector('.vote-down').addEventListener('click', () => vote(type, id, 'down', scoreEl));
});
function vote(type, id, direction, scoreEl) {
    fetch('{{ url("/Say-it/vote") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ type, id, direction })
    }).then(r => r.json()).then(data => { if (data.score !== undefined) scoreEl.textContent = data.score; });
}
</script>
@endpush
@endsection
