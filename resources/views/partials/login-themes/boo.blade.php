<div class="login-halloween-fog" aria-hidden="true"></div>

<div class="login-left-panel__login-decor top-[5%] left-[3%] opacity-70" aria-hidden="true">
    @include('partials.login-themes.partials.boo-ghost', ['size' => 'sm', 'delayClass' => '1'])
</div>

<div class="login-left-panel__login-decor top-[8%] right-[4%] opacity-55" aria-hidden="true">
    @include('partials.login-themes.partials.boo-ghost', ['size' => 'sm', 'delayClass' => '2'])
</div>

<div class="login-left-panel__content login-left-panel__content--halloween login-left-panel__content--stacked">
    <div class="login-theme-art">
        @include('partials.login-themes.partials.boo-ghost', ['size' => 'lg', 'delayClass' => '0'])
    </div>
    @include('partials.login-themes.partials.welcome-heading', [
        'tone' => 'halloween',
        'tagline' => 'Sign in — if you dare',
    ])
</div>
