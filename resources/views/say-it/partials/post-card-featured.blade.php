{{-- Single most popular post with fire border effect --}}
@php
    $sessionCodename = $sessionCodename ?? session('sayit_codename');
@endphp
<div class="say-it-featured-fire-wrapper mb-6 min-w-0 max-w-full">
    <div class="say-it-featured-fire-inner relative z-0">
        <div class="flex items-center gap-2 mb-3">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gradient-to-r from-amber-500/20 to-orange-600/20 text-amber-800 text-xs font-bold uppercase tracking-wide border border-amber-400/50">
                <i class="fas fa-fire text-amber-500"></i>
                Most popular
            </span>
        </div>
        {{-- Fire only on the rounded border of the post card --}}
        <div class="say-it-fire-border-wrap" aria-hidden="true">
            <article class="say-it-featured-card-inner bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-200">
                @php $sayItMesh = $post->card_background_mesh ?? ''; @endphp
                <div class="p-4 sm:p-5 @if($sayItMesh === ''){{ \App\Helpers\SayItHelper::cardContentBackgroundClasses($post->card_background) }}@endif" @if($sayItMesh !== '') style="{{ $sayItMesh }}" @endif>
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
                            <img src="{{ $post->image_url }}" alt="Post image" class="w-full max-w-full max-h-80 object-contain h-auto">
                        </div>
                    @endif
                    @if($post->relationLoaded('latestComment') && $post->latestComment)
                        <a href="{{ url('/Say-it/' . $post->id) }}#comments" class="mt-3 block rounded-xl bg-gray-50 border border-gray-100 px-3 py-2.5 hover:bg-gray-100/80 transition group">
                            <p class="text-xs font-medium text-gray-500 mb-0.5">Latest comment</p>
                            <p class="text-sm text-gray-800 line-clamp-2 break-words group-hover:text-violet-600 transition">
                                <span class="font-medium text-gray-700">{{ $post->latestComment->codename }}</span>
                                <span class="text-gray-400">·</span>
                                {{ Str::limit($post->latestComment->censored_content, 120) }}
                            </p>
                        </a>
                    @endif
                </div>
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
                    <a href="{{ url('/Say-it/' . $post->id) }}" class="inline-flex items-center rounded-full border border-gray-200 bg-gray-100 pl-2 pr-2 py-1 gap-1.5 text-gray-800 hover:bg-gray-200/80 transition touch-manipulation">
                        <i class="far fa-comment text-sm text-gray-600"></i>
                        <span class="font-semibold text-xs tabular-nums">{{ \App\Helpers\SayItHelper::formatCount($post->all_comments_count ?? 0) }}</span>
                    </a>
                    @php
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
        </div>
    </div>
    {{-- WebGL fire in front of the post (only for Most popular) --}}
    <div class="say-it-mostpopular-fire-bg say-it-mostpopular-fire-front" id="say-it-mostpopular-fire-wrap" aria-hidden="true">
        <canvas id="say-it-mostpopular-fire-canvas"></canvas>
    </div>
</div>
