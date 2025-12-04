<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Error' }} | {{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        @keyframes floatReverse {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(20px) rotate(-5deg); }
        }
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        @keyframes particleFloat {
            0% { transform: translateY(100vh) translateX(0) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100px) translateX(100px) rotate(360deg); opacity: 0; }
        }
        .parallax-layer {
            will-change: transform;
        }
        .particle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            animation: particleFloat linear infinite;
        }
        .shimmer-bg {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            background-size: 1000px 100%;
            animation: shimmer 3s infinite;
        }
    </style>
</head>
<body class="min-h-screen overflow-hidden relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);">
    <!-- Parallax Background Layers -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <!-- Layer 1: Deep background stars -->
        <div class="parallax-layer absolute inset-0" data-speed="0.2">
            <div class="absolute inset-0 opacity-30">
                <div class="absolute top-10 left-10 w-1 h-1 bg-white rounded-full"></div>
                <div class="absolute top-20 right-20 w-1 h-1 bg-white rounded-full"></div>
                <div class="absolute top-40 left-1/4 w-1 h-1 bg-white rounded-full"></div>
                <div class="absolute top-60 right-1/3 w-1 h-1 bg-white rounded-full"></div>
                <div class="absolute top-80 left-1/2 w-1 h-1 bg-white rounded-full"></div>
                <div class="absolute bottom-20 right-1/4 w-1 h-1 bg-white rounded-full"></div>
                <div class="absolute bottom-40 left-20 w-1 h-1 bg-white rounded-full"></div>
                <div class="absolute bottom-60 right-40 w-1 h-1 bg-white rounded-full"></div>
            </div>
        </div>

        <!-- Layer 2: Floating orbs -->
        <div class="parallax-layer absolute inset-0" data-speed="0.4">
            <div class="absolute top-20 left-10 w-72 h-72 bg-purple-500/20 rounded-full blur-3xl animate-[float_8s_ease-in-out_infinite]"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl animate-[floatReverse_10s_ease-in-out_infinite]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-pink-500/15 rounded-full blur-3xl animate-[float_12s_ease-in-out_infinite]"></div>
        </div>

        <!-- Layer 3: Animated particles -->
        <div class="parallax-layer absolute inset-0" data-speed="0.6" id="particles-container"></div>

        <!-- Layer 4: Gradient mesh -->
        <div class="parallax-layer absolute inset-0" data-speed="0.3">
            <div class="absolute top-0 left-0 w-full h-full opacity-40">
                <div class="absolute top-0 left-0 w-96 h-96 bg-gradient-to-br from-purple-600/30 to-transparent rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-gradient-to-tl from-indigo-600/30 to-transparent rounded-full blur-3xl"></div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
        <div class="w-full max-w-5xl">
            <!-- Glassmorphism Card -->
            <div class="relative backdrop-blur-xl bg-white/5 border border-white/10 rounded-3xl shadow-2xl overflow-hidden shimmer-bg">
                <!-- Inner glow effect -->
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 via-transparent to-indigo-500/10"></div>
                
                <div class="relative p-8 sm:p-12 lg:p-16">
                    <!-- Error Code Display -->
                    <div class="flex flex-col items-center justify-center mb-12">
                        <div class="relative mb-8">
                            <!-- Outer rotating ring -->
                            <div class="absolute inset-0 w-48 h-48 sm:w-64 sm:h-64 mx-auto">
                                <div class="absolute inset-0 border-4 border-dashed border-purple-400/40 rounded-full animate-spin" style="animation-duration: 20s;"></div>
                                <div class="absolute inset-4 border-4 border-dashed border-indigo-400/40 rounded-full animate-spin" style="animation-duration: 15s; animation-direction: reverse;"></div>
                            </div>
                            
                            <!-- Center error code -->
                            <div class="relative w-48 h-48 sm:w-64 sm:h-64 mx-auto flex items-center justify-center">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-600/20 to-indigo-600/20 rounded-full blur-xl"></div>
                                <div class="relative z-10 text-8xl sm:text-9xl font-black text-transparent bg-clip-text bg-gradient-to-br from-purple-400 via-pink-400 to-indigo-400 drop-shadow-2xl">
                                    {{ $code ?? 'ERR' }}
                                </div>
                            </div>
                        </div>

                        <!-- Main Message -->
                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-center text-white mb-4 tracking-tight">
                            <span class="inline-block animate-[float_3s_ease-in-out_infinite]">DILI</span>
                            <span class="inline-block animate-[floatReverse_3s_ease-in-out_infinite] mx-2">NA</span>
                            <span class="inline-block animate-[float_3s_ease-in-out_infinite]">PWD</span>
                            <span class="inline-block animate-[floatReverse_3s_ease-in-out_infinite] mx-2">ANA</span>
                            <span class="inline-block animate-[float_3s_ease-in-out_infinite]">SI</span>
                            <span class="inline-block animate-[floatReverse_3s_ease-in-out_infinite] mx-2">YGAY</span>
                        </h1>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6">
                        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}"
                           class="group relative inline-flex items-center justify-center px-8 py-4 rounded-2xl text-base font-semibold text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 overflow-hidden">
                            <span class="absolute inset-0 bg-white/20 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                            <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            <span class="relative z-10">Go Back</span>
                        </a>
                        <a href="{{ url('/') }}"
                           class="group relative inline-flex items-center justify-center px-8 py-4 rounded-2xl text-base font-semibold text-white bg-white/10 backdrop-blur-sm border-2 border-white/20 hover:bg-white/20 hover:border-white/40 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 overflow-hidden">
                            <span class="absolute inset-0 bg-white/10 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                            <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span class="relative z-10">Go to Dashboard</span>
                        </a>
                    </div>

                    <!-- Footer -->
                    <div class="mt-12 text-center">
                        <p class="text-sm text-white/60">
                            <span class="inline-flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                System Status: Alert
                            </span>
                            <span class="mx-4">•</span>
                            <span class="uppercase tracking-wider">Infosoft Studio</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Parallax effect on scroll/mouse move
        document.addEventListener('mousemove', (e) => {
            const layers = document.querySelectorAll('.parallax-layer');
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;

            layers.forEach((layer, index) => {
                const speed = parseFloat(layer.dataset.speed) || 0.5;
                const x = (mouseX - 0.5) * 50 * speed;
                const y = (mouseY - 0.5) * 50 * speed;
                layer.style.transform = `translate(${x}px, ${y}px)`;
            });
        });

        // Create floating particles
        function createParticle() {
            const container = document.getElementById('particles-container');
            if (!container) return;

            const particle = document.createElement('div');
            particle.className = 'particle';
            const size = Math.random() * 4 + 2;
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.background = `rgba(255, 255, 255, ${Math.random() * 0.5 + 0.2})`;
            particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
            particle.style.animationDelay = Math.random() * 5 + 's';
            
            container.appendChild(particle);

            // Remove particle after animation
            setTimeout(() => {
                particle.remove();
            }, 15000);
        }

        // Create particles periodically
        setInterval(createParticle, 500);
        for (let i = 0; i < 20; i++) {
            setTimeout(createParticle, i * 200);
        }
    </script>
</body>
</html>


