@php
    use App\Support\EmployeeDocumentRequestTypes;

    $documentTypes = old('employee_document_request_types', $settings['employee_document_request_types'] ?? EmployeeDocumentRequestTypes::all());
    if (! is_array($documentTypes)) {
        $documentTypes = EmployeeDocumentRequestTypes::defaultTypes();
    }
    $documentTypesForJs = array_map(static fn (array $row): array => [
        'key' => $row['key'] ?? '',
        'label' => $row['label'] ?? '',
        'enabled' => filter_var($row['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'sort' => (int) ($row['sort'] ?? 0),
    ], array_values($documentTypes));
@endphp

<div class="form-section"
     x-data="{
        types: @js($documentTypesForJs),
        addType() {
            this.types.push({ key: '', label: '', enabled: true, sort: this.types.length });
        },
        removeType(index) {
            if (this.types.length <= 1) {
                alert('Keep at least one document type.');
                return;
            }
            if (confirm('Remove this document type? Existing requests keep their stored type.')) {
                this.types.splice(index, 1);
                this.reindexSort();
            }
        },
        moveUp(index) {
            if (index <= 0) return;
            const item = this.types.splice(index, 1)[0];
            this.types.splice(index - 1, 0, item);
            this.reindexSort();
        },
        moveDown(index) {
            if (index >= this.types.length - 1) return;
            const item = this.types.splice(index, 1)[0];
            this.types.splice(index + 1, 0, item);
            this.reindexSort();
        },
        reindexSort() {
            this.types.forEach((type, i) => { type.sort = i; });
        }
     }"
     x-init="reindexSort()">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-2">
                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Employee document request types</h3>
                <p class="text-sm text-gray-500 mt-0.5 max-w-2xl">
                    Options shown when employees submit a request under <strong>Document Requests</strong>.
                    Use the arrows to change list order. Disabled types stay hidden from new requests but still display for existing records.
                </p>
            </div>
        </div>
        <button type="button"
                @click="addType()"
                class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors shrink-0">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add document type
        </button>
    </div>

    @error('employee_document_request_types')
        <p class="mb-4 text-sm text-red-600 font-medium">{{ $message }}</p>
    @enderror

    <div class="rounded-xl border border-gray-200 overflow-hidden bg-white shadow-sm">
        <div class="hidden sm:grid sm:grid-cols-[72px_1fr_minmax(140px,180px)_88px_44px] gap-3 px-4 py-3 bg-gray-50 border-b border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-500">
            <span>Order</span>
            <span>Display name</span>
            <span>Internal key</span>
            <span class="text-center">Enabled</span>
            <span class="sr-only">Remove</span>
        </div>

        <div class="divide-y divide-gray-100">
            <template x-for="(type, index) in types" :key="index">
                <div class="p-4 sm:px-4 sm:py-3 grid grid-cols-1 sm:grid-cols-[72px_1fr_minmax(140px,180px)_88px_44px] gap-3 sm:gap-3 sm:items-center">
                    <div class="flex items-center gap-2 sm:flex-col sm:gap-1">
                        <span class="inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-md bg-gray-100 px-2 text-xs font-bold text-gray-600 tabular-nums"
                              x-text="index + 1"
                              :title="'Position ' + (index + 1)"></span>
                        <input type="hidden" :name="'employee_document_request_types[' + index + '][sort]'" :value="index">
                        <div class="flex sm:flex-col gap-0.5">
                            <button type="button"
                                    @click="moveUp(index)"
                                    :disabled="index === 0"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-30 disabled:pointer-events-none"
                                    title="Move up">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            </button>
                            <button type="button"
                                    @click="moveDown(index)"
                                    :disabled="index === types.length - 1"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-30 disabled:pointer-events-none"
                                    title="Move down">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="sm:sr-only text-xs font-medium text-gray-600">Display name</label>
                        <input type="text"
                               :name="'employee_document_request_types[' + index + '][label]'"
                               x-model="type.label"
                               maxlength="120"
                               required
                               placeholder="e.g. Certificate of Employment"
                               class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="sm:sr-only text-xs font-medium text-gray-600">Internal key</label>
                        <input type="text"
                               :name="'employee_document_request_types[' + index + '][key]'"
                               x-model="type.key"
                               maxlength="80"
                               pattern="[a-z0-9_]*"
                               placeholder="auto-generated"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm font-mono text-gray-600 bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               title="Lowercase letters, numbers, and underscores only. Leave blank to generate from name.">
                        <p class="mt-1 text-[11px] text-gray-400 sm:hidden">Internal ID (optional)</p>
                    </div>
                    <div class="flex items-center justify-between sm:justify-center gap-2">
                        <span class="text-xs font-medium text-gray-600 sm:hidden">Enabled</span>
                        <input type="hidden" :name="'employee_document_request_types[' + index + '][enabled]'" value="0">
                        <input type="checkbox"
                               :name="'employee_document_request_types[' + index + '][enabled]'"
                               value="1"
                               :checked="type.enabled"
                               @change="type.enabled = $event.target.checked"
                               class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </div>
                    <div class="flex sm:justify-center">
                        <button type="button"
                                @click="removeType(index)"
                                class="inline-flex items-center justify-center h-9 w-9 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                title="Remove type">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <p class="mt-3 text-xs text-gray-500 leading-relaxed">
        List order is the order employees see in the dropdown (top = first). Save system settings to apply.
        Manage fulfilled files under
        <a href="{{ route('admin.file-request.index') }}" class="font-medium text-indigo-600 hover:text-indigo-800">Employee Management → File Request</a>.
    </p>
</div>
