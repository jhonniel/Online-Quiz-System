@extends('layouts.landing')

@section('title', 'Contact - ' . $settings['system_name'])
@section('description', 'Get in touch with us for support and inquiries')

@section('content')
<!-- Hero Section -->
<section class="gradient-bg text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">Contact Us</h1>
            <p class="text-xl text-gray-100">We're here to help and answer any questions you might have</p>
            <a href="{{ url('/login') }}" class="inline-block mt-6 bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">Log in</a>
        </div>
    </div>
</section>

<!-- Contact Content -->
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Contact Information -->
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Get in Touch</h2>
                <p class="text-gray-600 mb-8">
                    Have questions about {{ $settings['system_name'] }}? Need technical support?
                    Want to learn more about our features? We're here to help!
                </p>

                <div class="space-y-6">
                    <!-- Email -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Email Support</h3>
                            <p class="text-gray-600">{{ $settings['contact_email'] ?? 'support@system.com' }}</p>
                            <p class="text-sm text-gray-500">{{ $settings['contact_email_response_time'] ?? 'We typically respond within 24 hours' }}</p>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Phone Support</h3>
                            <p class="text-gray-600">{{ $settings['contact_phone'] ?? '+1 (555) 123-4567' }}</p>
                            <p class="text-sm text-gray-500">{{ $settings['contact_phone_hours'] ?? 'Monday - Friday, 9 AM - 6 PM EST' }}</p>
                        </div>
                    </div>

                    <!-- Live Chat -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Live Chat</h3>
                            <p class="text-gray-600">{{ $settings['contact_live_chat_description'] ?? 'Available on our platform' }}</p>
                            <p class="text-sm text-gray-500">{{ $settings['contact_live_chat_hours'] ?? 'Get instant help while using the system' }}</p>
                        </div>
                    </div>

                    <!-- Address -->
                    @if($settings['contact_address'] ?? '')
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Address</h3>
                            <p class="text-gray-600 whitespace-pre-line">{{ $settings['contact_address'] }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- FAQ Link -->
                @if($settings['contact_faq_url'] ?? '#')
                <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Frequently Asked Questions</h3>
                    <p class="text-gray-600 mb-4">Check out our FAQ section for quick answers to common questions.</p>
                    <a href="{{ $settings['contact_faq_url'] ?? '#' }}" class="text-primary hover:underline font-medium">{{ $settings['contact_faq_text'] ?? 'View FAQ →' }}</a>
                </div>
                @endif
            </div>

            <!-- Contact Form -->
            <div>
                <div class="bg-white shadow-lg rounded-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Send us a Message</h2>

                    @if (session('success'))
                        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Success!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">Please fix the following errors:</span>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @php
                        try {
                            $contactStoreRoute = url('/contact');
                        } catch (Exception $e) {
                            $contactStoreRoute = '/contact';
                        }
                    @endphp
                    <form action="{{ $contactStoreRoute }}" method="POST" class="space-y-6">
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

<!-- Support Hours -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Support Hours</h2>
            <p class="text-xl text-gray-600">We're here when you need us</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Email Support</h3>
                <p class="text-gray-600 mb-2">{{ $settings['contact_email_support_hours'] ?? '24/7 Available' }}</p>
                <p class="text-sm text-gray-500">{{ $settings['contact_email_support_response'] ?? 'Response within 24 hours' }}</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Phone Support</h3>
                <p class="text-gray-600 mb-2">{{ $settings['contact_phone_support_days'] ?? 'Monday - Friday' }}</p>
                <p class="text-sm text-gray-500">{{ $settings['contact_phone_support_time'] ?? '9:00 AM - 6:00 PM EST' }}</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Live Chat</h3>
                <p class="text-gray-600 mb-2">{{ $settings['contact_live_chat_days'] ?? 'Monday - Friday' }}</p>
                <p class="text-sm text-gray-500">{{ $settings['contact_live_chat_time'] ?? '10:00 AM - 5:00 PM EST' }}</p>
            </div>
        </div>

        <div class="mt-12 text-center">
            <a href="{{ url('/login') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                Log in to your account
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 gradient-bg text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to Get Started?</h2>
        <p class="text-xl mb-8 text-gray-100">Join thousands of users who trust our platform</p>
        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ url('/admin/dashboard') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">Access Admin Panel</a>
            @else
                <a href="{{ url('/dashboard') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">Access Student Portal</a>
            @endif
        @else
            <a href="{{ url('/login') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">Login Now</a>
        @endauth
    </div>
</section>
@endsection
