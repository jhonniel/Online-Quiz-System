@php
    $employeeOptions = $employees->map(fn ($employee) => [
        'id' => $employee->id,
        'name' => $employee->name,
        'email' => $employee->email,
        'department' => $employee->department?->name,
    ])->values();
@endphp

<div x-data="{
    search: '',
    employeeId: @js((string) old('employee_id', $payslip->user_id ?? '')),
    employees: @json($employeeOptions),
    get filteredEmployees() {
        const tokens = this.search.trim().toLowerCase().split(/\s+/).filter(Boolean);
        if (tokens.length === 0) {
            return this.employees;
        }
        return this.employees.filter((employee) => {
            const haystack = [employee.name, employee.email, employee.department || ''].join(' ').toLowerCase();
            return tokens.every((token) => haystack.includes(token));
        });
    },
    get selectedEmployee() {
        return this.employees.find((employee) => String(employee.id) === String(this.employeeId)) || null;
    },
    selectEmployee(employee) {
        this.employeeId = employee.id;
        this.search = employee.name;
    },
    clearEmployee() {
        this.employeeId = '';
        this.search = '';
    }
}" class="space-y-4">
    <form action="{{ route('admin.payslip.link', $payslip) }}" method="POST">
        @csrf
        @method('PATCH')

        <p class="text-sm text-gray-500">Future imports with the same payslip employee name will auto-link to this account, even when the CSV has no email.</p>

        <div>
            <label for="payslip-detail-link-search" class="block text-sm font-medium text-gray-700 mb-2">Employee account</label>

            <div x-show="!employeeId">
                <input type="text"
                       id="payslip-detail-link-search"
                       x-model="search"
                       placeholder="Search by name, email, or department..."
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                <div class="mt-2 max-h-56 overflow-y-auto rounded-xl border border-gray-200 bg-white divide-y divide-gray-100">
                    <template x-if="filteredEmployees.length === 0">
                        <p class="px-4 py-6 text-center text-sm text-gray-500">No employees match your search.</p>
                    </template>
                    <template x-for="employee in filteredEmployees" :key="employee.id">
                        <button type="button"
                                @click="selectEmployee(employee)"
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

            <div x-show="employeeId"
                 class="flex items-center justify-between gap-3 rounded-xl border border-indigo-200 bg-indigo-50/60 px-4 py-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate" x-text="selectedEmployee?.name"></p>
                    <p class="text-xs text-gray-600 truncate" x-text="selectedEmployee?.email"></p>
                </div>
                <button type="button"
                        @click="clearEmployee()"
                        class="shrink-0 text-sm font-medium text-indigo-700 hover:text-indigo-900">
                    Change
                </button>
            </div>

            <input type="hidden" name="employee_id" :value="employeeId" required>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit"
                    :disabled="!employeeId"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                Link payslip
            </button>
        </div>
    </form>
</div>
