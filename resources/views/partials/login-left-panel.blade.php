@php
    use App\Support\LoginPageTheme;
    $loginTheme = LoginPageTheme::normalize($settings['login_page_theme'] ?? LoginPageTheme::DEFAULT);
    $loginThemeView = 'partials.login-themes.'.$loginTheme;
    if (! view()->exists($loginThemeView)) {
        $loginTheme = LoginPageTheme::DEFAULT;
        $loginThemeView = 'partials.login-themes.default';
    }
@endphp

<div class="login-left-panel login-left-panel--{{ $loginTheme }} hidden lg:flex lg:w-2/3 relative overflow-hidden">
    @include($loginThemeView)
</div>
