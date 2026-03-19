<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media (prefers-reduced-motion: reduce) {
            .floaty, .parallax-layer { animation: none !important; transition: none !important; }
        }

        .floaty {
            animation: floaty 7s ease-in-out infinite;
        }

        .floaty-slow {
            animation: floaty 11s ease-in-out infinite;
        }

        .floaty-fast {
            animation: floaty 5.5s ease-in-out infinite;
        }

        @keyframes floaty {
            0%, 100% { transform: translate3d(0, 0, 0); }
            50% { transform: translate3d(0, -14px, 0); }
        }

        .glow {
            filter: drop-shadow(0 12px 22px rgba(99, 102, 241, 0.35));
        }

        .noise:before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            opacity: 0.04;
            background-image:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='140' height='140' filter='url(%23n)' opacity='.45'/%3E%3C/svg%3E");
            mix-blend-mode: overlay;
        }

        .parallax-layer {
            will-change: transform;
            transition: transform 120ms ease-out;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-950 text-white overflow-hidden noise">
    <div id="parallaxRoot" class="relative min-h-screen flex items-center justify-center px-6 py-12">
        <!-- Ambient blobs -->
        <div class="parallax-layer absolute -top-20 -left-28 h-72 w-72 rounded-full bg-indigo-600/30 blur-3xl floaty-slow"></div>
        <div class="parallax-layer absolute top-1/3 -right-32 h-80 w-80 rounded-full bg-purple-500/25 blur-3xl floaty"></div>
        <div class="parallax-layer absolute -bottom-24 left-1/4 h-72 w-72 rounded-full bg-fuchsia-400/20 blur-3xl floaty-fast"></div>

        <!-- Floating stars -->
        <div class="parallax-layer absolute inset-0 opacity-40">
            <div class="absolute left-[12%] top-[18%] h-1.5 w-1.5 rounded-full bg-white/70 floaty"></div>
            <div class="absolute left-[22%] top-[64%] h-1 w-1 rounded-full bg-white/60 floaty-fast"></div>
            <div class="absolute left-[46%] top-[28%] h-1 w-1 rounded-full bg-white/60 floaty-slow"></div>
            <div class="absolute left-[68%] top-[40%] h-1.5 w-1.5 rounded-full bg-white/70 floaty"></div>
            <div class="absolute left-[82%] top-[22%] h-1 w-1 rounded-full bg-white/60 floaty-fast"></div>
            <div class="absolute left-[78%] top-[76%] h-1.5 w-1.5 rounded-full bg-white/70 floaty-slow"></div>
        </div>

        <!-- Card -->
        <div class="relative w-full max-w-2xl">
            <div class="parallax-layer rounded-3xl border border-white/10 bg-white/5 backdrop-blur-xl shadow-2xl p-8 sm:p-10 glow">
                <div class="flex items-start justify-between gap-6">
                    <div class="space-y-3">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-white/80">
                            <span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>
                            Page not found
                        </div>
                        <h1 class="text-7xl sm:text-8xl md:text-9xl font-black tracking-tight leading-none floaty-slow">
                            404
                        </h1>
                        <p class="text-2xl sm:text-3xl md:text-4xl text-white/90 font-black tracking-tight floaty">
                            Dili na pwd ana si ygay
                        </p>
                        <p class="text-sm sm:text-base text-white/60 leading-relaxed">
                            The page you’re trying to open doesn’t exist or has been moved.
                        </p>
                    </div>
                    <div class="hidden sm:block parallax-layer">
                        <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-indigo-500/30 to-fuchsia-500/20 border border-white/10 flex items-center justify-center floaty">
                            <svg class="h-10 w-10 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <a href="{{ url('/') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-white text-slate-900 px-4 py-2.5 text-sm font-semibold hover:bg-white/90 transition">
                        Go to Home
                    </a>
                    <button type="button"
                            onclick="window.history.length > 1 ? history.back() : (window.location.href='{{ url('/') }}')"
                            class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10 transition">
                        Go Back
                    </button>
                </div>
            </div>

            <div class="mt-4 text-center text-xs text-white/40">
                Tip: move your mouse for parallax.
            </div>
        </div>
    </div>

    <script>
        (function () {
            const root = document.getElementById('parallaxRoot');
            if (!root) return;

            const layers = Array.from(root.querySelectorAll('.parallax-layer'));
            if (!layers.length) return;

            const clamp = (v, min, max) => Math.min(max, Math.max(min, v));
            const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (prefersReduced) return;

            let rafId = null;
            let targetX = 0;
            let targetY = 0;

            function apply() {
                rafId = null;
                const x = clamp(targetX, -1, 1);
                const y = clamp(targetY, -1, 1);

                layers.forEach((el, idx) => {
                    const depth = (idx % 6) + 1; // 1..6
                    const moveX = x * depth * 6;
                    const moveY = y * depth * 6;
                    el.style.transform = `translate3d(${moveX}px, ${moveY}px, 0)`;
                });
            }

            function onMove(e) {
                const rect = root.getBoundingClientRect();
                const cx = rect.left + rect.width / 2;
                const cy = rect.top + rect.height / 2;
                targetX = (e.clientX - cx) / (rect.width / 2);
                targetY = (e.clientY - cy) / (rect.height / 2);
                if (rafId === null) rafId = requestAnimationFrame(apply);
            }

            window.addEventListener('mousemove', onMove, { passive: true });
        })();
    </script>
</body>
</html>
