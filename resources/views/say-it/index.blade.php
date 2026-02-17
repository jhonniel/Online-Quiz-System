@extends('layouts.say-it')

@section('title', 'Say it – Anonymous confessions')

@section('content')
<div class="max-w-2xl mx-auto lg:max-w-none">
    {{-- Composer: Create post --}}
    <section id="create" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6 scroll-mt-24">
        <form id="say-it-form" action="{{ url('/Say-it') }}" method="POST" enctype="multipart/form-data" class="p-4 sm:p-5">
            @csrf
            <div class="flex gap-3">
                <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center {{ \App\Helpers\SayItHelper::avatarColorClassesForCodename('anon') }}" aria-hidden="true">
                    <i class="fas fa-paw text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <textarea name="content" id="content" rows="3" class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-[15px] text-gray-900 placeholder-gray-400 focus:bg-white focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition resize-none min-h-[88px] touch-manipulation" placeholder="What's on your mind? Share anonymously...">{{ old('content') }}</textarea>
                    <div id="image-preview-wrap" class="mt-3 rounded-xl overflow-hidden bg-gray-50 border border-gray-200 hidden">
                        <div class="relative inline-block">
                            <img id="image-preview" src="" alt="Preview" class="max-h-64 rounded-lg object-contain">
                            <button type="button" id="image-preview-remove" class="absolute top-2 right-2 w-8 h-8 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center transition" aria-label="Remove photo">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>
                    {{-- One line: Photo (first), Topic, Text size --}}
                    <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2">
                        <label class="flex items-center gap-1.5 text-gray-500 hover:text-violet-600 cursor-pointer text-xs font-medium transition-colors shrink-0 rounded-lg px-2 py-1.5 -ml-2">
                            <i class="fas fa-image text-sm"></i>
                            <span>Photo</span>
                            <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp" class="sr-only">
                        </label>
                        <div class="flex items-center gap-x-2 shrink-0">
                            <label for="topic_id" class="text-xs font-medium text-gray-500 shrink-0">Topic <span class="text-red-500">*</span></label>
                            <select name="topic_id" id="topic_id" class="rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs text-gray-800 focus:border-violet-500 focus:ring-1 focus:ring-violet-500/30 outline-none shrink-0 max-w-[130px]">
                                <option value="">Select...</option>
                                @foreach($topics ?? [] as $t)
                                    <option value="{{ $t->id }}" {{ old('topic_id') == $t->id ? 'selected' : '' }}>{{ Str::limit($t->name, 16) }} ({{ $t->posts_count }})</option>
                                @endforeach
                            </select>
                            <span class="text-xs text-gray-300 shrink-0">or</span>
                            <input type="text" name="topic_name" id="topic_name" value="{{ old('topic_name') }}" placeholder="New topic" maxlength="100" class="rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs text-gray-800 placeholder-gray-400 focus:border-violet-500 focus:ring-1 focus:ring-violet-500/30 outline-none w-24 sm:w-28 shrink-0">
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs font-medium text-gray-500 shrink-0">Text size</span>
                            <div class="inline-flex rounded-lg border border-gray-200 bg-gray-100/80 p-0.5" role="group" aria-label="Text size">
                                <input type="radio" name="text_size" value="normal" id="text_size_normal" {{ old('text_size', 'normal') === 'normal' ? 'checked' : '' }} class="sr-only">
                                <input type="radio" name="text_size" value="medium" id="text_size_medium" {{ old('text_size') === 'medium' ? 'checked' : '' }} class="sr-only">
                                <input type="radio" name="text_size" value="large" id="text_size_large" {{ old('text_size') === 'large' ? 'checked' : '' }} class="sr-only">
                                <label for="text_size_normal" class="text-size-segmented-label px-3 py-1.5 text-xs font-medium text-gray-600 rounded-md cursor-pointer transition-colors hover:text-gray-900 select-none">Normal</label>
                                <label for="text_size_medium" class="text-size-segmented-label px-3 py-1.5 text-xs font-medium text-gray-600 rounded-md cursor-pointer transition-colors hover:text-gray-900 select-none">Medium</label>
                                <label for="text_size_large" class="text-size-segmented-label px-3 py-1.5 text-xs font-medium text-gray-600 rounded-md cursor-pointer transition-colors hover:text-gray-900 select-none">Large</label>
                            </div>
                        </div>
                        @error('topic')<p class="w-full text-xs text-red-600 mt-0.5">{{ $message }}</p>@enderror
                        @error('topic_id')<p class="w-full text-xs text-red-600 mt-0.5">{{ $message }}</p>@enderror
                        @error('topic_name')<p class="w-full text-xs text-red-600 mt-0.5">{{ $message }}</p>@enderror
                        <button type="submit" class="ml-auto px-5 py-2.5 bg-violet-600 text-white rounded-full font-semibold text-sm hover:bg-violet-700 active:scale-[0.98] transition shadow-sm shrink-0 touch-manipulation">
                            Post
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </section>

    {{-- Feed header --}}
    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">
            @if(isset($topicSlug) && $topicSlug)
                <span class="text-violet-600">{{ $topics->firstWhere('slug', $topicSlug)->name ?? $topicSlug }}</span> · Trending
            @elseif(isset($hashtagSlug) && $hashtagSlug)
                <span class="text-violet-600">#{{ $hashtagSlug }}</span> · Trending
            @elseif(isset($sort) && $sort === 'popular')
                Popular
            @else
                Recent
            @endif
        </h2>
        @if(isset($topicSlug) && $topicSlug || isset($hashtagSlug) && $hashtagSlug)
            <a href="{{ url('/Say-it') }}" class="text-xs font-medium text-violet-600 hover:text-violet-700">Clear filter</a>
        @endif
    </div>

    {{-- Post cards (lazy load: 15 per page, no pagination) --}}
    <div id="posts-container" class="space-y-4">
        @forelse($posts as $post)
            @include('say-it.partials.post-card', ['post' => $post])
        @empty
            <div class="bg-white rounded-2xl border border-gray-200 border-dashed p-8 sm:p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-comments text-3xl text-gray-400"></i>
                </div>
                <p class="text-gray-600 font-medium">No posts yet</p>
                <p class="text-gray-400 text-sm mt-1">Be the first to share something anonymously.</p>
            </div>
        @endforelse
    </div>

    @if($posts->hasMorePages())
        <div id="posts-sentinel" class="py-6 flex justify-center" data-next-url="{{ $posts->nextPageUrl() }}" aria-hidden="true">
            <span class="text-sm text-gray-400">Scroll for more</span>
        </div>
        <div id="posts-loading" class="py-4 flex justify-center hidden">
            <span class="text-sm text-gray-500"><i class="fas fa-spinner fa-spin mr-1"></i> Loading...</span>
        </div>
    @endif
</div>

@push('scripts')
<script>
(function() {
    var form = document.getElementById('say-it-form');
    var content = document.getElementById('content');
    if (form && content) {
        content.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.submit();
            }
        });
    }
})();
(function() {
    var topicId = document.getElementById('topic_id');
    var topicName = document.getElementById('topic_name');
    if (topicId && topicName) {
        topicId.addEventListener('change', function() { if (this.value) topicName.value = ''; });
        topicName.addEventListener('input', function() { if (this.value.trim()) topicId.value = ''; });
    }
})();
(function() {
    var radios = document.querySelectorAll('input[name="text_size"]');
    var labels = document.querySelectorAll('.text-size-segmented-label');
    function updateTextSizeSegment() {
        var i = Array.from(radios).findIndex(function(r) { return r.checked; });
        labels.forEach(function(l, k) {
            l.classList.toggle('bg-white', k === i);
            l.classList.toggle('text-violet-700', k === i);
            l.classList.toggle('shadow-sm', k === i);
        });
    }
    radios.forEach(function(r) { r.addEventListener('change', updateTextSizeSegment); });
    updateTextSizeSegment();
})();
(function() {
    var imageInput = document.getElementById('image');
    var previewWrap = document.getElementById('image-preview-wrap');
    var previewImg = document.getElementById('image-preview');
    var removeBtn = document.getElementById('image-preview-remove');
    if (!imageInput || !previewWrap || !previewImg) return;

    imageInput.addEventListener('change', function() {
        var file = this.files && this.files[0];
        if (!file || !file.type.match(/^image\/(jpeg|png|gif|webp)$/)) {
            previewWrap.classList.add('hidden');
            previewImg.removeAttribute('src');
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewWrap.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    });

    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            imageInput.value = '';
            previewImg.removeAttribute('src');
            previewWrap.classList.add('hidden');
        });
    }
})();

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
function attachVoteHandlers(container) {
    var root = container || document;
    root.querySelectorAll('.vote-buttons').forEach(function(el) {
        if (el._voteBound) return;
        el._voteBound = true;
        var type = el.dataset.type;
        var id = el.dataset.id;
        var scoreEl = el.querySelector('.vote-score');
        if (!scoreEl) return;
        var up = el.querySelector('.vote-up');
        var down = el.querySelector('.vote-down');
        if (up) up.addEventListener('click', function() { vote(type, id, 'up', scoreEl); });
        if (down) down.addEventListener('click', function() { vote(type, id, 'down', scoreEl); });
    });
}
attachVoteHandlers();

(function() {
    var sentinel = document.getElementById('posts-sentinel');
    var container = document.getElementById('posts-container');
    var loading = document.getElementById('posts-loading');
    if (!sentinel || !container) return;
    var main = document.getElementById('say-it-main');
    var loadingMore = false;
    function loadNext() {
        var url = sentinel.getAttribute('data-next-url');
        if (!url || loadingMore) return;
        loadingMore = true;
        if (loading) loading.classList.remove('hidden');
        var lazyUrl = url + (url.indexOf('?') !== -1 ? '&' : '?') + 'lazy=1';
        fetch(lazyUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.html) {
                    var wrap = document.createElement('div');
                    wrap.innerHTML = data.html.trim();
                    while (wrap.firstChild) container.appendChild(wrap.firstChild);
                    attachVoteHandlers(container);
                }
                if (data.has_more && data.next_page_url) {
                    sentinel.setAttribute('data-next-url', data.next_page_url);
                } else {
                    sentinel.remove();
                    if (loading) loading.remove();
                }
            })
            .catch(function() {
                if (loading) loading.classList.add('hidden');
            })
            .then(function() { loadingMore = false; });
    }
    var root = main || null;
    var obs = new IntersectionObserver(function(entries) {
        if (!entries[0].isIntersecting) return;
        loadNext();
    }, { root: root, rootMargin: '200px', threshold: 0 });
    obs.observe(sentinel);
})();
</script>
@endpush
@endsection
