<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Say it')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" />
    <style>
        html, body { font-family: 'Outfit', sans-serif; height: 100%; overflow: hidden; }
        .say-it-feed { max-width: 36rem; }
        /* Hide scrollbar but keep scroll */
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
    </style>
    @stack('styles')
</head>
<body class="bg-[#f0f2f5] h-screen flex flex-col antialiased text-gray-900 overflow-hidden">
    {{-- Top bar --}}
    <header class="flex-shrink-0 z-30 bg-white border-b border-gray-200 shadow-sm">
        <div class="w-full px-3 sm:px-4 lg:px-6 h-12 sm:h-14 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                <button type="button" id="sidebar-toggle" class="lg:hidden flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 transition" aria-label="Open menu">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                <a href="{{ url('/Say-it') }}" class="flex items-center gap-2 text-gray-900 no-underline min-w-0">
                    <span class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-violet-600 text-white flex-shrink-0">
                        <i class="fas fa-robot text-sm sm:text-base"></i>
                    </span>
                    <span class="text-lg sm:text-xl font-bold tracking-tight truncate">Say it</span>
                </a>
            </div>
            <div class="hidden sm:block flex-1 max-w-xl mx-4">
                <div class="rounded-full bg-gray-100 border border-gray-200 px-4 py-2 flex items-center gap-2 text-gray-400 text-sm">
                    <i class="fas fa-search flex-shrink-0"></i>
                    <span>Search posts...</span>
                </div>
            </div>
            <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
                <a href="{{ url('/Say-it') }}#create" class="inline-flex items-center gap-1.5 px-3 py-2 sm:px-4 sm:py-2.5 bg-violet-600 text-white rounded-full text-sm font-semibold hover:bg-violet-700 active:scale-[0.98] transition shadow-sm">
                    <i class="fas fa-plus text-xs"></i>
                    <span class="hidden xs:inline">Create</span>
                </a>
                <a href="{{ url('/Say-it') }}" class="flex items-center justify-center w-9 h-9 rounded-full text-gray-500 hover:bg-gray-100 transition" aria-label="Home">
                    <i class="fas fa-home"></i>
                </a>
                <a href="{{ url('/Say-it') }}" class="flex items-center justify-center w-9 h-9 rounded-full text-gray-500 hover:bg-gray-100 transition" aria-label="Comments">
                    <i class="fas fa-comment"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="flex flex-1 min-h-0 w-full overflow-hidden">
        {{-- Left sidebar (desktop) --}}
        <aside id="left-sidebar" class="hidden lg:block w-56 flex-shrink-0 border-r border-gray-200 bg-white overflow-y-auto scrollbar-hide py-4">
            <nav class="px-3 space-y-1">
                <a href="{{ url('/Say-it') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium {{ request()->get('sort') !== 'popular' ? 'bg-violet-50 text-violet-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="fas fa-home w-5 text-center text-gray-500"></i>
                    Home
                </a>
                <a href="{{ url('/Say-it?sort=popular') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium {{ request()->get('sort') === 'popular' ? 'bg-violet-50 text-violet-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="fas fa-chart-line w-5 text-center text-gray-500"></i>
                    Popular
                </a>
                <a href="{{ url('/Say-it') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-clock w-5 text-center text-gray-500"></i>
                    Recent
                </a>
            </nav>
            <div class="mt-6 px-3">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Anonymous</p>
                <p class="px-3 mt-1 text-sm text-gray-500">Share thoughts without an account.</p>
            </div>
            {{-- Top 10 topics (below Anonymous) --}}
            <div class="mt-6 px-3">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Top 10 topics</p>
                @if(isset($topTopics) && $topTopics->isNotEmpty())
                <ul class="space-y-1">
                    @foreach($topTopics as $t)
                    <li>
                        <a href="{{ url('/Say-it?topic=' . urlencode($t->slug) . '&sort=popular') }}" class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-sm {{ (request()->get('topic') ?? '') === $t->slug ? 'bg-violet-50 text-violet-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="truncate">{{ $t->name }}</span>
                            <span class="tabular-nums text-gray-500 flex-shrink-0">{{ $t->posts_count }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="px-3 text-sm text-gray-400">No topics yet. Post to add one.</p>
                @endif
            </div>
            {{-- Top 10 hashtags (below topics) --}}
            <div class="mt-4 px-3">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Top 10 hashtags</p>
                @if(isset($topHashtags) && $topHashtags->isNotEmpty())
                <ul class="space-y-1">
                    @foreach($topHashtags as $h)
                    <li>
                        <a href="{{ url('/Say-it?hashtag=' . urlencode($h->slug) . '&sort=popular') }}" class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-sm {{ (request()->get('hashtag') ?? '') === $h->slug ? 'bg-violet-50 text-violet-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="truncate">{{ $h->name }}</span>
                            <span class="tabular-nums text-gray-500 flex-shrink-0">{{ $h->posts_count }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="px-3 text-sm text-gray-400">No hashtags yet. Use #word in posts.</p>
                @endif
            </div>
        </aside>

        {{-- Mobile sidebar overlay --}}
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden" aria-hidden="true"></div>
        <aside id="mobile-sidebar" class="fixed top-0 left-0 w-64 h-full bg-white border-r border-gray-200 z-50 transform -translate-x-full transition-transform duration-200 ease-out lg:hidden overflow-y-auto scrollbar-hide">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <span class="font-bold text-lg">Menu</span>
                <button type="button" id="sidebar-close" class="w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 flex items-center justify-center" aria-label="Close menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="p-3 space-y-1">
                <a href="{{ url('/Say-it') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium {{ request()->get('sort') !== 'popular' ? 'bg-violet-50 text-violet-700' : 'text-gray-700' }}" onclick="document.getElementById('mobile-sidebar').classList.add('-translate-x-full')">
                    <i class="fas fa-home w-5 text-center text-gray-500"></i>
                    Home
                </a>
                <a href="{{ url('/Say-it?sort=popular') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium {{ request()->get('sort') === 'popular' ? 'bg-violet-50 text-violet-700' : 'text-gray-700' }}" onclick="document.getElementById('mobile-sidebar').classList.add('-translate-x-full')">
                    <i class="fas fa-chart-line w-5 text-center text-gray-500"></i>
                    Popular
                </a>
                <a href="{{ url('/Say-it') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium text-gray-700" onclick="document.getElementById('mobile-sidebar').classList.add('-translate-x-full')">
                    <i class="fas fa-clock w-5 text-center text-gray-500"></i>
                    Recent
                </a>
            </nav>
            <div class="mt-4 px-3">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Top 10 topics</p>
                @if(isset($topTopics) && $topTopics->isNotEmpty())
                <ul class="space-y-1">
                    @foreach($topTopics as $t)
                    <li>
                        <a href="{{ url('/Say-it?topic=' . urlencode($t->slug) . '&sort=popular') }}" class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50" onclick="document.getElementById('mobile-sidebar').classList.add('-translate-x-full')">
                            <span class="truncate">{{ $t->name }}</span>
                            <span class="tabular-nums text-gray-500">{{ $t->posts_count }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="px-3 text-sm text-gray-400">No topics yet.</p>
                @endif
            </div>
            <div class="mt-4 px-3">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Top 10 hashtags</p>
                @if(isset($topHashtags) && $topHashtags->isNotEmpty())
                <ul class="space-y-1">
                    @foreach($topHashtags as $h)
                    <li>
                        <a href="{{ url('/Say-it?hashtag=' . urlencode($h->slug) . '&sort=popular') }}" class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-50" onclick="document.getElementById('mobile-sidebar').classList.add('-translate-x-full')">
                            <span class="truncate">{{ $h->name }}</span>
                            <span class="tabular-nums text-gray-500">{{ $h->posts_count }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="px-3 text-sm text-gray-400">No hashtags yet.</p>
                @endif
            </div>
        </aside>

        {{-- Main content: only this area scrolls (scrollbar hidden) --}}
        <main id="say-it-main" class="flex-1 min-w-0 min-h-0 overflow-y-auto scrollbar-hide py-4 sm:py-6 px-3 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 py-3 px-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 py-3 px-4 rounded-xl bg-red-50 text-red-800 text-sm font-medium border border-red-200">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 py-3 px-4 rounded-xl bg-red-50 text-red-800 text-sm border border-red-200">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>

        {{-- Right sidebar: Recent Posts (desktop only when variable set) --}}
        @if(isset($recentPosts) && $recentPosts->isNotEmpty())
        <aside class="hidden xl:block w-72 flex-shrink-0 border-l border-gray-200 bg-white overflow-hidden flex flex-col">
            <div class="px-4 flex items-center justify-between mb-3 py-4 flex-shrink-0">
                <h3 class="font-bold text-gray-900">Recent Posts</h3>
            </div>
            <div class="px-2 space-y-0 overflow-y-auto scrollbar-hide flex-1 min-h-0">
                @foreach($recentPosts as $rp)
                <a href="{{ url('/Say-it/' . $rp->id) }}" class="block px-3 py-3 rounded-lg hover:bg-gray-50 transition group border-b border-gray-100 last:border-b-0">
                    {{-- User icon + user name --}}
                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ \App\Helpers\SayItHelper::avatarColorClassesForCodename($rp->codename) }}">
                            <i class="{{ \App\Helpers\SayItHelper::animalIconForCodename($rp->codename) }} text-sm"></i>
                        </div>
                        <span class="font-medium text-gray-800 text-sm truncate group-hover:text-violet-600">{{ $rp->codename }}</span>
                        @if($rp->relationLoaded('topic') && $rp->topic)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-violet-100 text-violet-700">{{ $rp->topic->name }}</span>
                        @endif
                    </div>
                    {{-- Content (title/text or Image post) --}}
                    <div class="flex gap-2">
                        <p class="text-sm text-gray-900 truncate flex-1 min-w-0 group-hover:text-violet-600">{{ Str::limit($rp->content ? $rp->censored_content : 'Image post', 50) }}</p>
                        @if($rp->image_path)
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gray-100 overflow-hidden">
                            <img src="{{ $rp->image_url ?? '#' }}" alt="" class="w-full h-full object-cover">
                        </div>
                        @endif
                    </div>
                    {{-- Reactions, comments, time --}}
                    <div class="flex items-center gap-2 mt-1.5 text-xs text-gray-500 flex-wrap">
                        <span class="inline-flex items-center gap-1" title="Votes">
                            <i class="fas fa-thumbs-up text-[10px]"></i><i class="fas fa-thumbs-down text-[10px] ml-0.5"></i>
                            <span class="tabular-nums">{{ \App\Helpers\SayItHelper::formatCount($rp->upvotes_count - $rp->downvotes_count) }}</span>
                        </span>
                        <span>·</span>
                        <span>{{ $rp->all_comments_count ?? 0 }} {{ ($rp->all_comments_count ?? 0) === 1 ? 'comment' : 'comments' }}</span>
                        <span>·</span>
                        <time datetime="{{ $rp->created_at->toIso8601String() }}">{{ $rp->created_at->diffForHumans() }}</time>
                    </div>
                    @if($rp->relationLoaded('latestComment') && $rp->latestComment)
                        <p class="text-xs text-gray-600 mt-1 truncate"><span class="font-medium text-gray-700">{{ $rp->latestComment->codename }}</span>: {{ Str::limit($rp->latestComment->censored_content, 40) }}</p>
                    @endif
                </a>
                @endforeach
            </div>
        </aside>
        @endif
    </div>

    <script>
    (function() {
        var toggle = document.getElementById('sidebar-toggle');
        var closeBtn = document.getElementById('sidebar-close');
        var overlay = document.getElementById('sidebar-overlay');
        var sidebar = document.getElementById('mobile-sidebar');
        if (toggle) toggle.addEventListener('click', function() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);
    })();
    </script>
    @stack('scripts')
</body>
</html>
