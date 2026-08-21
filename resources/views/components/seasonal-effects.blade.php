@php
    $seasonalEffect = \App\Models\Setting::get('seasonal_effects', 'disabled');
@endphp

@if($seasonalEffect !== 'disabled')
    <div id="seasonal-effects" class="fixed inset-0 pointer-events-none z-50" style="overflow: visible;">
        @if($seasonalEffect === 'halloween')
            <!-- Halloween Effects -->
            <div id="halloween-effects">
                <!-- Flying Spiders -->
                <div class="spider-container">
                    <!-- Spiders will be dynamically created by JavaScript -->
                </div>

                <!-- Flying Bats -->
                <div class="bat-container">
                    <!-- Bats will be dynamically created by JavaScript -->
                </div>
            </div>
        @elseif($seasonalEffect === 'halloween_spidey')
            <!-- Halloween Spidey Effects -->
            <div id="halloween-spidey-effects">
                @for ($i = 0; $i < 6; $i++)
                    <div class="spidey-spider-unit spidey-spider-unit_{{ $i }}">
                        <div class="spidey-spider">
                            <div class="eye left"></div>
                            <div class="eye right"></div>
                            @for ($j = 0; $j < 4; $j++)
                                <span class="leg left"></span>
                            @endfor
                            @for ($j = 0; $j < 4; $j++)
                                <span class="leg right"></span>
                            @endfor
                        </div>
                    </div>
                @endfor
                <img src="{{ asset('images/seasonal/spiderweb-corner.svg') }}" alt="" class="spidey-web spidey-web-right" aria-hidden="true">
                <img src="{{ asset('images/seasonal/spiderweb-corner.svg') }}" alt="" class="spidey-web spidey-web-left" aria-hidden="true">
                <img src="{{ asset('images/seasonal/spiderweb-corner.svg') }}" alt="" class="spidey-web spidey-web-bottom-right" aria-hidden="true">
                <img src="{{ asset('images/seasonal/spiderweb-corner.svg') }}" alt="" class="spidey-web spidey-web-bottom-left" aria-hidden="true">
                <img src="{{ asset('images/seasonal/spiderweb-footer.svg') }}" alt="" class="spidey-web spidey-web-footer" aria-hidden="true">
            </div>
        @elseif($seasonalEffect === 'christmas')
            <!-- Christmas Effects -->
            <div id="christmas-effects" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; overflow: visible; pointer-events: none;">
                <canvas id="snow-canvas" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;"></canvas>
            </div>
        @endif
    </div>

    <style>
        /* Halloween Effects */
        .spider {
            position: absolute;
            width: 20px;
            height: 20px;
            background: #2d2d2d;
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            animation: spider-crawl 15s linear infinite;
            z-index: 1000;
            pointer-events: none !important;
        }

        .spider::before {
            content: '';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 8px;
            background: #2d2d2d;
        }

        .spider::after {
            content: '';
            position: absolute;
            top: -6px;
            left: 50%;
            transform: translateX(-50%);
            width: 1px;
            height: 6px;
            background: #2d2d2d;
        }

        .bat {
            position: absolute;
            width: 16px;
            height: 16px;
            background: #1a1a1a;
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            animation: bat-fly 12s linear infinite;
            z-index: 1000;
            pointer-events: none !important;
        }

        .bat::before {
            content: '';
            position: absolute;
            top: -4px;
            left: 50%;
            transform: translateX(-50%);
            width: 1px;
            height: 4px;
            background: #1a1a1a;
        }

        /* Christmas Effects - Canvas based */
        #snow-canvas {
            z-index: 1000;
        }

        /* Halloween Spidey — hanging spiders & corner webs (no page background) */
        #halloween-spidey-effects {
            position: fixed;
            inset: 0;
            overflow: visible;
            pointer-events: none;
        }

        #halloween-spidey-effects .spidey-spider-unit {
            position: absolute;
            width: 50px;
            z-index: 1001;
        }

        #halloween-spidey-effects .spidey-spider {
            position: relative;
            height: 40px;
            width: 50px;
            border-radius: 50%;
            margin: 40px 0 0 0;
            background: #110D04;
        }

        #halloween-spidey-effects .spidey-spider *,
        #halloween-spidey-effects .spidey-spider::before,
        #halloween-spidey-effects .spidey-spider::after,
        #halloween-spidey-effects .spidey-spider :after,
        #halloween-spidey-effects .spidey-spider :before {
            position: absolute;
            content: "";
        }

        #halloween-spidey-effects .spidey-spider::before {
            width: 1px;
            background: linear-gradient(to bottom, rgba(120, 120, 120, 0.75) 0%, rgba(140, 140, 140, 0.4) 50%, rgba(160, 160, 160, 0.12) 100%);
            left: 50%;
            transform: translateX(-50%);
            top: -320px;
            height: 320px;
            box-shadow: 0 0 1px rgba(0, 0, 0, 0.12);
            z-index: -2;
        }

        #halloween-spidey-effects .spidey-spider::after {
            top: -338px;
            left: 50%;
            transform: translateX(-50%);
            width: 54px;
            height: 54px;
            background: url("{{ asset('images/seasonal/spiderweb-hub.svg') }}") center / contain no-repeat;
            opacity: 0.72;
            z-index: -1;
        }

        #halloween-spidey-effects .spidey-spider .eye {
            top: 16px;
            height: 14px;
            width: 12px;
            background: #FFFFFF;
            border-radius: 50%;
        }

        #halloween-spidey-effects .spidey-spider .eye::after {
            top: 6px;
            height: 5px;
            width: 5px;
            border-radius: 50%;
            background: black;
        }

        #halloween-spidey-effects .spidey-spider .eye.left {
            left: 14px;
        }

        #halloween-spidey-effects .spidey-spider .eye.left::after {
            right: 3px;
        }

        #halloween-spidey-effects .spidey-spider .eye.right {
            right: 14px;
        }

        #halloween-spidey-effects .spidey-spider .eye.right::after {
            left: 3px;
        }

        #halloween-spidey-effects .spidey-spider .leg {
            top: 6px;
            height: 12px;
            width: 14px;
            border-top: 2px solid #110D04;
            border-left: 1px solid transparent;
            border-right: 1px solid transparent;
            border-bottom: 1px solid transparent;
            z-index: -1;
        }

        #halloween-spidey-effects .spidey-spider .leg.left {
            left: -8px;
            transform-origin: top right;
            transform: rotate(36deg) skewX(-20deg);
            border-left: 2px solid #110D04;
            border-radius: 60% 0 0 0;
            animation: spidey-legs-wriggle-left 1s 0s infinite;
        }

        #halloween-spidey-effects .spidey-spider .leg.right {
            right: -8px;
            transform-origin: top left;
            transform: rotate(-36deg) skewX(20deg);
            border-right: 2px solid #110D04;
            border-radius: 0 60% 0 0;
            animation: spidey-legs-wriggle-right 1s 0.2s infinite;
        }

        #halloween-spidey-effects .spidey-spider .leg.left:nth-of-type(2) {
            top: 14px;
            left: -11px;
            animation: spidey-legs-wriggle-left 1s 0.8s infinite;
        }

        #halloween-spidey-effects .spidey-spider .leg.left:nth-of-type(3) {
            top: 22px;
            left: -12px;
            animation: spidey-legs-wriggle-left 1s 0.2s infinite;
        }

        #halloween-spidey-effects .spidey-spider .leg.left:nth-of-type(4) {
            top: 31px;
            left: -10px;
            animation: spidey-legs-wriggle-left 1s 0.4s infinite;
        }

        #halloween-spidey-effects .spidey-spider .leg.right:nth-of-type(6) {
            top: 14px;
            right: -11px;
            animation: spidey-legs-wriggle-right 1s 0.4s infinite;
        }

        #halloween-spidey-effects .spidey-spider .leg.right:nth-of-type(7) {
            top: 22px;
            right: -12px;
            animation: spidey-legs-wriggle-right 1s 0.7s infinite;
        }

        #halloween-spidey-effects .spidey-spider .leg.right:nth-of-type(8) {
            top: 31px;
            right: -10px;
            animation: spidey-legs-wriggle-right 1s 0.3s infinite;
        }

        #halloween-spidey-effects .spidey-spider-unit_0 {
            left: 5%;
            animation: spidey-spider-move-0 5s infinite;
        }

        #halloween-spidey-effects .spidey-spider-unit_1 {
            left: 20%;
            animation: spidey-spider-move-1 5s infinite;
        }

        #halloween-spidey-effects .spidey-spider-unit_2 {
            left: 35%;
            animation: spidey-spider-move-2 5s infinite;
        }

        #halloween-spidey-effects .spidey-spider-unit_3 {
            right: 35%;
            margin-top: 160px;
            animation: spidey-spider-move-3 5s infinite;
        }

        #halloween-spidey-effects .spidey-spider-unit_4 {
            right: 20%;
            margin-top: 50px;
            animation: spidey-spider-move-4 5s infinite;
        }

        #halloween-spidey-effects .spidey-spider-unit_5 {
            right: 5%;
            margin-top: 210px;
            animation: spidey-spider-move-5 5s infinite;
        }

        #halloween-spidey-effects .spidey-web {
            filter: drop-shadow(0 0 0.6px rgba(255, 255, 255, 0.35)) drop-shadow(0 1px 3px rgba(0, 0, 0, 0.12));
            image-rendering: auto;
        }

        #halloween-spidey-effects .spidey-web-right {
            position: absolute;
            height: 260px;
            width: auto;
            right: -12px;
            top: -12px;
            z-index: 999;
            opacity: 0.78;
        }

        #halloween-spidey-effects .spidey-web-left {
            position: absolute;
            left: -12px;
            top: -12px;
            transform: rotate(-90deg);
            transform-origin: top left;
            z-index: 999;
            opacity: 0.78;
            height: 260px;
            width: auto;
        }

        #halloween-spidey-effects .spidey-web-bottom-right {
            position: absolute;
            right: -12px;
            bottom: -12px;
            height: 260px;
            width: auto;
            transform: rotate(180deg) scaleX(-1);
            transform-origin: right bottom;
            z-index: 999;
            opacity: 0.78;
        }

        #halloween-spidey-effects .spidey-web-bottom-left {
            position: absolute;
            left: -12px;
            bottom: -12px;
            height: 260px;
            width: auto;
            transform: rotate(180deg);
            transform-origin: left bottom;
            z-index: 999;
            opacity: 0.78;
        }

        #halloween-spidey-effects .spidey-web-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: clamp(90px, 16vh, 175px);
            object-fit: contain;
            object-position: bottom center;
            z-index: 9999;
            opacity: 0.62;
            pointer-events: none;
            filter: drop-shadow(0 0 0.5px rgba(255, 255, 255, 0.25));
        }

        @keyframes spidey-legs-wriggle-left {
            0%, 100% { transform: rotate(36deg) skewX(-20deg); }
            25%, 75% { transform: rotate(15deg) skewX(-20deg); }
            50% { transform: rotate(45deg) skewX(-20deg); }
        }

        @keyframes spidey-legs-wriggle-right {
            0%, 100% { transform: rotate(-36deg) skewX(20deg); }
            25%, 75% { transform: rotate(-15deg) skewX(20deg); }
            50% { transform: rotate(-45deg) skewX(20deg); }
        }

        @keyframes spidey-spider-move-0 {
            0%, 100% { margin-top: 95px; }
            42% { margin-top: calc(95px + 72px); }
        }

        @keyframes spidey-spider-move-1 {
            0%, 100% { margin-top: 180px; }
            58% { margin-top: calc(180px + 55px); }
        }

        @keyframes spidey-spider-move-2 {
            0%, 100% { margin-top: 130px; }
            35% { margin-top: calc(130px + 90px); }
        }

        @keyframes spidey-spider-move-3 {
            0%, 100% { margin-top: 160px; }
            48% { margin-top: calc(160px + 65px); }
        }

        @keyframes spidey-spider-move-4 {
            0%, 100% { margin-top: 50px; }
            62% { margin-top: calc(50px + 110px); }
        }

        @keyframes spidey-spider-move-5 {
            0%, 100% { margin-top: 210px; }
            40% { margin-top: calc(210px + 45px); }
        }

        @media (max-width: 768px) {
            /* Fewer spiders on narrow screens to avoid overlap */
            #halloween-spidey-effects .spidey-spider-unit_1,
            #halloween-spidey-effects .spidey-spider-unit_4 {
                display: none;
            }

            #halloween-spidey-effects .spidey-spider {
                height: 32px;
                width: 42px;
                margin-top: 28px;
            }

            #halloween-spidey-effects .spidey-spider::before {
                top: calc(-1 * clamp(90px, 20vh, 150px));
                height: clamp(90px, 20vh, 150px);
            }

            #halloween-spidey-effects .spidey-spider::after {
                top: calc(-1 * clamp(90px, 20vh, 150px) - 16px);
                width: 42px;
                height: 42px;
                opacity: 0.65;
            }

            #halloween-spidey-effects .spidey-spider .eye {
                top: 11px;
                height: 10px;
                width: 9px;
            }

            #halloween-spidey-effects .spidey-spider .eye::after {
                top: 4px;
                height: 4px;
                width: 4px;
            }

            #halloween-spidey-effects .spidey-spider .eye.left {
                left: 10px;
            }

            #halloween-spidey-effects .spidey-spider .eye.left::after {
                right: 2px;
            }

            #halloween-spidey-effects .spidey-spider .eye.right {
                right: 10px;
            }

            #halloween-spidey-effects .spidey-spider .eye.right::after {
                left: 2px;
            }

            #halloween-spidey-effects .spidey-spider .leg {
                top: 4px;
                height: 9px;
                width: 11px;
                border-top-width: 1.5px;
            }

            #halloween-spidey-effects .spidey-spider .leg.left {
                left: -6px;
                border-left-width: 1.5px;
            }

            #halloween-spidey-effects .spidey-spider .leg.right {
                right: -6px;
                border-right-width: 1.5px;
            }

            #halloween-spidey-effects .spidey-spider .leg.left:nth-of-type(2) {
                top: 10px;
                left: -8px;
            }

            #halloween-spidey-effects .spidey-spider .leg.left:nth-of-type(3) {
                top: 16px;
                left: -9px;
            }

            #halloween-spidey-effects .spidey-spider .leg.left:nth-of-type(4) {
                top: 22px;
                left: -7px;
            }

            #halloween-spidey-effects .spidey-spider .leg.right:nth-of-type(6) {
                top: 10px;
                right: -8px;
            }

            #halloween-spidey-effects .spidey-spider .leg.right:nth-of-type(7) {
                top: 16px;
                right: -9px;
            }

            #halloween-spidey-effects .spidey-spider .leg.right:nth-of-type(8) {
                top: 22px;
                right: -7px;
            }

            #halloween-spidey-effects .spidey-spider-unit_0 {
                left: 4%;
                right: auto;
                animation: spidey-spider-move-mobile-0 5s infinite;
            }

            #halloween-spidey-effects .spidey-spider-unit_2 {
                left: 36%;
                right: auto;
                animation: spidey-spider-move-mobile-2 5s infinite;
            }

            #halloween-spidey-effects .spidey-spider-unit_3 {
                left: auto;
                right: 36%;
                animation: spidey-spider-move-mobile-3 5s infinite;
            }

            #halloween-spidey-effects .spidey-spider-unit_5 {
                left: auto;
                right: 4%;
                animation: spidey-spider-move-mobile-5 5s infinite;
            }

            #halloween-spidey-effects .spidey-web-right,
            #halloween-spidey-effects .spidey-web-left,
            #halloween-spidey-effects .spidey-web-bottom-right,
            #halloween-spidey-effects .spidey-web-bottom-left {
                height: 180px;
                opacity: 0.8;
            }

            #halloween-spidey-effects .spidey-web-footer {
                height: clamp(72px, 14vh, 140px);
                opacity: 0.58;
            }
        }

        @media (max-width: 480px) {
            #halloween-spidey-effects .spidey-spider-unit_2 {
                display: none;
            }

            #halloween-spidey-effects .spidey-spider-unit_0 {
                left: 8%;
            }

            #halloween-spidey-effects .spidey-spider-unit_3 {
                right: 38%;
            }

            #halloween-spidey-effects .spidey-spider-unit_5 {
                right: 8%;
            }
        }

        @keyframes spidey-spider-move-mobile-0 {
            0%, 100% { margin-top: 36px; }
            42% { margin-top: calc(36px + 28px); }
        }

        @keyframes spidey-spider-move-mobile-2 {
            0%, 100% { margin-top: 72px; }
            35% { margin-top: calc(72px + 32px); }
        }

        @keyframes spidey-spider-move-mobile-3 {
            0%, 100% { margin-top: 52px; }
            48% { margin-top: calc(52px + 24px); }
        }

        @keyframes spidey-spider-move-mobile-5 {
            0%, 100% { margin-top: 88px; }
            40% { margin-top: calc(88px + 20px); }
        }

        /* Animations */
        @keyframes spider-crawl {
            0% {
                transform: translateX(-30px) translateY(0px) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateX(calc(100vw + 30px)) translateY(-20px) rotate(360deg);
                opacity: 0;
            }
        }

        @keyframes bat-fly {
            0% {
                transform: translateX(-30px) translateY(0px) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateX(calc(100vw + 30px)) translateY(-30px) rotate(180deg);
                opacity: 0;
            }
        }


        /* Responsive adjustments */
        @media (max-width: 768px) {
            .spider, .bat {
                width: 12px;
                height: 12px;
            }
            .snowflake {
                font-size: 10px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seasonalEffect = '{{ $seasonalEffect }}';
            
            // Debug: log the seasonal effect value
            console.log('Seasonal effect setting:', seasonalEffect);
            console.log('Seasonal effect type:', typeof seasonalEffect);
            console.log('Is christmas?', seasonalEffect === 'christmas');
            console.log('Is halloween?', seasonalEffect === 'halloween');

            if (seasonalEffect === 'halloween') {
                console.log('Initializing Halloween effects...');
                initHalloweenEffects();
            } else if (seasonalEffect === 'christmas') {
                console.log('Initializing Christmas effects...');
                initChristmasEffects();
            } else {
                console.log('Seasonal effects disabled or unknown value:', seasonalEffect);
            }
        });

        function initHalloweenEffects() {
            const spiderContainer = document.querySelector('.spider-container');
            const batContainer = document.querySelector('.bat-container');

            if (!spiderContainer || !batContainer) {
                console.error('Seasonal effects: Halloween containers not found');
                return;
            }

            // Create spiders
            setInterval(() => {
                if (Math.random() < 0.3) { // 30% chance every interval
                    createSpider(spiderContainer);
                }
            }, 3000);

            // Create bats
            setInterval(() => {
                if (Math.random() < 0.4) { // 40% chance every interval
                    createBat(batContainer);
                }
            }, 2500);
        }

        function createSpider(container) {
            if (!container) return;
            
            const spider = document.createElement('div');
            spider.className = 'spider';
            spider.style.top = Math.random() * (window.innerHeight - 100) + 'px';
            spider.style.animationDuration = (Math.random() * 10 + 10) + 's'; // 10-20 seconds
            spider.style.animationDelay = Math.random() * 2 + 's';

            container.appendChild(spider);

            // Remove spider after animation
            setTimeout(() => {
                if (spider.parentNode) {
                    spider.parentNode.removeChild(spider);
                }
            }, 25000);
        }

        function createBat(container) {
            if (!container) return;
            
            const bat = document.createElement('div');
            bat.className = 'bat';
            bat.style.top = Math.random() * (window.innerHeight - 100) + 'px';
            bat.style.animationDuration = (Math.random() * 8 + 8) + 's'; // 8-16 seconds
            bat.style.animationDelay = Math.random() * 2 + 's';

            container.appendChild(bat);

            // Remove bat after animation
            setTimeout(() => {
                if (bat.parentNode) {
                    bat.parentNode.removeChild(bat);
                }
            }, 20000);
        }

        function initChristmasEffects() {
            const canvas = document.getElementById('snow-canvas');
            if (!canvas) {
                console.error('Snow canvas not found');
                return;
            }

            const ctx = canvas.getContext('2d');
            let snowflakes = [];
            let mouseX = 0;
            let mouseY = 0;
            let windX = 0;
            let windY = 0;
            let deviceTiltX = 0;
            let deviceTiltY = 0;

            // Set canvas size
            function resizeCanvas() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            }
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            // Mouse movement tracking for wind effect
            document.addEventListener('mousemove', (e) => {
                mouseX = e.clientX;
                mouseY = e.clientY;
                // Calculate wind based on mouse position (stronger near mouse)
                const centerX = window.innerWidth / 2;
                const centerY = window.innerHeight / 2;
                const deltaX = (mouseX - centerX) / centerX;
                const deltaY = (mouseY - centerY) / centerY;
                windX = deltaX * 0.5; // Wind strength multiplier
                windY = deltaY * 0.3;
            });

            // Device orientation/gyroscope support
            if (window.DeviceOrientationEvent) {
                window.addEventListener('deviceorientation', (e) => {
                    // Normalize tilt values (-1 to 1)
                    deviceTiltX = (e.gamma || 0) / 90; // Left/right tilt
                    deviceTiltY = (e.beta || 0) / 90; // Forward/backward tilt
                    // Limit values
                    deviceTiltX = Math.max(-1, Math.min(1, deviceTiltX));
                    deviceTiltY = Math.max(-1, Math.min(1, deviceTiltY));
                });
            }

            // Draw detailed snowflake using canvas
            function drawSnowflake(ctx, size, rotation) {
                const branches = 6;
                const angleStep = (Math.PI * 2) / branches;
                
                ctx.beginPath();
                
                for (let i = 0; i < branches; i++) {
                    const angle = i * angleStep + rotation;
                    
                    // Main branch
                    const x1 = Math.cos(angle) * size;
                    const y1 = Math.sin(angle) * size;
                    ctx.moveTo(0, 0);
                    ctx.lineTo(x1, y1);
                    
                    // Side branches at 60% of main branch
                    const branchPoint = 0.6;
                    const x2 = Math.cos(angle) * size * branchPoint;
                    const y2 = Math.sin(angle) * size * branchPoint;
                    
                    // Left side branch
                    const sideAngle1 = angle + Math.PI / 3;
                    const sideSize = size * 0.3;
                    ctx.moveTo(x2, y2);
                    ctx.lineTo(x2 + Math.cos(sideAngle1) * sideSize, y2 + Math.sin(sideAngle1) * sideSize);
                    
                    // Right side branch
                    const sideAngle2 = angle - Math.PI / 3;
                    ctx.moveTo(x2, y2);
                    ctx.lineTo(x2 + Math.cos(sideAngle2) * sideSize, y2 + Math.sin(sideAngle2) * sideSize);
                }
                
                ctx.stroke();
                
                // Center dot
                ctx.beginPath();
                ctx.arc(0, 0, size * 0.15, 0, Math.PI * 2);
                ctx.fill();
            }

            // Snowflake class
            class Snowflake {
                constructor() {
                    this.reset();
                    this.y = Math.random() * canvas.height;
                }

                reset() {
                    this.x = Math.random() * canvas.width;
                    this.y = -10;
                    this.size = Math.random() * 4 + 2; // 2-6px
                    this.speed = Math.random() * 2 + 1; // 1-3 pixels per frame
                    this.windResistance = Math.random() * 0.02 + 0.01; // How much wind affects it
                    this.rotation = Math.random() * Math.PI * 2;
                    this.rotationSpeed = (Math.random() - 0.5) * 0.1;
                    this.opacity = Math.random() * 0.5 + 0.5; // 0.5-1.0
                }

                update() {
                    // Apply wind effect (mouse + device orientation)
                    const totalWindX = windX * this.windResistance + deviceTiltX * 0.3;
                    const totalWindY = windY * this.windResistance + deviceTiltY * 0.2;
                    
                    // Natural drift (slight swaying)
                    const naturalDrift = Math.sin(this.y * 0.01) * 0.3;
                    
                    this.x += totalWindX + naturalDrift;
                    this.y += this.speed + totalWindY;
                    this.rotation += this.rotationSpeed;

                    // Reset if off screen
                    if (this.y > canvas.height + 10) {
                        this.reset();
                    }
                    if (this.x < -10) {
                        this.x = canvas.width + 10;
                    }
                    if (this.x > canvas.width + 10) {
                        this.x = -10;
                    }
                }

                draw() {
                    ctx.save();
                    ctx.translate(this.x, this.y);
                    ctx.rotate(this.rotation);
                    ctx.strokeStyle = `rgba(255, 255, 255, ${this.opacity})`;
                    ctx.fillStyle = `rgba(255, 255, 255, ${this.opacity * 0.8})`;
                    ctx.lineWidth = 0.5;
                    ctx.lineCap = 'round';
                    
                    // Draw detailed snowflake
                    drawSnowflake(ctx, this.size, 0);
                    
                    ctx.restore();
                }
            }

            // Initialize snowflakes
            const snowflakeCount = Math.min(100, Math.floor((canvas.width * canvas.height) / 15000));
            for (let i = 0; i < snowflakeCount; i++) {
                const flake = new Snowflake();
                flake.y = Math.random() * canvas.height; // Start at random positions
                snowflakes.push(flake);
            }

            // Animation loop
            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                // Update and draw all snowflakes
                snowflakes.forEach(flake => {
                    flake.update();
                    flake.draw();
                });
                
                requestAnimationFrame(animate);
            }

            // Start animation
            animate();
            console.log('Realistic snow effect initialized with', snowflakes.length, 'snowflakes');
        }
    </script>
@endif
