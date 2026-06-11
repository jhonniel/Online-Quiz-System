@props(['feed' => collect()])

@php
    $storyFeedUsers = $feed->map(fn ($entry) => [
        'id' => $entry['user']->id,
        'name' => $entry['user']->name,
        'is_self' => (bool) ($entry['is_self'] ?? false),
        'has_story' => (bool) ($entry['has_story'] ?? true),
    ])->values();
@endphp

<div id="story-create-modal" class="hidden fixed inset-0 z-[70] overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/60" onclick="window.StoryUI && window.StoryUI.closeCreateModal()"></div>
        <div class="relative w-full max-w-md rounded-xl bg-white shadow-xl">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Create story</h3>
                <p class="text-sm text-gray-500 mt-1">Share a photo on your profile for 24 hours. Friends can view it from your avatar ring.</p>
            </div>
            <div class="px-6 py-5 space-y-4">
                <input type="file" id="story-image-input" class="hidden" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                <button type="button" onclick="document.getElementById('story-image-input').click()"
                        class="w-full border-2 border-dashed border-gray-300 rounded-lg py-8 text-sm text-gray-600 hover:border-indigo-400 hover:text-indigo-600">
                    Choose photo (max 5MB)
                </button>
                <img id="story-create-preview" src="" alt="Preview" class="hidden max-h-48 mx-auto rounded-lg object-contain">
                <input type="text" id="story-caption-input" maxlength="500" placeholder="Optional caption..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
                <button type="button" onclick="window.StoryUI && window.StoryUI.closeCreateModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                <button type="button" id="story-publish-button" onclick="window.StoryUI && window.StoryUI.publish()"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Post story</button>
            </div>
        </div>
    </div>
</div>

<div id="story-viewer" class="hidden fixed inset-0 z-[80] bg-black">
    <div id="story-progress-bars" class="absolute top-0 left-0 right-0 z-30 flex gap-1 px-3 pt-3 pointer-events-none"></div>
    <div class="absolute top-0 left-0 right-0 z-20 p-4 pt-8">
        <div class="flex items-center justify-between text-white gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <img id="story-viewer-avatar" src="" alt="" class="hidden w-9 h-9 rounded-full object-cover border border-white/30">
                <div id="story-viewer-initials" class="hidden w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-semibold"></div>
                <div class="min-w-0">
                    <p id="story-viewer-name" class="text-sm font-semibold truncate"></p>
                    <p id="story-viewer-timer" class="text-xs text-amber-200"></p>
                </div>
            </div>
            <button type="button" onclick="window.StoryUI && window.StoryUI.closeViewer()" class="text-white/90 hover:text-white p-2 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    <button type="button" class="absolute left-0 top-0 bottom-0 w-1/4 z-10" onclick="window.StoryUI && window.StoryUI.prev()" aria-label="Previous"></button>
    <button type="button" class="absolute right-0 top-0 bottom-0 w-1/4 z-10" onclick="window.StoryUI && window.StoryUI.next()" aria-label="Next"></button>
    <div class="absolute inset-0 flex items-center justify-center p-4 pt-20 pb-20">
        <img id="story-viewer-image" src="" alt="Story" class="max-w-full max-h-full object-contain select-none">
    </div>
    <p id="story-viewer-caption" class="absolute bottom-16 left-0 right-0 text-center text-white text-sm px-6 pointer-events-none"></p>
    <div class="absolute bottom-4 left-0 right-0 z-20 flex items-center justify-center gap-3 px-4">
        <button type="button"
                id="story-viewer-prev-btn"
                onclick="window.StoryUI && window.StoryUI.prev()"
                class="px-4 py-2 rounded-full bg-white/15 text-white text-sm font-medium hover:bg-white/25 backdrop-blur-sm">
            Previous
        </button>
        <button type="button"
                id="story-viewer-next-btn"
                onclick="window.StoryUI && window.StoryUI.next()"
                class="px-5 py-2 rounded-full bg-white text-gray-900 text-sm font-semibold hover:bg-gray-100 shadow-md">
            Next
        </button>
    </div>
</div>

<script>
window.StoryUI = (function () {
    let pendingFile = null;
    let viewerStories = [];
    let viewerIndex = 0;
    let viewerUser = null;
    let timerInterval = null;
    const authUserId = @json(auth()->id());
    const storyFeedUsers = @json($storyFeedUsers);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const STORY_LIFETIME_MS = 24 * 60 * 60 * 1000;

    function formatStoryAge(iso) {
        if (!iso) return '0s';
        const elapsedMs = Math.max(0, Date.now() - new Date(iso).getTime());
        const totalSeconds = Math.floor(elapsedMs / 1000);

        if (totalSeconds < 60) {
            return `${totalSeconds}s`;
        }

        const totalMinutes = Math.floor(totalSeconds / 60);
        if (totalMinutes < 60) {
            return `${totalMinutes}min`;
        }

        const totalHours = Math.floor(totalSeconds / 3600);
        return `${totalHours}hr`;
    }

    function storyLifetimeProgress(iso) {
        if (!iso) return 0;
        const elapsedMs = Math.max(0, Date.now() - new Date(iso).getTime());
        return Math.min(100, (elapsedMs / STORY_LIFETIME_MS) * 100);
    }

    function renderProgressBars() {
        const container = document.getElementById('story-progress-bars');
        if (!container) return;

        container.innerHTML = '';

        viewerStories.forEach((story, index) => {
            const track = document.createElement('div');
            track.className = 'story-progress-track h-0.5 flex-1 rounded-full bg-white/30 overflow-hidden';

            const fill = document.createElement('div');
            fill.className = 'story-progress-fill h-full bg-white rounded-full';
            fill.dataset.storyIndex = String(index);

            if (index < viewerIndex) {
                fill.style.width = '100%';
            } else if (index === viewerIndex) {
                fill.style.width = storyLifetimeProgress(story.created_at) + '%';
            } else {
                fill.style.width = '0%';
            }

            track.appendChild(fill);
            container.appendChild(track);
        });
    }

    function updateProgressBars() {
        const fills = document.querySelectorAll('#story-progress-bars .story-progress-fill');
        fills.forEach((fill) => {
            const index = Number(fill.dataset.storyIndex);
            const story = viewerStories[index];
            if (!story) return;

            if (index < viewerIndex) {
                fill.style.width = '100%';
            } else if (index === viewerIndex) {
                fill.style.width = storyLifetimeProgress(story.created_at) + '%';
            } else {
                fill.style.width = '0%';
            }
        });
    }

    function openCreateModal() {
        document.getElementById('story-create-modal').classList.remove('hidden');
    }

    function closeCreateModal() {
        document.getElementById('story-create-modal').classList.add('hidden');
        pendingFile = null;
        document.getElementById('story-image-input').value = '';
        document.getElementById('story-caption-input').value = '';
        const preview = document.getElementById('story-create-preview');
        preview.classList.add('hidden');
        preview.removeAttribute('src');
    }

    async function publish() {
        if (!pendingFile) {
            alert('Choose a photo first.');
            return;
        }

        const button = document.getElementById('story-publish-button');
        button.disabled = true;

        const formData = new FormData();
        formData.append('image', pendingFile);
        const caption = document.getElementById('story-caption-input').value.trim();
        if (caption) formData.append('caption', caption);

        try {
            const response = await fetch(@json(route('stories.store')), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
                body: formData,
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Unable to post story.');
            }
            closeCreateModal();
            window.location.reload();
        } catch (error) {
            alert(error.message || 'Unable to post story.');
        } finally {
            button.disabled = false;
        }
    }

    function updateViewerTimer() {
        const story = viewerStories[viewerIndex];
        if (!story) return;
        document.getElementById('story-viewer-timer').textContent = formatStoryAge(story.created_at);
        updateProgressBars();
    }

    function updateNavButtons() {
        const prevBtn = document.getElementById('story-viewer-prev-btn');
        const nextBtn = document.getElementById('story-viewer-next-btn');
        const hasPrevStory = viewerIndex > 0;
        const hasNextStory = viewerIndex < viewerStories.length - 1;
        const hasNextUser = findNextFeedUserIndex(viewerUser?.id) !== -1;

        prevBtn.disabled = !hasPrevStory;
        prevBtn.classList.toggle('opacity-40', !hasPrevStory);
        prevBtn.classList.toggle('cursor-not-allowed', !hasPrevStory);

        nextBtn.textContent = hasNextStory || hasNextUser ? 'Next' : 'Close';
    }

    function findNextFeedUserIndex(currentUserId) {
        const currentIndex = storyFeedUsers.findIndex(entry => Number(entry.id) === Number(currentUserId));
        for (let i = currentIndex + 1; i < storyFeedUsers.length; i++) {
            if (storyFeedUsers[i].has_story && !storyFeedUsers[i].is_self) {
                return i;
            }
        }
        return -1;
    }

    function findPrevFeedUserIndex(currentUserId) {
        const currentIndex = storyFeedUsers.findIndex(entry => Number(entry.id) === Number(currentUserId));
        for (let i = currentIndex - 1; i >= 0; i--) {
            if (storyFeedUsers[i].has_story && !storyFeedUsers[i].is_self) {
                return i;
            }
        }
        return -1;
    }

    async function showCurrentStory() {
        const story = viewerStories[viewerIndex];
        if (!story) {
            closeViewer();
            return;
        }

        document.getElementById('story-viewer-image').src = story.media_url;
        document.getElementById('story-viewer-caption').textContent = story.caption || '';
        renderProgressBars();
        updateViewerTimer();
        updateNavButtons();

        if (!story.is_own && !story.viewed) {
            await fetch(@json(url('/stories')) + `/${story.id}/view`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
            }).catch(() => {});
            story.viewed = true;
        }
    }

    function openViewer() {
        document.getElementById('story-viewer').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(updateViewerTimer, 1000);
        showCurrentStory();
    }

    function closeViewer() {
        document.getElementById('story-viewer').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = null;
        const progressBars = document.getElementById('story-progress-bars');
        if (progressBars) progressBars.innerHTML = '';
        viewerStories = [];
        viewerIndex = 0;
        viewerUser = null;
    }

    function applyViewerUser(user) {
        viewerUser = user;
        document.getElementById('story-viewer-name').textContent = viewerUser.name;
        const avatar = document.getElementById('story-viewer-avatar');
        const initials = document.getElementById('story-viewer-initials');
        if (viewerUser.profile_picture_url) {
            avatar.src = viewerUser.profile_picture_url;
            avatar.classList.remove('hidden');
            initials.classList.add('hidden');
        } else {
            avatar.classList.add('hidden');
            initials.textContent = viewerUser.initials || '?';
            initials.classList.remove('hidden');
        }
    }

    async function openUser(userId, fromAdvance = false) {
        try {
            const response = await fetch(@json(url('/stories/user')) + `/${userId}`, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Unable to load stories.');
            }

            if (!data.stories || data.stories.length === 0) {
                if (!fromAdvance && Number(userId) === authUserId) {
                    openCreateModal();
                    return;
                }
                if (fromAdvance) {
                    await advanceToNextUser(userId);
                    return;
                }
                return;
            }

            const wasOpen = !document.getElementById('story-viewer').classList.contains('hidden');
            viewerStories = data.stories;
            viewerIndex = 0;
            applyViewerUser(data.user);

            if (wasOpen) {
                await showCurrentStory();
            } else {
                openViewer();
            }
        } catch (error) {
            if (!fromAdvance) {
                alert(error.message || 'Unable to load stories.');
            } else {
                closeViewer();
            }
        }
    }

    async function advanceToNextUser(afterUserId) {
        const nextIndex = findNextFeedUserIndex(afterUserId);
        if (nextIndex === -1) {
            closeViewer();
            return;
        }
        await openUser(storyFeedUsers[nextIndex].id, true);
    }

    async function advanceToPrevUser(beforeUserId) {
        const prevIndex = findPrevFeedUserIndex(beforeUserId);
        if (prevIndex === -1) {
            return;
        }
        const response = await fetch(@json(url('/stories/user')) + `/${storyFeedUsers[prevIndex].id}`, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await response.json();
        if (!response.ok || !data.stories?.length) {
            return;
        }
        viewerStories = data.stories;
        viewerIndex = viewerStories.length - 1;
        applyViewerUser(data.user);
        await showCurrentStory();
    }

    async function next() {
        if (viewerIndex < viewerStories.length - 1) {
            viewerIndex++;
            await showCurrentStory();
            return;
        }
        if (viewerUser?.id) {
            await advanceToNextUser(viewerUser.id);
            return;
        }
        closeViewer();
    }

    async function prev() {
        if (viewerIndex > 0) {
            viewerIndex--;
            await showCurrentStory();
            return;
        }
        if (viewerUser?.id) {
            await advanceToPrevUser(viewerUser.id);
        }
    }

    document.getElementById('story-image-input')?.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;
        if (file.size > 5242880) {
            alert('Story images must be 5MB or smaller.');
            this.value = '';
            return;
        }
        pendingFile = file;
        const preview = document.getElementById('story-create-preview');
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
    });

    return {
        openCreateModal,
        closeCreateModal,
        publish,
        openUser,
        closeViewer,
        next,
        prev,
    };
})();
</script>
