@extends('layouts.say-it')

@section('title', 'Say it – Anonymous confessions')

@section('content')
<div class="space-y-4">
    {{-- Composer: "What's on your mind?" --}}
    <section class="bg-white rounded-2xl shadow-sm border border-gray-200/80 p-4">
        <form action="{{ url('/Say-it') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div class="flex gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-400 to-fuchsia-400 flex-shrink-0 flex items-center justify-center text-white text-sm font-semibold shadow-inner" aria-hidden="true">?</div>
                <div class="flex-1 min-w-0">
                    <textarea name="content" id="content" rows="3" class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-gray-900 placeholder-gray-400 focus:bg-white focus:border-violet-400 focus:ring-2 focus:ring-violet-400/20 outline-none transition resize-none" placeholder="What's on your mind? Share anonymously...">{{ old('content') }}</textarea>
                </div>
            </div>
            <div class="flex items-center justify-between gap-3 pt-1 border-t border-gray-100">
                <label class="flex items-center gap-2 text-gray-500 hover:text-violet-600 cursor-pointer text-sm font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Photo</span>
                    <input type="file" name="image" id="image" accept="image/*" class="sr-only">
                </label>
                <button type="submit" class="px-5 py-2.5 bg-violet-600 text-white rounded-xl font-semibold text-sm hover:bg-violet-700 active:scale-[0.98] transition shadow-sm">Post</button>
            </div>
        </form>
    </section>

    {{-- Feed --}}
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Recent</h2>
    </div>

    @forelse($posts as $post)
        <article class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden hover:border-gray-200 transition">
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
                                <img src="{{ $post->image_url }}" alt="Post image" class="w-full max-h-80 object-contain">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="px-4 py-2.5 bg-gray-50/80 border-t border-gray-100 flex items-center gap-4 text-sm">
                <a href="{{ url('/Say-it/' . $post->id) }}" class="flex items-center gap-1.5 text-gray-600 hover:text-violet-600 font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    {{ $post->all_comments_count ?? 0 }} {{ ($post->all_comments_count ?? 0) === 1 ? 'comment' : 'comments' }}
                </a>
                <a href="{{ url('/Say-it/' . $post->id) }}" class="ml-auto text-violet-600 hover:text-violet-700 font-semibold">View & comment →</a>
            </div>
        </article>
    @empty
        <div class="bg-white rounded-2xl border border-gray-200/80 border-dashed p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <p class="text-gray-600 font-medium">No confessions yet</p>
            <p class="text-gray-400 text-sm mt-1">Be the first to share something anonymously.</p>
        </div>
    @endforelse

    @if($posts->hasPages())
        <div class="flex justify-center pt-2">
            {{ $posts->links() }}
        </div>
    @endif
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
