@extends('layouts.landing')

@section('title', $settings['system_name'] . ' - Online Quiz Management System')
@section('description', $settings['system_description'])


@section('content')
<!-- Hero Section -->
<section class="relative text-white overflow-hidden @if(empty($heroBackgroundUrl)) gradient-bg py-16 md:py-24 lg:py-32 @endif">
    @if(!empty($heroBackgroundUrl))
        <img src="{{ $heroBackgroundUrl }}" alt="Hero Background" class="w-full h-auto object-contain" style="display: block; width: 100%;">
        <div class="absolute inset-0 bg-black/20" style="z-index: 1;"></div>
    @else
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-64 h-64 md:w-96 md:h-96 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-64 h-64 md:w-96 md:h-96 bg-white rounded-full translate-x-1/2 translate-y-1/2"></div>
        </div>
    @endif
    <div class="absolute inset-0 flex items-center justify-center max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-16" style="z-index: 2; pointer-events: none;">
        <div class="text-center max-w-4xl mx-auto w-full" style="pointer-events: auto;">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold mb-4 md:mb-6 leading-tight animate-fade-in-up px-2">
                {{ $heroTitle }}
            </h1>
            <p class="text-base sm:text-lg md:text-xl lg:text-2xl mb-6 md:mb-10 text-gray-100 leading-relaxed animate-fade-in-up animate-delay-100 px-4">
                {{ $heroSubtitle }}
            </p>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center animate-fade-in-up animate-delay-200 px-4">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="bg-white text-primary px-6 py-3 sm:px-8 sm:py-4 rounded-xl font-semibold hover:bg-gray-50 transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-1 text-sm sm:text-base">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('user.dashboard') }}" class="bg-white text-primary px-6 py-3 sm:px-8 sm:py-4 rounded-xl font-semibold hover:bg-gray-50 transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-1 text-sm sm:text-base">
                            Go to Dashboard
                        </a>
                    @endif
                @else
                    <a href="{{ $heroPrimaryButtonUrl ?? route('login') }}" class="bg-white text-primary px-6 py-3 sm:px-8 sm:py-4 rounded-xl font-semibold hover:bg-gray-50 transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-1 text-sm sm:text-base">
                        {{ $heroPrimaryButtonText }}
                    </a>
                @endauth
                <a href="{{ $heroSecondaryButtonUrl ?? route('landing.projects') }}" class="border-2 border-white text-white px-6 py-3 sm:px-8 sm:py-4 rounded-xl font-semibold hover:bg-white hover:text-gray-900 transition-all backdrop-blur-sm text-sm sm:text-base">
                    {{ $heroSecondaryButtonText }}
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Projects Section with Laptop Animation -->
<section class="py-12 sm:py-16 md:py-20 lg:py-24 bg-gradient-to-br from-gray-50 to-gray-100 relative overflow-hidden" id="projects-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8 sm:mb-12 md:mb-16">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mb-3 sm:mb-4 px-4">Our Projects</h2>
            <p class="text-base sm:text-lg md:text-xl text-gray-600 px-4">Explore the solutions we've delivered</p>
        </div>

        <!-- Three Laptops Container -->
        <div class="relative max-w-6xl mx-auto" id="laptops-container">
            @php
                // Filter out empty projects (projects without names) and default "Project X" names
                $validProjects = array_filter($projects, function($project) {
                    $name = $project['name'] ?? '';
                    // Exclude empty names and default "Project X" format names
                    return !empty($name) && !preg_match('/^Project\s+\d+$/i', trim($name));
                });
                $validProjects = array_values($validProjects); // Re-index array
                $totalProjects = count($validProjects);
                // Show carousel if there are more than 3 projects
                $hasCarousel = $totalProjects > 3;
            @endphp

            @if($hasCarousel)
                <!-- Carousel wrapper for more than 5 projects -->
                <div class="relative overflow-hidden">
                    <div class="projects-carousel flex transition-transform duration-700 ease-in-out" id="projects-carousel" style="transform: translateX(0);">
                        @foreach($validProjects as $index => $project)
                            <div class="laptop-carousel-item flex-shrink-0 w-full sm:w-1/2 md:w-1/3 px-2 sm:px-3" data-project-index="{{ $index }}">
                                <div class="relative mx-auto w-full max-w-[280px] sm:max-w-[300px]">
                                    <!-- Laptop Screen (open initially for carousel) -->
                                    <div class="laptop-screen relative mx-auto transition-transform duration-1000 ease-out" style="transform-origin: bottom center; transform: rotateX(0deg);">
                                        <div class="relative bg-gray-800 rounded-t-xl p-1.5 shadow-xl" style="width: 100%; padding-bottom: 62.5%;">
                                            <div class="absolute inset-1.5 bg-gray-900 rounded-lg overflow-hidden">
                                                <!-- Screen Content -->
                                                <div class="absolute inset-0 w-full h-full" style="background-color: #111827;">
                                                    @if(!empty($project['image_url']))
                                                        <img src="{{ $project['image_url'] }}"
                                                             alt="{{ $project['name'] }}"
                                                             class="absolute inset-0 w-full h-full object-cover"
                                                             style="min-width: 100%; min-height: 100%; display: block;"
                                                             onload="console.log('Image loaded successfully:', '{{ $project['name'] }}');"
                                                             onerror="console.error('Image failed to load:', '{{ $project['name'] }}', 'Path:', '{{ $project['image'] ?? 'unknown' }}', 'URL:', '{{ substr($project['image_url'] ?? 'no url', 0, 100) }}...'); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                        <div class="absolute inset-0 w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center hidden">
                                                            <div class="text-center p-3">
                                                                <h3 class="text-sm font-bold text-gray-900 mb-1">{{ $project['name'] }}</h3>
                                                                <p class="text-gray-700 text-xs line-clamp-2">{{ $project['description'] }}</p>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="absolute inset-0 w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center">
                                                            <div class="text-center p-3">
                                                                <h3 class="text-sm font-bold text-gray-900 mb-1">{{ $project['name'] }}</h3>
                                                                <p class="text-gray-700 text-xs line-clamp-2">{{ $project['description'] }}</p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <!-- Overlay with project info -->
                                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex items-end opacity-0 hover:opacity-100 transition-opacity">
                                                        <div class="p-3 w-full">
                                                            <h3 class="text-sm font-bold text-white mb-1 line-clamp-1">{{ $project['name'] }}</h3>
                                                            <p class="text-gray-200 text-xs mb-2 line-clamp-2">{{ $project['description'] }}</p>
                                                            <a href="{{ $project['url'] && $project['url'] !== '#' ? $project['url'] : '#' }}"
                                                               {{ $project['url'] && $project['url'] !== '#' ? 'target="_blank"' : '' }}
                                                               class="inline-flex items-center px-3 py-1.5 text-xs bg-white text-primary rounded-lg font-semibold hover:bg-gray-100 transition-all">
                                                                Visit Site
                                                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                                </svg>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <!-- Carousel Navigation Dots -->
                <div class="flex justify-center mt-4 sm:mt-6 gap-2" id="carousel-dots">
                    @php
                        // Calculate number of slides: desktop shows 3 per slide, tablet 2, mobile 1
                        // We'll create dots for desktop view (3 per slide), JS will adjust for smaller screens
                        $projectsPerSlideDesktop = 3;
                        $slidesCount = ceil($totalProjects / $projectsPerSlideDesktop);
                    @endphp
                    @for($i = 0; $i < $slidesCount; $i++)
                        <button class="carousel-dot w-2 h-2 rounded-full transition-all duration-300 {{ $i === 0 ? 'bg-primary w-8' : 'bg-gray-300' }}"
                                data-slide="{{ $i }}"
                                aria-label="Go to slide {{ $i + 1 }}"></button>
                    @endfor
                </div>
            @else
                <!-- Static grid for 5 or fewer projects -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($validProjects as $i => $project)
                        <!-- Laptop {{ $i + 1 }} -->
                        <div class="relative laptop-item" data-index="{{ $i }}">
                            <div class="relative mx-auto w-full max-w-[280px] sm:max-w-[300px]">
                                <!-- Laptop Screen (closed initially) -->
                                <div class="laptop-screen relative mx-auto transition-transform duration-1000 ease-out" style="transform-origin: bottom center; transform: rotateX(-90deg);">
                                    <div class="relative bg-gray-800 rounded-t-xl p-1.5 shadow-xl" style="width: 100%; padding-bottom: 62.5%;">
                                        <div class="absolute inset-1.5 bg-gray-900 rounded-lg overflow-hidden">
                                            <!-- Screen Content -->
                                            <div class="absolute inset-0 w-full h-full" style="background-color: #111827;">
                                                @if(!empty($project['image_url']))
                                                    <img src="{{ $project['image_url'] }}"
                                                         alt="{{ $project['name'] }}"
                                                         class="absolute inset-0 w-full h-full object-cover"
                                                         style="min-width: 100%; min-height: 100%; display: block;"
                                                         onload="console.log('Image loaded successfully:', '{{ $project['name'] }}');"
                                                         onerror="console.error('Image failed to load:', '{{ $project['name'] }}', 'Path:', '{{ $project['image'] ?? 'unknown' }}', 'URL:', '{{ substr($project['image_url'] ?? 'no url', 0, 100) }}...'); this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="absolute inset-0 w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center hidden">
                                                        <div class="text-center p-3">
                                                            <h3 class="text-sm font-bold text-gray-900 mb-1">{{ $project['name'] }}</h3>
                                                            <p class="text-gray-700 text-xs line-clamp-2">{{ $project['description'] }}</p>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="absolute inset-0 w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center">
                                                        <div class="text-center p-3">
                                                            <h3 class="text-sm font-bold text-gray-900 mb-1">{{ $project['name'] }}</h3>
                                                            <p class="text-gray-700 text-xs line-clamp-2">{{ $project['description'] }}</p>
                                                            @if(!empty($project['image']))
                                                                <p class="text-xs text-red-500 mt-1">Image path: {{ $project['image'] }}</p>
                                                                <p class="text-xs text-blue-500 mt-1">No URL generated</p>
                                                            @else
                                                                <p class="text-xs text-yellow-500 mt-1">No image uploaded</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                                <!-- Overlay with project info -->
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex items-end opacity-0 hover:opacity-100 transition-opacity">
                                                    <div class="p-3 w-full">
                                                        <h3 class="text-sm font-bold text-white mb-1 line-clamp-1">{{ $project['name'] }}</h3>
                                                        <p class="text-gray-200 text-xs mb-2 line-clamp-2">{{ $project['description'] }}</p>
                                                        <a href="{{ $project['url'] && $project['url'] !== '#' ? $project['url'] : '#' }}"
                                                           {{ $project['url'] && $project['url'] !== '#' ? 'target="_blank"' : '' }}
                                                           class="inline-flex items-center px-3 py-1.5 text-xs bg-white text-primary rounded-lg font-semibold hover:bg-gray-100 transition-all">
                                                            Visit Site
                                                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Recent Quizzes Section -->
@if($recentQuizzes->count() > 0)
<section class="py-12 sm:py-16 md:py-20 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8 sm:mb-12 md:mb-16">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mb-3 sm:mb-4 px-4">Featured Quizzes</h2>
            <p class="text-base sm:text-lg md:text-xl text-gray-600 px-4">Explore our latest and most popular assessments</p>
        </div>

        <div class="relative">
            <div class="overflow-hidden">
                <div id="quiz-carousel" class="flex space-x-4 sm:space-x-6 transition-transform duration-1000 ease-in-out" style="width: {{ $recentQuizzes->count() * 2 * 320 }}px; max-width: 100vw;">
                    @foreach($recentQuizzes as $quiz)
                        <div class="flex-shrink-0 w-80 sm:w-96 bg-white rounded-2xl shadow-lg overflow-hidden hover-lift border border-gray-100 quiz-card" data-quiz-id="{{ $quiz->id }}">
                            <div class="p-6 sm:p-8">
                                <div class="flex items-center justify-between mb-3 sm:mb-4">
                                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 line-clamp-2">{{ $quiz->title }}</h3>
                                    @if($quiz->topic)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                                            {{ $quiz->topic }}
                                        </span>
                                    @endif
                                </div>
                                @if($quiz->description)
                                    <p class="text-gray-600 mb-6 leading-relaxed">{{ Str::limit($quiz->description, 120) }}</p>
                                @endif
                                <div class="flex items-center justify-between text-sm text-gray-500 mb-6 pb-6 border-b border-gray-100">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        {{ $quiz->total_questions }} questions
                                    </span>
                                    @if($quiz->time_limit)
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $quiz->time_limit }} min
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 font-mono bg-gray-50 px-3 py-1 rounded-lg">{{ $quiz->quiz_code }}</span>
                                    @auth
                                        <a href="{{ route('user.quizzes.enter-code') }}" class="text-primary hover:text-primary/80 font-semibold flex items-center">
                                            Take Quiz
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    @else
                                        <a href="{{ route('login') }}" class="text-primary hover:text-primary/80 font-semibold flex items-center">
                                            Login to Take
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Duplicate set for seamless looping -->
                    @foreach($recentQuizzes as $quiz)
                        <div class="flex-shrink-0 w-96 bg-white rounded-2xl shadow-lg overflow-hidden hover-lift border border-gray-100 quiz-card" data-quiz-id="{{ $quiz->id }}">
                            <div class="p-8">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-bold text-gray-900">{{ $quiz->title }}</h3>
                                    @if($quiz->topic)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                                            {{ $quiz->topic }}
                                        </span>
                                    @endif
                                </div>
                                @if($quiz->description)
                                    <p class="text-gray-600 mb-6 leading-relaxed">{{ Str::limit($quiz->description, 120) }}</p>
                                @endif
                                <div class="flex items-center justify-between text-sm text-gray-500 mb-6 pb-6 border-b border-gray-100">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        {{ $quiz->total_questions }} questions
                                    </span>
                                    @if($quiz->time_limit)
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $quiz->time_limit }} min
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 font-mono bg-gray-50 px-3 py-1 rounded-lg">{{ $quiz->quiz_code }}</span>
                                    @auth
                                        <a href="{{ route('user.quizzes.enter-code') }}" class="text-primary hover:text-primary/80 font-semibold flex items-center">
                                            Take Quiz
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    @else
                                        <a href="{{ route('login') }}" class="text-primary hover:text-primary/80 font-semibold flex items-center">
                                            Login to Take
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Progress indicators -->
            <div class="flex justify-center mt-8 space-x-2">
                @for($i = 0; $i < min($recentQuizzes->count(), 6); $i++)
                    <div class="w-2 h-2 bg-gray-300 rounded-full quiz-indicator transition-all {{ $i === 0 ? 'bg-primary w-8' : '' }}" data-index="{{ $i }}"></div>
                @endfor
            </div>
        </div>
    </div>
</section>
@endif

<!-- Rankings Section -->
<section class="py-12 sm:py-16 md:py-20 lg:py-24 bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8 sm:mb-12 md:mb-16">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mb-3 sm:mb-4 px-4">Platform Insights</h2>
            <p class="text-base sm:text-lg md:text-xl text-gray-600 px-4">See how our community is performing</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8 sm:mb-12">
            <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-lg border border-yellow-100 hover-lift">
                <div class="text-2xl sm:text-3xl mb-2 sm:mb-3">🏆</div>
                <p class="text-xs sm:text-sm font-semibold text-gray-600 mb-1">Top Student</p>
                <p class="text-lg sm:text-xl font-bold text-gray-900">
                    @if($topStudents->count() > 0)
                        {{ Str::limit($topStudents->first()->name, 20) }}
                    @else
                        N/A
                    @endif
                </p>
            </div>

            <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-lg border border-green-100 hover-lift">
                <div class="text-2xl sm:text-3xl mb-2 sm:mb-3">🏫</div>
                <p class="text-xs sm:text-sm font-semibold text-gray-600 mb-1">Top University</p>
                <p class="text-lg sm:text-xl font-bold text-gray-900">
                    @if($universityRanking->count() > 0)
                        {{ Str::limit($universityRanking->first()->name, 20) }}
                    @else
                        N/A
                    @endif
                </p>
            </div>

            <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-lg border border-blue-100 hover-lift">
                <div class="text-2xl sm:text-3xl mb-2 sm:mb-3">🔥</div>
                <p class="text-xs sm:text-sm font-semibold text-gray-600 mb-1">Popular Quiz</p>
                <p class="text-lg sm:text-xl font-bold text-gray-900">
                    @if($quizPopularity->count() > 0)
                        {{ Str::limit($quizPopularity->first()->title, 20) }}
                    @else
                        N/A
                    @endif
                </p>
            </div>

            <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-lg border border-purple-100 hover-lift">
                <div class="text-2xl sm:text-3xl mb-2 sm:mb-3">🎯</div>
                <p class="text-xs sm:text-sm font-semibold text-gray-600 mb-1">Best Performance</p>
                <p class="text-lg sm:text-xl font-bold text-gray-900">
                    @if($quizPerformance->count() > 0)
                        {{ number_format($quizPerformance->first()->average_score, 1) }} avg
                    @else
                        N/A
                    @endif
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
            <!-- Top Students -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-lg border border-gray-100">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6 flex items-center">
                    <span class="mr-2 sm:mr-3 text-xl sm:text-2xl">🏆</span>
                    Top Students
                </h3>
                <div class="space-y-3 sm:space-y-4">
                    @forelse($topStudents as $index => $student)
                        <div class="flex items-center justify-between p-3 sm:p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-3 sm:space-x-4">
                                    <div class="flex-shrink-0">
                                        @if($index === 0)
                                            <span class="text-2xl sm:text-3xl">🥇</span>
                                        @elseif($index === 1)
                                            <span class="text-2xl sm:text-3xl">🥈</span>
                                        @elseif($index === 2)
                                            <span class="text-2xl sm:text-3xl">🥉</span>
                                        @else
                                            <span class="w-8 h-8 sm:w-10 sm:h-10 bg-primary/10 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold text-primary">
                                                {{ $index + 1 }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-gray-900 text-sm sm:text-base truncate">{{ $student->name }}</p>
                                        <p class="text-xs sm:text-sm text-gray-500 truncate">
                                        @if($student->university)
                                            {{ $student->university->name }}
                                        @else
                                            No university
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 ml-2">
                                <p class="text-lg sm:text-xl font-bold text-primary">{{ $student->total_score }}</p>
                                <p class="text-xs text-gray-500">points</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-8">No quiz attempts yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Top Universities -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-lg border border-gray-100">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6 flex items-center">
                    <span class="mr-2 sm:mr-3 text-xl sm:text-2xl">🏫</span>
                    Top Universities
                </h3>
                <div class="space-y-3 sm:space-y-4">
                    @forelse($universityRanking as $index => $university)
                        <div class="flex items-center justify-between p-3 sm:p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-3 sm:space-x-4 min-w-0 flex-1">
                                <div class="flex-shrink-0">
                                    @if($index === 0)
                                        <span class="text-2xl sm:text-3xl">🏆</span>
                                    @elseif($index === 1)
                                        <span class="text-2xl sm:text-3xl">🥈</span>
                                    @elseif($index === 2)
                                        <span class="text-2xl sm:text-3xl">🥉</span>
                                    @else
                                        <span class="w-8 h-8 sm:w-10 sm:h-10 bg-green-100 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold text-green-700">
                                            {{ $index + 1 }}
                                        </span>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-gray-900 text-sm sm:text-base truncate">{{ $university->name }}</p>
                                    @if($university->location)
                                        <p class="text-xs sm:text-sm text-gray-500 truncate">{{ $university->location }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 ml-2">
                                <p class="text-lg sm:text-xl font-bold text-green-600">{{ $university->users_count }}</p>
                                <p class="text-xs text-gray-500">students</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-8">No universities with students yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quiz carousel
    const carousel = document.getElementById('quiz-carousel');
    const indicators = document.querySelectorAll('.quiz-indicator');

    if (carousel && indicators.length > 0) {
        const totalQuizzes = {{ $recentQuizzes->count() }};
        const cardWidth = 384; // w-96 = 24rem = 384px
        const gap = 24; // space-x-6 = 1.5rem = 24px
        let currentPosition = 0;
        let autoScrollInterval;

        function updateCarousel() {
            const translateX = -currentPosition * (cardWidth + gap);
            carousel.style.transform = `translateX(${translateX}px)`;

            indicators.forEach((indicator, index) => {
                indicator.classList.remove('bg-primary', 'w-8');
                indicator.classList.add('bg-gray-300', 'w-2');
            });

            const activeIndicator = currentPosition % indicators.length;
            if (indicators[activeIndicator]) {
                indicators[activeIndicator].classList.remove('bg-gray-300', 'w-2');
                indicators[activeIndicator].classList.add('bg-primary', 'w-8');
            }
        }

        function startAutoScroll() {
            if (autoScrollInterval) clearInterval(autoScrollInterval);
            autoScrollInterval = setInterval(() => {
                currentPosition++;
                if (currentPosition >= totalQuizzes) {
                    carousel.style.transition = 'none';
                    currentPosition = 0;
                    updateCarousel();
                    setTimeout(() => {
                        carousel.style.transition = 'transform 1s ease-in-out';
                    }, 50);
                } else {
                    updateCarousel();
                }
            }, 3000);
        }

        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                currentPosition = index;
                updateCarousel();
            });
        });

        updateCarousel();
        startAutoScroll();
    }

    // Laptop Animation on Scroll - Open all 3 laptops
    const laptopsContainer = document.getElementById('laptops-container');
    const laptopScreens = document.querySelectorAll('.laptop-screen');
    let laptopsOpened = false;

    if (laptopsContainer && laptopScreens.length > 0) {
        const laptopsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !laptopsOpened) {
                    // Open all laptops with staggered animation
                    laptopScreens.forEach((screen, index) => {
                        setTimeout(() => {
                            screen.style.transform = 'rotateX(0deg)';
                        }, 500 + (index * 150)); // Stagger the opening
                    });
                    laptopsOpened = true;
                }
            });
        }, {
            threshold: 0.3,
            rootMargin: '0px 0px -100px 0px'
        });

        laptopsObserver.observe(laptopsContainer);
    }

    // Auto carousel for projects (if more than 5 projects)
    const projectsCarousel = document.getElementById('projects-carousel');
    const carouselDots = document.querySelectorAll('.carousel-dot');
    let currentSlide = 0;
    let carouselInterval = null;
    const totalSlides = carouselDots.length;

    if (projectsCarousel) {
        // Open all laptop screens immediately in carousel (always do this)
        const carouselLaptopScreens = projectsCarousel.querySelectorAll('.laptop-screen');
        carouselLaptopScreens.forEach((screen) => {
            screen.style.transform = 'rotateX(0deg)';
        });

        // Initialize carousel position
        projectsCarousel.style.transform = 'translateX(0%)';

        // Calculate projects per slide based on screen size (define once)
        const getProjectsPerSlide = () => {
            if (window.innerWidth >= 768) {
                return 3; // Desktop: 3 projects per slide
            } else if (window.innerWidth >= 640) {
                return 2; // Tablet: 2 projects per slide
            } else {
                return 1; // Mobile: 1 project per slide
            }
        };

        // Only proceed with carousel logic if there are multiple slides
        if (totalSlides > 1) {
            function updateCarousel() {
                const projectsPerSlide = getProjectsPerSlide();
                const slideWidth = 100 / projectsPerSlide; // Each slide is 100% / projectsPerSlide of viewport
                const translateX = -(currentSlide * slideWidth);
                projectsCarousel.style.transform = `translateX(${translateX}%)`;

                // Update dots based on current screen size
                const maxSlides = Math.ceil(projectsCarousel.children.length / projectsPerSlide);
                carouselDots.forEach((dot, index) => {
                    if (index < maxSlides) {
                        dot.style.display = '';
                        if (index === currentSlide) {
                            dot.classList.add('bg-primary', 'w-8');
                            dot.classList.remove('bg-gray-300');
                        } else {
                            dot.classList.remove('bg-primary', 'w-8');
                            dot.classList.add('bg-gray-300');
                        }
                    } else {
                        dot.style.display = 'none';
                    }
                });
            }

        function nextSlide() {
            const projectsPerSlide = getProjectsPerSlide();
            const maxSlides = Math.ceil(projectsCarousel.children.length / projectsPerSlide);
            currentSlide = (currentSlide + 1) % maxSlides;
            updateCarousel();
        }

        function goToSlide(slideIndex) {
            currentSlide = slideIndex;
            updateCarousel();
            // Reset auto-play timer
            clearInterval(carouselInterval);
            startCarousel();
        }

        function startCarousel() {
            carouselInterval = setInterval(nextSlide, 4000); // Change slide every 4 seconds
        }

        // Add click handlers to dots
        carouselDots.forEach((dot, index) => {
            dot.addEventListener('click', () => goToSlide(index));
        });

        // Initialize carousel on load
        updateCarousel();

        // Start auto carousel
        startCarousel();

        // Handle window resize
        let resizeTimeout;
        const handleResize = () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                currentSlide = 0; // Reset to first slide on resize
                updateCarousel();
            }, 250);
        };
        window.addEventListener('resize', handleResize);

        // Pause on hover
        projectsCarousel.addEventListener('mouseenter', () => {
            clearInterval(carouselInterval);
        });

        projectsCarousel.addEventListener('mouseleave', () => {
            startCarousel();
        });
        }
    }

});
</script>

<style>
/* 3D Transform for laptop screens */
.laptop-screen {
    perspective: 1000px;
    transform-style: preserve-3d;
}

/* Ensure carousel items maintain their width */
.projects-carousel {
    display: flex;
}

.laptop-carousel-item {
    flex-shrink: 0;
}

</style>
@endsection
