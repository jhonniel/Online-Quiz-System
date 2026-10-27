<div class="login-halloween-fog" aria-hidden="true"></div>

<div class="login-left-panel__login-decor top-[6%] left-[4%] opacity-65" aria-hidden="true">
    @include('partials.login-themes.partials.boo-ghost', ['size' => 'sm', 'delayClass' => '1'])
</div>

<div class="login-left-panel__login-decor bottom-[22%] right-[2%] z-[9]" aria-hidden="true">
    @include('partials.login-themes.partials.ghosto-scene', ['size' => 'sm'])
</div>

<div class="login-left-panel__content login-left-panel__content--halloween login-left-panel__content--stacked">
    <div class="login-theme-art login-theme-art--trio">
        <div class="relative mx-auto flex flex-col items-center">
            @include('partials.login-themes.partials.witch-scene')
        </div>
        <div class="relative mx-auto mt-1 flex flex-wrap items-end justify-center gap-4 sm:gap-6">
            @include('partials.login-themes.partials.boo-ghost', ['size' => 'md', 'delayClass' => '0'])
            @include('partials.login-themes.partials.ghosto-scene', ['size' => 'lg'])
        </div>
    </div>

    @include('partials.login-themes.partials.welcome-heading', [
        'tone' => 'halloween',
        'tagline' => 'Sign in — if you dare',
    ])
</div>
