@extends('layouts.landing')

@section('title', 'About - ' . $settings['system_name'])
@section('description', 'Learn more about our online quiz management system')

@section('content')
<!-- Hero Section -->
<section class="gradient-bg text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">{{ $aboutPageHeroTitle ?: 'About ' . $settings['system_name'] }}</h1>
            <p class="text-xl text-gray-100">{{ $aboutPageHeroSubtitle ?: 'Empowering education through technology' }}</p>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-lg mx-auto">
            @if(!empty($aboutPageMission))
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Our Mission</h2>
            <p class="text-gray-600 mb-6">
                {!! nl2br(e($aboutPageMission)) !!}
            </p>
            @endif

            @if(!empty($aboutPageWhatWeOffer))
            <h2 class="text-3xl font-bold text-gray-900 mb-6">What We Offer</h2>
            <p class="text-gray-600 mb-8">
                {!! nl2br(e($aboutPageWhatWeOffer)) !!}
            </p>
            @endif

            @if(!empty($aboutPageFeatures))
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Why Choose Us?</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                @foreach($aboutPageFeatures as $feature)
                    <div class="bg-gray-50 p-6 rounded-lg">
                        @if(!empty($feature['title']))
                            <h3 class="text-xl font-semibold mb-3 text-primary">{{ $feature['title'] }}</h3>
                        @endif
                        @if(!empty($feature['description']))
                            <p class="text-gray-600">{{ $feature['description'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
            @endif

            @if(!empty($aboutPageVision))
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Our Vision</h2>
            <p class="text-gray-600 mb-8">
                {!! nl2br(e($aboutPageVision)) !!}
            </p>
            @endif
        </div>
    </div>
</section>

@if(!empty($aboutPageTeamTitle) || !empty($aboutPageTeamFeatures))
<!-- Team Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            @if(!empty($aboutPageTeamTitle))
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $aboutPageTeamTitle }}</h2>
            @endif
            @if(!empty($aboutPageTeamDescription))
                <p class="text-xl text-gray-600">{{ $aboutPageTeamDescription }}</p>
            @endif
        </div>

        @if(!empty($aboutPageTeamFeatures))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($aboutPageTeamFeatures as $index => $feature)
                <div class="text-center">
                    <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                        @if($index === 0)
                            <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        @elseif($index === 1)
                            <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        @else
                            <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        @endif
                    </div>
                    @if(!empty($feature['title']))
                        <h3 class="text-xl font-semibold mb-2">{{ $feature['title'] }}</h3>
                    @endif
                    @if(!empty($feature['description']))
                        <p class="text-gray-600">{{ $feature['description'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
@endif

@endsection
