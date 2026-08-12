@props(['feed'])

@php
    $visibleEntries = $feed
        ->filter(fn ($entry) => (bool) ($entry['has_story'] ?? false))
        ->values();
@endphp

@if($visibleEntries->isNotEmpty())
    <div class="mb-6">
        <h4 class="text-sm font-semibold text-gray-900 mb-3">Stories</h4>
        <div class="story-bar-track scrollbar-thin-x">
            @foreach($visibleEntries as $entry)
                @php($storyUser = $entry['user'])
                <div class="story-bar-item">
                    @if($entry['is_self'])
                        <div class="story-bar-self-wrap">
                            <x-profile-avatar
                                :user="$storyUser"
                                size="lg"
                                :has-story="true"
                                :clickable="true"
                                :story-user-id="$storyUser->id" />
                        </div>
                        <p class="text-xs text-gray-700 mt-2 truncate w-full font-medium">Your story</p>
                    @else
                        <x-profile-avatar
                            :user="$storyUser"
                            size="lg"
                            :has-story="true"
                            :has-unviewed="$entry['has_unviewed']"
                            :clickable="true"
                            :story-user-id="$storyUser->id" />
                        <p class="text-xs text-gray-600 mt-2 truncate w-full"><x-user-name :user="$storyUser" :size="14" /></p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
