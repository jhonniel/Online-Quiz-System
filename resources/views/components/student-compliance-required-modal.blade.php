@props([
    'items' => [],
])

@if(count($items) > 0)
<div
    id="student-compliance-required-modal-root"
    x-data="{
        open: false,
        init() {
            const suppressOnce = sessionStorage.getItem('studentComplianceModalSuppressOnce') === '1';
            if (suppressOnce) {
                sessionStorage.removeItem('studentComplianceModalSuppressOnce');
                return;
            }
            if (sessionStorage.getItem('studentComplianceModalDismissed') === '1') {
                return;
            }
            this.open = true;
            document.body.classList.add('overflow-hidden', 'user-portal-modal-open');
        },
        dismissForSession() {
            sessionStorage.setItem('studentComplianceModalDismissed', '1');
            this.open = false;
            document.body.classList.remove('overflow-hidden', 'user-portal-modal-open');
        },
        close() {
            this.open = false;
            document.body.classList.remove('overflow-hidden', 'user-portal-modal-open');
        }
    }"
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="close()"
    class="user-portal-modal user-portal-modal--indigo user-portal-modal--stack"
    role="dialog"
    aria-modal="true"
    aria-labelledby="student-compliance-modal-title"
>
    <div class="user-portal-modal__backdrop" @click="close()" aria-hidden="true"></div>

    <div class="user-portal-modal__dialog">
        <div class="user-portal-modal__panel">
            <div class="user-portal-modal__header">
                <h2 id="student-compliance-modal-title" class="user-portal-modal__title">
                    Complete required documents
                </h2>
                <p class="user-portal-modal__subtitle">
                    Finish these items to use the system fully, including DTR attendance.
                </p>
            </div>

            <div class="user-portal-modal__body custom-scrollbar">
                @foreach ($items as $item)
                    @php
                        $tone = $item['status_tone'] ?? 'amber';
                        $badgeClass = match ($tone) {
                            'emerald' => 'bg-emerald-100 text-emerald-800',
                            'rose' => 'bg-rose-100 text-rose-800',
                            default => 'bg-amber-100 text-amber-800',
                        };
                    @endphp
                    <div class="user-portal-modal__item">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="user-portal-modal__item-title">{{ $item['label'] }}</p>
                                <p class="user-portal-modal__item-meta">{{ $item['description'] }}</p>
                            </div>
                            <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeClass }}">
                                {{ $item['status_label'] }}
                            </span>
                        </div>
                        <a href="{{ $item['url'] }}"
                           onclick="sessionStorage.setItem('studentComplianceModalSuppressOnce', '1')"
                           class="mt-2 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            Open {{ $item['key'] === 'nda' ? 'NDA page' : 'document' }}
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="user-portal-modal__footer">
                <button type="button"
                        @click="dismissForSession()"
                        class="user-portal-modal__btn user-portal-modal__btn--secondary">
                    Remind me later
                </button>
                @if(collect($items)->firstWhere('key', 'nda'))
                    <a href="{{ route('user.nda.index') }}"
                       onclick="sessionStorage.setItem('studentComplianceModalSuppressOnce', '1')"
                       class="user-portal-modal__btn user-portal-modal__btn--primary">
                        Go to NDA
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
