@php
    use App\Support\LoginPageTheme;
    $currentLoginTheme = LoginPageTheme::normalize($settings['login_page_theme'] ?? LoginPageTheme::DEFAULT);
    $loginThemeLabels = LoginPageTheme::labels();
    if ($currentLoginTheme === LoginPageTheme::HALLOWEEN && ! array_key_exists(LoginPageTheme::HALLOWEEN, $loginThemeLabels)) {
        $loginThemeLabels = [LoginPageTheme::HALLOWEEN => 'Halloween — all three (legacy combined)'] + $loginThemeLabels;
    }
@endphp

<div class="form-section" id="login-page-theme">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 bg-orange-100 rounded-lg p-2">
                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Login page (left panel)</h3>
                <p class="text-sm text-gray-500 mt-1 max-w-2xl">
                    Separate from homepage seasonal effects. Pick <strong class="text-gray-800">Boo</strong>, <strong class="text-gray-800">Witch</strong>, or <strong class="text-gray-800">Ghosto</strong> individually on <strong class="text-gray-800">/login</strong>.
                </p>
            </div>
        </div>
        <a href="{{ url('/login') }}" target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-orange-200 bg-white text-sm font-semibold text-orange-800 hover:bg-orange-50 whitespace-nowrap shrink-0">
            Open login page
            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
        </a>
    </div>

    <label for="login_page_theme" class="block text-sm font-medium text-gray-700 mb-2">Left panel theme</label>
    <select name="login_page_theme" id="login_page_theme"
            class="w-full max-w-xl px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        @foreach($loginThemeLabels as $value => $label)
            <option value="{{ $value }}" @selected($currentLoginTheme === $value)>
                @if($value === 'boo')👻
                @elseif($value === 'witch')🧙
                @elseif($value === 'ghosto')🎃
                @elseif($value === 'halloween')🎃
                @elseif($value === 'christmas')❄️
                @elseif($value === 'hohoho')🎅
                @endif{{ $label }}
            </option>
        @endforeach
    </select>
    <ul class="mt-3 text-xs text-gray-500 space-y-1 list-disc list-inside max-w-2xl">
        <li>Click <strong class="text-gray-700">Save Settings</strong> at the bottom after changing this.</li>
        <li>Animations show on the <strong class="text-gray-700">left two-thirds</strong> of the login page on screens <strong class="text-gray-700">1024px wide or larger</strong> (laptop/desktop). Phones and narrow windows only show the login form — that is intentional.</li>
        <li>Homepage spiders/snow use <strong class="text-gray-700">Seasonal Effects</strong> below; they do not change the login panel.</li>
    </ul>
</div>
