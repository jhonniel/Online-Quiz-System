@php
    $rawRules = $settings['student_rules_regulations_html'] ?? '';
    $rawRules = is_string($rawRules) ? $rawRules : '';
    $studentRulesHtml = preg_replace('#<\s*script\b[^>]*>.*?</script>#is', '', $rawRules);
    $u = auth()->check() ? auth()->user() : null;
    $studentRulesFinalWarning = $u
        && $u->role === 'student'
        && (bool) ($u->student_rules_marquee_enabled ?? false)
        && trim((string) ($u->student_rules_notice_message ?? '')) !== '';
    $studentRulesViolation = $u
        && $u->role === 'student'
        && (bool) ($u->student_rules_warning ?? false)
        && ! $studentRulesFinalWarning;
@endphp
<script>
    function studentRulesRegulationsModal() {
        return {
            agreed: false,
            readToBottom: false,
            open: true,
            submitting: false,
            error: '',
            ackUrl: @json(url('/dashboard/rules-regulations/acknowledge')),
            csrf: @json(csrf_token()),
            __rulesResizeBound: null,
            updateReadToBottom() {
                if (this.readToBottom) {
                    return;
                }
                const el = this.$refs.rulesScroll;
                if (!el) {
                    return;
                }
                const overflow = el.scrollHeight - el.clientHeight;
                if (overflow <= 4) {
                    this.readToBottom = true;
                    this.detachRulesResize();
                    return;
                }
                const threshold = 48;
                if (el.scrollHeight - el.scrollTop - el.clientHeight <= threshold) {
                    this.readToBottom = true;
                    this.detachRulesResize();
                }
            },
            onRulesScroll() {
                this.updateReadToBottom();
            },
            detachRulesResize() {
                if (this.__rulesResizeBound) {
                    window.removeEventListener('resize', this.__rulesResizeBound);
                    this.__rulesResizeBound = null;
                }
            },
            initRulesScrollGate() {
                this.$nextTick(() => {
                    this.updateReadToBottom();
                    if (this.readToBottom) {
                        return;
                    }
                    this.__rulesResizeBound = () => this.updateReadToBottom();
                    window.addEventListener('resize', this.__rulesResizeBound, { passive: true });
                });
            },
            async confirm() {
                if (!this.readToBottom || !this.agreed || this.submitting) {
                    return;
                }
                if (!this.ackUrl || !this.csrf) {
                    this.error = 'Unable to submit. Please refresh the page and try again.';
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
                        body: JSON.stringify({}),
                    });
                    if (!response.ok) {
                        const data = await response.json().catch(() => ({}));
                        throw new Error(data.message || 'Something went wrong. Please try again.');
                    }
                    this.open = false;
                    this.detachRulesResize();
                    document.body.classList.remove('overflow-hidden');
                } catch (e) {
                    this.error = e.message || 'Something went wrong. Please try again.';
                } finally {
                    this.submitting = false;
                }
            },
        };
    }
</script>
@php
    $modalCard = $studentRulesFinalWarning
        ? 'bg-red-50 border-red-400'
        : ($studentRulesViolation ? 'bg-amber-50 border-amber-300' : 'bg-white border-gray-200');
    $modalHeader = $studentRulesFinalWarning
        ? 'border-red-300 bg-red-100/80'
        : ($studentRulesViolation ? 'border-amber-300 bg-amber-100/70' : 'border-gray-100');
    $modalBody = $studentRulesFinalWarning
        ? 'bg-red-50/90'
        : ($studentRulesViolation ? 'bg-amber-50/80' : '');
    $modalFooter = $studentRulesFinalWarning
        ? 'border-red-200 bg-red-50/90'
        : ($studentRulesViolation ? 'border-amber-200 bg-amber-50/90' : 'border-gray-100');
    $btnClass = $studentRulesFinalWarning
        ? 'bg-red-700 hover:bg-red-800 focus:ring-red-500'
        : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500';
@endphp
<div
    x-data="studentRulesRegulationsModal()"
    x-init="document.body.classList.add('overflow-hidden'); initRulesScrollGate()"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6"
    aria-modal="true"
    role="dialog"
    aria-labelledby="student-rules-modal-title"
>
    <div class="absolute inset-0 bg-gray-900/60" aria-hidden="true"></div>
    <div class="relative w-full max-w-2xl rounded-xl shadow-xl">
        <div class="flex max-h-[85vh] flex-col overflow-hidden rounded-xl border {{ $modalCard }}">
            <div class="flex-shrink-0 border-b px-5 py-4 {{ $modalHeader }}">
            @if($studentRulesFinalWarning)
                <p class="text-center text-sm font-semibold text-red-950 mb-2" role="alert">This is your final warning for violating the rules</p>
            @elseif($studentRulesViolation)
                <p class="text-center text-sm font-semibold text-amber-950 mb-2" role="alert">You have violated the rules</p>
            @endif
            <h2 id="student-rules-modal-title" class="text-base sm:text-lg font-bold {{ $studentRulesFinalWarning ? 'text-red-950' : 'text-gray-900' }} text-center tracking-tight">
                RULES AND REGULATIONS
            </h2>
            <p class="mt-1 text-xs {{ $studentRulesFinalWarning ? 'text-red-800' : 'text-gray-600' }} text-center">
                Please read the following in full. You must agree before using the system.
            </p>
            </div>
            <div
                x-ref="rulesScroll"
                @scroll.passive="onRulesScroll()"
                tabindex="0"
                role="region"
                aria-label="Rules and regulations full text"
                class="relative flex-1 min-h-0 overflow-y-auto px-5 py-4 custom-scrollbar text-sm {{ $studentRulesFinalWarning ? 'text-red-950' : 'text-gray-700' }} space-y-4 leading-relaxed {{ $modalBody }} outline-none focus-visible:ring-2 focus-visible:ring-offset-0 {{ $studentRulesFinalWarning ? 'focus-visible:ring-red-400' : ($studentRulesViolation ? 'focus-visible:ring-amber-400' : 'focus-visible:ring-indigo-400') }}"
            >
            @if(trim($studentRulesHtml) !== '')
                <div class="student-rules-regulations-html space-y-4">{!! $studentRulesHtml !!}</div>
            @else
                @include('components.student-rules-regulations-default-body')
            @endif
            </div>
            <div
                x-show="!readToBottom"
                x-cloak
                class="flex flex-shrink-0 flex-col items-center gap-0.5 bg-white px-3 py-px text-center leading-none shadow-[0_-6px_12px_-6px_rgba(15,23,42,0.05)]"
            >
                <p
                    id="student-rules-scroll-hint"
                    class="m-0 text-[11px] sm:text-xs font-medium leading-none {{ $studentRulesFinalWarning ? 'text-red-800' : ($studentRulesViolation ? 'text-amber-900' : 'text-gray-600') }}"
                >
                    Scroll down to read more, then you can agree.
                </p>
                <svg class="block h-5 w-5 shrink-0 {{ $studentRulesFinalWarning ? 'text-red-300' : ($studentRulesViolation ? 'text-amber-400' : 'text-gray-300') }} motion-safe:animate-bounce" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex-shrink-0 border-t px-5 py-4 space-y-3 {{ $modalFooter }}">
            <label
                class="flex items-start gap-3 select-none"
                :class="readToBottom ? 'cursor-pointer' : 'cursor-not-allowed opacity-70'"
            >
                <input
                    type="checkbox"
                    x-model="agreed"
                    :disabled="!readToBottom"
                    x-bind:aria-describedby="readToBottom ? false : 'student-rules-scroll-hint'"
                    class="mt-1 h-4 w-4 rounded border-gray-300 disabled:cursor-not-allowed disabled:opacity-50 {{ $studentRulesFinalWarning ? 'text-red-700 focus:ring-red-500' : 'text-indigo-600 focus:ring-indigo-500' }}"
                >
                <span class="text-sm {{ $studentRulesFinalWarning ? 'text-red-900' : 'text-gray-700' }}">
                    I have read and agree to the rules and regulations above.
                </span>
            </label>
            <p x-show="error" x-text="error" class="text-sm text-red-600" x-cloak></p>
            <button
                type="button"
                x-on:click="confirm()"
                :disabled="!readToBottom || !agreed || submitting"
                class="w-full inline-flex justify-center items-center rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition
                    focus:outline-none focus:ring-2 focus:ring-offset-2
                    disabled:opacity-50 disabled:cursor-not-allowed
                    {{ $btnClass }}"
            >
                <span x-show="!submitting">I agree and continue</span>
                <span x-show="submitting" x-cloak>Please wait…</span>
            </button>
            </div>
        </div>
    </div>
</div>
