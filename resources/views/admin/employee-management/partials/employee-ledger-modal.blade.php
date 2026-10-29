<div x-data="employeeLedgerModal()"
     @open-employee-ledger.window="open($event.detail)"
     @keydown.escape.window="close()">
    <div x-show="isOpen"
         x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 sm:p-6"
         role="dialog"
         aria-modal="true"
         aria-labelledby="employee-ledger-modal-title">
        <div x-show="isOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/50 backdrop-blur-[1px]"
             @click="close()"></div>

        <div x-show="isOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative w-full max-w-4xl max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-xl ring-1 ring-gray-200 overflow-hidden">
            <div class="flex items-start justify-between gap-4 px-5 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                <div class="min-w-0">
                    <h3 id="employee-ledger-modal-title" class="text-base font-semibold text-gray-900 truncate" x-text="(data?.employee?.name || name) + ' — Ledger'"></h3>
                    <p class="mt-1 text-sm text-gray-500 truncate">
                        <span x-text="data?.employee?.email || ''"></span>
                        <span x-show="data && !data.employee.is_active" class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">Inactive</span>
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <select x-model="year" @change="load()" aria-label="Year"
                            class="h-8 rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                        <template x-for="option in yearOptions" :key="option.value">
                            <option :value="option.value" x-text="option.label" :selected="String(option.value) === String(year)"></option>
                        </template>
                    </select>
                    <button type="button" @click="close()" class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors" aria-label="Close">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto">
                <div x-show="loading" class="px-6 py-16 text-center text-sm text-gray-500">
                    <svg class="mx-auto h-6 w-6 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                    <p class="mt-2">Loading ledger…</p>
                </div>

                <div x-show="!loading && error" class="px-6 py-10 text-center">
                    <p class="text-sm text-rose-700" x-text="error"></p>
                    <button type="button" @click="load()" class="mt-3 inline-flex items-center px-3 py-1.5 rounded-md border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50">Try again</button>
                </div>

                <template x-if="!loading && !error && data">
                    <div>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 px-5 sm:px-6 py-4 border-b border-gray-100">
                            <div class="rounded-lg border border-gray-200 p-3">
                                <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Leave allowance (<span x-text="data.summary_year"></span>)</p>
                                <p class="mt-1 text-xl font-bold text-gray-900 tabular-nums" x-text="data.leave_summary.allowance"></p>
                                <p class="text-xs text-gray-500">days credited</p>
                            </div>
                            <div class="rounded-lg border border-gray-200 p-3">
                                <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Leave used (<span x-text="data.summary_year"></span>)</p>
                                <p class="mt-1 text-xl font-bold text-amber-700 tabular-nums" x-text="'−' + data.leave_summary.used"></p>
                                <p class="text-xs text-gray-500">approved leave days</p>
                            </div>
                            <div class="rounded-lg border border-gray-200 p-3">
                                <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Leave remaining</p>
                                <p class="mt-1 text-xl font-bold text-emerald-700 tabular-nums" x-text="data.leave_summary.remaining"></p>
                                <p class="text-xs text-gray-500">days available</p>
                            </div>
                            <div class="rounded-lg border border-gray-200 p-3">
                                <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Overtime balance</p>
                                <p class="mt-1 text-xl font-bold tabular-nums" :class="data.overtime_summary.is_negative ? 'text-rose-700' : 'text-indigo-700'" x-text="data.overtime_summary.balance"></p>
                                <p class="text-xs text-gray-500" x-text="'+' + data.overtime_summary.earned + ' earned, −' + data.overtime_summary.offset + ' offset'"></p>
                            </div>
                        </div>

                        <div class="border-b border-gray-200 px-3 sm:px-4 overflow-x-auto">
                            <nav class="flex min-w-max" aria-label="Ledger tabs">
                                <template x-for="item in tabs" :key="item.key">
                                    <button type="button" @click="tab = item.key"
                                            class="px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap"
                                            :class="tab === item.key ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                        <span x-text="item.label"></span>
                                        <span class="ml-1 text-xs text-gray-400" x-text="'(' + data[item.key + '_ledger'].length + ')'"></span>
                                    </button>
                                </template>
                            </nav>
                        </div>

                        <div class="overflow-x-auto" x-show="tab === 'requests'">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Type</th>
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Period</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Duration</th>
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Filed</th>
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Reviewed by</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Request</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <template x-for="request in data.requests_ledger" :key="'request-' + request.id">
                                        <tr class="hover:bg-gray-50/70">
                                            <td class="px-4 py-2.5 text-sm text-gray-900" x-text="request.type_label"></td>
                                            <td class="px-4 py-2.5 text-sm text-gray-700 whitespace-nowrap" x-text="request.period"></td>
                                            <td class="px-4 py-2.5 text-sm text-right text-gray-700 whitespace-nowrap tabular-nums" x-text="request.duration"></td>
                                            <td class="px-4 py-2.5 text-sm">
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold" :class="statusClasses[request.status] || 'bg-gray-100 text-gray-700'" x-text="request.status_label"></span>
                                            </td>
                                            <td class="px-4 py-2.5 text-sm text-gray-500 whitespace-nowrap" x-text="request.filed_at"></td>
                                            <td class="px-4 py-2.5 text-sm text-gray-500">
                                                <span x-text="request.reviewed_by || '—'"></span>
                                                <div class="text-xs text-gray-400" x-show="request.reviewed_at" x-text="request.reviewed_at"></div>
                                            </td>
                                            <td class="px-4 py-2.5 text-sm text-right">
                                                <a :href="request.url" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-900" x-text="'#' + request.id"></a>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="data.requests_ledger.length === 0">
                                        <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">No leave requests for this period.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="overflow-x-auto" x-show="tab !== 'requests'">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Description</th>
                                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Covered dates</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Amount</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Balance after</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <template x-for="(entry, index) in currentEntries" :key="tab + '-' + index">
                                        <tr class="hover:bg-gray-50/70">
                                            <td class="px-4 py-2.5 text-sm text-gray-700 whitespace-nowrap" x-text="entry.date"></td>
                                            <td class="px-4 py-2.5 text-sm text-gray-900" x-text="entry.description"></td>
                                            <td class="px-4 py-2.5 text-sm text-gray-600 whitespace-nowrap" x-text="entry.reference || '—'"></td>
                                            <td class="px-4 py-2.5 text-sm text-right font-semibold whitespace-nowrap tabular-nums"
                                                :class="entry.direction === 'credit' ? 'text-emerald-700' : 'text-rose-700'"
                                                x-text="(entry.direction === 'credit' ? '+' : '−') + entry.amount"></td>
                                            <td class="px-4 py-2.5 text-sm text-right text-gray-900 whitespace-nowrap tabular-nums" x-text="entry.balance_after"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="currentEntries.length === 0">
                                        <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No entries for this period.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function employeeLedgerModal() {
    const currentYear = {{ (int) now()->year }};

    return {
        isOpen: false,
        loading: false,
        error: '',
        data: null,
        url: '',
        name: '',
        tab: 'leave',
        year: String(currentYear),
        requestId: 0,
        tabs: [
            { key: 'leave', label: 'Leave credits' },
            { key: 'requests', label: 'Leave requests' },
            { key: 'overtime', label: 'Overtime earned' },
            { key: 'offset', label: 'Offset used' },
        ],
        statusClasses: {
            approved: 'bg-green-100 text-green-800',
            rejected: 'bg-red-100 text-red-800',
            pending: 'bg-amber-100 text-amber-800',
            resubmission: 'bg-amber-100 text-amber-800',
            for_more_verification: 'bg-orange-100 text-orange-800',
        },
        yearOptions: [
            ...Array.from({ length: 6 }, (_, i) => ({ value: String(currentYear - i), label: String(currentYear - i) })),
            { value: 'all', label: 'All years' },
        ],
        get currentEntries() {
            return this.data ? (this.data[this.tab + '_ledger'] || []) : [];
        },
        open(detail) {
            this.url = detail.url;
            this.name = detail.name || '';
            this.tab = detail.tab || 'leave';
            this.year = String(currentYear);
            this.data = null;
            this.isOpen = true;
            document.body.classList.add('overflow-hidden');
            this.load();
        },
        close() {
            if (!this.isOpen) {
                return;
            }
            this.isOpen = false;
            this.requestId++;
            document.body.classList.remove('overflow-hidden');
        },
        async load() {
            const requestId = ++this.requestId;
            this.loading = true;
            this.error = '';

            try {
                const response = await fetch(this.url + '?year=' + encodeURIComponent(this.year), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (requestId !== this.requestId) {
                    return;
                }
                if (!response.ok) {
                    throw new Error(response.status === 403
                        ? 'You do not have access to this employee.'
                        : 'Could not load the ledger. Please try again.');
                }
                this.data = await response.json();
            } catch (e) {
                if (requestId === this.requestId) {
                    this.error = e instanceof TypeError
                        ? 'Could not load the ledger. Please check your connection and try again.'
                        : e.message;
                }
            } finally {
                if (requestId === this.requestId) {
                    this.loading = false;
                }
            }
        },
    };
}
</script>
