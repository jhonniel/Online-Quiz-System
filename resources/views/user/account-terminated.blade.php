<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Access restricted</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body {
            margin: 0;
            height: 100%;
            overflow: hidden;
            background: #030303;
            color: #fecaca;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .bg-layer {
            position: fixed;
            inset: 0;
            pointer-events: none;
        }
        .bg-gradient {
            z-index: 0;
            background:
                radial-gradient(ellipse 80% 50% at 20% 40%, rgba(220, 38, 38, 0.35) 0%, transparent 55%),
                radial-gradient(ellipse 60% 45% at 80% 70%, rgba(127, 29, 29, 0.4) 0%, transparent 50%),
                radial-gradient(ellipse 100% 80% at 50% 100%, rgba(69, 10, 10, 0.5) 0%, transparent 45%),
                #030303;
            animation: bg-drift 12s ease-in-out infinite alternate;
        }
        @keyframes bg-drift {
            0% { filter: hue-rotate(0deg) brightness(1); transform: scale(1); }
            100% { filter: hue-rotate(-8deg) brightness(1.08); transform: scale(1.03); }
        }
        .bg-orbs span {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            animation: orb-float 8s ease-in-out infinite;
        }
        .bg-orbs span:nth-child(1) {
            width: 280px; height: 280px;
            background: rgba(239, 68, 68, 0.25);
            top: 10%; left: 5%;
            animation-delay: 0s;
        }
        .bg-orbs span:nth-child(2) {
            width: 200px; height: 200px;
            background: rgba(185, 28, 28, 0.3);
            bottom: 15%; right: 10%;
            animation-delay: -2s;
        }
        .bg-orbs span:nth-child(3) {
            width: 160px; height: 160px;
            background: rgba(248, 113, 113, 0.15);
            top: 50%; left: 45%;
            animation-delay: -4s;
        }
        @keyframes orb-float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(20px, -30px) scale(1.1); }
            66% { transform: translate(-15px, 20px) scale(0.95); }
        }
        .bg-grid {
            z-index: 1;
            opacity: 0.12;
            background-image:
                linear-gradient(rgba(239, 68, 68, 0.4) 1px, transparent 1px),
                linear-gradient(90deg, rgba(239, 68, 68, 0.4) 1px, transparent 1px);
            background-size: 48px 48px;
            transform: perspective(400px) rotateX(72deg) scale(2.2);
            transform-origin: center 120%;
            animation: grid-scroll 4s linear infinite;
        }
        @keyframes grid-scroll {
            0% { background-position: 0 0; }
            100% { background-position: 0 48px; }
        }
        .bg-scanlines {
            z-index: 3;
            background: repeating-linear-gradient(
                0deg,
                rgba(0, 0, 0, 0.2) 0px,
                rgba(0, 0, 0, 0.2) 1px,
                transparent 1px,
                transparent 4px
            );
            animation: scanlines 5s linear infinite;
            opacity: 0.4;
        }
        @keyframes scanlines {
            0% { transform: translateY(0); }
            100% { transform: translateY(4px); }
        }
        .bg-vignette {
            z-index: 4;
            box-shadow: inset 0 0 180px 60px rgba(0, 0, 0, 0.85);
            animation: vignette-pulse 3s ease-in-out infinite;
        }
        @keyframes vignette-pulse {
            0%, 100% { box-shadow: inset 0 0 180px 60px rgba(0, 0, 0, 0.85); }
            50% { box-shadow: inset 0 0 200px 80px rgba(40, 0, 0, 0.5); }
        }
        .bg-glitch-bars {
            z-index: 5;
            overflow: hidden;
        }
        .bg-glitch-bars::before,
        .bg-glitch-bars::after {
            content: "";
            position: absolute;
            left: -10%;
            width: 120%;
            height: 3px;
            background: rgba(239, 68, 68, 0.35);
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.6);
        }
        .bg-glitch-bars::before {
            top: 22%;
            animation: bar-1 3s infinite;
        }
        .bg-glitch-bars::after {
            top: 68%;
            animation: bar-2 2.2s infinite;
        }
        @keyframes bar-1 {
            0%, 90%, 100% { opacity: 0; transform: translateX(0) scaleX(0.3); }
            92% { opacity: 1; transform: translateX(5%) scaleX(1); }
            94% { opacity: 0; transform: translateX(-8%) scaleX(0.5); }
        }
        @keyframes bar-2 {
            0%, 85%, 100% { opacity: 0; transform: translateX(0); }
            87% { opacity: 0.8; transform: translateX(-12%); }
            89% { opacity: 0; transform: translateX(10%); }
        }
        .bg-chroma {
            z-index: 6;
            mix-blend-mode: screen;
            opacity: 0.04;
            animation: chroma-shift 0.15s steps(2) infinite;
            background: linear-gradient(90deg, #f00, #0f0, #00f);
        }
        @keyframes chroma-shift {
            0% { transform: translateX(-2px); }
            100% { transform: translateX(2px); }
        }
        .noise {
            z-index: 7;
            opacity: 0.08;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            animation: noise-shift 0.35s steps(3) infinite;
        }
        @keyframes noise-shift {
            0% { transform: translate(0, 0); }
            33% { transform: translate(-3%, 2%); }
            66% { transform: translate(2%, -2%); }
            100% { transform: translate(0, 0); }
        }
        .particles {
            z-index: 2;
        }
        .particles span {
            position: absolute;
            width: 2px;
            height: 2px;
            background: #f87171;
            border-radius: 50%;
            box-shadow: 0 0 6px #ef4444;
            animation: particle-fall linear infinite;
        }
        @keyframes particle-fall {
            0% { transform: translateY(-10vh); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 0.6; }
            100% { transform: translateY(110vh); opacity: 0; }
        }
        .terminated-screen {
            position: fixed;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            z-index: 100;
            pointer-events: none;
        }
        .terminated-screen > * {
            pointer-events: auto;
        }
        .terminated-screen::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            animation: screen-flicker 0.1s infinite;
            opacity: 0.03;
            background: #fff;
            z-index: -1;
        }
        @keyframes screen-flicker {
            0%, 100% { opacity: 0.02; }
            50% { opacity: 0.06; }
        }
        .glitch-wrap {
            position: relative;
            text-align: center;
            max-width: 42rem;
            z-index: 1;
        }
        .glitch-title {
            position: relative;
            font-size: clamp(2rem, 8vw, 4.25rem);
            font-weight: 900;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            line-height: 1.1;
            color: #ef4444;
            text-shadow:
                0 0 20px rgba(239, 68, 68, 0.8),
                0 0 60px rgba(220, 38, 38, 0.5);
            animation: title-pulse 2s ease-in-out infinite;
        }
        .glitch-title::before,
        .glitch-title::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        .glitch-title::before {
            left: 2px;
            text-shadow: -3px 0 #00ffff;
            clip-path: inset(0 0 65% 0);
            animation: glitch-top 2.5s infinite linear alternate-reverse;
        }
        .glitch-title::after {
            left: -2px;
            text-shadow: 3px 0 #ff00ff;
            clip-path: inset(35% 0 0 0);
            animation: glitch-bottom 2s infinite linear alternate-reverse;
        }
        @keyframes glitch-top {
            0% { transform: translate(0); }
            20% { transform: translate(-4px, 2px); }
            40% { transform: translate(4px, -2px); }
            60% { transform: translate(-2px, 1px); }
            80% { transform: translate(2px, -1px); }
            100% { transform: translate(0); }
        }
        @keyframes glitch-bottom {
            0% { transform: translate(0); }
            25% { transform: translate(3px, -1px); }
            50% { transform: translate(-3px, 2px); }
            75% { transform: translate(2px, 1px); }
            100% { transform: translate(0); }
        }
        @keyframes title-pulse {
            0%, 100% { filter: brightness(1); }
            50% { filter: brightness(1.15); }
        }
        .subtitle {
            margin-top: 1.25rem;
            font-size: clamp(0.95rem, 2.5vw, 1.125rem);
            color: #fca5a5;
            line-height: 1.6;
        }
        .actions {
            margin-top: 2rem;
            z-index: 1;
        }
        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(248, 113, 113, 0.5);
            background: rgba(127, 29, 29, 0.5);
            color: #fecaca;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s, border-color 0.15s;
        }
        .btn-logout:hover {
            background: rgba(153, 27, 27, 0.7);
            border-color: #f87171;
        }
    </style>
</head>
<body>
    <div class="bg-layer bg-gradient" aria-hidden="true"></div>
    <div class="bg-layer bg-orbs" aria-hidden="true">
        <span></span><span></span><span></span>
    </div>
    <div class="bg-layer bg-grid" aria-hidden="true"></div>
    <div class="bg-layer particles" id="particles" aria-hidden="true"></div>
    <div class="bg-layer bg-scanlines" aria-hidden="true"></div>
    <div class="bg-layer bg-vignette" aria-hidden="true"></div>
    <div class="bg-layer bg-glitch-bars" aria-hidden="true"></div>
    <div class="bg-layer bg-chroma" aria-hidden="true"></div>
    <div class="bg-layer noise" aria-hidden="true"></div>

    <div class="terminated-screen" role="alert" aria-live="assertive">
        <div class="glitch-wrap">
            <h1 class="glitch-title" data-text="Account terminated">Account terminated</h1>
            <p class="subtitle">
                Your student account has been terminated by administration. You cannot use quizzes, DTR, leave requests, or any other part of this system.
                Contact your administrator if you believe this is an error.
            </p>
        </div>
        <div class="actions">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Sign out</button>
            </form>
        </div>
    </div>
    <script>
        (function () {
            var fragment = @json($urlFragment ?? 'restricted');
            var targetUrl = @json($targetUrl ?? url('/access#restricted'));

            if (!location.pathname.endsWith('/access') || location.hash !== '#' + fragment) {
                location.replace(targetUrl);
                return;
            }

            history.replaceState(null, '', targetUrl);

            history.pushState(null, '', targetUrl);
            window.addEventListener('popstate', function () {
                history.pushState(null, '', targetUrl);
            });

            document.addEventListener('click', function (e) {
                var a = e.target.closest('a');
                if (a && !a.closest('form')) {
                    e.preventDefault();
                }
            }, true);

            var container = document.getElementById('particles');
            if (container) {
                for (var i = 0; i < 48; i++) {
                    var p = document.createElement('span');
                    p.style.left = Math.random() * 100 + '%';
                    p.style.animationDuration = (4 + Math.random() * 8) + 's';
                    p.style.animationDelay = (Math.random() * 6) + 's';
                    p.style.opacity = (0.3 + Math.random() * 0.7).toString();
                    container.appendChild(p);
                }
            }
        })();
    </script>
</body>
</html>
