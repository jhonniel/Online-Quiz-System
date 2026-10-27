<div class="login-christmas-snow login-christmas-snow--hohoho" aria-hidden="true">
    @for ($i = 0; $i < 22; $i++)
        <span style="left: {{ ($i * 4.3) % 100 }}%; animation-duration: {{ 9 + ($i % 6) }}s; animation-delay: {{ ($i % 4) * 0.5 }}s;"></span>
    @endfor
</div>

<div class="login-left-panel__content login-left-panel__content--hohoho">
    <div class="text-center text-white max-w-xl w-full mx-auto">
        @include('partials.login-themes.partials.hohoho-scene')
        @include('partials.login-themes.partials.welcome-heading', [
            'tone' => 'hohoho',
            'tagline' => 'Merry season — sign in to continue',
        ])
    </div>
</div>
