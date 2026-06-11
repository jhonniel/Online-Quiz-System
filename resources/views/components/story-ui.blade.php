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
    <div class="absolute top-0 left-0 right-0 z-20 p-4 space-y-3">
        <div id="story-progress-bars" class="flex gap-1"></div>
        <div class="flex items-center justify-between text-white">
            <div class="flex items-center gap-3 min-w-0">
                <img id="story-viewer-avatar" src="" alt="" class="hidden w-9 h-9 rounded-full object-cover border border-white/30">
                <div id="story-viewer-initials" class="hidden w-9 h-9 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-semibold"></div>
                <div class="min-w-0">
                    <p id="story-viewer-name" class="text-sm font-semibold truncate"></p>
                    <p id="story-viewer-timer" class="text-xs text-amber-200"></p>
                </div>
            </div>
            <button type="button" onclick="window.StoryUI && window.StoryUI.closeViewer()" class="text-white/90 hover:text-white p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    <button type="button" class="absolute left-0 top-0 bottom-0 w-1/3 z-10" onclick="window.StoryUI && window.StoryUI.prev()" aria-label="Previous"></button>
    <button type="button" class="absolute right-0 top-0 bottom-0 w-1/3 z-10" onclick="window.StoryUI && window.StoryUI.next()" aria-label="Next"></button>
    <div class="absolute inset-0 flex items-center justify-center p-4 pt-24 pb-16">
        <img id="story-viewer-image" src="" alt="Story" class="max-w-full max-h-full object-contain select-none">
    </div>
    <p id="story-viewer-caption" class="absolute bottom-6 left-0 right-0 text-center text-white text-sm px-6"></p>
</div>

<script>
window.StoryUI = (function () {
    let pendingFile = null;
    let viewerStories = [];
    let viewerIndex = 0;
    let viewerUser = null;
    let timerInterval = null;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function formatRemaining(iso) {
        const remainingMs = new Date(iso).getTime() - Date.now();
        if (remainingMs <= 0) return 'Expired';
        const totalSeconds = Math.floor(remainingMs / 1000);
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')} left`;
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

    function renderProgressBars() {
        const container = document.getElementById('story-progress-bars');
        container.innerHTML = '';
        viewerStories.forEach((_, index) => {
            const bar = document.createElement('div');
            bar.className = 'h-1 flex-1 rounded-full bg-white/30 overflow-hidden';
            const fill = document.createElement('div');
            fill.className = 'h-full bg-amber-300 transition-all';
            fill.style.width = index < viewerIndex ? '100%' : (index === viewerIndex ? '50%' : '0%');
            bar.appendChild(fill);
            container.appendChild(bar);
        });
    }

    function updateViewerTimer() {
        const story = viewerStories[viewerIndex];
        if (!story) return;
        document.getElementById('story-viewer-timer').textContent = formatRemaining(story.expires_at);
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
        viewerStories = [];
        viewerIndex = 0;
        viewerUser = null;
    }

    async function openUser(userId) {
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
                if (Number(userId) === @json(auth()->id())) {
                    openCreateModal();
                }
                return;
            }

            viewerUser = data.user;
            viewerStories = data.stories;
            viewerIndex = 0;

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

            openViewer();
        } catch (error) {
            alert(error.message || 'Unable to load stories.');
        }
    }

    function next() {
        if (viewerIndex < viewerStories.length - 1) {
            viewerIndex++;
            showCurrentStory();
        } else {
            closeViewer();
            window.location.reload();
        }
    }

    function prev() {
        if (viewerIndex > 0) {
            viewerIndex--;
            showCurrentStory();
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
