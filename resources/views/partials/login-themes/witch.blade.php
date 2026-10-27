<div class="login-halloween-fog" aria-hidden="true"></div>

<div class="login-left-panel__content login-left-panel__content--halloween">
    <div class="text-center text-white max-w-xl w-full">
        <div class="relative mx-auto flex flex-col items-center justify-center">
            @include('partials.login-themes.partials.witch-scene')
        </div>
        @include('partials.login-themes.partials.welcome-heading', [
            'tone' => 'halloween',
            'tagline' => 'Sign in — if you dare',
        ])
    </div>
</div>
