<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Say it')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" />
    <style>
        html { font-family: 'Outfit', sans-serif; height: 100%; overflow: hidden; }
        /* dvh avoids mobile browser chrome clipping; flex column keeps main as the scroll region */
        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100%;
            min-height: 100dvh;
            height: 100%;
            height: 100dvh;
            max-height: 100dvh;
            overflow: hidden;
            -webkit-tap-highlight-color: transparent;
        }
        .say-it-feed { max-width: 36rem; }
        /* Hide scrollbar but keep scroll */
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        #say-it-main { -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; }
    </style>
    @stack('styles')
</head>
<body class="bg-[#f0f2f5] min-h-0 h-[100dvh] max-h-[100dvh] flex flex-col antialiased text-gray-900 overflow-hidden">
    {{-- Top bar --}}
    <header class="flex-shrink-0 z-30 bg-white border-b border-gray-200 shadow-sm pt-[env(safe-area-inset-top,0px)]">
        <div class="w-full px-2.5 sm:px-4 lg:px-6 min-h-12 sm:min-h-14 flex items-center justify-between gap-1.5 sm:gap-2">
            <div class="flex items-center gap-1.5 sm:gap-4 min-w-0 flex-1">
                <button type="button" id="sidebar-toggle" class="lg:hidden flex items-center justify-center w-10 h-10 shrink-0 rounded-lg text-gray-500 hover:bg-gray-100 transition touch-manipulation" aria-label="Open menu">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                <a href="{{ url('/Say-it') }}" class="flex items-center gap-1.5 sm:gap-2 text-gray-900 no-underline min-w-0">
                    <span class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-violet-600 text-white flex-shrink-0">
                        <i class="fas fa-circle-question text-sm sm:text-base"></i>
                    </span>
                    <span class="text-base sm:text-xl font-bold tracking-tight truncate">Say it</span>
                </a>
            </div>
            <div class="hidden md:flex flex-1 max-w-xl mx-2 lg:mx-4 min-w-0">
                <div class="rounded-full bg-gray-100 border border-gray-200 px-4 py-2 flex items-center gap-2 text-gray-400 text-sm w-full">
                    <i class="fas fa-search flex-shrink-0"></i>
                    <span class="truncate">Search posts...</span>
                </div>
            </div>
            <div class="flex items-center gap-0.5 sm:gap-2 flex-shrink-0">
                <a href="{{ url('/Say-it') }}#create" class="inline-flex items-center justify-center gap-1.5 min-h-10 min-w-10 sm:min-w-0 px-2.5 sm:px-4 py-2 sm:py-2.5 bg-violet-600 text-white rounded-full text-xs sm:text-sm font-semibold hover:bg-violet-700 active:scale-[0.98] transition shadow-sm touch-manipulation">
                    <i class="fas fa-plus text-xs shrink-0"></i>
                    <span class="hidden sm:inline">Create</span>
                </a>
                <a href="{{ route('say-it.chat.index') }}" class="hidden sm:flex items-center justify-center w-9 h-9 rounded-full text-gray-500 hover:bg-gray-100 transition touch-manipulation {{ request()->is('Say-it/chat*') ? 'bg-violet-50 text-violet-700' : '' }}" aria-label="Anonymous chat">
                    <i class="fas fa-comments"></i>
                </a>
                <a href="{{ route('say-it.games.index') }}" class="hidden sm:flex items-center justify-center w-9 h-9 rounded-full text-gray-500 hover:bg-gray-100 transition touch-manipulation {{ request()->is('Say-it/games*') ? 'bg-violet-50 text-violet-700' : '' }}" aria-label="Mini games">
                    <i class="fas fa-gamepad"></i>
                </a>
                <a href="{{ url('/Say-it') }}" class="hidden sm:flex items-center justify-center w-9 h-9 rounded-full text-gray-500 hover:bg-gray-100 transition touch-manipulation" aria-label="Home">
                    <i class="fas fa-home"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="flex flex-1 min-h-0 w-full overflow-hidden">
        {{-- Left sidebar (desktop) --}}
        <aside id="left-sidebar" class="hidden lg:block w-56 flex-shrink-0 border-r border-gray-200 bg-white overflow-y-auto scrollbar-hide py-4">
            <nav class="px-3 space-y-1">
                <a href="{{ url('/Say-it') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium {{ !request()->is('Say-it/chat*') && !request()->is('Say-it/games*') && request()->get('sort') !== 'popular' ? 'bg-violet-50 text-violet-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="fas fa-home w-5 text-center text-gray-500"></i>
                    Home
                </a>
                <a href="{{ url('/Say-it?sort=popular') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium {{ !request()->is('Say-it/chat*') && !request()->is('Say-it/games*') && request()->get('sort') === 'popular' ? 'bg-violet-50 text-violet-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="fas fa-chart-line w-5 text-center text-gray-500"></i>
                    Popular
                </a>
                <a href="{{ route('say-it.chat.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium {{ request()->is('Say-it/chat*') ? 'bg-violet-50 text-violet-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="fas fa-comments w-5 text-center text-gray-500"></i>
                    Chat
                </a>
                <a href="{{ route('say-it.games.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium {{ request()->is('Say-it/games*') ? 'bg-violet-50 text-violet-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="fas fa-gamepad w-5 text-center text-gray-500"></i>
                    Games
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
        <aside id="mobile-sidebar" class="fixed top-0 left-0 w-[min(100vw-2rem,16rem)] max-w-[85vw] h-full bg-white border-r border-gray-200 z-50 transform -translate-x-full transition-transform duration-200 ease-out lg:hidden overflow-y-auto scrollbar-hide pt-[env(safe-area-inset-top,0px)] pb-[env(safe-area-inset-bottom,0px)]">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <span class="font-bold text-lg">Menu</span>
                <button type="button" id="sidebar-close" class="w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 flex items-center justify-center" aria-label="Close menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="p-3 space-y-1">
                <a href="{{ url('/Say-it') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium {{ !request()->is('Say-it/chat*') && !request()->is('Say-it/games*') && request()->get('sort') !== 'popular' ? 'bg-violet-50 text-violet-700' : 'text-gray-700' }}" onclick="document.getElementById('mobile-sidebar').classList.add('-translate-x-full')">
                    <i class="fas fa-home w-5 text-center text-gray-500"></i>
                    Home
                </a>
                <a href="{{ url('/Say-it?sort=popular') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium {{ !request()->is('Say-it/chat*') && !request()->is('Say-it/games*') && request()->get('sort') === 'popular' ? 'bg-violet-50 text-violet-700' : 'text-gray-700' }}" onclick="document.getElementById('mobile-sidebar').classList.add('-translate-x-full')">
                    <i class="fas fa-chart-line w-5 text-center text-gray-500"></i>
                    Popular
                </a>
                <a href="{{ route('say-it.chat.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium {{ request()->is('Say-it/chat*') ? 'bg-violet-50 text-violet-700' : 'text-gray-700' }}" onclick="document.getElementById('mobile-sidebar').classList.add('-translate-x-full')">
                    <i class="fas fa-comments w-5 text-center text-gray-500"></i>
                    Chat
                </a>
                <a href="{{ route('say-it.games.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium {{ request()->is('Say-it/games*') ? 'bg-violet-50 text-violet-700' : 'text-gray-700' }}" onclick="document.getElementById('mobile-sidebar').classList.add('-translate-x-full')">
                    <i class="fas fa-gamepad w-5 text-center text-gray-500"></i>
                    Games
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

        {{-- Main content: only this area scrolls (scrollbar hidden). Chat room fills the pane. --}}
        <main id="say-it-main" class="flex-1 min-w-0 min-h-0 overflow-y-auto overflow-x-hidden scrollbar-hide @yield('main_class', 'py-3 sm:py-6 px-2.5 sm:px-6 lg:px-8 pb-[max(0.75rem,env(safe-area-inset-bottom))]')">
            @hasSection('hide_flash')
            @else
                @if(session('success'))
                    <div class="mb-4 py-3 px-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-4 py-3 px-4 rounded-xl bg-red-50 text-red-800 text-sm font-medium border border-red-200">{{ session('error') }}</div>
                @endif
                @php
                    $errorBag = $errors->getBag('default');
                    $topicKeys = ['topic', 'topic_id', 'topic_name'];
                    $errorsExceptTopic = array_diff_key($errorBag->getMessages(), array_flip($topicKeys));
                @endphp
                @if(!empty($errorsExceptTopic))
                    <div class="mb-4 py-3 px-4 rounded-xl bg-red-50 text-red-800 text-sm border border-red-200">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errorsExceptTopic as $msgs)
                                @foreach((array) $msgs as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
            @yield('content')
        </main>

        {{-- Right sidebar: Recent Posts (hidden on chat so the room can use full width) --}}
        @if(isset($recentPosts) && $recentPosts->isNotEmpty() && !request()->is('Say-it/chat*') && !request()->is('Say-it/games*'))
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
    @include('components.seasonal-effects')
</body>
</html>
