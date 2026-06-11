@props(['feed'])

<div class="mb-6">
        <h4 class="text-sm font-semibold text-gray-900 mb-3">Stories</h4>
        <div class="story-bar-track scrollbar-thin-x">
            @foreach($feed as $entry)
                @php($storyUser = $entry['user'])
                <div class="story-bar-item">
                    @if($entry['is_self'])
                        <div class="story-bar-self-wrap">
                            <x-profile-avatar
                                :user="$storyUser"
                                size="lg"
                                :has-story="$entry['has_story']"
                                :clickable="true"
                                :story-user-id="$storyUser->id" />
                            <button type="button"
                                    onclick="event.stopPropagation(); window.StoryUI && window.StoryUI.openCreateModal()"
                                    class="absolute bottom-0 right-0 translate-x-1/4 translate-y-1/4 w-7 h-7 rounded-full bg-indigo-600 text-white border-2 border-white flex items-center justify-center shadow-md hover:bg-indigo-700 z-10"
                                    title="Add to your story">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"></path>
                                </svg>
                            </button>
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
                        <p class="text-xs text-gray-600 mt-2 truncate w-full">{{ $storyUser->name }}</p>
                    @endif
                </div>
            @endforeach
        </div>
</div>
