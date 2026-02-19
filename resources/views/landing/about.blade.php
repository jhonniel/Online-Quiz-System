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

@endsection
