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
