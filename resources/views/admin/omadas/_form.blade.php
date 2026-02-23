@php
    $omada = $omada ?? null;
    $subscriptionPlanTypes = $subscriptionPlanTypes ?? collect();
    $inputClass = 'mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2';
    $selectClass = 'mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2';
@endphp
<div class="mb-8">
    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">Omada device details</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
        <div>
            <label for="account_linked_email" class="block text-sm font-medium text-gray-700">Account linked (email)</label>
            <input type="email" name="account_linked_email" id="account_linked_email" value="{{ old('account_linked_email', $omada?->account_linked_email ?? '') }}" placeholder="Email" class="{{ $inputClass }}" />
            @error('account_linked_email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="site" class="block text-sm font-medium text-gray-700">Site</label>
            <input type="text" name="site" id="site" value="{{ old('site', $omada?->site ?? '') }}" class="{{ $inputClass }}" />
            @error('site')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="office" class="block text-sm font-medium text-gray-700">Office</label>
            <input type="text" name="office" id="office" value="{{ old('office', $omada?->office ?? '') }}" class="{{ $inputClass }}" />
            @error('office')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
            <input type="text" name="type" id="type" value="{{ old('type', $omada?->type ?? '') }}" placeholder="e.g. Controller, Gateway" class="{{ $inputClass }}" />
            @error('type')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="serial_number" class="block text-sm font-medium text-gray-700">Serial number</label>
            <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $omada?->serial_number ?? '') }}" class="{{ $inputClass }}" />
            @error('serial_number')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="mac_address" class="block text-sm font-medium text-gray-700">MAC address</label>
            <input type="text" name="mac_address" id="mac_address" value="{{ old('mac_address', $omada?->mac_address ?? '') }}" placeholder="e.g. AA:BB:CC:DD:EE:FF" class="{{ $inputClass }}" />
            @error('mac_address')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="license" class="block text-sm font-medium text-gray-700">License</label>
            <input type="text" name="license" id="license" value="{{ old('license', $omada?->license ?? '') }}" class="{{ $inputClass }}" />
            @error('license')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2 xl:col-span-1">
            <label for="license_expiration" class="block text-sm font-medium text-gray-700">License expiration</label>
            <input type="date" name="license_expiration" id="license_expiration" value="{{ old('license_expiration', $omada?->license_expiration?->format('Y-m-d') ?? '') }}" class="{{ $inputClass }}" />
            @error('license_expiration')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-gray-500">Status (Active/Expired) is automatically set from this date.</p>
        </div>
        <div>
            <label for="subscription_plan_type_id" class="block text-sm font-medium text-gray-700">Plan</label>
            <select name="subscription_plan_type_id" id="subscription_plan_type_id" class="{{ $selectClass }}">
                <option value="">— None —</option>
                @foreach($subscriptionPlanTypes as $planType)
                    <option value="{{ $planType->id }}" {{ old('subscription_plan_type_id', $omada?->subscription_plan_type_id ?? '') == $planType->id ? 'selected' : '' }}>
                        {{ $planType->name }}{{ $planType->billing_type_label ? ' (' . $planType->billing_type_label . ')' : '' }}
                    </option>
                @endforeach
            </select>
            @error('subscription_plan_type_id')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
