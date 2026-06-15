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
            document.body.classList.add('overflow-hidden');
        },
        dismissForSession() {
            sessionStorage.setItem('studentComplianceModalDismissed', '1');
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        },
        close() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        }
    }"
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="close()"
    class="fixed inset-0 z-[10045] flex items-center justify-center p-4 sm:p-6 isolate"
    role="dialog"
    aria-modal="true"
    aria-labelledby="student-compliance-modal-title"
>
    <div
        class="absolute inset-0 z-0 bg-gray-900/60 backdrop-blur-[1px]"
        @click="close()"
        aria-hidden="true"
    ></div>

    <div class="relative z-10 max-w-lg w-full rounded-2xl border border-indigo-200 bg-white shadow-2xl overflow-hidden pointer-events-auto">
        <div class="border-b border-indigo-100 bg-gradient-to-r from-indigo-600 to-indigo-700 px-4 py-4 sm:px-5 sm:py-5 text-white">
            <h2 id="student-compliance-modal-title" class="text-lg font-bold">
                Complete required documents
            </h2>
            <p class="mt-1 text-sm text-indigo-100">
                Finish the items below to fully use the system, including recording attendance on your DTR.
            </p>
        </div>

        <div class="max-h-[55vh] overflow-y-auto px-4 py-4 sm:px-5 space-y-3">
            @foreach ($items as $item)
                @php
                    $tone = $item['status_tone'] ?? 'amber';
                    $badgeClass = match ($tone) {
                        'emerald' => 'bg-emerald-100 text-emerald-800',
                        'rose' => 'bg-rose-100 text-rose-800',
                        default => 'bg-amber-100 text-amber-800',
                    };
                @endphp
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900">{{ $item['label'] }}</p>
                            <p class="mt-1 text-xs text-gray-600 leading-relaxed">{{ $item['description'] }}</p>
                        </div>
                        <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeClass }}">
                            {{ $item['status_label'] }}
                        </span>
                    </div>
                    <a href="{{ $item['url'] }}"
                       onclick="sessionStorage.setItem('studentComplianceModalSuppressOnce', '1')"
                       class="mt-3 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                        Open {{ $item['key'] === 'nda' ? 'NDA page' : 'document' }}
                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 px-4 py-4 sm:px-5 border-t border-gray-100 bg-gray-50">
            <button type="button"
                    @click="dismissForSession()"
                    class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Remind me later
            </button>
            @if(collect($items)->firstWhere('key', 'nda'))
                <a href="{{ route('user.nda.index') }}"
                   onclick="sessionStorage.setItem('studentComplianceModalSuppressOnce', '1')"
                   class="inline-flex justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Go to NDA
                </a>
            @endif
        </div>
    </div>
</div>
@endif
