@props([
    'user',
    'size' => 'md',
    'hasStory' => false,
    'hasUnviewed' => false,
    'clickable' => false,
    'storyUserId' => null,
])

@php
    $sizes = [
        'xs' => ['wrap' => 'w-8 h-8', 'text' => 'text-xs', 'online' => 'w-2 h-2'],
        'sm' => ['wrap' => 'w-10 h-10', 'text' => 'text-sm', 'online' => 'w-2.5 h-2.5'],
        'md' => ['wrap' => 'w-12 h-12', 'text' => 'text-sm', 'online' => 'w-3 h-3'],
        'lg' => ['wrap' => 'w-20 h-20', 'text' => 'text-lg', 'online' => 'w-3.5 h-3.5'],
        'xl' => ['wrap' => 'w-32 h-32', 'text' => 'text-2xl', 'online' => 'w-6 h-6'],
    ];
    $sizeConfig = $sizes[$size] ?? $sizes['md'];
    $ringClass = $hasStory ? ($hasUnviewed ? 'story-ring story-ring--unviewed' : 'story-ring') : '';
    $shellClass = $hasStory ? ($hasUnviewed ? 'story-avatar-shell--unviewed' : 'story-avatar-shell--has-story') : '';
    $targetUserId = $storyUserId ?? $user->id;
@endphp

<div {{ $attributes->merge(['class' => 'story-avatar-shell '.$shellClass]) }}>
    @if($hasStory)
        <span class="story-ring-glow" aria-hidden="true"></span>
    @endif
    @if($clickable)
        <button type="button"
                class="story-avatar-trigger {{ $ringClass }} focus:outline-none focus:ring-2 focus:ring-amber-400/60"
                data-story-user-id="{{ $targetUserId }}"
                onclick="event.stopPropagation(); window.StoryUI && window.StoryUI.openUser({{ $targetUserId }})">
            <span class="story-avatar-inner block {{ $sizeConfig['wrap'] }} rounded-full overflow-hidden bg-white ring-2 ring-white shadow-sm">
                @if($user->profile_picture)
                    <img src="{{ $user->getProfilePictureUrl() }}"
                         alt="{{ $user->name }}"
                         class="w-full h-full object-cover">
                @else
                    <span class="w-full h-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white font-semibold {{ $sizeConfig['text'] }}">
                        {{ $user->getInitials() }}
                    </span>
                @endif
            </span>
        </button>
    @else
        <div class="{{ $ringClass }}">
            <span class="story-avatar-inner block {{ $sizeConfig['wrap'] }} rounded-full overflow-hidden bg-white ring-2 ring-white shadow-sm">
                @if($user->profile_picture)
                    <img src="{{ $user->getProfilePictureUrl() }}"
                         alt="{{ $user->name }}"
                         class="w-full h-full object-cover">
                @else
                    <span class="w-full h-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white font-semibold {{ $sizeConfig['text'] }}">
                        {{ $user->getInitials() }}
                    </span>
                @endif
            </span>
        </div>
    @endif
</div>
