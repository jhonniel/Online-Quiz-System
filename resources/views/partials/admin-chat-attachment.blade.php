@php
    $media = $media ?? null;
@endphp

@if(is_array($media))
    <div class="mt-3">
        @if(! empty($media['available']) && ! empty($media['url']))
            <a href="{{ $media['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-block">
                <img
                    src="{{ $media['url'] }}"
                    alt="Chat attachment"
                    class="max-w-xs max-h-56 rounded-lg border border-gray-200 shadow-sm object-contain bg-gray-50"
                >
            </a>
            <p class="mt-1 text-xs text-gray-500">
                {{ $media['mode'] === \App\Models\ChatMessageMedia::MODE_VIEW_ONCE ? 'View once' : '24-hour image' }}
                @if(! empty($media['expires_at']))
                    · expires {{ \Carbon\Carbon::parse($media['expires_at'])->format('M j, g:i A') }}
                @endif
                @if(($media['view_count'] ?? 0) > 0)
                    · viewed {{ $media['view_count'] }} {{ Str::plural('time', $media['view_count']) }}
                @endif
                @if(! empty($media['expired']))
                    · <span class="text-amber-700 font-medium">expired (admin preview)</span>
                @endif
            </p>
        @else
            <p class="text-xs text-gray-500 italic">
                Image attachment unavailable
                @if(! empty($media['expired']))
                    (expired)
                @else
                    (removed)
                @endif
            </p>
        @endif
    </div>
@endif
