@php
    $area = \App\Support\AdminPermissionAreas::area($areaKey);
    $column = $area['column'] ?? '';
    $features = $area['features'] ?? [];
    $parentChecked = $parentChecked ?? false;
    $allowed = $permission?->{$column};
    $containerId = $containerId ?? ($areaKey . '-feature-selection');
    $toggleFn = $toggleFn ?? ('toggleSubFeatureSelection_' . str_replace(['-', '.'], '_', $areaKey));
@endphp
@if(!empty($features) && $column)
<div id="{{ $containerId }}" class="mt-4 {{ $parentChecked ? '' : 'hidden' }}">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Allowed areas
        <span class="text-xs text-gray-500 font-normal">(Leave empty to allow all areas below)</span>
    </label>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-gray-50 rounded-md border border-gray-200">
        @foreach($features as $featureKey => $featureLabel)
            <div class="flex items-start">
                <input type="checkbox"
                       name="{{ $column }}[]"
                       id="{{ $areaKey }}_feature_{{ $featureKey }}"
                       value="{{ $featureKey }}"
                       {{ ($parentChecked && (($allowed === null) || in_array($featureKey, $allowed ?? [], true))) ? 'checked' : '' }}
                       class="mt-0.5 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="{{ $areaKey }}_feature_{{ $featureKey }}" class="ml-2 text-sm text-gray-700 cursor-pointer">
                    {{ $featureLabel }}
                </label>
            </div>
        @endforeach
    </div>
</div>
@endif
