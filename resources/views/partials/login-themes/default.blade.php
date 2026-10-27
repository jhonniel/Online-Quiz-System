<div class="absolute top-0 left-0 w-96 h-96 bg-yellow-300 rounded-full -translate-x-48 -translate-y-24 opacity-80 animate-float-slow"></div>
<div class="absolute bottom-0 right-0 w-80 h-80 bg-teal-300 rounded-full translate-x-40 translate-y-20 opacity-60 animate-float-medium"></div>

<div class="absolute top-1/4 left-1/4 w-4 h-4 bg-white rounded-full opacity-60 animate-float-fast animate-twinkle"></div>
<div class="absolute top-3/4 right-1/3 w-6 h-6 bg-pink-200 rounded-full opacity-50 animate-float-slow"></div>
<div class="absolute top-1/2 left-1/2 w-3 h-3 bg-yellow-200 rounded-full opacity-70 animate-float-medium animate-twinkle"></div>
<div class="absolute bottom-1/4 left-1/3 w-5 h-5 bg-white rounded-full opacity-40 animate-float-fast"></div>

<div class="absolute top-1/6 right-1/4 w-2 h-2 bg-white rounded-full opacity-80 animate-twinkle"></div>
<div class="absolute top-2/3 left-1/6 w-1 h-1 bg-yellow-300 rounded-full opacity-90 animate-twinkle"></div>
<div class="absolute bottom-1/3 right-1/6 w-2 h-2 bg-white rounded-full opacity-70 animate-twinkle"></div>

<div class="login-left-panel__content">
    <div class="text-center text-white max-w-xl">
        <div class="relative mb-8 animate-float-gentle">
            <div class="w-32 h-32 mx-auto relative animate-float-slow">
                <div class="w-16 h-16 bg-pink-200 rounded-full mx-auto mb-2"></div>
                <div class="w-20 h-16 bg-green-300 rounded-t-2xl mx-auto"></div>
                <div class="flex justify-center space-x-2 mt-1">
                    <div class="w-8 h-12 bg-gray-700 rounded-b-lg"></div>
                    <div class="w-8 h-12 bg-gray-700 rounded-b-lg"></div>
                </div>
                <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
                    <div class="w-20 h-12 bg-gray-800 rounded-lg">
                        <div class="w-16 h-8 bg-white rounded mx-auto mt-1 flex items-center justify-center">
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute -left-8 top-12 animate-float-slow">
                <div class="w-8 h-8 bg-pink-300 rounded-full"></div>
                <div class="w-4 h-4 bg-teal-400 rounded-full ml-4 -mt-2 animate-float-fast"></div>
            </div>
            <div class="absolute -right-8 top-16 animate-float-medium">
                <div class="w-6 h-12 bg-gray-600 rounded-full"></div>
            </div>
        </div>

        @include('partials.login-themes.partials.welcome-heading', [
            'variant' => 'default',
            'tone' => 'default',
            'tagline' => 'Your learning journey starts here',
        ])
    </div>
</div>
