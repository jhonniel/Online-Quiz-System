@props([
    'items' => [],
])

@if(count($items) > 0)
@php
    $leaveResubmissionWindowDays = \App\Services\LeaveRequestStaleResubmissionService::RESPONSE_WINDOW_DAYS;
@endphp
<div
    id="leave-resubmission-required-modal-root"
    x-data="{
        open: true,
        init() {
            const suppressOnce = sessionStorage.getItem('leaveResubmissionModalSuppressOnce') === '1';
            if (suppressOnce) {
                this.open = false;
                sessionStorage.removeItem('leaveResubmissionModalSuppressOnce');
                document.body.classList.remove('overflow-hidden', 'user-portal-modal-open');
                return;
            }

            document.body.classList.add('overflow-hidden', 'user-portal-modal-open');
            window.addEventListener('pageshow', () => {
                this.open = true;
                document.body.classList.add('overflow-hidden', 'user-portal-modal-open');
            });
        }
    }"
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="open = false; document.body.classList.remove('overflow-hidden', 'user-portal-modal-open')"
    class="user-portal-modal user-portal-modal--amber user-portal-modal--top"
    role="dialog"
    aria-modal="true"
    aria-labelledby="leave-resubmission-modal-title"
>
    <div
        class="user-portal-modal__backdrop"
        @click="open = false; document.body.classList.remove('overflow-hidden', 'user-portal-modal-open')"
        aria-hidden="true"
    ></div>

    <div class="user-portal-modal__dialog">
        <div class="user-portal-modal__panel">
            <div class="user-portal-modal__header">
                <h2 id="leave-resubmission-modal-title" class="user-portal-modal__title">
                    Resubmission required
                </h2>
                <p class="user-portal-modal__subtitle">
                    {{ count($items) === 1 ? 'One leave request needs' : count($items) . ' leave requests need' }} your updates before review can continue.
                </p>
            </div>

            <div class="user-portal-modal__body custom-scrollbar">
                <p class="user-portal-modal__note user-portal-modal__note--compact">
                    Update each request from the list below. If not updated within <strong>{{ $leaveResubmissionWindowDays }} days</strong> of the last update while resubmission is required,
                    {{ count($items) === 1 ? 'it may be rejected automatically.' : 'each may be rejected automatically.' }}
                </p>
                @foreach ($items as $row)
                    <div class="user-portal-modal__item">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                            <div class="min-w-0">
                                <p class="user-portal-modal__item-title">
                                    #{{ $row['id'] }} — {{ $row['type_label'] }}
                                </p>
                                @foreach($row['type_detail_lines'] ?? [] as $detailLine)
                                    <p class="user-portal-modal__item-meta">{{ $detailLine }}</p>
                                @endforeach
                                <p class="user-portal-modal__item-meta">
                                    Last updated: {{ $row['last_updated_formatted'] }} ({{ config('app.timezone') }})
                                </p>
                                @if(!empty($row['past_due']))
                                    <p class="mt-2 text-xs font-semibold text-red-800 bg-red-50 border border-red-100 rounded px-2 py-1 inline-block">
                                        Response window elapsed — rejection runs on the next scheduled check.
                                    </p>
                                @else
                                    @php
                                        $remainingLabel = trim((string) ($row['time_remaining_label'] ?? ''));
                                        $urgent = ($row['hours_remaining'] ?? 0) <= 48;
                                    @endphp
                                    @if($remainingLabel !== '')
                                        <p class="mt-2 text-sm font-bold tabular-nums {{ $urgent ? 'text-red-800' : 'text-amber-950' }}">
                                            Time left: {{ $remainingLabel }}
                                        </p>
                                    @endif
                                    <p class="user-portal-modal__item-meta {{ $urgent ? 'text-red-700 font-medium' : '' }}">
                                        Deadline: <time datetime="{{ $row['deadline_at']?->toIso8601String() }}">{{ $row['deadline_formatted'] }}</time>
                                    </p>
                                @endif
                            </div>
                            <a href="{{ $row['edit_url'] }}"
                               class="user-portal-modal__btn user-portal-modal__btn--primary shrink-0">
                                Update
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="user-portal-modal__footer">
                <button type="button"
                        class="user-portal-modal__btn user-portal-modal__btn--secondary"
                        @click="open = false; document.body.classList.remove('overflow-hidden', 'user-portal-modal-open')">
                    Close
                </button>
                <a href="{{ url('/leave-requests') }}"
                   class="user-portal-modal__btn user-portal-modal__btn--primary"
                   @click="sessionStorage.setItem('leaveResubmissionModalSuppressOnce', '1'); open = false; document.body.classList.remove('overflow-hidden', 'user-portal-modal-open')">
                    Leave requests
                </a>
            </div>
        </div>
    </div>
</div>
@endif
