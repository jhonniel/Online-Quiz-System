@php
    $planType = $planType ?? null;
    $inputClass = 'mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2';
    $selectClass = 'mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2';
@endphp

<div class="mb-8" x-data="{ billingType: '{{ old('billing_type', $planType?->billing_type ?? 'monthly') }}' }">
    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">Plan details</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
        <div class="md:col-span-2">
            <label for="name" class="block text-sm font-medium text-gray-700">Plan name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $planType?->name ?? '') }}" class="{{ $inputClass }}" placeholder="e.g. Standard Monthly" required />
            @error('name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" id="description" rows="2" class="{{ $inputClass }}" placeholder="Optional description">{{ old('description', $planType?->description ?? '') }}</textarea>
            @error('description')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="subscription_type" class="block text-sm font-medium text-gray-700">Subscription type</label>
            <select name="subscription_type" id="subscription_type" class="{{ $selectClass }}" required>
                <option value="">— Select —</option>
                <option value="starlink" {{ old('subscription_type', $planType?->subscription_type ?? '') === 'starlink' ? 'selected' : '' }}>Starlink</option>
                <option value="omada" {{ old('subscription_type', $planType?->subscription_type ?? '') === 'omada' ? 'selected' : '' }}>Omada</option>
            </select>
            @error('subscription_type')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-gray-500">Which device type this plan applies to.</p>
        </div>
        <div>
            <label for="billing_type" class="block text-sm font-medium text-gray-700">Billing type</label>
            <select name="billing_type" id="billing_type" class="{{ $selectClass }}" x-model="billingType" required>
                <option value="monthly" {{ old('billing_type', $planType?->billing_type ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="yearly" {{ old('billing_type', $planType?->billing_type ?? '') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                <option value="custom" {{ old('billing_type', $planType?->billing_type ?? '') === 'custom' ? 'selected' : '' }}>Custom (set interval)</option>
            </select>
            @error('billing_type')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-gray-500">How often the plan is billed.</p>
        </div>
        <div x-show="billingType === 'custom'" x-cloak>
            <label for="billing_interval_months" class="block text-sm font-medium text-gray-700">Billing interval (months)</label>
            <input type="number" name="billing_interval_months" id="billing_interval_months" value="{{ old('billing_interval_months', $planType?->billing_interval_months ?? '') }}" min="1" max="24" class="{{ $inputClass }} max-w-xs" placeholder="e.g. 3 for quarterly" />
            @error('billing_interval_months')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-gray-500">Bill every N months (e.g. 3 = quarterly, 6 = semi-annual).</p>
        </div>
        <div>
            <label for="billing_day" class="block text-sm font-medium text-gray-700">Billing day of month</label>
            <input type="number" name="billing_day" id="billing_day" value="{{ old('billing_day', $planType?->billing_day ?? '') }}" min="1" max="31" class="{{ $inputClass }} max-w-[8rem]" placeholder="1–31 (optional)" />
            @error('billing_day')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-gray-500">Optional: day of month when billing occurs.</p>
        </div>
    </div>
</div>
