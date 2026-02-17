@props(['title' => '', 'subtitle' => '', 'actions' => null, 'class' => ''])

<div class="bg-white shadow-xl rounded-xl border border-gray-100 {{ $class }}">
    @if($title || $subtitle || $actions)
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    @if($title)
                        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                    @endif
                    @if($subtitle)
                        <p class="mt-1 text-sm text-gray-600">{{ $subtitle }}</p>
                    @endif
                </div>
                @if($actions)
                    <div class="flex items-center space-x-2">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="px-6 py-6">
        {{ $slot }}
    </div>
</div>
