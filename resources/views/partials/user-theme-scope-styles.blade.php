@php
    $cssPrefix = $varPrefix ?? 'user-theme';
    $themeDark = \App\Support\UserThemeColor::darken($themeColor, 0.82);
    $themeDarker = \App\Support\UserThemeColor::darken($themeColor, 0.68);
    $themeLight = \App\Support\UserThemeColor::lighten($themeColor, 1.08);
    $themeMid = \App\Support\UserThemeColor::lighten($themeColor, 0.95);
@endphp
<style>
    .{{ $scopeClass }} {
        --{{ $cssPrefix }}: {{ $themeColor }};
        --{{ $cssPrefix }}-dark: {{ $themeDark }};
        --{{ $cssPrefix }}-darker: {{ $themeDarker }};
        --{{ $cssPrefix }}-light: {{ $themeLight }};
        --{{ $cssPrefix }}-mid: {{ $themeMid }};
    }

    .{{ $scopeClass }} .bg-indigo-600,
    .{{ $scopeClass }} .hover\:bg-indigo-600:hover {
        background-color: var(--{{ $cssPrefix }}) !important;
    }

    .{{ $scopeClass }} .bg-indigo-700,
    .{{ $scopeClass }} .hover\:bg-indigo-700:hover {
        background-color: var(--{{ $cssPrefix }}-dark) !important;
    }

    .{{ $scopeClass }} .bg-indigo-800,
    .{{ $scopeClass }} .hover\:bg-indigo-800:hover {
        background-color: var(--{{ $cssPrefix }}-darker) !important;
    }

    .{{ $scopeClass }} .bg-indigo-500,
    .{{ $scopeClass }} .hover\:bg-indigo-500:hover {
        background-color: var(--{{ $cssPrefix }}-mid) !important;
    }

    .{{ $scopeClass }} .bg-indigo-400 {
        background-color: var(--{{ $cssPrefix }}-light) !important;
    }

    .{{ $scopeClass }} .text-indigo-100 {
        color: rgba(255, 255, 255, 0.85) !important;
    }

    .{{ $scopeClass }} .text-indigo-600,
    .{{ $scopeClass }} .hover\:text-indigo-600:hover {
        color: var(--{{ $cssPrefix }}) !important;
    }

    .{{ $scopeClass }} .text-indigo-700,
    .{{ $scopeClass }} .hover\:text-indigo-700:hover {
        color: var(--{{ $cssPrefix }}-dark) !important;
    }

    .{{ $scopeClass }} .border-indigo-500,
    .{{ $scopeClass }} .focus\:border-indigo-500:focus {
        border-color: var(--{{ $cssPrefix }}) !important;
    }

    .{{ $scopeClass }} .ring-indigo-500,
    .{{ $scopeClass }} .focus\:ring-indigo-500:focus {
        --tw-ring-color: var(--{{ $cssPrefix }}) !important;
    }

    .{{ $scopeClass }} .from-indigo-400.to-indigo-600,
    .{{ $scopeClass }} .from-indigo-400.to-indigo-600.bg-gradient-to-br {
        background-image: linear-gradient(to bottom right, var(--{{ $cssPrefix }}-light), var(--{{ $cssPrefix }})) !important;
    }

    .{{ $scopeClass }} .from-indigo-500.to-indigo-600,
    .{{ $scopeClass }} .from-indigo-500.to-indigo-600.bg-gradient-to-r {
        background-image: linear-gradient(to right, var(--{{ $cssPrefix }}-mid), var(--{{ $cssPrefix }})) !important;
    }

    .{{ $scopeClass }} .from-indigo-600.to-indigo-700,
    .{{ $scopeClass }} .from-indigo-600.to-indigo-700.bg-gradient-to-r {
        background-image: linear-gradient(to right, var(--{{ $cssPrefix }}), var(--{{ $cssPrefix }}-dark)) !important;
    }
</style>
