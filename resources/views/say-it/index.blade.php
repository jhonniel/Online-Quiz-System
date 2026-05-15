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

<div class="max-w-2xl mx-auto lg:max-w-none w-full min-w-0">
    {{-- Composer: Create post --}}
    <section id="create" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6 scroll-mt-24">
        <form id="say-it-form" action="{{ url('/Say-it') }}" method="POST" enctype="multipart/form-data" class="p-3 sm:p-5 max-w-full min-w-0">
            @csrf
            <div class="flex gap-2 sm:gap-3 min-w-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex-shrink-0 flex items-center justify-center {{ \App\Helpers\SayItHelper::avatarColorClassesForCodename('anon') }}" aria-hidden="true">
                    <i class="fas fa-paw text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    @php
                        $sayItCardBgOptions = \App\Helpers\SayItHelper::cardBackgroundFormOptions();
                        $sayItDefaultCardBg = $sayItCardBgOptions[0]['key'] ?? 'white';
                        $sayItComposerMeshInline = old('card_background_mode', 'pick') === 'random' ? \App\Helpers\SayItHelper::randomMeshBackgroundStyle() : '';
                    @endphp
                    <textarea
                        name="content"
                        id="content"
                        rows="3"
                        class="w-full max-w-full rounded-xl border border-gray-200 px-3 sm:px-4 py-2.5 sm:py-3 text-base sm:text-[15px] text-gray-900 placeholder-gray-600/85 placeholder:text-[10px] placeholder:leading-snug sm:placeholder:text-xs sm:placeholder:leading-normal focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition-[background,background-color,background-image] duration-200 resize-none min-h-[76px] sm:min-h-[88px] touch-manipulation bg-white"
                        @if($sayItComposerMeshInline !== '') style="{!! $sayItComposerMeshInline !!}" @endif
                        placeholder="What's on your mind? Anonymous — with a photo, this text is your AI image prompt."
                    >{{ old('content') }}</textarea>
                    <div id="image-preview-wrap" class="mt-3 rounded-xl overflow-hidden bg-gray-50 border border-gray-200 hidden max-w-full">
                        <div class="relative inline-block max-w-full">
                            <img id="image-preview" src="" alt="Preview" class="max-h-64 max-w-full rounded-lg object-contain">
                            <button type="button" id="image-preview-remove" class="absolute top-2 right-2 w-8 h-8 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center transition" aria-label="Remove photo">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <textarea name="ai_generated_image_dataurl" id="ai_generated_image_dataurl" class="sr-only" rows="1" autocomplete="off">{{ old('ai_generated_image_dataurl') }}</textarea>
                    @error('ai_generated_image_dataurl')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    <script type="application/json" id="say-it-bg-class-map">{!! json_encode(collect($sayItCardBgOptions)->map(fn ($o) => ['key' => $o['key'], 'classes' => $o['classes']])->values()) !!}</script>
                    {{-- Composer options: Post background left; Topic + Text size right on lg --}}
                    <div class="mt-3 w-full min-w-0 space-y-3">
                        <div class="flex flex-col gap-4 min-w-0 lg:flex-row lg:items-start lg:justify-between lg:gap-8">
                            <div class="min-w-0 space-y-2 lg:flex-none lg:max-w-fit lg:self-start">
                                <p class="text-xs font-medium text-gray-500">Post background</p>
                                <div class="flex flex-wrap gap-x-4 gap-y-2 items-center w-full">
                                    <label class="inline-flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer select-none touch-manipulation">
                                        <input type="radio" name="card_background_mode" value="random" id="card_bg_mode_random" class="rounded-full border-gray-300 text-violet-600 focus:ring-violet-500 shrink-0" {{ old('card_background_mode', 'pick') === 'random' ? 'checked' : '' }}>
                                        <span>Gradient</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer select-none touch-manipulation">
                                        <input type="radio" name="card_background_mode" value="pick" id="card_bg_mode_pick" class="rounded-full border-gray-300 text-violet-600 focus:ring-violet-500 shrink-0" {{ old('card_background_mode', 'pick') === 'pick' ? 'checked' : '' }}>
                                        <span>Pick a color</span>
                                    </label>
                                </div>
                                <input type="hidden" name="card_background" id="card_background" value="{{ old('card_background', $sayItDefaultCardBg) }}">
                                <div id="card-bg-swatches" class="grid w-fit max-w-full grid-cols-5 sm:grid-cols-6 lg:grid-cols-11 gap-2 justify-items-start content-start {{ old('card_background_mode', 'pick') === 'pick' ? '' : 'hidden' }}" role="group" aria-label="Background colors">
                                    @foreach($sayItCardBgOptions as $opt)
                                        <button type="button" class="card-bg-swatch h-9 w-9 shrink-0 rounded-lg border-2 border-transparent ring-offset-0 transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-violet-500 {{ $opt['classes'] }} {{ ($opt['key'] ?? '') === 'white' ? 'border-gray-200' : '' }}" data-card-bg-key="{{ $opt['key'] }}" data-card-bg-classes="{{ $opt['classes'] }}" title="{{ $opt['label'] }}" aria-label="{{ $opt['label'] }}"></button>
                                    @endforeach
                                </div>
                                <div class="pt-1">
                                    <label for="image" class="inline-flex items-center gap-2 cursor-pointer touch-manipulation rounded-lg border border-violet-200 bg-violet-50 px-3 py-2 text-xs font-semibold text-violet-800 shadow-sm transition hover:bg-violet-100 hover:border-violet-300 focus-within:ring-2 focus-within:ring-violet-400 focus-within:ring-offset-1">
                                        <i class="fas fa-image text-sm shrink-0 text-violet-600" aria-hidden="true"></i>
                                        <span>Photo</span>
                                        <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp" class="sr-only">
                                    </label>
                                </div>
                                @error('card_background_mode')<p class="w-full text-xs text-red-600">{{ $message }}</p>@enderror
                                @error('card_background')<p class="w-full text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="flex flex-col gap-3 min-w-0 w-full lg:w-auto lg:max-w-sm lg:flex-none lg:items-start lg:pt-0.5">
                                <div class="flex flex-col gap-1.5 min-w-0 w-full lg:max-w-full">
                                    <label for="topic_id" class="text-xs font-medium text-gray-500">Topic <span class="text-red-500">*</span></label>
                                    <div class="flex flex-wrap items-center gap-2 min-w-0 w-full">
                                        <select name="topic_id" id="topic_id" class="rounded-lg border border-gray-200 bg-white px-2.5 py-2 sm:py-1.5 text-xs text-gray-800 focus:border-violet-500 focus:ring-1 focus:ring-violet-500/30 outline-none min-w-0 w-full sm:w-[11rem] sm:shrink-0 max-w-full touch-manipulation">
                                            <option value="">Select...</option>
                                            @foreach($topics ?? [] as $t)
                                                <option value="{{ $t->id }}" {{ old('topic_id') == $t->id ? 'selected' : '' }}>{{ Str::limit($t->name, 16) }} ({{ $t->posts_count }})</option>
                                            @endforeach
                                        </select>
                                        <span class="text-xs text-gray-400 shrink-0">or</span>
                                        <input type="text" name="topic_name" id="topic_name" value="{{ old('topic_name') }}" placeholder="New topic" maxlength="100" class="rounded-lg border border-gray-200 bg-white px-2.5 py-2 sm:py-1.5 text-xs text-gray-800 placeholder-gray-400 focus:border-violet-500 focus:ring-1 focus:ring-violet-500/30 outline-none min-w-0 w-full sm:w-36 sm:shrink-0 max-w-full touch-manipulation">
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1.5 min-w-0 w-full lg:max-w-full">
                                    <span class="text-xs font-medium text-gray-500">Text size</span>
                                    <div class="inline-flex rounded-lg border border-gray-200 bg-gray-100/80 p-0.5 gap-0.5 w-fit max-w-full self-start" role="group" aria-label="Text size">
                                        <input type="radio" name="text_size" value="normal" id="text_size_normal" {{ old('text_size', 'normal') === 'normal' ? 'checked' : '' }} class="sr-only">
                                        <input type="radio" name="text_size" value="medium" id="text_size_medium" {{ old('text_size') === 'medium' ? 'checked' : '' }} class="sr-only">
                                        <input type="radio" name="text_size" value="large" id="text_size_large" {{ old('text_size') === 'large' ? 'checked' : '' }} class="sr-only">
                                        <label for="text_size_normal" class="text-size-segmented-label px-3 py-2 sm:py-1.5 text-xs font-medium text-gray-600 rounded-md cursor-pointer transition-colors hover:text-gray-900 select-none touch-manipulation min-h-10 sm:min-h-0 flex items-center justify-center shrink-0">Normal</label>
                                        <label for="text_size_medium" class="text-size-segmented-label px-3 py-2 sm:py-1.5 text-xs font-medium text-gray-600 rounded-md cursor-pointer transition-colors hover:text-gray-900 select-none touch-manipulation min-h-10 sm:min-h-0 flex items-center justify-center shrink-0">Medium</label>
                                        <label for="text_size_large" class="text-size-segmented-label px-3 py-2 sm:py-1.5 text-xs font-medium text-gray-600 rounded-md cursor-pointer transition-colors hover:text-gray-900 select-none touch-manipulation min-h-10 sm:min-h-0 flex items-center justify-center shrink-0">Large</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @error('topic')<p class="w-full text-xs text-red-600">{{ $message }}</p>@enderror
                        @error('topic_id')<p class="w-full text-xs text-red-600">{{ $message }}</p>@enderror
                        @error('topic_name')<p class="w-full text-xs text-red-600">{{ $message }}</p>@enderror
                        <p id="say-it-ai-gen-error" class="w-full text-xs text-red-600 hidden sm:text-left"></p>
                        <div class="flex flex-col gap-2 w-full pt-1 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                            @if(!empty($sayItAiImageConfigured))
                            <div id="say-it-generate-wrap" class="hidden flex-col gap-2 sm:flex-row sm:items-center" data-ai-configured="1">
                                <button type="button" id="say-it-ai-generate-btn" class="inline-flex items-center justify-center gap-1.5 w-full sm:w-auto min-h-11 px-3 py-2.5 rounded-full border border-violet-300 bg-violet-50 text-violet-800 text-xs font-semibold hover:bg-violet-100 disabled:opacity-50 disabled:cursor-not-allowed touch-manipulation">
                                    <i class="fas fa-wand-magic-sparkles text-sm"></i>
                                    <span>Generate image</span>
                                </button>
                                <span id="say-it-ai-gen-loading" class="text-xs text-violet-600 hidden text-center sm:text-left whitespace-nowrap"><i class="fas fa-spinner fa-spin mr-1"></i> Generating…</span>
                            </div>
                            @endif
                            <button type="submit" class="w-full sm:w-auto min-h-11 px-5 py-2.5 bg-violet-600 text-white rounded-full font-semibold text-sm hover:bg-violet-700 active:scale-[0.98] transition shadow-sm touch-manipulation">
                                Post
                            </button>
                        </div>
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
            if (e.key !== 'Enter' || e.shiftKey) return;
            // On small screens, Enter inserts a new line; use Post button to submit.
            if (typeof window.matchMedia === 'function' && window.matchMedia('(max-width: 639px)').matches) return;
            e.preventDefault();
            form.submit();
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
    var modeRandom = document.getElementById('card_bg_mode_random');
    var modePick = document.getElementById('card_bg_mode_pick');
    var swatchesWrap = document.getElementById('card-bg-swatches');
    var hiddenBg = document.getElementById('card_background');
    var contentTa = document.getElementById('content');
    var mapEl = document.getElementById('say-it-bg-class-map');
    if (!modeRandom || !modePick || !swatchesWrap || !hiddenBg || !contentTa) return;

    var meshPreviewUrl = @json(url('/Say-it/composer-mesh-preview'));
    var bgMap = [];
    if (mapEl) {
        try { bgMap = JSON.parse(mapEl.textContent || '[]'); } catch (e) { bgMap = []; }
    }

    var swatches = swatchesWrap.querySelectorAll('.card-bg-swatch');
    var bgClassTokens = [];
    bgMap.forEach(function(o) {
        (o.classes || '').split(/\s+/).forEach(function(c) {
            if (c && bgClassTokens.indexOf(c) === -1) bgClassTokens.push(c);
        });
    });

    function stripPickBgClasses() {
        bgClassTokens.forEach(function(c) { contentTa.classList.remove(c); });
    }

    function applyPickToTextarea(key) {
        stripPickBgClasses();
        contentTa.style.cssText = '';
        var entry = null;
        for (var i = 0; i < bgMap.length; i++) {
            if (bgMap[i].key === key) { entry = bgMap[i]; break; }
        }
        var cls = entry ? entry.classes : '';
        if (cls) {
            cls.split(/\s+/).forEach(function(c) { if (c) contentTa.classList.add(c); });
        } else {
            contentTa.classList.add('bg-white');
        }
    }

    function fetchMeshForTextarea() {
        stripPickBgClasses();
        fetch(meshPreviewUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(j) {
                if (j && j.style) contentTa.style.cssText = j.style;
            })
            .catch(function() {
                contentTa.style.cssText = 'background-color:#fafafa;background-image:linear-gradient(135deg,#f5f3ff 0%,#ecfeff 50%,#fdf4ff 100%);background-repeat:no-repeat;background-size:100% 100%;';
            });
    }

    /** @param {boolean} userToggledMode - true when radio changed (fetch new mesh for Gradient) */
    function syncComposerTextareaBackground(userToggledMode) {
        if (modePick.checked) {
            applyPickToTextarea(hiddenBg.value);
            return;
        }
        stripPickBgClasses();
        var existing = (contentTa.getAttribute('style') || '').trim();
        if (userToggledMode || !existing) {
            fetchMeshForTextarea();
        }
    }

    function syncSwatchSelection() {
        var v = hiddenBg.value;
        swatches.forEach(function(btn) {
            var on = btn.getAttribute('data-card-bg-key') === v;
            btn.classList.toggle('border-violet-600', on);
            btn.classList.toggle('ring-2', on);
            btn.classList.toggle('ring-violet-400', on);
        });
    }

    function syncModeUI() {
        var pick = modePick.checked;
        swatchesWrap.classList.toggle('hidden', !pick);
        if (pick) syncSwatchSelection();
    }

    modeRandom.addEventListener('change', function() {
        syncModeUI();
        syncComposerTextareaBackground(true);
    });
    modePick.addEventListener('change', function() {
        syncModeUI();
        syncComposerTextareaBackground(true);
    });

    swatches.forEach(function(btn) {
        btn.addEventListener('click', function() {
            hiddenBg.value = this.getAttribute('data-card-bg-key') || '';
            syncSwatchSelection();
            if (modePick.checked) applyPickToTextarea(hiddenBg.value);
        });
    });

    syncModeUI();
    syncComposerTextareaBackground(false);
})();
(function() {
    var imageInput = document.getElementById('image');
    var previewWrap = document.getElementById('image-preview-wrap');
    var previewImg = document.getElementById('image-preview');
    var removeBtn = document.getElementById('image-preview-remove');
    var aiData = document.getElementById('ai_generated_image_dataurl');
    if (!imageInput || !previewWrap || !previewImg) return;

    imageInput.addEventListener('change', function() {
        if (aiData) aiData.value = '';
        var file = this.files && this.files[0];
        if (!file || !file.type.match(/^image\/(jpeg|png|gif|webp)$/)) {
            previewWrap.classList.add('hidden');
            previewImg.removeAttribute('src');
            if (typeof window.sayItUpdateGenerateVisibility === 'function') window.sayItUpdateGenerateVisibility();
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewWrap.classList.remove('hidden');
            if (typeof window.sayItUpdateGenerateVisibility === 'function') window.sayItUpdateGenerateVisibility();
        };
        reader.readAsDataURL(file);
    });

    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            imageInput.value = '';
            previewImg.removeAttribute('src');
            previewWrap.classList.add('hidden');
            if (aiData) aiData.value = '';
            if (typeof window.sayItUpdateGenerateVisibility === 'function') window.sayItUpdateGenerateVisibility();
        });
    }
})();
(function() {
    function updateSayItGenerateVisibility() {
        var wrap = document.getElementById('say-it-generate-wrap');
        var ce = document.getElementById('content');
        var imgIn = document.getElementById('image');
        if (!wrap) return;
        var configured = wrap.getAttribute('data-ai-configured') === '1';
        var hasFile = imgIn && imgIn.files && imgIn.files.length > 0;
        var len = ce ? (ce.value || '').trim().length : 0;
        var show = configured && hasFile && len >= 3;
        wrap.classList.toggle('hidden', !show);
        wrap.classList.toggle('flex', show);
    }
    window.sayItUpdateGenerateVisibility = updateSayItGenerateVisibility;

    var btn = document.getElementById('say-it-ai-generate-btn');
    var contentEl = document.getElementById('content');
    var errEl = document.getElementById('say-it-ai-gen-error');
    var loadEl = document.getElementById('say-it-ai-gen-loading');
    var aiData = document.getElementById('ai_generated_image_dataurl');
    var imageInput = document.getElementById('image');
    var previewWrap = document.getElementById('image-preview-wrap');
    var previewImg = document.getElementById('image-preview');
    if (!btn || !contentEl || !aiData || !previewWrap || !previewImg) return;

    var removePreviewBtn = document.getElementById('image-preview-remove');

    if (contentEl) {
        contentEl.addEventListener('input', updateSayItGenerateVisibility);
        contentEl.addEventListener('change', updateSayItGenerateVisibility);
    }
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            updateSayItGenerateVisibility();
        });
    }
    if (removePreviewBtn) {
        removePreviewBtn.addEventListener('click', function() {
            setTimeout(updateSayItGenerateVisibility, 0);
        });
    }

    function showErr(msg) {
        if (!errEl) return;
        errEl.textContent = msg || '';
        errEl.classList.toggle('hidden', !msg);
    }

    function buildImagePrompt() {
        var post = (contentEl.value || '').trim();
        if (post.length > 2000) {
            post = post.slice(0, 2000);
        }
        return post;
    }

    btn.addEventListener('click', function() {
        var wrap = document.getElementById('say-it-generate-wrap');
        if (wrap && wrap.getAttribute('data-ai-configured') !== '1') {
            showErr('AI image generation is not configured. Ask an admin: Admin → Settings → Say-it (Image generation).');
            return;
        }
        var p = buildImagePrompt();
        if (p.length < 3) {
            showErr('Write at least 3 characters in your post above — that text becomes the image prompt.');
            return;
        }
        if (!imageInput || !imageInput.files || imageInput.files.length === 0) {
            showErr('Attach a photo first — it is used as the base for Generate image.');
            return;
        }
        showErr('');
        btn.disabled = true;
        if (loadEl) loadEl.classList.remove('hidden');
        fetch('{{ url("/Say-it/generate-image") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ prompt: p })
        }).then(function(r) {
            return r.json().catch(function() { return {}; }).then(function(j) {
                return { ok: r.ok, body: j };
            });
        })
        .then(function(res) {
            if (!res.body || !res.body.ok) {
                showErr((res.body && res.body.message) ? res.body.message : 'Could not generate image.');
                return;
            }
            aiData.value = res.body.data_url || '';
            previewImg.src = res.body.data_url;
            previewWrap.classList.remove('hidden');
            if (imageInput) imageInput.value = '';
        }).catch(function() {
            showErr('Network error while generating image.');
        }).then(function() {
            btn.disabled = false;
            if (loadEl) loadEl.classList.add('hidden');
            updateSayItGenerateVisibility();
        });
    });

    updateSayItGenerateVisibility();

    if (aiData && aiData.value && aiData.value.indexOf('data:image/') === 0) {
        previewImg.src = aiData.value;
        previewWrap.classList.remove('hidden');
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
