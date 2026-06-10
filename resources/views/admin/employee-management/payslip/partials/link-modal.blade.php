@php
    $employeeOptions = $employees->map(fn ($employee) => [
        'id' => $employee->id,
        'name' => $employee->name,
        'email' => $employee->email,
        'department' => $employee->department?->name,
    ])->values();
@endphp

<div x-show="linkOpen"
     x-cloak
     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 sm:p-6"
     role="dialog"
     aria-modal="true"
     aria-labelledby="payslip-link-modal-title">
    <div x-show="linkOpen"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/50 backdrop-blur-[1px]"
         @click="closeLinkModal()"></div>

    <div x-show="linkOpen"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl ring-1 ring-gray-200 overflow-hidden"
         @keydown.escape.window="closeLinkModal()">
        <div class="flex items-start justify-between gap-4 px-5 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/80">
            <div class="min-w-0">
                <h3 id="payslip-link-modal-title" class="text-base font-semibold text-gray-900">Link payslip to employee</h3>
                <p class="mt-1 text-sm text-gray-500">Connect this payslip to an employee account so they can view it. Future imports with the same employee name will auto-link even without email.</p>
            </div>
            <button type="button" @click="closeLinkModal()" class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors" aria-label="Close">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="px-5 sm:px-6 py-4 bg-amber-50/70 border-b border-amber-100">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="min-w-0 text-sm">
                    <p class="font-semibold text-gray-900 truncate" x-text="linkPayslipName"></p>
                    <p class="text-gray-600 mt-0.5" x-show="linkPayslipEmail" x-text="linkPayslipEmail ? 'CSV email: ' + linkPayslipEmail : ''"></p>
                    <p class="text-gray-500 mt-0.5" x-show="linkPeriodLabel" x-text="linkPeriodLabel"></p>
                </div>
            </div>
        </div>

        <form :action="linkFormAction" method="POST" class="px-5 sm:px-6 py-5 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="payslip-link-search" class="block text-sm font-medium text-gray-700 mb-2">Employee account</label>

                <div x-show="!linkEmployeeId">
                    <input type="text"
                           id="payslip-link-search"
                           x-model="linkSearch"
                           placeholder="Search by name, email, or department..."
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                    <div class="mt-2 max-h-52 overflow-y-auto rounded-xl border border-gray-200 bg-white divide-y divide-gray-100">
                        <template x-if="filteredLinkEmployees.length === 0">
                            <p class="px-4 py-6 text-center text-sm text-gray-500">No employees match your search.</p>
                        </template>
                        <template x-for="employee in filteredLinkEmployees" :key="employee.id">
                            <button type="button"
                                    @click="selectLinkEmployee(employee)"
                                    class="w-full text-left px-4 py-3 hover:bg-indigo-50 transition-colors focus:outline-none focus:bg-indigo-50">
                                <div class="font-medium text-gray-900" x-text="employee.name"></div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    <span x-text="employee.email"></span>
                                    <span x-show="employee.department"> · <span x-text="employee.department"></span></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <div x-show="linkEmployeeId"
                     class="flex items-center justify-between gap-3 rounded-xl border border-indigo-200 bg-indigo-50/60 px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate" x-text="selectedLinkEmployee?.name"></p>
                        <p class="text-xs text-gray-600 truncate" x-text="selectedLinkEmployee?.email"></p>
                    </div>
                    <button type="button"
                            @click="clearLinkEmployee()"
                            class="shrink-0 text-sm font-medium text-indigo-700 hover:text-indigo-900">
                        Change
                    </button>
                </div>

                <input type="hidden" name="employee_id" :value="linkEmployeeId" required>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1">
                <button type="button"
                        @click="closeLinkModal()"
                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        :disabled="!linkEmployeeId"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Link payslip
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function payslipFilterEmployees(employees, query) {
    const tokens = String(query || '').trim().toLowerCase().split(/\s+/).filter(Boolean);
    if (tokens.length === 0) {
        return employees;
    }

    return employees.filter((employee) => {
        const haystack = [
            employee.name,
            employee.email,
            employee.department || '',
        ].join(' ').toLowerCase();

        return tokens.every((token) => haystack.includes(token));
    });
}

function payslipLinkModal() {
    return {
        linkOpen: false,
        linkFormAction: '',
        linkPayslipName: '',
        linkPayslipEmail: '',
        linkPeriodLabel: '',
        linkSearch: '',
        linkEmployeeId: '',
        linkEmployees: @json($employeeOptions),
        get filteredLinkEmployees() {
            return payslipFilterEmployees(this.linkEmployees, this.linkSearch);
        },
        get selectedLinkEmployee() {
            return this.linkEmployees.find((employee) => String(employee.id) === String(this.linkEmployeeId)) || null;
        },
        openLinkModal(action, name, email, period) {
            this.linkFormAction = action;
            this.linkPayslipName = name;
            this.linkPayslipEmail = email || '';
            this.linkPeriodLabel = period || '';
            this.linkSearch = '';
            this.linkEmployeeId = '';
            this.linkOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        closeLinkModal() {
            this.linkOpen = false;
            document.body.classList.remove('overflow-hidden');
        },
        selectLinkEmployee(employee) {
            this.linkEmployeeId = employee.id;
            this.linkSearch = employee.name;
        },
        clearLinkEmployee() {
            this.linkEmployeeId = '';
            this.linkSearch = '';
        },
    };
}
</script>
