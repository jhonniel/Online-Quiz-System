<div class="login-christmas-snow login-christmas-snow--hohoho" aria-hidden="true">
    @for ($i = 0; $i < 22; $i++)
        <span style="left: {{ ($i * 4.3) % 100 }}%; animation-duration: {{ 9 + ($i % 6) }}s; animation-delay: {{ ($i % 4) * 0.5 }}s;"></span>
    @endfor
</div>

<div class="login-left-panel__content login-left-panel__content--hohoho login-left-panel__content--stacked">
    <div class="login-theme-art login-theme-art--hohoho">
        @include('partials.login-themes.partials.hohoho-scene')
    </div>
    @include('partials.login-themes.partials.welcome-heading', [
        'tone' => 'hohoho',
        'tagline' => 'Merry season — sign in to continue',
    ])
</div>
