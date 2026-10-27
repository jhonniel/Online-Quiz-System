<div class="login-halloween-fog" aria-hidden="true"></div>

<div class="login-left-panel__content login-left-panel__content--halloween login-left-panel__content--stacked">
    <div class="login-theme-art">
        @include('partials.login-themes.partials.ghosto-scene', ['size' => 'lg'])
    </div>
    @include('partials.login-themes.partials.welcome-heading', [
        'tone' => 'halloween',
        'tagline' => 'Sign in — if you dare',
    ])
</div>
