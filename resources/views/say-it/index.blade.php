@extends('layouts.say-it')

@section('title', 'Say it – Anonymous confessions')

@push('styles')
<style>
/* Featured post (Most popular only): fire on the rounded border of the card */
.say-it-featured-fire-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: 1.25rem;
}

.say-it-featured-fire-inner {
}

/* WebGL fire in front of the Most popular card */
.say-it-mostpopular-fire-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 1.25rem;
    pointer-events: none;
}
.say-it-mostpopular-fire-front {
    z-index: 10;
}
.say-it-mostpopular-fire-bg canvas {
    display: block;
    width: 100% !important;
    height: 100% !important;
    border-radius: inherit;
    object-fit: cover;
}

/* Wrapper that draws the fire as a thin rounded border around the post */
.say-it-fire-border-wrap {
    position: relative;
    padding: 4px;
    border-radius: 1rem;
    background: linear-gradient(135deg, #ffaa00 0%, #ff6600 25%, #cc3300 50%, #ff6600 75%, #ffaa00 100%);
    background-size: 300% 300%;
    animation: say-it-border-glow 3s ease-in-out infinite;
    box-shadow:
        0 0 20px rgba(255, 102, 0, 0.7),
        0 0 40px rgba(255, 136, 0, 0.5),
        0 0 60px rgba(255, 80, 0, 0.3),
        inset 0 0 12px rgba(255, 150, 0, 0.2);
}

.say-it-featured-card-inner {
    border-radius: calc(1rem - 4px) !important;
}

@keyframes say-it-border-glow {
    0%, 100% {
        background-position: 0% 50%;
        box-shadow:
            0 0 20px rgba(255, 102, 0, 0.7),
            0 0 40px rgba(255, 136, 0, 0.5),
            0 0 60px rgba(255, 80, 0, 0.3),
            inset 0 0 12px rgba(255, 150, 0, 0.2);
    }
    50% {
        background-position: 100% 50%;
        box-shadow:
            0 0 32px rgba(255, 136, 0, 0.9),
            0 0 64px rgba(255, 100, 0, 0.6),
            0 0 96px rgba(255, 60, 0, 0.4),
            inset 0 0 16px rgba(255, 180, 0, 0.35);
    }
}
</style>
@endpush

@section('content')
{{-- Shaders for Most popular post WebGL fire (used only when featured post exists) --}}
<script type="x-shader/x-fragment" id="say-it-mostpopular-fragmentShader">
    varying vec2 vUv;
    uniform float u_ratio;
    uniform float u_time;
    uniform float u_speed;
    uniform float u_shape_offset;
    uniform float u_power;
    uniform float u_addition;
    vec3 permute(vec3 x) { return mod(((x*34.0)+1.0)*x, 289.0); }
    float snoise(vec2 v){
        const vec4 C = vec4(0.211324865405187, 0.366025403784439, -0.577350269189626, 0.024390243902439);
        vec2 i = floor(v + dot(v, C.yy));
        vec2 x0 = v - i + dot(i, C.xx);
        vec2 i1 = (x0.x > x0.y) ? vec2(1.0, 0.0) : vec2(0.0, 1.0);
        vec4 x12 = x0.xyxy + C.xxzz;
        x12.xy -= i1;
        i = mod(i, 289.0);
        vec3 p = permute(permute(i.y + vec3(0.0, i1.y, 1.0)) + i.x + vec3(0.0, i1.x, 1.0));
        vec3 m = max(0.5 - vec3(dot(x0, x0), dot(x12.xy, x12.xy), dot(x12.zw, x12.zw)), 0.0);
        m = m*m; m = m*m;
        vec3 x = 2.0 * fract(p * C.www) - 1.0;
        vec3 h = abs(x) - 0.5;
        vec3 ox = floor(x + 0.5);
        vec3 a0 = x - ox;
        m *= 1.79284291400159 - 0.85373472095314 * (a0*a0 + h*h);
        vec3 g;
        g.x = a0.x * x0.x + h.x * x0.y;
        g.yz = a0.yz * x12.xz + h.yz * x12.yw;
        return 130.0 * dot(m, g);
    }
    vec3 hsv2rgb(vec3 c) {
        vec4 K = vec4(1.0, 2.0 / 3.0, 1.0 / 3.0, 3.0);
        vec3 p = abs(fract(c.xxx + K.xyz) * 6.0 - K.www);
        return c.z * mix(K.xxx, clamp(p - K.xxx, 0.0, 1.0), c.y);
    }
    const float STEPS = 4.;
    float get_noise(vec2 uv, float t){
        float SCALE = 8.;
        float noise = snoise(vec2(uv.x * SCALE, uv.y * .25 * SCALE - t));
        SCALE = 10.;
        noise += .2 * snoise(vec2(uv.x * SCALE + 1.5 * t, uv.y * .3 * SCALE));
        noise = min(1., .5 * noise + u_addition);
        return noise;
    }
    void main () {
        vec2 uv = vUv;
        uv.y /= u_ratio;
        float t = u_time * u_speed;
        float noise = get_noise(uv, t);
        float shape = pow(.8 * uv.y * u_ratio, .5);
        shape += 3. * pow(abs(uv.x - .5), 2.);
        shape *= u_shape_offset;
        float stepped_noise = floor(get_noise(uv, t) * STEPS) / STEPS;
        float d = pow(stepped_noise, u_power);
        d *= (1.2 - shape);
        vec3 hsv = vec3(d * .15, .8 - .2 * d, max(0.5, d + .5 + .1 * uv.y));
        vec3 col = hsv2rgb(hsv);
        col *= smoothstep(shape, shape + .2, noise);
        vec3 minWarm = vec3(0.1, 0.05, 0.0);
        col = max(col, minWarm);
        float alpha = step(shape, noise) - .5;
        gl_FragColor = vec4(col, alpha);
    }
</script>
<script type="x-shader/x-vertex" id="say-it-mostpopular-vertexShader">
    varying vec2 vUv;
    void main() {
        vUv = uv;
        gl_Position = vec4(position, 1.);
    }
</script>

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

    {{-- Most popular post (one only, above recent) with fire border --}}
    @if(!empty($mostPopularPost) && empty($topicSlug) && empty($hashtagSlug))
        @include('say-it.partials.post-card-featured', ['post' => $mostPopularPost, 'sessionCodename' => $sessionCodename])
    @endif

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
<script type="module">
(function() {
    var container = document.getElementById('say-it-mostpopular-fire-wrap');
    var canvasEl = document.getElementById('say-it-mostpopular-fire-canvas');
    var fragmentEl = document.getElementById('say-it-mostpopular-fragmentShader');
    var vertexEl = document.getElementById('say-it-mostpopular-vertexShader');
    if (!container || !canvasEl || !fragmentEl || !vertexEl) return;

    import('https://cdn.skypack.dev/three@0.133.1/build/three.module').then(function(THREEModule) {
        var THREE = THREEModule.default || THREEModule;
        var params = { speed: 2, shape: 0.6, power: 0.6, addition: 0.6 };
        var renderer, scene, camera, clock, material;

        function initScene() {
            var w = container.clientWidth;
            var h = container.clientHeight;
            if (w < 20 || h < 20) return;

            renderer = new THREE.WebGLRenderer({ alpha: true, canvas: canvasEl });
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            renderer.setSize(w, h);
            scene = new THREE.Scene();
            camera = new THREE.OrthographicCamera(-0.5, 0.5, 0.5, -0.5, 0.1, 10);
            clock = new THREE.Clock();

            material = new THREE.ShaderMaterial({
                uniforms: {
                    u_time: { value: 0 },
                    u_ratio: { value: w / h },
                    u_speed: { value: params.speed },
                    u_shape_offset: { value: params.shape },
                    u_power: { value: params.power },
                    u_addition: { value: params.addition },
                },
                vertexShader: vertexEl.textContent,
                fragmentShader: fragmentEl.textContent,
                transparent: true
            });
            var plane = new THREE.PlaneGeometry(2, 2);
            scene.add(new THREE.Mesh(plane, material));
            requestAnimationFrame(render);
        }

        function render() {
            if (!material || !renderer) return;
            material.uniforms.u_time.value = clock.getElapsedTime();
            renderer.render(scene, camera);
            requestAnimationFrame(render);
        }

        function updateSize() {
            if (!material || !renderer) return;
            var w = container.clientWidth;
            var h = container.clientHeight;
            if (w < 1 || h < 1) return;
            material.uniforms.u_ratio.value = w / h;
            renderer.setSize(w, h);
        }

        initScene();
        window.addEventListener('resize', updateSize);
        var ro = new ResizeObserver(updateSize);
        ro.observe(container);
    }).catch(function(err) { console.warn('Say-it Most popular fire:', err); });
})();
</script>
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

// Delete post functionality with 50-second timer
(function() {
    function initDeleteButtons(container) {
        container = container || document;
        var deleteButtons = container.querySelectorAll('.delete-post-btn');
        deleteButtons.forEach(function(btn) {
            if (btn._deleteInitialized) return;
            btn._deleteInitialized = true;
            
            var postId = btn.dataset.postId;
            var createdAt = parseInt(btn.dataset.createdAt);
            var timeRemaining = parseInt(btn.dataset.timeRemaining);
            var timerEl = btn.querySelector('.delete-timer');
            var startTime = Date.now() / 1000;
            var postCreatedAt = createdAt;
            
            // Update timer every second
            var timerInterval = setInterval(function() {
                var elapsed = Math.floor((Date.now() / 1000) - startTime);
                var remaining = Math.max(0, 50 - (Math.floor(Date.now() / 1000) - postCreatedAt));
                
                if (timerEl) {
                    timerEl.textContent = remaining + 's';
                }
                
                if (remaining <= 0) {
                    clearInterval(timerInterval);
                    btn.style.display = 'none';
                }
            }, 1000);
            
            // Delete button click handler
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                    return;
                }
                
                btn.disabled = true;
                btn.style.opacity = '0.5';
                
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
                        var article = btn.closest('article');
                        if (article) {
                            article.style.transition = 'opacity 0.3s';
                            article.style.opacity = '0';
                            setTimeout(function() {
                                article.remove();
                            }, 300);
                        }
                    } else {
                        alert(data.message || 'Failed to delete post.');
                        btn.disabled = false;
                        btn.style.opacity = '1';
                    }
                })
                .catch(function() {
                    alert('An error occurred while deleting the post.');
                    btn.disabled = false;
                    btn.style.opacity = '1';
                });
            });
        });
    }
    
    // Initialize on page load
    initDeleteButtons();
    
    // Re-initialize after lazy load
    var originalAttachVoteHandlers = window.attachVoteHandlers;
    window.attachVoteHandlers = function(container) {
        if (originalAttachVoteHandlers) originalAttachVoteHandlers(container);
        initDeleteButtons(container);
    };
})();
</script>
@endpush
@endsection
