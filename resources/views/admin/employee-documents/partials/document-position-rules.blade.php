@php
    use App\Models\Department;

    $positionRulesFieldName = $positionRulesFieldName ?? 'document_position_rules';
    $showPoliciesField = (bool) ($showPoliciesField ?? false);
    $showJobDescriptionField = (bool) ($showJobDescriptionField ?? false);
    $jobDescriptionPlaceholder = $jobDescriptionPlaceholder ?? "Design, develop, test, maintain, and improve web, mobile, desktop, and other software applications\nWrite clean, secure, maintainable, and well-documented source code";
    $sectionTitle = $sectionTitle ?? 'Position-specific content';
    $sectionDescription = $sectionDescription ?? 'Add content below that only appears for selected positions.';
    $emptyMessage = $emptyMessage ?? 'No position rules yet.';
    $contentPlaceholder = $contentPlaceholder ?? '<p>Additional content for this role...</p>';
    $errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag();

    $initialRules = collect($documentPositionRules ?? [])
        ->map(fn (array $rule): array => [
            'position_ids' => array_values(array_map('intval', $rule['position_ids'] ?? [])),
            'policies_text' => (string) ($rule['policies_text'] ?? implode("\n", $rule['policies'] ?? [])),
            'job_description_text' => (string) ($rule['job_description_text'] ?? implode("\n", $rule['job_description_duties'] ?? [])),
            'content_html' => (string) ($rule['content_html'] ?? ''),
        ])
        ->values()
        ->all();

    $departments = Department::query()
        ->active()
        ->with(['positions' => fn ($query) => $query->active()->orderBy('sort_order')->orderBy('name')])
        ->orderBy('name')
        ->get()
        ->map(fn (Department $department): array => [
            'id' => (int) $department->id,
            'name' => (string) $department->name,
            'positions' => $department->positions
                ->map(fn ($position): array => [
                    'id' => (int) $position->id,
                    'name' => (string) $position->name,
                ])
                ->values()
                ->all(),
        ])
        ->values()
        ->all();
@endphp

<div class="shrink-0 border-b border-violet-200 bg-violet-50/80 px-4 sm:px-6 lg:px-8 py-5"
     x-data="{
        fieldName: @js($positionRulesFieldName),
        showPoliciesField: @js($showPoliciesField),
        showJobDescriptionField: @js($showJobDescriptionField),
        contentPlaceholder: @js($contentPlaceholder),
        jobDescriptionPlaceholder: @js($jobDescriptionPlaceholder),
        rules: @js($initialRules),
        departments: @js($departments),
        addRule() {
            this.rules.push({ position_ids: [], policies_text: '', job_description_text: '', content_html: '' });
        },
        removeRule(index) {
            this.rules.splice(index, 1);
        },
        togglePosition(ruleIndex, positionId, checked) {
            const ids = this.rules[ruleIndex].position_ids.map(Number);
            const id = Number(positionId);
            if (checked) {
                if (!ids.includes(id)) {
                    ids.push(id);
                }
                this.rules[ruleIndex].position_ids = ids;
                return;
            }
            this.rules[ruleIndex].position_ids = ids.filter((value) => value !== id);
        },
        isPositionSelected(ruleIndex, positionId) {
            return this.rules[ruleIndex].position_ids.map(Number).includes(Number(positionId));
        }
     }">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div class="max-w-3xl">
            <h2 class="text-sm font-semibold text-violet-950">{{ $sectionTitle }}</h2>
            <p class="mt-1 text-xs text-violet-900/80 leading-relaxed">{!! $sectionDescription !!}</p>
        </div>
        <button type="button"
                x-on:click="addRule()"
                class="inline-flex items-center justify-center shrink-0 rounded-lg border border-violet-300 bg-white px-4 py-2 text-xs font-semibold text-violet-800 hover:bg-violet-100 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add position rule
        </button>
    </div>

    <template x-if="rules.length === 0">
        <p class="mt-4 text-xs text-violet-800/70 italic">{{ $emptyMessage }}</p>
    </template>

    <div class="mt-4 space-y-4">
        <template x-for="(rule, ruleIndex) in rules" :key="ruleIndex">
            <div class="rounded-xl border border-violet-200 bg-white p-4 sm:p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-violet-800" x-text="'Rule ' + (ruleIndex + 1)"></p>
                    <button type="button"
                            x-on:click="removeRule(ruleIndex)"
                            class="text-xs font-medium text-red-600 hover:text-red-800">
                        Remove rule
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-800 mb-2">Applies to positions</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                            <template x-for="department in departments" :key="department.id">
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2" x-text="department.name"></p>
                                    <div class="space-y-1.5">
                                        <template x-for="position in department.positions" :key="position.id">
                                            <label class="flex items-start gap-2 text-sm text-gray-700">
                                                <input type="checkbox"
                                                       class="mt-0.5 h-4 w-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                                                       :checked="isPositionSelected(ruleIndex, position.id)"
                                                       x-on:change="togglePosition(ruleIndex, position.id, $event.target.checked)">
                                                <span x-text="position.name"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <template x-for="positionId in rule.position_ids" :key="positionId">
                            <input type="hidden"
                                   :name="fieldName + '[' + ruleIndex + '][position_ids][]'"
                                   :value="positionId">
                        </template>
                    </div>

                    <template x-if="showJobDescriptionField">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Job description duties</label>
                            <textarea rows="8"
                                      class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm font-mono"
                                      :name="fieldName + '[' + ruleIndex + '][job_description_text]'"
                                      x-model="rule.job_description_text"
                                      :placeholder="jobDescriptionPlaceholder"></textarea>
                            <p class="mt-1 text-[11px] text-gray-500">One duty per line. Replaces the standard Job Description list for matching positions only.</p>
                        </div>
                    </template>

                    <div class="grid grid-cols-1 gap-4" :class="showPoliciesField ? 'lg:grid-cols-2' : ''">
                        <template x-if="showPoliciesField">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Extra policies for these positions</label>
                                <textarea rows="6"
                                          class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm font-mono"
                                          :name="fieldName + '[' + ruleIndex + '][policies_text]'"
                                          x-model="rule.policies_text"
                                          placeholder="- Remote Work Policy&#10;- Equipment Use Policy"></textarea>
                                <p class="mt-1 text-[11px] text-gray-500">One policy per line. Added to the standard list for matching positions only.</p>
                            </div>
                        </template>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                <span x-show="showJobDescriptionField">Additional position-specific content (HTML, optional)</span>
                                <span x-show="!showJobDescriptionField">Position-specific content (HTML)</span>
                            </label>
                            <textarea rows="6"
                                      class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm font-mono"
                                      :name="fieldName + '[' + ruleIndex + '][content_html]'"
                                      x-model="rule.content_html"
                                      :placeholder="contentPlaceholder"></textarea>
                            <p class="mt-1 text-[11px] text-gray-500" x-show="showJobDescriptionField">Optional extra clauses shown after the Position section.</p>
                            <p class="mt-1 text-[11px] text-gray-500" x-show="!showJobDescriptionField">Shown only for employees in the selected positions.</p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    @if($errorBag->has($positionRulesFieldName) || collect($errorBag->keys())->contains(fn ($key) => str_starts_with($key, $positionRulesFieldName.'.')))
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 space-y-1">
            @if($errorBag->has($positionRulesFieldName))
                @foreach($errorBag->get($positionRulesFieldName) as $message)
                    <p>{{ $message }}</p>
                @endforeach
            @endif
            @foreach($errorBag->getMessages() as $key => $messages)
                @if(str_starts_with($key, $positionRulesFieldName.'.'))
                    @foreach($messages as $message)
                        <p>{{ $message }}</p>
                    @endforeach
                @endif
            @endforeach
        </div>
    @endif
</div>
