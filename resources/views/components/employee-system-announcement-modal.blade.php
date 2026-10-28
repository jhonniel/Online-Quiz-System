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
                    document.body.classList.remove('overflow-hidden', 'user-portal-modal-open');
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
    x-init="document.body.classList.add('overflow-hidden', 'user-portal-modal-open')"
    x-show="open"
    x-cloak
    class="user-portal-modal user-portal-modal--top"
    aria-modal="true"
    role="dialog"
    aria-labelledby="employee-announcement-modal-title"
>
    <div class="user-portal-modal__backdrop" aria-hidden="true"></div>
    <div class="user-portal-modal__dialog user-portal-modal__dialog--wide">
        <div class="user-portal-modal__panel">
            <div class="user-portal-modal__header text-center">
                <h2 id="employee-announcement-modal-title" class="user-portal-modal__title">
                    {{ $announcement->title }}
                </h2>
                <p class="user-portal-modal__subtitle">
                    Please read this announcement before continuing.
                </p>
            </div>
            <div
                tabindex="0"
                role="region"
                aria-label="Announcement content"
                class="user-portal-modal__body custom-scrollbar space-y-4"
            >
                <div class="employee-announcement-content space-y-3">{!! $announcementContent !!}</div>
                @if($announcement->hasFeatureLinks())
                    <div class="pt-2 flex flex-wrap gap-2">
                        @foreach($announcement->resolvedFeatureLinks() as $featureLink)
                            <a href="{{ $featureLink['href'] }}"
                               class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                {{ $featureLink['label'] }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="user-portal-modal__footer flex-col !items-stretch">
                <label class="flex items-start gap-3 cursor-pointer select-none w-full sm:col-span-full">
                    <input
                        type="checkbox"
                        x-model="agreed"
                        class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    >
                    <span class="text-sm text-gray-700">
                        I have read and agree to this announcement.
                    </span>
                </label>
                <p x-show="error" x-text="error" class="text-sm text-red-600 w-full sm:col-span-full" x-cloak></p>
                <button
                    type="button"
                    x-on:click="confirm()"
                    :disabled="!agreed || submitting"
                    class="user-portal-modal__btn user-portal-modal__btn--primary w-full sm:w-auto sm:ml-auto"
                >
                    <span x-show="!submitting">I agree</span>
                    <span x-show="submitting" x-cloak>Please wait…</span>
                </button>
            </div>
        </div>
    </div>
</div>
