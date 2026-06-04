@php
    $cssPrefix = $varPrefix ?? 'user-theme';
    $themeDark = \App\Support\UserThemeColor::darken($themeColor, 0.82);
    $themeDarker = \App\Support\UserThemeColor::darken($themeColor, 0.68);
    $themeLight = \App\Support\UserThemeColor::lighten($themeColor, 1.08);
    $themeMid = \App\Support\UserThemeColor::lighten($themeColor, 0.95);
    $surfaceBg = \App\Support\UserThemeColor::rgba($themeColor, 0.10);
    $surfaceBgStrong = \App\Support\UserThemeColor::rgba($themeColor, 0.16);
    $surfaceBorder = \App\Support\UserThemeColor::rgba($themeColor, 0.28);
    $surfaceText = \App\Support\UserThemeColor::darken($themeColor, 0.45);
    $surfaceTextStrong = \App\Support\UserThemeColor::darken($themeColor, 0.58);
    $badgeBg = \App\Support\UserThemeColor::rgba($themeColor, 0.18);
    $badgeText = \App\Support\UserThemeColor::darken($themeColor, 0.35);
    $rowHover = \App\Support\UserThemeColor::rgba($themeColor, 0.06);
@endphp
<style>
    .{{ $scopeClass }} {
        --{{ $cssPrefix }}: {{ $themeColor }};
        --{{ $cssPrefix }}-dark: {{ $themeDark }};
        --{{ $cssPrefix }}-darker: {{ $themeDarker }};
        --{{ $cssPrefix }}-light: {{ $themeLight }};
        --{{ $cssPrefix }}-mid: {{ $themeMid }};
        --{{ $cssPrefix }}-surface: {{ $surfaceBg }};
        --{{ $cssPrefix }}-surface-strong: {{ $surfaceBgStrong }};
        --{{ $cssPrefix }}-surface-border: {{ $surfaceBorder }};
        --{{ $cssPrefix }}-surface-text: {{ $surfaceText }};
        --{{ $cssPrefix }}-surface-text-strong: {{ $surfaceTextStrong }};
        --{{ $cssPrefix }}-badge-bg: {{ $badgeBg }};
        --{{ $cssPrefix }}-badge-text: {{ $badgeText }};
        --{{ $cssPrefix }}-row-hover: {{ $rowHover }};
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

    .{{ $scopeClass }} .text-indigo-900,
    .{{ $scopeClass }} .text-indigo-950 {
        color: var(--{{ $cssPrefix }}-surface-text-strong) !important;
    }

    .{{ $scopeClass }} .border-indigo-500,
    .{{ $scopeClass }} .focus\:border-indigo-500:focus {
        border-color: var(--{{ $cssPrefix }}) !important;
    }

    .{{ $scopeClass }} .border-indigo-200,
    .{{ $scopeClass }} .border-indigo-100 {
        border-color: var(--{{ $cssPrefix }}-surface-border) !important;
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

    /* Themed list/card surfaces */
    .{{ $scopeClass }} .bg-blue-50,
    .{{ $scopeClass }} .bg-sky-50,
    .{{ $scopeClass }} .bg-indigo-50,
    .{{ $scopeClass }} .bg-amber-50,
    .{{ $scopeClass }} .bg-amber-50\/60,
    .{{ $scopeClass }} .bg-amber-50\/40,
    .{{ $scopeClass }} .bg-amber-100\/80,
    .{{ $scopeClass }} .bg-amber-100\/50 {
        background-color: var(--{{ $cssPrefix }}-surface) !important;
    }

    .{{ $scopeClass }} .border-blue-200,
    .{{ $scopeClass }} .border-sky-200,
    .{{ $scopeClass }} .border-sky-300,
    .{{ $scopeClass }} .border-amber-200,
    .{{ $scopeClass }} .border-amber-300 {
        border-color: var(--{{ $cssPrefix }}-surface-border) !important;
    }

    .{{ $scopeClass }} .text-blue-700,
    .{{ $scopeClass }} .text-blue-800,
    .{{ $scopeClass }} .text-sky-700,
    .{{ $scopeClass }} .text-sky-800,
    .{{ $scopeClass }} .text-sky-900,
    .{{ $scopeClass }} .text-amber-900,
    .{{ $scopeClass }} .text-amber-800 {
        color: var(--{{ $cssPrefix }}-surface-text) !important;
    }

    .{{ $scopeClass }} .text-blue-400,
    .{{ $scopeClass }} .text-sky-600 {
        color: var(--{{ $cssPrefix }}-mid) !important;
    }

    .{{ $scopeClass }} .bg-blue-100,
    .{{ $scopeClass }} .hover\:bg-blue-200:hover,
    .{{ $scopeClass }} .hover\:bg-sky-100:hover {
        background-color: var(--{{ $cssPrefix }}-badge-bg) !important;
    }

    .{{ $scopeClass }} .text-blue-700.bg-blue-100,
    .{{ $scopeClass }} .bg-blue-100.text-blue-800,
    .{{ $scopeClass }} .text-sky-900.bg-white {
        color: var(--{{ $cssPrefix }}-badge-text) !important;
    }

    .{{ $scopeClass }} thead.bg-gray-50 {
        background-color: var(--{{ $cssPrefix }}-surface) !important;
    }

    .{{ $scopeClass }} tbody tr.hover\:bg-gray-50:hover {
        background-color: var(--{{ $cssPrefix }}-row-hover) !important;
    }

    .{{ $scopeClass }} .border-amber-200.rounded-lg {
        border-color: var(--{{ $cssPrefix }}-surface-border) !important;
    }
</style>
