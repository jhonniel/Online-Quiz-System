@php
    $seasonalEffect = \App\Models\Setting::get('seasonal_effects', 'disabled');
@endphp

@if($seasonalEffect !== 'disabled')
    <div id="seasonal-effects" class="fixed inset-0 pointer-events-none z-50 overflow-hidden">
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
            <div id="christmas-effects">
                <!-- Snowflakes -->
                <div class="snow-container">
                    <!-- Snowflakes will be dynamically created by JavaScript -->
                </div>
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

        /* Christmas Effects */
        .snowflake {
            position: absolute;
            color: white;
            font-size: 12px;
            animation: snow-fall linear infinite;
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

        @keyframes snow-fall {
            0% {
                transform: translateY(-10px) translateX(0px) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(calc(100vh + 10px)) translateX(20px) rotate(360deg);
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

            if (seasonalEffect === 'halloween') {
                initHalloweenEffects();
            } else if (seasonalEffect === 'christmas') {
                initChristmasEffects();
            }
        });

        function initHalloweenEffects() {
            const spiderContainer = document.querySelector('.spider-container');
            const batContainer = document.querySelector('.bat-container');

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
            const snowContainer = document.querySelector('.snow-container');

            // Create snowflakes
            setInterval(() => {
                if (Math.random() < 0.8) { // 80% chance every interval
                    createSnowflake(snowContainer);
                }
            }, 200);
        }

        function createSnowflake(container) {
            const snowflake = document.createElement('div');
            snowflake.className = 'snowflake';
            snowflake.innerHTML = '❄';
            snowflake.style.left = Math.random() * window.innerWidth + 'px';
            snowflake.style.animationDuration = (Math.random() * 3 + 2) + 's'; // 2-5 seconds
            snowflake.style.animationDelay = Math.random() * 1 + 's';
            snowflake.style.fontSize = (Math.random() * 8 + 8) + 'px'; // 8-16px

            container.appendChild(snowflake);

            // Remove snowflake after animation
            setTimeout(() => {
                if (snowflake.parentNode) {
                    snowflake.parentNode.removeChild(snowflake);
                }
            }, 6000);
        }
    </script>
@endif
