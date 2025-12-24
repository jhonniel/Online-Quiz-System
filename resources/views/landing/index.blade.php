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

<!-- Statistics Section -->
<section class="py-12 sm:py-16 md:py-20 lg:py-24 bg-white" id="statistics-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-8 md:gap-12">
            <!-- Clients Count -->
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-primary/10 rounded-full mb-4 sm:mb-6">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="text-4xl sm:text-5xl md:text-6xl font-bold text-primary mb-2 sm:mb-3" id="statistics-clients-count" data-target="{{ $statisticsClientsCount }}">
                    0
                </div>
                <h3 class="text-lg sm:text-xl md:text-2xl font-semibold text-gray-900">Clients</h3>
            </div>

            <!-- Projects Count -->
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-primary/10 rounded-full mb-4 sm:mb-6">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div class="text-4xl sm:text-5xl md:text-6xl font-bold text-primary mb-2 sm:mb-3" id="statistics-projects-count" data-target="{{ $statisticsProjectsCount }}">
                    0
                </div>
                <h3 class="text-lg sm:text-xl md:text-2xl font-semibold text-gray-900">Projects</h3>
            </div>

            <!-- LGUs Count -->
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-primary/10 rounded-full mb-4 sm:mb-6">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <div class="text-4xl sm:text-5xl md:text-6xl font-bold text-primary mb-2 sm:mb-3" id="statistics-lgus-count" data-target="{{ $statisticsLgusCount }}">
                    0
                </div>
                <h3 class="text-lg sm:text-xl md:text-2xl font-semibold text-gray-900">LGUs</h3>
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

                // Debug: Log project data
                \Log::info('Projects for landing page', [
                    'total_projects' => count($projects),
                    'valid_projects' => $totalProjects,
                    'projects_data' => array_map(function($p) {
                        return [
                            'name' => $p['name'] ?? 'N/A',
                            'has_image' => !empty($p['image']),
                            'has_image_url' => !empty($p['image_url']),
                            'image_url' => $p['image_url'] ?? 'null'
                        ];
                    }, $validProjects)
                ]);
            @endphp

            @if($hasCarousel)
                <!-- Carousel wrapper for more than 5 projects -->
                <div class="relative overflow-hidden">
                    <div class="projects-carousel flex transition-transform duration-700 ease-in-out" id="projects-carousel" style="transform: translateX(0); visibility: visible !important;">
                        @foreach($validProjects as $index => $project)
                            <div class="laptop-carousel-item flex-shrink-0 w-full sm:w-1/2 md:w-1/3 px-2 sm:px-3 md:px-4" data-project-index="{{ $index }}" style="visibility: visible !important; display: block !important;">
                                <div class="relative mx-auto w-full max-w-[240px] sm:max-w-[280px] md:max-w-[300px]">
                                    <!-- Laptop Image Frame -->
                                    <div class="relative w-full laptop-frame-container" style="aspect-ratio: 16/10; background-image: url('{{ $laptopImageUrl }}'); background-size: contain; background-repeat: no-repeat; background-position: center; z-index: 20 !important;">
                                        <!-- Project Image and Overlay - positioned INSIDE laptop screen area -->
                                        <div class="absolute laptop-screen-area" style="top: 8.5%; left: 15.69%; right: 15.69%; bottom: 20.67%; flex-shrink: 0; z-index: 10; pointer-events: none; background: transparent !important; background-color: transparent !important;">
                                            <div class="laptop-screen-content" style="background-color: transparent !important; background: transparent !important; position: relative; width: 100%; height: 100%; flex-shrink: 0; overflow: hidden; min-width: 1px; min-height: 1px; z-index: 10;">
                                                @php
                                                    // Use the image_url provided by the controller (already uses proxy)
                                                    $imageUrl = !empty($project['image_url']) && $project['image_url'] !== null && $project['image_url'] !== '' ? trim($project['image_url']) : null;
                                                    // Trust the controller - if image_url exists, use it
                                                    $hasImage = !empty($imageUrl);
                                                @endphp
                                                @if($hasImage && $imageUrl)
                                                    <!-- Debug: Show image URL in console -->
                                                    <script>
                                                        console.log('🖼️ Project Image Setup:', {
                                                            project: {!! json_encode($project['name']) !!},
                                                            imageUrl: {!! json_encode($imageUrl) !!},
                                                            hasImage: true,
                                                            imageElement: 'will be created',
                                                            rawImageUrl: {!! json_encode($imageUrl) !!},
                                                            imageUrlLength: {{ strlen($imageUrl ?? '') }},
                                                            isValidUrl: {{ filter_var($imageUrl, FILTER_VALIDATE_URL) ? 'true' : 'false' }},
                                                            isProxyUrl: {{ strpos($imageUrl, url('/image-proxy/')) === 0 ? 'true' : 'false' }}
                                                        });
                                                    </script>
                                                    <!-- Project image - INSIDE the laptop screen -->
                                                    <div class="laptop-project-image-wrapper"
                                                         data-image-url="{{ $imageUrl }}"
                                                         data-project-name="{{ $project['name'] }}"
                                                         style="position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-image: url('{{ addslashes($imageUrl) }}') !important; background-size: cover !important; background-position: center !important; background-repeat: no-repeat !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 90% !important; min-height: 100% !important;">
                                                        <img src="{{ $imageUrl }}"
                                                             alt="{{ $project['name'] }}"
                                                             class="laptop-project-image"
                                                             data-project-name="{{ $project['name'] }}"
                                                             data-image-url="{{ $imageUrl }}"
                                                             style="width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;"
                                                             loading="eager"
                                                             decoding="async"
                                                             crossorigin="anonymous"
                                                             referrerpolicy="no-referrer"
                                                             onload='(function(img){console.log("✅ Image loaded successfully:", {!! json_encode($project['name']) !!}, "URL:", img.src, "Size:", img.naturalWidth + "x" + img.naturalHeight); img.classList.add("image-loaded"); img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;"; const wrapper = img.parentElement; if(wrapper && wrapper.classList.contains("laptop-project-image-wrapper")){ wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;"; } const screenContent = img.closest(".laptop-screen-content"); if(screenContent){ screenContent.style.setProperty("background-color", "transparent", "important"); screenContent.style.setProperty("background", "transparent", "important"); } setTimeout(function(){img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;"; if(wrapper && wrapper.classList.contains("laptop-project-image-wrapper")){wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;";}}, 100);})(this);'
                                                         onerror='console.error("❌ Image failed to load:", {!! json_encode($project['name']) !!}, "URL:", this.src, "Error:", event); const imgUrl = this.getAttribute("data-image-url"); if(imgUrl && imgUrl !== this.src) { setTimeout(() => { this.src = imgUrl; this.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;"; }, 500); } const wrapper = this.parentElement; if(wrapper && wrapper.classList.contains("laptop-project-image-wrapper")) { wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;"; } const screenContent = this.closest(".laptop-screen-content"); const fallback = screenContent?.querySelector(".fallback-content"); if(fallback) { fallback.style.setProperty("display", "flex", "important"); fallback.style.setProperty("visibility", "visible", "important"); fallback.style.setProperty("opacity", "1", "important"); fallback.classList.remove("hidden"); } else if(screenContent) { screenContent.style.setProperty("background", "linear-gradient(to bottom right, rgba(99, 102, 241, 0.2), rgba(99, 102, 241, 0.4))", "important"); }'>
                                                    </div>
                                                    <div class="w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center fallback-content" style="position: absolute !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important; z-index: 1 !important; display: none !important; visibility: hidden !important;">
                                                        <div class="text-center p-3">
                                                            <h3 class="text-sm font-bold text-gray-900 mb-1">{{ $project['name'] }}</h3>
                                                            <p class="text-gray-700 text-xs line-clamp-2">{{ $project['description'] }}</p>
                                                        </div>
                                                    </div>
                                                @else
                                                    <!-- Debug: Show why image is not being displayed -->
                                                    <script>
                                                        console.warn('⚠️ Project Image Skipped:', {
                                                            project: {!! json_encode($project['name']) !!},
                                                            imageUrl: {!! json_encode($imageUrl ?? 'null') !!},
                                                            hasImage: false,
                                                            reason: {!! json_encode(
                                                                empty($imageUrl) ? 'imageUrl is empty' :
                                                                ($imageUrl === '/' ? 'imageUrl is just "/"' :
                                                                ($imageUrl === url('/') ? 'imageUrl is base URL' :
                                                                (strlen($imageUrl ?? '') <= 5 ? 'imageUrl too short (' . strlen($imageUrl ?? '') . ' chars)' : 'unknown')))
                                                            ) !!}
                                                        });
                                                    </script>
                                                    <div class="w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center">
                                                        <div class="text-center p-3">
                                                            <h3 class="text-sm font-bold text-gray-900 mb-1">{{ $project['name'] }}</h3>
                                                            <p class="text-gray-700 text-xs line-clamp-2">{{ $project['description'] }}</p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <!-- Overlay with project info - always visible, positioned on left side INSIDE screen -->
                                            <div class="project-overlay absolute inset-0 flex items-center justify-start z-4" style="top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; pointer-events: none; background: transparent !important; background-color: transparent !important; z-index: 13 !important;">
                                                <div class="p-2 sm:p-4 md:p-5 pointer-events-auto flex flex-col items-start justify-start text-left" style="background: transparent !important; background-color: transparent !important; max-width: 90%;">
                                                    <h3 class="text-xs sm:text-base md:text-lg lg:text-xl font-bold text-white mb-2 sm:mb-3 md:mb-4 leading-tight drop-shadow-lg break-words" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5); word-wrap: break-word; overflow-wrap: break-word; hyphens: auto; max-width: 100%; margin-left: 9% !important;">{{ $project['name'] }}</h3>
                                                    <a href="{{ $project['url'] && $project['url'] !== '#' ? $project['url'] : '#' }}"
                                                       {{ $project['url'] && $project['url'] !== '#' ? 'target="_blank"' : '' }}
                                                       class="inline-flex items-center justify-start px-2.5 py-1 sm:px-3.5 sm:py-1.5 md:px-4 md:py-2 text-xs sm:text-sm bg-white text-primary rounded-lg font-semibold hover:bg-gray-100 transition-all whitespace-nowrap shadow-lg" style="margin-left: 9% !important;">
                                                        Visit Site
                                                        <svg class="w-4 h-4 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Laptop frame border overlay - appears on top -->
                                        <div class="absolute inset-0 laptop-frame-overlay" style="background-image: url('{{ $laptopImageUrl }}'); background-size: contain; background-repeat: no-repeat; background-position: center; z-index: 20 !important; pointer-events: none;"></div>
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
                            <div class="relative mx-auto w-full max-w-[240px] sm:max-w-[280px] md:max-w-[300px]">
                                <!-- Laptop Image Frame -->
                                <div class="relative w-full laptop-frame-container" style="aspect-ratio: 16/10; background-image: url('{{ $laptopImageUrl }}'); background-size: contain; background-repeat: no-repeat; background-position: center; z-index: 20 !important;">
                                    <!-- Project Image and Overlay - positioned INSIDE laptop screen area -->
                                    <div class="absolute laptop-screen-area" style="flex-shrink: 0; z-index: 10; pointer-events: none; background: transparent !important; background-color: transparent !important;">
                                        <div class="laptop-screen-content" style="background-color: transparent !important; background: transparent !important; position: relative; width: 100%; height: 100%; flex-shrink: 0; overflow: hidden; min-width: 1px; min-height: 1px; z-index: 10;">
                                            @php
                                                // Use the image_url provided by the controller (already uses proxy)
                                                $imageUrl = !empty($project['image_url']) && $project['image_url'] !== null && $project['image_url'] !== '' ? trim($project['image_url']) : null;
                                                // Trust the controller - if image_url exists, use it
                                                $hasImage = !empty($imageUrl);
                                            @endphp
                                            @if($hasImage && $imageUrl)
                                                <!-- Debug: Show image URL in console -->
                                                <script>
                                                    console.log('🖼️ Project Image Setup (Static):', {
                                                        project: {!! json_encode($project['name']) !!},
                                                        imageUrl: {!! json_encode($imageUrl) !!},
                                                        hasImage: true,
                                                        rawImageUrl: {!! json_encode($imageUrl) !!},
                                                        imageUrlLength: {{ strlen($imageUrl ?? '') }},
                                                        isValidUrl: {{ filter_var($imageUrl, FILTER_VALIDATE_URL) ? 'true' : 'false' }},
                                                        isProxyUrl: {{ strpos($imageUrl, url('/image-proxy/')) === 0 ? 'true' : 'false' }}
                                                    });
                                                </script>
                                                <!-- Project image - INSIDE the laptop screen -->
                                                <div class="laptop-project-image-wrapper"
                                                     data-image-url="{{ $imageUrl }}"
                                                     data-project-name="{{ $project['name'] }}"
                                                     style="position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-image: url('{{ addslashes($imageUrl) }}') !important; background-size: cover !important; background-position: center !important; background-repeat: no-repeat !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 90% !important; min-height: 100% !important;">
                                                    <img src="{{ $imageUrl }}"
                                                         alt="{{ $project['name'] }}"
                                                         class="laptop-project-image"
                                                         data-project-name="{{ $project['name'] }}"
                                                         data-image-url="{{ $imageUrl }}"
                                                         style="width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;"
                                                         loading="eager"
                                                         decoding="async"
                                                         crossorigin="anonymous"
                                                         referrerpolicy="no-referrer"
                                                         onload='(function(img){console.log("✅ Image loaded successfully:", {!! json_encode($project['name']) !!}, "URL:", img.src, "Size:", img.naturalWidth + "x" + img.naturalHeight); img.classList.add("image-loaded"); img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;"; const wrapper = img.parentElement; if(wrapper && wrapper.classList.contains("laptop-project-image-wrapper")){ wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;"; } const screenContent = img.closest(".laptop-screen-content"); if(screenContent){ screenContent.style.setProperty("background-color", "transparent", "important"); screenContent.style.setProperty("background", "transparent", "important"); } setTimeout(function(){img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;"; if(wrapper && wrapper.classList.contains("laptop-project-image-wrapper")){wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;";}}, 100);})(this);'
                                                         onerror='console.error("❌ Image failed to load:", {!! json_encode($project['name']) !!}, "URL:", this.src, "Error:", event); const imgUrl = this.getAttribute("data-image-url") || {!! json_encode($imageUrl) !!}; if(imgUrl && imgUrl !== this.src) { setTimeout(() => { this.src = imgUrl; this.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;"; }, 500); } const wrapper = this.parentElement; if(wrapper && wrapper.classList.contains("laptop-project-image-wrapper")) { const bgImgUrl = imgUrl || {!! json_encode($imageUrl) !!}; wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-image: url(" + bgImgUrl + ") !important; background-size: cover !important; background-position: center !important; background-repeat: no-repeat !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 90% !important; min-height: 100% !important;"; } const fallback = this.closest(".laptop-screen-content")?.querySelector(".fallback-content"); if(fallback) { fallback.style.setProperty("display", "flex", "important"); fallback.classList.remove("hidden"); }'>
                                                </div>
                                                <div class="w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 items-center justify-center hidden fallback-content" style="display: none;">
                                                    <div class="text-center p-3">
                                                        <h3 class="text-sm font-bold text-gray-900 mb-1">{{ $project['name'] }}</h3>
                                                        <p class="text-gray-700 text-xs line-clamp-2">{{ $project['description'] }}</p>
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Debug: Show why image is not being displayed -->
                                                <script>
                                                    console.warn('⚠️ Project Image Skipped (Static):', {
                                                        project: {!! json_encode($project['name']) !!},
                                                        imageUrl: {!! json_encode($imageUrl ?? 'null') !!},
                                                        hasImage: false,
                                                        reason: {!! json_encode(
                                                            empty($imageUrl) ? 'imageUrl is empty' :
                                                            ($imageUrl === '/' ? 'imageUrl is just "/"' :
                                                            ($imageUrl === url('/') ? 'imageUrl is base URL' :
                                                            (strlen($imageUrl ?? '') <= 5 ? 'imageUrl too short (' . strlen($imageUrl ?? '') . ' chars)' : 'unknown')))
                                                        ) !!}
                                                    });
                                                </script>
                                                <div class="w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center">
                                                    <div class="text-center p-3">
                                                        <h3 class="text-sm font-bold text-gray-900 mb-1">{{ $project['name'] }}</h3>
                                                        <p class="text-gray-700 text-xs line-clamp-2">{{ $project['description'] }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <!-- Overlay with project info - always visible, positioned on left side INSIDE screen -->
                                            <div class="project-overlay absolute inset-0 flex items-center justify-start z-4" style="top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; pointer-events: none; background: transparent !important; background-color: transparent !important; z-index: 13 !important;">
                                                <div class="p-2 sm:p-4 md:p-5 pointer-events-auto flex flex-col items-start justify-start text-left" style="background: transparent !important; background-color: transparent !important; max-width: 90%;">
                                                <h3 class="text-xs sm:text-base md:text-lg lg:text-xl font-bold text-white mb-2 sm:mb-3 md:mb-4 leading-tight drop-shadow-lg break-words" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5); word-wrap: break-word; overflow-wrap: break-word; hyphens: auto; max-width: 100%; margin-left: 9% !important;">{{ $project['name'] }}</h3>
                                                <a href="{{ $project['url'] && $project['url'] !== '#' ? $project['url'] : '#' }}"
                                                   {{ $project['url'] && $project['url'] !== '#' ? 'target="_blank"' : '' }}
                                                   class="inline-flex items-center justify-start px-2.5 py-1 sm:px-3.5 sm:py-1.5 md:px-4 md:py-2 text-xs sm:text-sm bg-white text-primary rounded-lg font-semibold hover:bg-gray-100 transition-all whitespace-nowrap shadow-lg" style="margin-left: 9% !important;">
                                                    Visit Site
                                                    <svg class="w-4 h-4 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                        <!-- Laptop frame border overlay - appears on top -->
                                        <div class="absolute inset-0 laptop-frame-overlay" style="background-image: url('{{ $laptopImageUrl }}'); background-size: contain; background-repeat: no-repeat; background-position: center; z-index: 20 !important; pointer-events: none;"></div>
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

<!-- Stacks Section -->
@if($stacks->count() > 0)
<section class="py-12 sm:py-16 md:py-20 lg:py-24 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8 sm:mb-12 md:mb-16">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mb-3 sm:mb-4 px-4">Stacks</h2>
            <p class="text-base sm:text-lg md:text-xl text-gray-600 px-4">Technologies and tools we use to build amazing solutions</p>
        </div>

        <div class="stacks-marquee-container relative">
            <div class="stacks-marquee-wrapper">
                <div class="stacks-marquee" id="stacks-marquee">
                    @foreach($stacks as $stack)
                        <div class="stack-item flex items-center justify-center p-2 sm:p-4 md:p-6 mx-1 sm:mx-2 md:mx-4">
                            @if($stack->image)
                                <img src="{{ $stack->image_url }}" alt="{{ $stack->name }}" class="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 object-contain grayscale hover:grayscale-0 transition-all duration-300">
                            @elseif($stack->icon)
                                @if(filter_var($stack->icon, FILTER_VALIDATE_URL))
                                    <img src="{{ $stack->icon }}" alt="{{ $stack->name }}" class="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 object-contain grayscale hover:grayscale-0 transition-all duration-300">
                                @else
                                    <div class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl text-gray-400 hover:text-gray-700 transition-colors duration-300">
                                        <i class="{{ $stack->icon }}"></i>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.stacks-marquee-container {
    width: 100%;
    overflow: hidden;
    position: relative;
    padding: 0;
}

.stacks-marquee-wrapper {
    width: 100%;
    overflow: hidden;
    mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
    -webkit-mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
}

@media (max-width: 640px) {
    .stacks-marquee-wrapper {
        mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
        -webkit-mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
    }
}

.stacks-marquee {
    display: flex;
    will-change: transform;
    width: max-content;
}

.stacks-marquee:hover {
    animation-play-state: paused !important;
}

.stack-item {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const marquee = document.getElementById('stacks-marquee');
    if (!marquee) return;

    // Store original content
    const originalContent = marquee.innerHTML;

    // Clone content multiple times to ensure seamless infinite loop
    // We need enough duplicates so content is always visible on both ends
    marquee.innerHTML = originalContent + originalContent + originalContent + originalContent;

    // Wait for layout to calculate widths properly
    requestAnimationFrame(function() {
        requestAnimationFrame(function() {
            // Get all items
            const allItems = marquee.querySelectorAll('.stack-item');
            if (allItems.length === 0) return;

            // Calculate the width of one complete set
            const itemsPerSet = allItems.length / 4; // We have 4 sets now
            let singleSetWidth = 0;

            // Sum up widths of all items in the first set
            for (let i = 0; i < itemsPerSet; i++) {
                const item = allItems[i];
                const rect = item.getBoundingClientRect();
                const styles = window.getComputedStyle(item);
                const marginLeft = parseFloat(styles.marginLeft) || 0;
                const marginRight = parseFloat(styles.marginRight) || 0;
                singleSetWidth += rect.width + marginLeft + marginRight;
            }

            // Calculate animation duration
            // Speed: pixels per second (lower = faster, higher = slower)
            const speed = 50;
            const duration = singleSetWidth / speed;

            // Create keyframes for continuous loop
            // Move exactly one set width, then seamlessly loop
            const style = document.createElement('style');
            style.id = 'stacks-marquee-keyframes';
            style.textContent = `
                @keyframes scroll-stacks {
                    0% {
                        transform: translateX(0);
                    }
                    100% {
                        transform: translateX(calc(-100% / 4));
                    }
                }
            `;

            // Remove old keyframes if exists
            const oldStyle = document.getElementById('stacks-marquee-keyframes');
            if (oldStyle) {
                oldStyle.remove();
            }
            document.head.appendChild(style);

            // Apply animation - continuous loop without reset
            marquee.style.animation = `scroll-stacks ${duration}s linear infinite`;
            marquee.style.animationTimingFunction = 'linear';
        });
    });
});
</script>
@endif

<!-- Contact Us Section -->
<section class="py-12 sm:py-16 md:py-20 lg:py-24 bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8 sm:mb-12 md:mb-16">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mb-3 sm:mb-4 px-4">Contact Us</h2>
            <p class="text-base sm:text-lg md:text-xl text-gray-600 px-4">We're here to help and answer any questions you might have</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 sm:gap-12">
            <!-- Contact Information -->
            <div>
                <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4 sm:mb-6">Get in Touch</h3>
                <p class="text-gray-600 mb-6 sm:mb-8">
                    Have questions about {{ $settings['system_name'] }}? Need technical support?
                    Want to learn more about our features? We're here to help!
                </p>

                <div class="space-y-4 sm:space-y-6">
                    <!-- Email -->
                    <div class="flex items-start space-x-3 sm:space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">Email Support</h4>
                            <p class="text-gray-600">{{ $settings['contact_email'] ?? 'support@system.com' }}</p>
                            <p class="text-sm text-gray-500">{{ $settings['contact_email_response_time'] ?? 'We typically respond within 24 hours' }}</p>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="flex items-start space-x-3 sm:space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">Phone Support</h4>
                            <p class="text-gray-600">{{ $settings['contact_phone'] ?? '+1 (555) 123-4567' }}</p>
                            <p class="text-sm text-gray-500">{{ $settings['contact_phone_hours'] ?? 'Monday - Friday, 9 AM - 6 PM EST' }}</p>
                        </div>
                    </div>

                    <!-- Live Chat -->
                    <div class="flex items-start space-x-3 sm:space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">Live Chat</h4>
                            <p class="text-gray-600">{{ $settings['contact_live_chat_description'] ?? 'Available on our platform' }}</p>
                            <p class="text-sm text-gray-500">{{ $settings['contact_live_chat_hours'] ?? 'Get instant help while using the system' }}</p>
                        </div>
                    </div>
                </div>

                <!-- FAQ Link -->
                @if($settings['contact_faq_url'] ?? '#')
                <div class="mt-6 sm:mt-8 p-4 sm:p-6 bg-white rounded-lg shadow-sm">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Frequently Asked Questions</h4>
                    <p class="text-gray-600 mb-4">Check out our FAQ section for quick answers to common questions.</p>
                    <a href="{{ $settings['contact_faq_url'] ?? '#' }}" class="text-primary hover:underline font-medium">{{ $settings['contact_faq_text'] ?? 'View FAQ →' }}</a>
                </div>
                @endif
            </div>

            <!-- Contact Form -->
            <div>
                <div class="bg-white shadow-lg rounded-lg p-6 sm:p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4 sm:mb-6">Send us a Message</h3>

                    @if (session('success'))
                        <div class="mb-4 sm:mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Success!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 sm:mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">Please fix the following errors:</span>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('landing.contact.store') }}" method="POST" class="space-y-4 sm:space-y-6">
                        @csrf

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" id="name" name="name" required
                                   value="{{ old('name') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" id="email" name="email" required
                                   value="{{ old('email') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                            <input type="text" id="subject" name="subject" required
                                   value="{{ old('subject') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                                   placeholder="Brief description of your inquiry">
                            @error('subject')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea id="message" name="message" rows="4" required
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                                      placeholder="Please describe your question or issue in detail...">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit"
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-opacity">
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Adjust laptop screen area to fit the actual laptop image size
    function adjustLaptopScreenArea() {
        const laptopFrames = document.querySelectorAll('.laptop-frame-container');

        laptopFrames.forEach((frame) => {
            const laptopImage = frame.querySelector('.laptop-frame-image');
            const screenArea = frame.querySelector('.laptop-screen-area');

            if (!screenArea) return;

            const adjustPosition = () => {
                const containerWidth = frame.offsetWidth;
                const containerHeight = frame.offsetHeight;

                if (containerWidth === 0 || containerHeight === 0) return;

                // If no laptop image, screen area fills the entire container
                if (!laptopImage) {
                    screenArea.style.top = '0%';
                    screenArea.style.left = '0%';
                    screenArea.style.right = '0%';
                    screenArea.style.bottom = '0%';
                    screenArea.style.width = '100%';
                    screenArea.style.height = '100%';
                    return;
                }

                // Get natural image dimensions
                const naturalWidth = laptopImage.naturalWidth;
                const naturalHeight = laptopImage.naturalHeight;

                if (naturalWidth === 0 || naturalHeight === 0) return;

                // Calculate displayed image size (object-contain behavior)
                const imgAspectRatio = naturalWidth / naturalHeight;
                const containerAspectRatio = containerWidth / containerHeight;

                let displayedWidth, displayedHeight, offsetX, offsetY;

                if (imgAspectRatio > containerAspectRatio) {
                    // Image is wider - container width determines size
                    displayedWidth = containerWidth;
                    displayedHeight = displayedWidth / imgAspectRatio;
                    offsetX = 0;
                    offsetY = (containerHeight - displayedHeight) / 2;
                } else {
                    // Image is taller - container height determines size
                    displayedHeight = containerHeight;
                    displayedWidth = displayedHeight * imgAspectRatio;
                    offsetX = (containerWidth - displayedWidth) / 2;
                    offsetY = 0;
                }

                // Screen area percentages (adjusted for 612x360 laptop.png with 420x255 screen)
                // Laptop image: 612x360 pixels
                // Screen area: 420x255 pixels
                // Precise calculations:
                // - Horizontal bezels: (612 - 420) / 2 = 96px on each side
                //   Left bezel: 96 / 612 = 15.686% ≈ 15.69%
                //   Right bezel: same = 15.69%
                // - Vertical: Screen height is 255px out of 360px total
                //   Screen takes: 255 / 360 = 70.833% of height
                //   Remaining for bezels: 29.167%
                //   Assuming screen is positioned with bezels above and keyboard below:
                //   Top bezel: ~8.5% (approximately 30.6px)
                //   Bottom (keyboard): ~20.67% (approximately 74.4px)
                const screenTopPercent = 8.5;      // Top bezel: ~30.6px / 360px
                const screenLeftPercent = 15.69;   // Left bezel: 96px / 612px = 15.686%
                const screenRightPercent = 15.69;  // Right bezel: 96px / 612px = 15.686%
                const screenBottomPercent = 20.67;  // Bottom (keyboard): ~74.4px / 360px

                // Calculate screen area position relative to container
                const screenTop = offsetY + (displayedHeight * screenTopPercent / 100);
                const screenLeft = offsetX + (displayedWidth * screenLeftPercent / 100);
                const screenRight = containerWidth - (offsetX + displayedWidth * (100 - screenRightPercent) / 100);
                const screenBottom = containerHeight - (offsetY + displayedHeight * (100 - screenBottomPercent) / 100);

                // Ensure screen area doesn't exceed container bounds
                const finalTop = Math.max(0, screenTop);
                const finalLeft = Math.max(0, screenLeft);
                const finalRight = Math.min(containerWidth, screenRight);
                const finalBottom = Math.min(containerHeight, screenBottom);

                // Apply positions as percentages (browser will calculate dimensions automatically)
                screenArea.style.top = (finalTop / containerHeight * 100) + '%';
                screenArea.style.left = (finalLeft / containerWidth * 100) + '%';
                screenArea.style.right = ((containerWidth - finalRight) / containerWidth * 100) + '%';
                screenArea.style.bottom = ((containerHeight - finalBottom) / containerHeight * 100) + '%';

                // Remove any explicit width/height to let percentages work
                screenArea.style.width = '';
                screenArea.style.height = '';
                screenArea.style.minWidth = '';
                screenArea.style.minHeight = '';
                screenArea.style.maxWidth = '';
                screenArea.style.maxHeight = '';

                // Keep overflow hidden to contain content INSIDE screen
                screenArea.style.overflow = 'hidden';
                screenArea.style.boxSizing = 'border-box';
                screenArea.style.zIndex = '10';
                screenArea.style.pointerEvents = 'none';
                screenArea.style.background = 'transparent';
                screenArea.style.backgroundColor = 'transparent';

                // Get screen area dimensions after positioning
                const screenAreaRect = screenArea.getBoundingClientRect();

                // Ensure screen content is visible and fills the screen area
                const screenContent = screenArea.querySelector('.laptop-screen-content');
                if (screenContent) {
                    // First ensure screen area has dimensions
                    if (screenAreaRect.width === 0 || screenAreaRect.height === 0) {
                        console.error('❌ Screen area has zero dimensions!', {
                            width: screenAreaRect.width,
                            height: screenAreaRect.height,
                            containerWidth: containerWidth,
                            containerHeight: containerHeight,
                            top: screenArea.style.top,
                            left: screenArea.style.left,
                            right: screenArea.style.right,
                            bottom: screenArea.style.bottom
                        });
                    }

                    screenContent.style.width = '100%';
                    screenContent.style.height = '100%';
                    screenContent.style.minWidth = '100%';
                    screenContent.style.minHeight = '100%';
                    screenContent.style.display = 'block';
                    screenContent.style.visibility = 'visible';
                    screenContent.style.opacity = '1';
                    screenContent.style.position = 'relative';
                    screenContent.style.boxSizing = 'border-box';
                    screenContent.style.overflow = 'hidden';
                    screenContent.style.zIndex = '2';
                    screenContent.style.pointerEvents = 'auto';
                    screenContent.style.background = 'transparent';
                    screenContent.style.backgroundColor = 'transparent';
                    // Ensure it has minimum dimensions
                    screenContent.style.minWidth = '1px';
                    screenContent.style.minHeight = '1px';

                    // Check screen content dimensions
                    const screenContentRect = screenContent.getBoundingClientRect();
                    console.log('📐 Screen dimensions:', {
                        screenArea: {
                            width: screenAreaRect.width,
                            height: screenAreaRect.height,
                            top: screenAreaRect.top,
                            left: screenAreaRect.left
                        },
                        screenContent: {
                            width: screenContentRect.width,
                            height: screenContentRect.height,
                            offsetWidth: screenContent.offsetWidth,
                            offsetHeight: screenContent.offsetHeight
                        }
                    });

                    // Ensure project image is visible and fills container
                    // Try both the image wrapper and the image itself
                    const imageWrapper = screenContent.querySelector('.laptop-project-image-wrapper');
                    const projectImage = screenContent.querySelector('.laptop-project-image');

                    if (imageWrapper) {
                        const bgImage = imageWrapper.getAttribute('data-image-url') || imageWrapper.style.backgroundImage;
                        const computedStyle = window.getComputedStyle(imageWrapper);
                        const wrapperRect = imageWrapper.getBoundingClientRect();
                        console.log('🖼️ Image wrapper found:', {
                            hasBackgroundImage: !!bgImage,
                            backgroundImage: bgImage,
                            dataUrl: imageWrapper.getAttribute('data-image-url'),
                            display: computedStyle.display,
                            visibility: computedStyle.visibility,
                            opacity: computedStyle.opacity,
                            zIndex: computedStyle.zIndex,
                            width: computedStyle.width,
                            height: computedStyle.height,
                            actualWidth: wrapperRect.width,
                            actualHeight: wrapperRect.height,
                            parentWidth: screenContent.offsetWidth,
                            parentHeight: screenContent.offsetHeight,
                            computedBgImage: computedStyle.backgroundImage
                        });

                        // Force wrapper to be visible using cssText
                        imageWrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;";
                        imageWrapper.style.setProperty('top', '7%', 'important');
                        imageWrapper.style.setProperty('left', '9%', 'important');
                        imageWrapper.style.setProperty('width', '82%', 'important');
                        imageWrapper.style.setProperty('height', '84%', 'important');
                        imageWrapper.style.setProperty('background-color', 'transparent', 'important');

                        // Ensure parent screen-content stays transparent
                        if (screenContent) {
                            screenContent.style.setProperty('background-color', 'transparent', 'important');
                            screenContent.style.setProperty('background', 'transparent', 'important');
                        }

                        // Ensure background image is set
                        const dataUrl = imageWrapper.getAttribute('data-image-url');
                        if (dataUrl) {
                            // Escape the URL properly for CSS
                            // Wrap URL in quotes to handle special characters
                            const escapedUrl = dataUrl.replace(/'/g, "\\'").replace(/"/g, '\\"');
                            imageWrapper.style.setProperty('background-image', `url("${escapedUrl}")`, 'important');
                            imageWrapper.style.setProperty('background-size', 'cover', 'important');
                            imageWrapper.style.setProperty('background-position', 'center', 'important');
                            imageWrapper.style.setProperty('background-repeat', 'no-repeat', 'important');
                            imageWrapper.style.setProperty('background-color', 'transparent', 'important');

                            // Verify it was set
                            const afterComputed = window.getComputedStyle(imageWrapper);
                            console.log('🖼️ Set background image on wrapper:', {
                                url: dataUrl,
                                escapedUrl: escapedUrl,
                                inlineBg: imageWrapper.style.backgroundImage,
                                computedBg: afterComputed.backgroundImage,
                                isSet: afterComputed.backgroundImage !== 'none' && afterComputed.backgroundImage.includes('url')
                            });

                            // Test if image loads by creating a test image
                            const testImg = new Image();
                            testImg.onload = function() {
                                console.log('✅ Background image URL is valid and loads:', dataUrl);
                            };
                            testImg.onerror = function() {
                                console.error('❌ Background image URL failed to load:', dataUrl);
                            };
                            testImg.src = dataUrl;
                        } else {
                            console.warn('⚠️ No data-image-url attribute found on wrapper');
                        }
                    } else {
                        console.warn('⚠️ Image wrapper not found in screen content');
                    }

                    if (projectImage) {
                        const computedStyle = window.getComputedStyle(projectImage);
                        console.log('🔍 Found project image:', {
                            src: projectImage.src,
                            dataUrl: projectImage.getAttribute('data-image-url'),
                            complete: projectImage.complete,
                            naturalWidth: projectImage.naturalWidth,
                            naturalHeight: projectImage.naturalHeight,
                            currentDisplay: computedStyle.display,
                            currentVisibility: computedStyle.visibility,
                            currentOpacity: computedStyle.opacity,
                            currentZIndex: computedStyle.zIndex,
                            parentDisplay: window.getComputedStyle(screenContent).display,
                            parentVisibility: window.getComputedStyle(screenContent).visibility,
                            parentWidth: screenContent.offsetWidth,
                            parentHeight: screenContent.offsetHeight
                        });

                        // If image URL is empty or invalid, log it
                        if (!projectImage.src || projectImage.src === window.location.href || projectImage.src.includes('undefined') || projectImage.src.includes('null')) {
                            console.error('❌ Invalid or missing image URL detected!', {
                                src: projectImage.src,
                                dataUrl: projectImage.getAttribute('data-image-url'),
                                projectName: projectImage.getAttribute('data-project-name')
                            });
                        }

                        // Force image to be visible immediately using cssText
                        projectImage.classList.add('image-loaded');
                        projectImage.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; max-width: none !important; max-height: none !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; right: auto !important; bottom: auto !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; flex-shrink: 0 !important; flex-grow: 0 !important; box-sizing: border-box !important; background-color: transparent !important;";

                        // Check if image URL is valid
                        if (!projectImage.src || projectImage.src === window.location.href) {
                            console.error('❌ Invalid image URL:', projectImage.src);
                        }

                        // Ensure image loads
                        if (!projectImage.complete || projectImage.naturalHeight === 0) {
                            const imgSrc = projectImage.src;
                            if (imgSrc && imgSrc !== window.location.href) {
                                console.log('⏳ Image not loaded yet, forcing reload:', imgSrc);
                                // Force reload
                                projectImage.src = '';
                                setTimeout(() => {
                                    projectImage.src = imgSrc;
                                }, 10);
                            } else {
                                console.error('❌ Invalid image source:', imgSrc);
                            }
                        } else {
                            console.log('✅ Image already loaded:', projectImage.naturalWidth + 'x' + projectImage.naturalHeight);
                        }

                        // Re-apply styles after image loads - ensure it stays visible
                        projectImage.addEventListener('load', () => {
                            console.log('✅ Image load event fired, ensuring visibility');
                            projectImage.classList.add('image-loaded');
                            projectImage.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;";
                            // Ensure parent containers stay transparent and visible
                            if (imageWrapper) {
                                imageWrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;";
                            }
                            if (screenContent) {
                                screenContent.style.setProperty('background-color', 'transparent', 'important');
                                screenContent.style.setProperty('background', 'transparent', 'important');
                            }
                            // Double-check visibility after a short delay using cssText
                            setTimeout(() => {
                                projectImage.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; max-width: none !important; max-height: none !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; right: auto !important; bottom: auto !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; flex-shrink: 0 !important; flex-grow: 0 !important; box-sizing: border-box !important; background-color: transparent !important;";
                                if (imageWrapper) {
                                    imageWrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;";
                                }
                            }, 100);
                        }, { once: true });

                        projectImage.addEventListener('error', (e) => {
                            console.error('❌ Image error event:', {
                                src: projectImage.src,
                                error: e,
                                computedStyle: window.getComputedStyle(projectImage)
                            });
                        }, { once: true });
                    } else {
                        console.warn('⚠️ No project image found in screen content');
                    }
                }

                // Debug: Log screen area info
                if (window.location.search.includes('debug=laptop')) {
                    console.log('Screen Area Adjusted:', {
                        container: `${containerWidth}x${containerHeight}`,
                        screenArea: {
                            top: (finalTop / containerHeight * 100).toFixed(2) + '%',
                            left: (finalLeft / containerWidth * 100).toFixed(2) + '%',
                            right: ((containerWidth - finalRight) / containerWidth * 100).toFixed(2) + '%',
                            bottom: ((containerHeight - finalBottom) / containerHeight * 100).toFixed(2) + '%',
                            width: (finalRight - finalLeft).toFixed(1) + 'px',
                            height: (finalBottom - finalTop).toFixed(1) + 'px'
                        },
                        screenContent: screenContent ? {
                            width: screenContent.offsetWidth + 'px',
                            height: screenContent.offsetHeight + 'px',
                            visible: window.getComputedStyle(screenContent).visibility
                        } : 'not found'
                    });
                }

                // Debug: Log screen area info (remove in production)
                if (window.location.search.includes('debug=laptop')) {
                    console.log('Laptop Screen Area Debug:', {
                        containerSize: `${containerWidth}x${containerHeight}`,
                        imageSize: `${displayedWidth.toFixed(1)}x${displayedHeight.toFixed(1)}`,
                        imageOffset: `${offsetX.toFixed(1)}, ${offsetY.toFixed(1)}`,
                        screenArea: {
                            top: (screenTop / containerHeight * 100).toFixed(2) + '%',
                            left: (screenLeft / containerWidth * 100).toFixed(2) + '%',
                            right: (screenRight / containerWidth * 100).toFixed(2) + '%',
                            bottom: (screenBottom / containerHeight * 100).toFixed(2) + '%'
                        },
                        screenDimensions: {
                            width: (screenRight - screenLeft).toFixed(1) + 'px',
                            height: (screenBottom - screenTop).toFixed(1) + 'px'
                        }
                    });
                }
            };

            const runAdjustment = () => {
                // Wait a bit to ensure all images are loaded
                setTimeout(() => {
                    adjustPosition();
                    // Also re-run after a short delay to catch late-loading images
                    setTimeout(adjustPosition, 100);
                }, 50);
            };

            // If no laptop image, run adjustment immediately
            if (!laptopImage) {
                runAdjustment();
            } else if (laptopImage.complete && laptopImage.naturalWidth > 0) {
                runAdjustment();
            } else {
                laptopImage.addEventListener('load', runAdjustment, { once: true });
                laptopImage.addEventListener('error', () => {
                    console.warn('Laptop image failed to load');
                    runAdjustment(); // Still run adjustment even if laptop image fails
                });
            }

            // Also wait for project images to load
            const projectImages = frame.querySelectorAll('.laptop-project-image');
            let loadedCount = 0;
            const totalImages = projectImages.length;

            if (totalImages > 0) {
                projectImages.forEach(img => {
                    if (img.complete && img.naturalHeight > 0) {
                        loadedCount++;
                    } else {
                        img.addEventListener('load', () => {
                            loadedCount++;
                            if (loadedCount === totalImages) {
                                setTimeout(adjustPosition, 50);
                            }
                        }, { once: true });
                        img.addEventListener('error', () => {
                            loadedCount++;
                            if (loadedCount === totalImages) {
                                setTimeout(adjustPosition, 50);
                            }
                        }, { once: true });
                    }
                });

                // If all images are already loaded
                if (loadedCount === totalImages) {
                    setTimeout(adjustPosition, 50);
                }
            }
        });
    }

    // Run adjustment multiple times to catch all image loads
    const runAdjustmentWithRetry = () => {
        adjustLaptopScreenArea();
        // Retry after delays to catch late-loading images
        setTimeout(() => adjustLaptopScreenArea(), 200);
        setTimeout(() => adjustLaptopScreenArea(), 500);
        setTimeout(() => adjustLaptopScreenArea(), 1000);
    };

    // Run adjustment on load and resize, and also after images load
    window.addEventListener('load', () => {
        runAdjustmentWithRetry();
    });

    // Also adjust when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(runAdjustmentWithRetry, 100);
        });
    } else {
        setTimeout(runAdjustmentWithRetry, 100);
    }

    // Also adjust when project images load
    document.addEventListener('DOMContentLoaded', () => {
        const projectImages = document.querySelectorAll('.laptop-project-image');
        console.log('📸 Found', projectImages.length, 'project image(s) in DOM');

        if (projectImages.length === 0) {
            console.warn('⚠️ No project images found! Check if projects have image_url set.');
        }

        projectImages.forEach((img, index) => {
            const imgUrl = img.getAttribute('data-image-url') || img.src;
            console.log(`📸 Image ${index + 1}:`, {
                src: img.src,
                dataUrl: imgUrl,
                complete: img.complete,
                naturalWidth: img.naturalWidth,
                naturalHeight: img.naturalHeight,
                display: window.getComputedStyle(img).display,
                visibility: window.getComputedStyle(img).visibility,
                opacity: window.getComputedStyle(img).opacity
            });

            // Ensure image URL is set correctly
            if (imgUrl && (!img.src || img.src === window.location.href)) {
                console.log(`🔄 Setting image ${index + 1} src to:`, imgUrl);
                img.src = imgUrl;
            }

            // Force image to be visible using cssText for stronger application
            img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;";
            img.classList.add('image-loaded');

            // Ensure wrapper is visible using cssText
            const wrapper = img.parentElement;
            if (wrapper && wrapper.classList.contains('laptop-project-image-wrapper')) {
                wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;";
            }

            if (img.complete && img.naturalHeight > 0) {
                console.log(`✅ Image ${index + 1} already loaded: ${img.naturalWidth}x${img.naturalHeight}`);
                img.classList.add('image-loaded');
                img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;";
                const wrapper = img.parentElement;
                if (wrapper && wrapper.classList.contains('laptop-project-image-wrapper')) {
                    wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;";
                }
                setTimeout(() => adjustLaptopScreenArea(), 50);
            } else {
                // Preload image to ensure it loads
                const preloadImg = new Image();
                preloadImg.onload = function() {
                    console.log(`✅ Image ${index + 1} preloaded successfully`);
                    img.src = imgUrl;
                    img.style.setProperty('display', 'block', 'important');
                    img.style.setProperty('visibility', 'visible', 'important');
                    img.style.setProperty('opacity', '1', 'important');
                    setTimeout(() => adjustLaptopScreenArea(), 50);
                };
                preloadImg.onerror = function() {
                    console.error(`❌ Image ${index + 1} preload failed:`, imgUrl);
                };
                if (imgUrl) {
                    preloadImg.src = imgUrl;
                }

                img.addEventListener('load', () => {
                    console.log(`✅ Image ${index + 1} loaded via event: ${img.naturalWidth}x${img.naturalHeight}`);
                    img.classList.add('image-loaded');
                    img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;";
                    const wrapper = img.parentElement;
                    if (wrapper && wrapper.classList.contains('laptop-project-image-wrapper')) {
                        wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;";
                    }
                    setTimeout(() => {
                        img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;";
                        adjustLaptopScreenArea();
                    }, 50);
                }, { once: true });
                img.addEventListener('error', (e) => {
                    console.error(`❌ Image ${index + 1} failed to load:`, {
                        src: img.src,
                        dataUrl: imgUrl,
                        error: e
                    });
                    // Retry loading after a delay
                    if (imgUrl && img.src !== imgUrl) {
                        setTimeout(() => {
                            console.log(`🔄 Retrying image ${index + 1}...`);
                            img.src = imgUrl;
                        }, 1000);
                    }
                    setTimeout(() => adjustLaptopScreenArea(), 50);
                }, { once: true });
            }
        });
    });

    // Force all images to load and be visible - ALWAYS show ALL images
    function forceImageVisibility() {
        const allImages = document.querySelectorAll('.laptop-project-image');
        allImages.forEach((img, index) => {
            const imgUrl = img.getAttribute('data-image-url') || img.src;
            if (imgUrl && imgUrl !== window.location.href && !imgUrl.includes('undefined') && !imgUrl.includes('null')) {
                // Always ensure image src is set
                if (!img.src || img.src === window.location.href || img.src.includes('undefined') || img.src.includes('null')) {
                    img.src = imgUrl;
                }

                // ALWAYS force visibility using cssText - apply to ALL images
                img.classList.add('image-loaded');
                img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;";

                // ALWAYS ensure wrapper is visible
                const wrapper = img.parentElement;
                if (wrapper && wrapper.classList.contains('laptop-project-image-wrapper')) {
                    wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;";
                }

                // Ensure parent containers are visible
                const carouselItem = img.closest('.laptop-carousel-item');
                if (carouselItem) {
                    carouselItem.style.setProperty('visibility', 'visible', 'important');
                    carouselItem.style.setProperty('display', 'block', 'important');
                }

                // Ensure screen content is transparent and visible
                const screenContent = img.closest('.laptop-screen-content');
                if (screenContent) {
                    screenContent.style.setProperty('background-color', 'transparent', 'important');
                    screenContent.style.setProperty('background', 'transparent', 'important');
                    screenContent.style.setProperty('visibility', 'visible', 'important');
                }

                // If image hasn't loaded, try to load it
                if (!img.complete || img.naturalHeight === 0) {
                    const testImg = new Image();
                    testImg.onload = function() {
                        img.src = imgUrl;
                        img.classList.add('image-loaded');
                        img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;";
                        const wrapper = img.parentElement;
                        if (wrapper && wrapper.classList.contains('laptop-project-image-wrapper')) {
                            wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;";
                        }
                        const carouselItem = img.closest('.laptop-carousel-item');
                        if (carouselItem) {
                            carouselItem.style.setProperty('visibility', 'visible', 'important');
                        }
                        console.log(`🔄 Forced image ${index + 1} to load:`, imgUrl);
                    };
                    testImg.onerror = function() {
                        console.error(`❌ Failed to force load image ${index + 1}:`, imgUrl);
                    };
                    testImg.src = imgUrl;
                }
            }
        });
    }

    // Monitor and ensure images stay visible - ALWAYS show all images
    function monitorImageVisibility() {
        const allImages = document.querySelectorAll('.laptop-project-image');
        allImages.forEach((img) => {
            // ALWAYS ensure images are visible if they have a valid src or data-image-url
            const imgUrl = img.getAttribute('data-image-url') || img.src;
            if (imgUrl && imgUrl !== window.location.href && !imgUrl.includes('undefined') && !imgUrl.includes('null')) {
                // Ensure src is set
                if (!img.src || img.src === window.location.href || img.src.includes('undefined') || img.src.includes('null')) {
                    img.src = imgUrl;
                }

                const computedStyle = window.getComputedStyle(img);
                const wrapper = img.parentElement;

                // ALWAYS force visibility - don't check, just apply
                img.classList.add('image-loaded');
                img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;";

                // ALWAYS ensure wrapper is visible
                if (wrapper && wrapper.classList.contains('laptop-project-image-wrapper')) {
                    wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;";
                }

                // Ensure parent containers are visible
                const carouselItem = img.closest('.laptop-carousel-item');
                if (carouselItem) {
                    carouselItem.style.setProperty('visibility', 'visible', 'important');
                }

                const screenContent = img.closest('.laptop-screen-content');
                if (screenContent) {
                    screenContent.style.setProperty('background-color', 'transparent', 'important');
                    screenContent.style.setProperty('background', 'transparent', 'important');
                    screenContent.style.setProperty('visibility', 'visible', 'important');
                }
            }
        });
    }

    // Run force visibility on load and after delays
    window.addEventListener('load', () => {
        setTimeout(forceImageVisibility, 100);
        setTimeout(forceImageVisibility, 500);
        setTimeout(forceImageVisibility, 1000);
        setTimeout(forceImageVisibility, 2000);

        // Monitor images periodically - very frequent checks to ensure they always show
        setInterval(monitorImageVisibility, 300); // Check every 300ms
        setInterval(forceImageVisibility, 500); // Force visibility every 500ms

        // Ultimate protection: Prevent any hiding attempts
        setInterval(() => {
            const allImages = document.querySelectorAll('.laptop-project-image');
            allImages.forEach((img) => {
                const computedStyle = window.getComputedStyle(img);
                // If image is hidden by any means, immediately restore
                if (computedStyle.display === 'none' ||
                    computedStyle.visibility === 'hidden' ||
                    computedStyle.opacity === '0' ||
                    img.classList.contains('hidden')) {
                    console.warn('🛡️ Ultimate protection: Restoring hidden image');
                    img.classList.remove('hidden');
                    img.classList.add('image-loaded');
                    img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;";
                    const wrapper = img.parentElement;
                    if (wrapper && wrapper.classList.contains('laptop-project-image-wrapper')) {
                        wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;";
                    }
                }
            });
        }, 200); // Check every 200ms for ultimate protection
    });

    // Also monitor on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(monitorImageVisibility, 500);
        setTimeout(monitorImageVisibility, 1500);
        setTimeout(monitorImageVisibility, 3000);

        // Watch for images that complete loading and ensure they're visible
        const projectImages = document.querySelectorAll('.laptop-project-image');
        projectImages.forEach((img) => {
            // Watch for src changes and style changes
            const imageObserver = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes') {
                        const img = mutation.target;
                        if (mutation.attributeName === 'src') {
                            if (img.classList.contains('laptop-project-image') && img.complete && img.naturalHeight > 0) {
                                console.log('🔄 Image src changed and loaded, ensuring visibility');
                                img.classList.add('image-loaded');
                                img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;";
                                const wrapper = img.parentElement;
                                if (wrapper && wrapper.classList.contains('laptop-project-image-wrapper')) {
                                    wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;";
                                }
                            }
                        } else if (mutation.attributeName === 'style') {
                            // ALWAYS prevent hiding - check and restore immediately
                            const computedStyle = window.getComputedStyle(img);
                            const styleValue = img.getAttribute('style') || '';

                            // Check if style contains hiding properties
                            if (styleValue.includes('display: none') ||
                                styleValue.includes('display:none') ||
                                styleValue.includes('visibility: hidden') ||
                                styleValue.includes('visibility:hidden') ||
                                styleValue.includes('opacity: 0') ||
                                styleValue.includes('opacity:0') ||
                                computedStyle.display === 'none' ||
                                computedStyle.visibility === 'hidden' ||
                                computedStyle.opacity === '0') {
                                console.warn('⚠️ Attempt to hide image detected, preventing and restoring visibility');
                                img.classList.add('image-loaded');
                                img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;";
                                const wrapper = img.parentElement;
                                if (wrapper && wrapper.classList.contains('laptop-project-image-wrapper')) {
                                    wrapper.style.cssText = "position: absolute !important; top: 7% !important; left: 9% !important; width: 82% !important; height: 84% !important; z-index: 11 !important; background-color: transparent !important; display: block !important; visibility: visible !important; opacity: 1 !important; min-width: 82% !important; min-height: 84% !important;";
                                }
                            }
                        } else if (mutation.attributeName === 'class') {
                            // Prevent hidden class from being added
                            if (img.classList.contains('hidden')) {
                                console.warn('⚠️ Hidden class detected, removing it');
                                img.classList.remove('hidden');
                                img.classList.add('image-loaded');
                                img.style.cssText = "width: 100% !important; height: 100% !important; min-width: 100% !important; min-height: 100% !important; object-fit: cover !important; object-position: center center !important; display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; z-index: 12 !important; visibility: visible !important; opacity: 1 !important; background-color: transparent !important;";
                            }
                        }
                    }
                });
            });
            // Watch for src, style, and class changes - prevent any hiding
            imageObserver.observe(img, {
                attributes: true,
                attributeFilter: ['src', 'style', 'class'],
                attributeOldValue: true
            });

            // Override style property setters to prevent hiding
            const originalSetProperty = img.style.setProperty.bind(img.style);
            img.style.setProperty = function(property, value, priority) {
                if ((property === 'display' && value === 'none') ||
                    (property === 'visibility' && value === 'hidden') ||
                    (property === 'opacity' && (value === '0' || value === 0))) {
                    console.warn('🚫 Blocked attempt to hide image via setProperty:', property, value);
                    return; // Block the hiding attempt
                }
                return originalSetProperty(property, value, priority);
            };

            // Override cssText setter to prevent hiding
            Object.defineProperty(img.style, 'cssText', {
                get: function() {
                    return this._cssText || '';
                },
                set: function(value) {
                    if (value && (
                        value.includes('display: none') ||
                        value.includes('display:none') ||
                        value.includes('visibility: hidden') ||
                        value.includes('visibility:hidden') ||
                        value.includes('opacity: 0') ||
                        value.includes('opacity:0')
                    )) {
                        console.warn('🚫 Blocked attempt to hide image via cssText');
                        // Remove hiding properties and set the rest
                        const cleaned = value
                            .replace(/display\s*:\s*none[^;]*;?/gi, 'display: block !important;')
                            .replace(/visibility\s*:\s*hidden[^;]*;?/gi, 'visibility: visible !important;')
                            .replace(/opacity\s*:\s*0[^;]*;?/gi, 'opacity: 1 !important;');
                        this._cssText = cleaned;
                        img.setAttribute('style', cleaned);
                        return;
                    }
                    this._cssText = value;
                    img.setAttribute('style', value);
                },
                configurable: true
            });
        });
    });

    // Run adjustment on load and resize
    const runAdjustment = () => {
        runAdjustmentWithRetry();
    };

    // Enable debug mode if URL has ?debug=laptop
    if (window.location.search.includes('debug=laptop')) {
        document.documentElement.setAttribute('data-debug', 'laptop');
        console.log('Laptop screen area debug mode enabled. Check console for positioning details.');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runAdjustment);
    } else {
        runAdjustment();
    }

    window.addEventListener('load', runAdjustment);
    window.addEventListener('resize', runAdjustment);

    // Also adjust when images become visible (for lazy loading)
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                runAdjustment();
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.laptop-frame-container').forEach(frame => {
        observer.observe(frame);
    });

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
        // Clone all items for infinite loop
        const originalItems = Array.from(projectsCarousel.children);
        originalItems.forEach(item => {
            const clone = item.cloneNode(true);
            projectsCarousel.appendChild(clone);
        });

        // Open all laptop screens immediately in carousel (always do this)
        const carouselLaptopScreens = projectsCarousel.querySelectorAll('.laptop-screen');
        carouselLaptopScreens.forEach((screen) => {
            screen.style.transform = 'rotateX(0deg)';
        });

        // Initialize carousel position
        projectsCarousel.style.transform = 'translateX(0%)';

        // Ensure all images in carousel are visible immediately
        setTimeout(() => {
            forceImageVisibility();
            monitorImageVisibility();
        }, 100);

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
            let transitionCheckInterval = null;
            const originalItemCount = originalItems.length;

            function updateCarousel() {
                const projectsPerSlide = getProjectsPerSlide();
                const slideWidth = 100 / projectsPerSlide; // Each slide is 100% / projectsPerSlide of viewport
                const translateX = -(currentSlide * slideWidth);
                projectsCarousel.style.transform = `translateX(${translateX}%)`;

                // Update dots based on current screen size (only show dots for original items)
                const maxSlides = Math.ceil(originalItemCount / projectsPerSlide);
                const displaySlide = currentSlide % maxSlides; // Use modulo for dot display
                carouselDots.forEach((dot, index) => {
                    if (index < maxSlides) {
                        dot.style.display = '';
                        if (index === displaySlide) {
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

                // Clear any existing transition check interval
                if (transitionCheckInterval) {
                    clearInterval(transitionCheckInterval);
                }

                // Force visibility immediately
                forceImageVisibility();

                // Check every 100ms during transition (700ms duration)
                let checks = 0;
                transitionCheckInterval = setInterval(() => {
                    forceImageVisibility();
                    checks++;
                    if (checks >= 8) { // Stop after 800ms (8 * 100ms)
                        clearInterval(transitionCheckInterval);
                        transitionCheckInterval = null;
                    }
                }, 100);

                // Ensure all images stay visible after carousel update
                setTimeout(() => {
                    forceImageVisibility();
                    monitorImageVisibility();
                }, 100);
            }

        function nextSlide() {
            const projectsPerSlide = getProjectsPerSlide();
            const maxSlides = Math.ceil(originalItemCount / projectsPerSlide);
            currentSlide++;

            // If we've reached the end of cloned items, seamlessly jump back to start
            if (currentSlide >= maxSlides * 2) {
                // Disable transition for instant jump back to start
                projectsCarousel.style.transition = 'none';
                currentSlide = 0;
                updateCarousel();
                // Re-enable transition after a brief moment
                setTimeout(() => {
                    projectsCarousel.style.transition = 'transform 0.7s ease-in-out';
                }, 50);
            } else {
                updateCarousel();
            }
        }

        function goToSlide(slideIndex) {
            // Ensure we're in the original items range (0 to maxSlides-1)
            const projectsPerSlide = getProjectsPerSlide();
            const maxSlides = Math.ceil(originalItemCount / projectsPerSlide);
            if (slideIndex < 0) slideIndex = 0;
            if (slideIndex >= maxSlides) slideIndex = maxSlides - 1;
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

        // Ensure images are visible after carousel initialization
        setTimeout(() => {
            forceImageVisibility();
            monitorImageVisibility();
        }, 200);

        // Start auto carousel
        startCarousel();

        // Ensure images stay visible during carousel transitions
        projectsCarousel.addEventListener('transitionstart', () => {
            // Force visibility at start of transition
            forceImageVisibility();
        });

        projectsCarousel.addEventListener('transitionend', () => {
            // Force visibility at end of transition
            forceImageVisibility();
            monitorImageVisibility();
        });

        // Touch swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        let isDragging = false;

        projectsCarousel.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            isDragging = true;
            clearInterval(carouselInterval); // Pause auto-scroll while dragging
        }, { passive: true });

        projectsCarousel.addEventListener('touchmove', (e) => {
            if (isDragging) {
                touchEndX = e.changedTouches[0].screenX;
            }
        }, { passive: true });

        projectsCarousel.addEventListener('touchend', (e) => {
            if (!isDragging) return;
            isDragging = false;

            const swipeThreshold = 50; // Minimum distance for swipe
            const diff = touchStartX - touchEndX;

            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left - next slide
                    nextSlide();
                } else {
                    // Swipe right - previous slide
                    prevSlide();
                }
            }

            // Resume auto-scroll after a delay
            setTimeout(() => {
                startCarousel();
            }, 2000);
        }, { passive: true });

        // Handle window resize
        let resizeTimeout;
        const handleResize = () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                currentSlide = 0; // Reset to first slide on resize
                updateCarousel();
                // Ensure images stay visible after resize
                setTimeout(() => {
                    forceImageVisibility();
                    monitorImageVisibility();
                }, 300);
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

    // Statistics counting animation
    function animateCount(element, target, duration = 2000) {
        const start = 0;
        const startTime = performance.now();
        const targetNum = parseInt(target) || 0;

        function updateCount(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Easing function for smooth animation (ease-out)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(start + (targetNum - start) * easeOut);

            element.textContent = current.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(updateCount);
            } else {
                element.textContent = targetNum.toLocaleString();
            }
        }

        requestAnimationFrame(updateCount);
    }

    // Intersection Observer for statistics section
    const statisticsSection = document.getElementById('statistics-section');
    if (statisticsSection) {
        const clientsCountEl = document.getElementById('statistics-clients-count');
        const projectsCountEl = document.getElementById('statistics-projects-count');
        const lgusCountEl = document.getElementById('statistics-lgus-count');

        let hasAnimated = false;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !hasAnimated) {
                    hasAnimated = true;

                    // Start animations with slight delays for staggered effect
                    if (clientsCountEl) {
                        const target = clientsCountEl.getAttribute('data-target');
                        setTimeout(() => animateCount(clientsCountEl, target, 2000), 100);
                    }

                    if (projectsCountEl) {
                        const target = projectsCountEl.getAttribute('data-target');
                        setTimeout(() => animateCount(projectsCountEl, target, 2000), 300);
                    }

                    if (lgusCountEl) {
                        const target = lgusCountEl.getAttribute('data-target');
                        setTimeout(() => animateCount(lgusCountEl, target, 2000), 500);
                    }

                    // Unobserve after animation starts
                    observer.unobserve(statisticsSection);
                }
            });
        }, {
            threshold: 0.3,
            rootMargin: '0px 0px -50px 0px'
        });

        observer.observe(statisticsSection);
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

/* Ensure images stay visible during carousel transitions and always visible */
.projects-carousel .laptop-project-image {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 12 !important;
    width: 100% !important;
    height: 100% !important;
    min-width: 100% !important;
    min-height: 100% !important;
    object-fit: cover !important;
    object-position: center center !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    background-color: transparent !important;
}

.projects-carousel .laptop-project-image-wrapper {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 11 !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    min-width: 100% !important;
    min-height: 100% !important;
    background-color: transparent !important;
}

/* Ensure all carousel items show their images */
.projects-carousel .laptop-carousel-item {
    visibility: visible !important;
    display: block !important;
}

.projects-carousel .laptop-carousel-item .laptop-project-image,
.projects-carousel .laptop-carousel-item .laptop-project-image-wrapper {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Ensure ALL project images are always visible - applies to both carousel and static */
.laptop-project-image {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 12 !important;
}

/* Prevent hiding via any method */
.laptop-project-image[style*="display: none"],
.laptop-project-image[style*="display:none"],
.laptop-project-image[style*="visibility: hidden"],
.laptop-project-image[style*="visibility:hidden"],
.laptop-project-image[style*="opacity: 0"],
.laptop-project-image[style*="opacity:0"] {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.laptop-project-image-wrapper {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 11 !important;
}

/* Prevent wrapper hiding */
.laptop-project-image-wrapper[style*="display: none"],
.laptop-project-image-wrapper[style*="display:none"],
.laptop-project-image-wrapper[style*="visibility: hidden"],
.laptop-project-image-wrapper[style*="visibility:hidden"],
.laptop-project-image-wrapper[style*="opacity: 0"],
.laptop-project-image-wrapper[style*="opacity:0"] {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Ensure images with image-loaded class are ALWAYS visible */
.laptop-project-image.image-loaded {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 12 !important;
    width: 100% !important;
    height: 100% !important;
    min-width: 100% !important;
    min-height: 100% !important;
    object-fit: cover !important;
    object-position: center center !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    background-color: transparent !important;
}

.laptop-carousel-item {
    flex-shrink: 0;
}

/* Laptop Frame and Screen Positioning */
.laptop-frame-container {
    position: relative;
}

.laptop-frame-image {
    display: block;
    width: 100%;
    height: 100%;
    z-index: 0 !important; /* Behind project images (z-index: 10+) */
    position: relative;
}

.laptop-screen-area {
    /* Default positioning - will be dynamically adjusted by JavaScript */
    /* Typical laptop screen area: top bezel ~6%, sides ~7%, bottom (keyboard) ~22% */
    top: 6%;
    left: 7%;
    right: 7%;
    bottom: 22%;
    position: absolute;
    display: block;
    overflow: hidden; /* Hide overflow to contain content INSIDE screen */
    transition: none; /* Disable transition to prevent shrinking during resize */
    box-sizing: border-box;
    z-index: 10 !important; /* Above laptop frame (z-index: 0) */
    flex-shrink: 0 !important;
    pointer-events: none; /* Allow clicks to pass through to overlay */
    background: transparent !important;
    background-color: transparent !important;
    /* Dimensions will be set by JavaScript */
}

.laptop-screen-content {
    border-radius: 1px;
    position: relative !important;
    overflow: hidden !important; /* Hide overflow to contain content INSIDE screen */
    display: block !important;
    box-sizing: border-box !important;
    z-index: 10 !important; /* Above laptop frame (z-index: 0) */
    background-color: transparent !important;
    background: transparent !important;
    flex-shrink: 0 !important;
    pointer-events: auto; /* Allow interactions with content */
    width: 100% !important;
    height: 100% !important;
    min-width: 1px !important;
    min-height: 1px !important;
    /* Width and height will be set explicitly by JavaScript to prevent shrinking */
}

/* Project overlay - positioned inside screen area, always visible */
.project-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    z-index: 13 !important; /* Above images (image z-index: 12) so overlay shows on top */
    pointer-events: none;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    background: transparent !important;
    background-color: transparent !important;
}

.project-overlay > div {
    pointer-events: auto;
    flex-shrink: 0;
    background: transparent !important;
    background-color: transparent !important;
}

.laptop-project-image-wrapper {
    position: absolute !important;
    top: 0 !important;
    left: 5% !important;
    min-width: 90% !important;
    min-height: 100% !important;
    width: 90% !important;
    height: 100% !important;
    z-index: 11 !important; /* Above screen area (z-index: 10) */
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    background-size: cover !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
    background-color: transparent !important;
    background: transparent !important;
}

/* Ensure wrapper background image shows */
.laptop-screen-content .laptop-project-image-wrapper {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 11 !important; /* Above screen area (z-index: 10) */
    background-color: transparent !important;
    background: transparent !important;
}

.laptop-project-image {
    width: 100% !important;
    height: 100% !important;
    min-width: 100% !important;
    min-height: 100% !important;
    max-width: none !important;
    max-height: none !important;
    object-fit: cover !important;
    object-position: center center !important;
    display: block !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: auto !important;
    bottom: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    z-index: 12 !important; /* Above wrapper (z-index: 11) */
    visibility: visible !important;
    opacity: 1 !important;
    box-sizing: border-box !important;
    flex-shrink: 0 !important;
    background-color: transparent !important;
    background: transparent !important;
}

/* Ensure laptop-screen-content stays transparent */
.laptop-screen-content {
    background-color: transparent !important;
    background: transparent !important;
}

/* Force image to be visible - override any other styles */
.laptop-screen-content .laptop-project-image-wrapper {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 11 !important; /* Above screen area (z-index: 10) */
    background-color: transparent !important;
    background: transparent !important;
}

.laptop-screen-content .laptop-project-image {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 12 !important; /* Above wrapper (z-index: 11) */
    background-color: transparent !important;
    background: transparent !important;
}

/* Ensure images with image-loaded class are always visible */
.laptop-project-image.image-loaded {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 12 !important; /* Above wrapper (z-index: 11) */
    width: 100% !important;
    height: 100% !important;
    min-width: 100% !important;
    min-height: 100% !important;
    object-fit: cover !important;
    object-position: center center !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    background-color: transparent !important;
}

/* Ensure wrapper with loaded image is visible */
.laptop-project-image-wrapper:has(.image-loaded) {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 11 !important;
}

/* Fallback for browsers that don't support :has() */
.laptop-project-image-wrapper .image-loaded {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Ensure fallback content is properly positioned */
.laptop-screen-content .fallback-content {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    z-index: 1 !important;
    background: linear-gradient(to bottom right, rgba(99, 102, 241, 0.2), rgba(99, 102, 241, 0.4)) !important;
}

/* Debug mode: Show screen area border (add ?debug=laptop to URL) */
body:has([data-debug="laptop"]) .laptop-screen-area::before,
html[data-debug="laptop"] .laptop-screen-area::before {
    content: '';
    position: absolute;
    inset: -2px;
    border: 2px solid rgba(255, 0, 0, 0.5);
    pointer-events: none;
    z-index: 1000;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .laptop-screen-area {
        top: 5.5%;
        left: 6.5%;
        right: 6.5%;
        bottom: 21%;
    }

    /* Improve touch targets on mobile */
    .carousel-dot {
        min-width: 24px;
        min-height: 24px;
    }

    /* Better spacing for mobile */
    .laptop-carousel-item {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    /* Improve button sizes for mobile */
    .project-overlay a {
        font-size: 0.75rem;
        padding: 0.5rem 0.75rem;
    }
}

/* Tablet adjustments */
@media (min-width: 641px) and (max-width: 768px) {
    .laptop-carousel-item {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
}

</style>
@endsection
