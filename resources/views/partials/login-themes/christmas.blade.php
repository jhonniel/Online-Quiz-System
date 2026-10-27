<div class="login-christmas-snow" aria-hidden="true">
    @for ($i = 0; $i < 28; $i++)
        <span style="left: {{ ($i * 3.7) % 100 }}%; animation-duration: {{ 8 + ($i % 7) }}s; animation-delay: {{ ($i % 5) * 0.7 }}s;"></span>
    @endfor
</div>

<div class="login-left-panel__content login-left-panel__content--stacked">
    <div class="login-theme-art">
        <div class="text-7xl animate-float-gentle" aria-hidden="true">🎄</div>
    </div>
    @include('partials.login-themes.partials.welcome-heading', [
        'tone' => 'christmas',
        'tagline' => 'Warm wishes — sign in to continue',
    ])
</div>
