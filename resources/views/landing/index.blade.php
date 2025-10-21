@extends('layouts.landing')

@section('title', $settings['system_name'] . ' - Online Quiz Management System')
@section('description', $settings['system_description'])

@section('content')
<!-- Hero Section -->
<section class="gradient-bg text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Welcome to {{ $settings['system_name'] }}
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-gray-100">
                {{ $settings['system_description'] }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                            Admin Dashboard
                        </a>
                    @else
                        <a href="{{ route('user.dashboard') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                            My Dashboard
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        Get Started
                    </a>
                @endauth
                <a href="{{ route('landing.features') }}" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-primary transition-colors">
                    Learn More
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div class="bg-white p-8 rounded-lg shadow-lg counter-card">
                <div class="text-4xl font-bold text-primary mb-2 counter-number" id="quiz-counter">0</div>
                <div class="text-gray-600">Active Quizzes</div>
            </div>
            <div class="bg-white p-8 rounded-lg shadow-lg counter-card">
                <div class="text-4xl font-bold text-primary mb-2 counter-number" id="user-counter">0</div>
                <div class="text-gray-600">Active Users</div>
            </div>
            <div class="bg-white p-8 rounded-lg shadow-lg counter-card">
                <div class="text-4xl font-bold text-primary mb-2 counter-number" id="security-counter">0%</div>
                <div class="text-gray-600">Secure & Reliable</div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Choose Our Platform?</h2>
            <p class="text-xl text-gray-600">Powerful features designed for modern learning and assessment</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Easy Quiz Creation</h3>
                <p class="text-gray-600">Create engaging quizzes with multiple question types including multiple choice, true/false, and text answers.</p>
            </div>

            <!-- Feature 2 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Time Management</h3>
                <p class="text-gray-600">Set time limits for quizzes and track completion times to ensure fair and efficient assessments.</p>
            </div>

            <!-- Feature 3 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Detailed Analytics</h3>
                <p class="text-gray-600">Get comprehensive reports on quiz performance, user engagement, and detailed result analysis.</p>
            </div>

            <!-- Feature 4 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">User Management</h3>
                <p class="text-gray-600">Easily manage users, assign quizzes, and control access with our comprehensive admin panel.</p>
            </div>

            <!-- Feature 5 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Quiz Codes</h3>
                <p class="text-gray-600">Secure quiz access with unique codes, ensuring only authorized users can take specific quizzes.</p>
            </div>

            <!-- Feature 6 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Secure & Reliable</h3>
                <p class="text-gray-600">Built with security in mind, ensuring your data and assessments are protected and reliable.</p>
            </div>
        </div>
    </div>
</section>

<!-- Recent Quizzes Section -->
@if($recentQuizzes->count() > 0)
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Recent Quizzes</h2>
            <p class="text-xl text-gray-600">Check out our latest quiz offerings</p>
        </div>

        <!-- Auto-Scrolling Quiz Carousel -->
        <div class="relative">
            <div class="overflow-hidden">
                <div id="quiz-carousel" class="flex space-x-6 transition-transform duration-1000 ease-in-out" style="width: {{ $recentQuizzes->count() * 2 * 320 }}px;">
                    <!-- First set of quiz cards -->
                    @foreach($recentQuizzes as $quiz)
                        <div class="flex-shrink-0 w-80 bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 quiz-card" data-quiz-id="{{ $quiz->id }}">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-xl font-semibold">{{ $quiz->title }}</h3>
                                    @if($quiz->topic)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $quiz->topic }}
                                        </span>
                                    @endif
                                </div>
                                @if($quiz->description)
                                    <p class="text-gray-600 mb-4">{{ Str::limit($quiz->description, 100) }}</p>
                                @endif
                                <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                    <span>{{ $quiz->total_questions }} questions</span>
                                    @if($quiz->time_limit)
                                        <span>{{ $quiz->time_limit }} minutes</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 font-mono">{{ $quiz->quiz_code }}</span>
                                    @auth
                                        <a href="{{ route('user.quizzes.enter-code') }}" class="text-primary hover:underline font-medium">Take Quiz</a>
                                    @else
                                        <a href="{{ route('login') }}" class="text-primary hover:underline font-medium">Login to Take</a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Duplicate set for seamless looping -->
                    @foreach($recentQuizzes as $quiz)
                        <div class="flex-shrink-0 w-80 bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 quiz-card" data-quiz-id="{{ $quiz->id }}">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-xl font-semibold">{{ $quiz->title }}</h3>
                                    @if($quiz->topic)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $quiz->topic }}
                                        </span>
                                    @endif
                                </div>
                                @if($quiz->description)
                                    <p class="text-gray-600 mb-4">{{ Str::limit($quiz->description, 100) }}</p>
                                @endif
                                <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                    <span>{{ $quiz->total_questions }} questions</span>
                                    @if($quiz->time_limit)
                                        <span>{{ $quiz->time_limit }} minutes</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 font-mono">{{ $quiz->quiz_code }}</span>
                                    @auth
                                        <a href="{{ route('user.quizzes.enter-code') }}" class="text-primary hover:underline font-medium">Take Quiz</a>
                                    @else
                                        <a href="{{ route('login') }}" class="text-primary hover:underline font-medium">Login to Take</a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>


            <!-- Progress indicators -->
            <div class="flex justify-center mt-4 space-x-2">
                @for($i = 0; $i < min($recentQuizzes->count(), 6); $i++)
                    <div class="w-2 h-2 bg-gray-300 rounded-full quiz-indicator {{ $i === 0 ? 'bg-primary' : '' }}" data-index="{{ $i }}"></div>
                @endfor
            </div>
        </div>
    </div>
</section>
@endif

<!-- Rankings & Analytics Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">📊 Rankings & Analytics</h2>
            <p class="text-xl text-gray-600">See how students and universities are performing on our platform</p>
        </div>

        <!-- Ranking Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-lg p-6 text-white text-center">
                <div class="text-3xl mb-2">🏆</div>
                <p class="text-sm font-medium">Top Student</p>
                <p class="text-lg font-bold">
                    @if($topStudents->count() > 0)
                        {{ $topStudents->first()->name }}
                    @else
                        N/A
                    @endif
                </p>
            </div>

            <div class="bg-gradient-to-r from-green-400 to-green-500 rounded-lg p-6 text-white text-center">
                <div class="text-3xl mb-2">🏫</div>
                <p class="text-sm font-medium">Top University</p>
                <p class="text-lg font-bold">
                    @if($universityRanking->count() > 0)
                        {{ Str::limit($universityRanking->first()->name, 15) }}
                    @else
                        N/A
                    @endif
                </p>
            </div>

            <div class="bg-gradient-to-r from-blue-400 to-blue-500 rounded-lg p-6 text-white text-center">
                <div class="text-3xl mb-2">🔥</div>
                <p class="text-sm font-medium">Popular Quiz</p>
                <p class="text-lg font-bold">
                    @if($quizPopularity->count() > 0)
                        {{ Str::limit($quizPopularity->first()->title, 15) }}
                    @else
                        N/A
                    @endif
                </p>
            </div>

            <div class="bg-gradient-to-r from-purple-400 to-purple-500 rounded-lg p-6 text-white text-center">
                <div class="text-3xl mb-2">🎯</div>
                <p class="text-sm font-medium">Best Performance</p>
                <p class="text-lg font-bold">
                    @if($quizPerformance->count() > 0)
                        {{ number_format($quizPerformance->first()->average_score, 1) }} avg
                    @else
                        N/A
                    @endif
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Top Students by Score -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">🏆 Top Students by Total Score</h3>
                <div class="space-y-3">
                    @forelse($topStudents as $index => $student)
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($index === 0)
                                        <span class="text-2xl">🥇</span>
                                    @elseif($index === 1)
                                        <span class="text-2xl">🥈</span>
                                    @elseif($index === 2)
                                        <span class="text-2xl">🥉</span>
                                    @else
                                        <span class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium text-gray-600">
                                            {{ $index + 1 }}
                                        </span>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $student->name }}</p>
                                    <p class="text-sm text-gray-500">
                                        @if($student->university)
                                            {{ $student->university->name }}
                                        @else
                                            No university
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-indigo-600">{{ $student->total_score }} pts</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No quiz attempts yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- University Student Count -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">🏫 Universities by Student Count</h3>
                <div class="space-y-3">
                    @forelse($universityRanking as $index => $university)
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($index === 0)
                                        <span class="text-2xl">🏆</span>
                                    @elseif($index === 1)
                                        <span class="text-2xl">🥈</span>
                                    @elseif($index === 2)
                                        <span class="text-2xl">🥉</span>
                                    @else
                                        <span class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium text-gray-600">
                                            {{ $index + 1 }}
                                        </span>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $university->name }}</p>
                                    @if($university->location)
                                        <p class="text-sm text-gray-500">{{ $university->location }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600">{{ $university->users_count }} students</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No universities with students yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
            <!-- Quiz Popularity -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">📊 Most Popular Quizzes</h3>
                <div class="space-y-3">
                    @forelse($quizPopularity as $index => $quiz)
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($index === 0)
                                        <span class="text-2xl">🔥</span>
                                    @elseif($index === 1)
                                        <span class="text-2xl">⭐</span>
                                    @elseif($index === 2)
                                        <span class="text-2xl">💫</span>
                                    @else
                                        <span class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium text-gray-600">
                                            {{ $index + 1 }}
                                        </span>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $quiz->title }}</p>
                                    <p class="text-sm text-gray-500">{{ $quiz->total_questions }} questions</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-blue-600">{{ $quiz->student_count }} takers</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No quiz attempts yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Quiz Performance -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">🎯 Best Performing Quizzes</h3>
                <div class="space-y-3">
                    @forelse($quizPerformance as $index => $quiz)
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($index === 0)
                                        <span class="text-2xl">🎯</span>
                                    @elseif($index === 1)
                                        <span class="text-2xl">💯</span>
                                    @elseif($index === 2)
                                        <span class="text-2xl">✨</span>
                                    @else
                                        <span class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium text-gray-600">
                                            {{ $index + 1 }}
                                        </span>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $quiz->title }}</p>
                                    <p class="text-sm text-gray-500">{{ $quiz->student_count }} students</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-purple-600">{{ number_format($quiz->average_score, 1) }} avg</p>
                                <p class="text-xs text-gray-500">{{ $quiz->highest_score }} max</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No quiz performance data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 gradient-bg text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to Get Started?</h2>
        <p class="text-xl mb-8 text-gray-100">Join thousands of users who trust our platform for their assessment needs</p>

        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                    Go to Admin Dashboard
                </a>
            @else
                <a href="{{ route('user.dashboard') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                    Go to My Dashboard
                </a>
            @endif
        @else
            <a href="{{ route('login') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                Login Now
            </a>
        @endauth
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('quiz-carousel');
    const indicators = document.querySelectorAll('.quiz-indicator');

    if (!carousel) return; // Exit if carousel doesn't exist

    const totalQuizzes = {{ $recentQuizzes->count() }};
    const cardWidth = 320; // 80 * 4 (w-80 = 20rem = 320px)
    const gap = 24; // space-x-6 = 1.5rem = 24px
    const totalCards = totalQuizzes * 2; // We have duplicated cards

    let currentPosition = 0;
    let autoScrollInterval;

    // Initialize carousel
    function updateCarousel() {
        const translateX = -currentPosition * (cardWidth + gap);
        carousel.style.transform = `translateX(${translateX}px)`;

        // Update indicators
        indicators.forEach((indicator, index) => {
            indicator.classList.remove('bg-primary');
            indicator.classList.add('bg-gray-300');
        });

        const activeIndicator = currentPosition % indicators.length;
        if (indicators[activeIndicator]) {
            indicators[activeIndicator].classList.remove('bg-gray-300');
            indicators[activeIndicator].classList.add('bg-primary');
        }
    }

    // Seamless infinite loop auto-scroll function
    function startAutoScroll() {
        if (autoScrollInterval) clearInterval(autoScrollInterval);

        autoScrollInterval = setInterval(() => {
            currentPosition++;

            // When we reach the end of the first set, reset to beginning seamlessly
            if (currentPosition >= totalQuizzes) {
                // Disable transition temporarily for instant reset
                carousel.style.transition = 'none';
                currentPosition = 0;
                updateCarousel();

                // Re-enable transition after reset
                setTimeout(() => {
                    carousel.style.transition = 'transform 1s ease-in-out';
                }, 50);
            } else {
                updateCarousel();
            }
        }, 3000); // Auto-scroll every 3 seconds
    }

    // Stop auto-scroll
    function stopAutoScroll() {
        if (autoScrollInterval) {
            clearInterval(autoScrollInterval);
            autoScrollInterval = null;
        }
    }

    // Indicator clicks
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            currentPosition = index;
            updateCarousel();
        });
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        updateCarousel();
    });

    // Initialize
    updateCarousel();
    startAutoScroll();

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        stopAutoScroll();
    });

    // Counter Animation
    function animateCounter(elementId, targetValue, duration = 2000, suffix = '') {
        const element = document.getElementById(elementId);
        if (!element) return;

        const startValue = 0;
        const increment = targetValue / (duration / 16); // 60fps
        let currentValue = startValue;

        // Add animating class for scale effect
        element.classList.add('animating');

        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= targetValue) {
                currentValue = targetValue;
                clearInterval(timer);
                // Remove animating class when done
                setTimeout(() => {
                    element.classList.remove('animating');
                }, 300);
            }

            // Format the number
            if (suffix === '%') {
                element.textContent = Math.floor(currentValue) + suffix;
            } else {
                element.textContent = Math.floor(currentValue);
            }
        }, 16);
    }

    // Intersection Observer for counter animation
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Animate cards entrance
                const cards = entry.target.querySelectorAll('.counter-card');
                cards.forEach((card, index) => {
                    setTimeout(() => {
                        card.classList.add('animate-in');
                    }, index * 200); // Stagger animation
                });

                // Start counter animations after cards are visible
                setTimeout(() => {
                    animateCounter('quiz-counter', {{ $totalQuizzes }}, 2000);
                    animateCounter('user-counter', {{ $totalUsers }}, 2000);
                    animateCounter('security-counter', 100, 2000, '%');
                }, 600);

                // Stop observing after animation starts
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe the stats section
    const statsSection = document.querySelector('.py-16.bg-gray-50');
    if (statsSection) {
        observer.observe(statsSection);
    }

    // Also trigger animation on page load if section is already visible
    setTimeout(() => {
        const rect = statsSection.getBoundingClientRect();
        const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
        if (isVisible) {
            // Trigger animation immediately if section is visible
            const cards = statsSection.querySelectorAll('.counter-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('animate-in');
                }, index * 200);
            });

            setTimeout(() => {
                animateCounter('quiz-counter', {{ $totalQuizzes }}, 2000);
                animateCounter('user-counter', {{ $totalUsers }}, 2000);
                animateCounter('security-counter', 100, 2000, '%');
            }, 600);
        }
    }, 100);
});
</script>
@endsection
