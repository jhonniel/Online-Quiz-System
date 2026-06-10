@php
    $positionValues = old('positions', $positionValues ?? ['']);
    if (! is_array($positionValues) || $positionValues === []) {
        $positionValues = [''];
    }
@endphp

<div class="md:col-span-2" x-data="{
    rows: @js(array_values($positionValues)),
    addRow() { this.rows.push(''); },
    removeRow(index) {
        if (this.rows.length === 1) {
            this.rows[0] = '';
            return;
        }
        this.rows.splice(index, 1);
    }
}">
    <div class="flex items-center justify-between gap-3 mb-2">
        <label class="block text-sm font-medium text-gray-700">Roles / Positions</label>
        <button type="button" @click="addRow()"
                class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200">
            Add position
        </button>
    </div>
    <p class="text-sm text-gray-500 mb-3">Add one or more positions for this department. Employees choose their assigned position when linked to this department.</p>

    <div class="space-y-2">
        <template x-for="(row, index) in rows" :key="index">
            <div class="flex items-center gap-2">
                <input type="text"
                       :name="'positions[' + index + ']'"
                       x-model="rows[index]"
                       placeholder="e.g., Software Developer"
                       class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                <button type="button" @click="removeRow(index)"
                        class="shrink-0 p-2 text-gray-400 hover:text-red-600 rounded-md hover:bg-red-50"
                        title="Remove position">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    @error('positions')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error('positions.*')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
