@extends('layouts.landing')

@section('title', 'About - ' . $settings['system_name'])
@section('description', 'Learn more about our online quiz management system')

@section('content')
<!-- Hero Section -->
<section class="gradient-bg text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">About {{ $settings['system_name'] }}</h1>
            <p class="text-xl text-gray-100">Empowering education through technology</p>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-lg mx-auto">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Our Mission</h2>
            <p class="text-gray-600 mb-6">
                {{ $settings['system_name'] }} is designed to revolutionize the way educational assessments are conducted.
                We believe that learning should be engaging, accessible, and efficient. Our platform provides educators
                and institutions with powerful tools to create, manage, and deliver quizzes while giving students a
                seamless experience for taking assessments.
            </p>

            <h2 class="text-3xl font-bold text-gray-900 mb-6">What We Offer</h2>
            <p class="text-gray-600 mb-6">
                Our comprehensive quiz management system offers everything needed for modern educational assessment:
            </p>

            <ul class="list-disc list-inside text-gray-600 mb-8 space-y-2">
                <li>Intuitive quiz creation with multiple question types</li>
                <li>Secure access through unique quiz codes</li>
                <li>Real-time assessment and immediate feedback</li>
                <li>Comprehensive user management and analytics</li>
                <li>Customizable branding and system settings</li>
                <li>Mobile-responsive design for any device</li>
            </ul>

            <h2 class="text-3xl font-bold text-gray-900 mb-6">Why Choose Us?</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-3 text-primary">User-Friendly Design</h3>
                    <p class="text-gray-600">Our interface is designed with both educators and students in mind, ensuring a smooth experience for all users.</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-3 text-primary">Secure & Reliable</h3>
                    <p class="text-gray-600">Built with security as a priority, your data and assessments are protected with industry-standard practices.</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-3 text-primary">Scalable Solution</h3>
                    <p class="text-gray-600">Whether you're a small classroom or a large institution, our platform scales to meet your needs.</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-3 text-primary">Continuous Support</h3>
                    <p class="text-gray-600">We're committed to providing ongoing support and regular updates to enhance your experience.</p>
                </div>
            </div>

            <h2 class="text-3xl font-bold text-gray-900 mb-6">Our Vision</h2>
            <p class="text-gray-600 mb-8">
                We envision a future where educational technology seamlessly integrates with learning processes,
                making assessment more effective and engaging. {{ $settings['system_name'] }} is our contribution
                to this vision, providing educators with the tools they need to create meaningful learning experiences
                and helping students achieve their full potential.
            </p>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Built for Education</h2>
            <p class="text-xl text-gray-600">Designed by educators, for educators</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Educational Focus</h3>
                <p class="text-gray-600">Every feature is designed with educational best practices in mind.</p>
            </div>

            <div class="text-center">
                <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Innovation</h3>
                <p class="text-gray-600">Continuously evolving to meet the changing needs of education.</p>
            </div>

            <div class="text-center">
                <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Passion</h3>
                <p class="text-gray-600">Driven by our passion for improving educational outcomes.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 gradient-bg text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Join Our Educational Community</h2>
        <p class="text-xl mb-8 text-gray-100">Be part of the future of educational assessment</p>

        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                    Access Admin Panel
                </a>
            @else
                <a href="{{ route('user.dashboard') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                    Access Student Portal
                </a>
            @endif
        @else
            <a href="{{ route('login') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                Get Started Today
            </a>
        @endauth
    </div>
</section>
@endsection
