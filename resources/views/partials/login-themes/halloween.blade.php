<div class="login-halloween-fog" aria-hidden="true"></div>

<div class="absolute top-[6%] left-[4%] opacity-65 z-20" aria-hidden="true">
    @include('partials.login-themes.partials.boo-ghost', ['size' => 'sm', 'delayClass' => '1'])
</div>

<div class="absolute bottom-[8%] right-[2%] z-20" aria-hidden="true">
    @include('partials.login-themes.partials.ghosto-scene', ['size' => 'sm'])
</div>

<div class="login-left-panel__content login-left-panel__content--halloween">
    <div class="text-center text-white max-w-xl w-full">
        <div class="relative mx-auto mb-2 flex flex-col items-center">
            @include('partials.login-themes.partials.witch-scene')
        </div>

        <div class="relative z-20 mx-auto flex flex-wrap items-end justify-center gap-6">
            @include('partials.login-themes.partials.boo-ghost', ['size' => 'md', 'delayClass' => '0'])
            @include('partials.login-themes.partials.ghosto-scene', ['size' => 'lg'])
        </div>
        @include('partials.login-themes.partials.welcome-heading', [
            'tone' => 'halloween',
            'tagline' => 'Sign in — if you dare',
        ])
    </div>
</div>
