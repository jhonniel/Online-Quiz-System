@php
    $materialType = $materialType ?? 'handbook';
    $fieldPrefix = $fieldPrefix ?? $materialType.'_materials';
    $materials = old($fieldPrefix, $materialItems ?? []);
    if (! is_array($materials)) {
        $materials = [];
    }

    $sortedMaterials = collect($materials)
        ->filter(fn ($material) => is_array($material) && trim((string) ($material['id'] ?? '')) !== '')
        ->sortBy(fn (array $material) => (int) ($material['sort'] ?? 0))
        ->values()
        ->all();

    $materialsForJs = array_map(static fn (array $material): array => [
        'id' => (string) $material['id'],
        'name' => (string) ($material['name'] ?? ''),
        'pathLabel' => basename((string) ($material['path'] ?? '')),
        'sort' => (int) ($material['sort'] ?? 0),
    ], $sortedMaterials);

    $sectionTitle = $sectionTitle ?? ucfirst($materialType).' material PDFs';
    $sectionDescription = $sectionDescription ?? 'Upload one or more PDFs. Each file appears under <strong>'.ucfirst($materialType).'</strong> in the employee Documents menu using the display name you set here.';
    $namePlaceholder = $namePlaceholder ?? 'e.g. Company '.ucfirst($materialType);
@endphp

<div class="rounded-xl border border-violet-200 bg-violet-50/60 p-4 sm:p-5 space-y-4"
     x-data="{
        materials: @js($materialsForJs),
        newRows: [{ name: '' }],
        addRow() { this.newRows.push({ name: '' }); },
        removeRow(index) { this.newRows.splice(index, 1); },
        moveUp(index) {
            if (index <= 0) return;
            const item = this.materials.splice(index, 1)[0];
            this.materials.splice(index - 1, 0, item);
            this.reindexSort();
        },
        moveDown(index) {
            if (index >= this.materials.length - 1) return;
            const item = this.materials.splice(index, 1)[0];
            this.materials.splice(index + 1, 0, item);
            this.reindexSort();
        },
        reindexSort() {
            this.materials.forEach((material, index) => { material.sort = index; });
        }
     }"
     x-init="reindexSort()">
    <div>
        <h4 class="text-sm font-semibold text-violet-950">{{ $sectionTitle }}</h4>
        <p class="mt-1 text-xs text-violet-900/80 leading-relaxed">{!! $sectionDescription !!}</p>
        <p class="mt-1 text-xs text-violet-800/70">Use the arrows to change menu order. The first item appears at the top of the employee Documents menu.</p>
    </div>

    <template x-if="materials.length > 0">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Uploaded files</p>
            <div class="rounded-lg border border-gray-200 bg-white overflow-hidden divide-y divide-gray-100">
                <template x-for="(material, index) in materials" :key="material.id">
                    <div class="p-3 sm:p-4">
                        <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-md bg-violet-100 px-2 text-xs font-bold text-violet-800 tabular-nums"
                                      x-text="index + 1"
                                      :title="'Position ' + (index + 1)"></span>
                                <div class="flex sm:flex-col gap-0.5">
                                    <button type="button"
                                            x-on:click="moveUp(index)"
                                            :disabled="index === 0"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-30 disabled:pointer-events-none"
                                            title="Move up">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button type="button"
                                            x-on:click="moveDown(index)"
                                            :disabled="index === materials.length - 1"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-30 disabled:pointer-events-none"
                                            title="Move down">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0 space-y-2">
                                <input type="hidden"
                                       :name="'{{ $fieldPrefix }}[' + index + '][id]'"
                                       :value="material.id">
                                <input type="hidden"
                                       :name="'{{ $fieldPrefix }}[' + index + '][sort]'"
                                       :value="index">
                                <label class="block text-xs font-medium text-gray-700">Display name in employee menu</label>
                                <input type="text"
                                       :name="'{{ $fieldPrefix }}[' + index + '][name]'"
                                       x-model="material.name"
                                       maxlength="120"
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm"
                                       placeholder="{{ $namePlaceholder }}">
                                <p class="text-[11px] text-gray-500 truncate" x-text="'File: ' + material.pathLabel"></p>
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm text-red-700 shrink-0 pt-1">
                                <input type="checkbox"
                                       name="{{ $fieldPrefix }}_remove[]"
                                       :value="material.id"
                                       class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                Remove
                            </label>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <template x-if="materials.length === 0">
        <p class="text-xs text-gray-500 italic">No PDFs uploaded yet.</p>
    </template>

    <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Add new PDFs</p>
            <button type="button"
                    x-on:click="addRow()"
                    class="text-xs font-semibold text-violet-700 hover:text-violet-900">
                + Add another file
            </button>
        </div>

        <template x-for="(row, rowIndex) in newRows" :key="rowIndex">
            <div class="rounded-lg border border-dashed border-violet-300 bg-white p-3 sm:p-4 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-medium text-gray-700" x-text="'New file ' + (rowIndex + 1)"></p>
                    <button type="button"
                            x-show="newRows.length > 1"
                            x-on:click="removeRow(rowIndex)"
                            class="text-xs text-red-600 hover:text-red-800">
                        Remove row
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Display name</label>
                        <input type="text"
                               :name="'{{ $fieldPrefix }}_new[' + rowIndex + '][name]'"
                               x-model="row.name"
                               maxlength="120"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm"
                               placeholder="{{ $namePlaceholder }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">PDF file</label>
                        <input type="file"
                               :name="'{{ $fieldPrefix }}_new[' + rowIndex + '][pdf]'"
                               accept=".pdf,application/pdf"
                               class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-violet-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-violet-700">
                    </div>
                </div>
            </div>
        </template>
        <p class="text-[11px] text-gray-500">PDF only, up to 20MB per file. Leave name and file empty to skip a row.</p>
    </div>

    @error($fieldPrefix)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error($fieldPrefix.'_new.*.name')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error($fieldPrefix.'_new.*.pdf')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
