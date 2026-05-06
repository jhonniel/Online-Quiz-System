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
                document.body.classList.remove('overflow-hidden');
                return;
            }

            document.body.classList.add('overflow-hidden');
            window.addEventListener('pageshow', () => {
                this.open = true;
                document.body.classList.add('overflow-hidden');
            });
        }
    }"
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="open = false; document.body.classList.remove('overflow-hidden')"
    class="fixed inset-0 z-[10050] flex items-center justify-center p-4 sm:p-6 isolate"
    role="dialog"
    aria-modal="true"
    aria-labelledby="leave-resubmission-modal-title"
>
    <div
        class="absolute inset-0 z-0 bg-gray-900/60 backdrop-blur-[1px]"
        @click="open = false; document.body.classList.remove('overflow-hidden')"
        aria-hidden="true"
    ></div>

    <div class="relative z-10 max-w-lg w-full rounded-2xl border-2 border-amber-400 bg-amber-50 shadow-2xl overflow-hidden pointer-events-auto">
        <div class="border-b border-amber-200 bg-amber-100/80 px-4 py-3 sm:px-5 sm:py-4">
            <h2 id="leave-resubmission-modal-title" class="text-lg font-bold text-amber-950">
                Resubmission required on your leave request
            </h2>
            <p class="mt-1 text-sm text-amber-900">
                Your administrator requested changes on {{ count($items) === 1 ? 'a leave request' : count($items) . ' leave requests' }}.
                Update and resubmit from your leave requests page.
                If the request is not updated within <strong>{{ $leaveResubmissionWindowDays }} days</strong> from its <strong>last update</strong> (while resubmission is required),
                {{ count($items) === 1 ? 'it will be rejected automatically.' : 'each will be rejected automatically.' }}
            </p>
        </div>

        <div class="max-h-[55vh] overflow-y-auto px-4 py-3 sm:px-5 sm:py-4 space-y-3">
            @foreach ($items as $row)
                <div class="rounded-xl border border-amber-200 bg-white p-3 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">
                                #{{ $row['id'] }} — {{ $row['type_label'] }}
                            </p>
                            <p class="mt-1 text-xs text-gray-600">
                                Last updated (request): {{ $row['last_updated_formatted'] }} ({{ config('app.timezone') }})
                            </p>
                            @if(!empty($row['past_due']))
                                <p class="mt-2 text-xs font-semibold text-red-800 bg-red-50 border border-red-100 rounded px-2 py-1 inline-block">
                                    Response window elapsed — rejection will run on the next scheduled check (typically within an hour).
                                </p>
                            @else
                                @php
                                    $remainingLabel = trim((string) ($row['time_remaining_label'] ?? ''));
                                    $urgent = ($row['hours_remaining'] ?? 0) <= 48;
                                @endphp
                                @if($remainingLabel !== '')
                                    <p class="mt-2 text-sm font-bold tabular-nums {{ $urgent ? 'text-red-800' : 'text-amber-950' }}">
                                        Time remaining to update: <span class="font-extrabold">{{ $remainingLabel }}</span>
                                    </p>
                                @endif
                                <p class="mt-1 text-xs {{ $urgent ? 'text-red-700 font-medium' : 'text-gray-600' }}">
                                    Auto-reject deadline: <time datetime="{{ $row['deadline_at']?->toIso8601String() }}">{{ $row['deadline_formatted'] }}</time>
                                    · {{ config('app.timezone') }}
                                </p>
                            @endif
                        </div>
                        <a href="{{ $row['edit_url'] }}"
                           class="inline-flex shrink-0 items-center justify-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Update request
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex flex-col sm:flex-row sm:justify-end gap-2 border-t border-amber-200 bg-amber-100/60 px-4 py-3 sm:px-5">
            <a href="{{ url('/leave-requests') }}"
               class="inline-flex justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 relative z-10"
               @click="sessionStorage.setItem('leaveResubmissionModalSuppressOnce', '1'); open = false; document.body.classList.remove('overflow-hidden')">
                Leave requests
            </a>
            <button type="button"
                    class="inline-flex justify-center rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                    @click="open = false; document.body.classList.remove('overflow-hidden')">
                Close
            </button>
        </div>
    </div>
</div>
@endif
