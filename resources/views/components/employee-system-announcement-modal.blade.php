@php
    /** @var \App\Models\SystemAnnouncement $announcement */
    $announcementContent = $announcement->displayContentHtml();
@endphp
<style>
    .employee-announcement-content .announcement-list {
        margin: 0.5rem 0 0.75rem;
        padding-left: 1.25rem;
        list-style-type: disc;
    }
    .employee-announcement-content .announcement-list-ordered {
        list-style-type: decimal;
    }
    .employee-announcement-content .announcement-list li {
        margin: 0.35rem 0;
        padding-left: 0.15rem;
    }
    .employee-announcement-content p {
        margin: 0 0 0.75rem;
    }
    .employee-announcement-content p:last-child {
        margin-bottom: 0;
    }
</style>
<script>
    function employeeSystemAnnouncementModal() {
        return {
            agreed: false,
            open: true,
            submitting: false,
            error: '',
            announcementId: @json($announcement->id),
            ackUrl: @json(route('user.system-announcement.acknowledge')),
            csrf: @json(csrf_token()),
            async confirm() {
                if (!this.agreed || this.submitting) {
                    return;
                }
                this.submitting = true;
                this.error = '';
                try {
                    const response = await fetch(this.ackUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ announcement_id: this.announcementId }),
                    });
                    if (!response.ok) {
                        const data = await response.json().catch(() => ({}));
                        throw new Error(data.message || 'Something went wrong. Please try again.');
                    }
                    this.open = false;
                    document.body.classList.remove('overflow-hidden');
                    window.location.reload();
                } catch (e) {
                    this.error = e.message || 'Something went wrong. Please try again.';
                } finally {
                    this.submitting = false;
                }
            },
        };
    }
</script>
<div
    x-data="employeeSystemAnnouncementModal()"
    x-init="document.body.classList.add('overflow-hidden')"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6"
    aria-modal="true"
    role="dialog"
    aria-labelledby="employee-announcement-modal-title"
>
    <div class="absolute inset-0 bg-gray-900/60" aria-hidden="true"></div>
    <div class="relative w-full max-w-2xl rounded-xl shadow-xl">
        <div class="flex max-h-[85vh] flex-col overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="flex-shrink-0 border-b border-gray-100 px-5 py-4">
                <h2 id="employee-announcement-modal-title" class="text-base sm:text-lg font-bold text-gray-900 text-center tracking-tight">
                    {{ $announcement->title }}
                </h2>
                <p class="mt-1 text-xs text-gray-600 text-center">
                    Please read this announcement.
                </p>
            </div>
            <div
                tabindex="0"
                role="region"
                aria-label="Announcement content"
                class="flex-1 min-h-0 overflow-y-auto px-5 py-4 custom-scrollbar text-sm text-gray-700 leading-relaxed space-y-4"
            >
                <div class="employee-announcement-content space-y-3">{!! $announcementContent !!}</div>
            </div>
            <div class="flex-shrink-0 border-t border-gray-100 px-5 py-4 space-y-3">
                <label class="flex items-start gap-3 cursor-pointer select-none">
                    <input
                        type="checkbox"
                        x-model="agreed"
                        class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    >
                    <span class="text-sm text-gray-700">
                        I have read and agree to this announcement.
                    </span>
                </label>
                <p x-show="error" x-text="error" class="text-sm text-red-600" x-cloak></p>
                <button
                    type="button"
                    x-on:click="confirm()"
                    :disabled="!agreed || submitting"
                    class="w-full inline-flex justify-center items-center rounded-lg px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!submitting">I agree</span>
                    <span x-show="submitting" x-cloak>Please wait…</span>
                </button>
            </div>
        </div>
    </div>
</div>
