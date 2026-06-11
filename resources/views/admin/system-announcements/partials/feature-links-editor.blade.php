<div class="rounded-lg border border-indigo-100 bg-indigo-50/60 p-4 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
            <p class="text-sm font-semibold text-gray-900">Feature links <span class="font-normal text-gray-500">(optional)</span></p>
            <p class="mt-1 text-xs text-gray-600 leading-relaxed">
                Add one or more links so employees can open related features from the popup. Use app paths such as <code class="text-[11px] bg-white px-1 rounded border">/user/payslips</code>.
            </p>
        </div>
        <button type="button"
                x-on:click="addFeatureLink()"
                class="inline-flex items-center justify-center shrink-0 rounded-lg border border-indigo-200 bg-white px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add link
        </button>
    </div>

    <template x-if="featureLinks.length === 0">
        <p class="text-xs text-gray-500 italic">No feature links yet. Click “Add link” to include shortcuts in the employee popup.</p>
    </template>

    <div class="space-y-3">
        <template x-for="(link, index) in featureLinks" :key="index">
            <div class="rounded-lg border border-white bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500" x-text="'Link ' + (index + 1)"></p>
                    <button type="button"
                            x-on:click="removeFeatureLink(index)"
                            class="text-xs font-medium text-red-600 hover:text-red-800">
                        Remove
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" :for="'feature_link_url_' + index">Link URL or path</label>
                        <input type="text"
                               :id="'feature_link_url_' + index"
                               :name="'feature_links[' + index + '][url]'"
                               x-model="link.url"
                               maxlength="500"
                               placeholder="/user/payslips"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" :for="'feature_link_label_' + index">Button label</label>
                        <input type="text"
                               :id="'feature_link_label_' + index"
                               :name="'feature_links[' + index + '][label]'"
                               x-model="link.label"
                               maxlength="100"
                               placeholder="e.g. Go to Payslips"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                </div>
            </div>
        </template>
    </div>

    @if($errors->has('feature_links') || $errors->has('feature_links.*'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 space-y-1">
            @error('feature_links')<p>{{ $message }}</p>@enderror
            @foreach($errors->getMessages() as $key => $messages)
                @if(str_starts_with($key, 'feature_links.'))
                    @foreach($messages as $message)
                        <p>{{ $message }}</p>
                    @endforeach
                @endif
            @endforeach
        </div>
    @endif
</div>
