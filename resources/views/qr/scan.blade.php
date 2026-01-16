@extends('layouts.landing')

@section('title', 'Employee Information - ' . ($settings['system_name'] ?? 'System'))

@section('styles')
<style>
    .parallax-container {
        perspective: 1000px;
        height: 100vh;
        overflow-x: hidden;
        overflow-y: auto;
    }
    
    .id-card {
        transform-style: preserve-3d;
        transition: transform 0.3s ease;
        position: relative;
    }
    
    .parallax-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
        z-index: -1;
    }
    
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    .id-card-inner {
        transform-style: preserve-3d;
        position: relative;
    }
    
    .id-card-front {
        backface-visibility: hidden;
        transform: rotateY(0deg);
    }
    
    .holographic-effect {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(
            45deg,
            rgba(255, 255, 255, 0.1) 0%,
            transparent 50%,
            rgba(255, 255, 255, 0.1) 100%
        );
        background-size: 200% 200%;
        animation: shimmer 3s infinite;
        pointer-events: none;
        border-radius: 24px;
    }
    
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    
    .floating-elements {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        pointer-events: none;
    }
    
    .floating-circle {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        animation: float 20s infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) translateX(0); }
        25% { transform: translateY(-50px) translateX(30px); }
        50% { transform: translateY(-100px) translateX(-30px); }
        75% { transform: translateY(-50px) translateX(20px); }
    }
    
    .glass-effect {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
    
    .parallax-layer {
        transition: transform 0.1s ease-out;
    }
</style>
@endsection

@section('content')
@if(isset($user) && $user)
<div class="parallax-container" id="parallaxContainer">
    <div class="parallax-bg"></div>
    
    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="floating-circle w-64 h-64" style="top: 10%; left: 10%; animation-delay: 0s;"></div>
        <div class="floating-circle w-48 h-48" style="top: 60%; right: 15%; animation-delay: 2s;"></div>
        <div class="floating-circle w-32 h-32" style="bottom: 20%; left: 20%; animation-delay: 4s;"></div>
        <div class="floating-circle w-40 h-40" style="top: 30%; right: 30%; animation-delay: 6s;"></div>
    </div>
    
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="w-full max-w-2xl">
            <!-- Virtual ID Card -->
            <div class="id-card parallax-layer" id="idCard">
                <div class="id-card-inner">
                    <div class="id-card-front glass-effect rounded-3xl shadow-2xl overflow-hidden relative">
                        <!-- Holographic Effect -->
                        <div class="holographic-effect"></div>
                        
                        <!-- ID Card Header -->
                        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 px-8 py-6 relative overflow-hidden">
                            <div class="absolute inset-0 bg-black opacity-10"></div>
                            <div class="relative z-10 flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <!-- Profile Picture -->
                                    <div class="relative">
                                        @if($user->profile_picture ?? null)
                                            <img src="{{ $user->getProfilePictureUrl() }}" 
                                                 alt="{{ $user->name }}" 
                                                 class="w-24 h-24 rounded-full border-4 border-white shadow-xl object-cover ring-4 ring-white ring-opacity-50">
                                        @else
                                            <div class="w-24 h-24 rounded-full border-4 border-white shadow-xl bg-white flex items-center justify-center ring-4 ring-white ring-opacity-50">
                                                <span class="text-3xl font-bold text-indigo-600">{{ $user->getInitials() }}</span>
                                            </div>
                                        @endif
                                        <!-- Status Indicator -->
                                        <div class="absolute bottom-0 right-0 w-6 h-6 rounded-full border-4 border-white {{ $user->is_active ? 'bg-green-500' : 'bg-red-500' }} shadow-lg"></div>
                                    </div>
                                    
                                    <!-- Name and Title -->
                                    <div class="text-white">
                                        <h1 class="text-2xl font-bold mb-1 drop-shadow-lg">{{ $user->name }}</h1>
                                        <p class="text-sm text-white text-opacity-90 font-medium">
                                            {{ $user->department ? $user->department->name : 'Employee' }}
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- ID Badge Icon -->
                                <div class="text-white opacity-80">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ID Card Body -->
                        <div class="px-8 py-6 bg-white bg-opacity-50">
                            <!-- Employment Status -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between p-4 rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 shadow-sm">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Employment Status</p>
                                            <p class="text-lg font-bold text-green-700">
                                                {{ $user->is_active ? 'Active - Currently Employed' : 'Inactive - Not Employed' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Department Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div class="p-4 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 shadow-sm">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Department</p>
                                            <p class="text-base font-bold text-gray-900">
                                                {{ $user->department ? $user->department->name : 'Not Assigned' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="p-4 rounded-xl bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 shadow-sm">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <div class="w-10 h-10 rounded-lg bg-purple-500 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</p>
                                            <p class="text-base font-bold text-gray-900">
                                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Verification Badge -->
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex items-center justify-center space-x-2 text-sm text-gray-600">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    <span class="font-medium">Verified Employee ID</span>
                                    <span class="text-gray-400">•</span>
                                    <span>{{ $settings['system_name'] ?? 'System' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Decorative Corner Elements -->
                        <div class="absolute top-0 left-0 w-20 h-20 border-t-4 border-l-4 border-white border-opacity-30 rounded-tl-3xl"></div>
                        <div class="absolute top-0 right-0 w-20 h-20 border-t-4 border-r-4 border-white border-opacity-30 rounded-tr-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-20 h-20 border-b-4 border-l-4 border-white border-opacity-30 rounded-bl-3xl"></div>
                        <div class="absolute bottom-0 right-0 w-20 h-20 border-b-4 border-r-4 border-white border-opacity-30 rounded-br-3xl"></div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-8 parallax-layer">
                <p class="text-white text-sm font-medium drop-shadow-lg">
                    {{ $settings['system_name'] ?? 'System' }} - Employee Verification
                </p>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('parallaxContainer');
        const idCard = document.getElementById('idCard');
        const layers = document.querySelectorAll('.parallax-layer');
        
        if (!container || !idCard) return;
        
        // Parallax effect on scroll
        container.addEventListener('scroll', function() {
            const scrolled = container.scrollTop;
            const rate = scrolled * 0.5;
            
            // Apply parallax to ID card
            idCard.style.transform = `translateY(${rate * 0.3}px) rotateX(${rate * 0.01}deg)`;
            
            // Apply different parallax speeds to layers
            layers.forEach((layer, index) => {
                const speed = (index + 1) * 0.1;
                layer.style.transform = `translateY(${rate * speed}px)`;
            });
        });
        
        // Mouse move parallax effect
        container.addEventListener('mousemove', function(e) {
            const rect = container.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 20;
            const rotateY = (centerX - x) / 20;
            
            idCard.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });
        
        // Reset on mouse leave
        container.addEventListener('mouseleave', function() {
            idCard.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)';
        });
    });
</script>
@endsection
@else
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-200">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Employee Not Found</h1>
            <p class="text-gray-600 mb-4">Unable to load employee information.</p>
            <a href="{{ route('landing.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Go to Home
            </a>
        </div>
    </div>
</div>
@endif
@endsection
