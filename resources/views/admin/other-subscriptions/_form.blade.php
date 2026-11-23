@php
    /** @var \App\Models\OtherSubscription|null $subscription */
    /** @var \Illuminate\Support\Collection|\App\Models\OtherSubscriptionProvider[] $providers */
    $billingTypes = \App\Models\OtherSubscription::BILLING_TYPES;
    $providers = $providers ?? collect();
    $selectedProviderId = old(
        'other_subscription_provider_id',
        $subscription->other_subscription_provider_id ?? ''
    );
    $showNewProvider = old('other_subscription_provider_id') === 'new' || filled(old('new_provider_name'));
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5" x-data="{ providerMode: @js($showNewProvider ? 'new' : 'existing') }">
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Subscription name <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="name" required
               value="{{ old('name', $subscription->name ?? '') }}"
               placeholder="e.g. ChatGPT Plus, Gemini Advanced, Notion"
               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="other_subscription_provider_id" class="block text-sm font-medium text-gray-700 mb-1">Provider <span class="text-red-500">*</span></label>
        <div class="flex flex-col sm:flex-row gap-2">
            <select name="other_subscription_provider_id" id="other_subscription_provider_id"
                    x-show="providerMode === 'existing'"
                    x-bind:disabled="providerMode !== 'existing'"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="">Select provider</option>
                @foreach($providers as $provider)
                    <option value="{{ $provider->id }}" @selected((string) $selectedProviderId === (string) $provider->id)>
                        {{ $provider->name }}
                    </option>
                @endforeach
            </select>
            <input type="text" name="new_provider_name" id="new_provider_name"
                   x-show="providerMode === 'new'"
                   x-bind:disabled="providerMode !== 'new'"
                   value="{{ old('new_provider_name') }}"
                   placeholder="New provider name (e.g. OpenAI)"
                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <button type="button"
                    class="inline-flex items-center justify-center px-3 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 whitespace-nowrap"
                    @click="providerMode = providerMode === 'new' ? 'existing' : 'new'">
                <span x-text="providerMode === 'new' ? 'Pick existing' : 'Add new provider'"></span>
            </button>
        </div>
        <p class="mt-1 text-xs text-gray-500">Saved providers can be reused later. Manage them from the Others list page.</p>
        @error('other_subscription_provider_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('new_provider_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="billing_type" class="block text-sm font-medium text-gray-700 mb-1">Billing type <span class="text-red-500">*</span></label>
        <select name="billing_type" id="billing_type" required
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            @foreach($billingTypes as $value => $label)
                <option value="{{ $value }}" @selected(old('billing_type', $subscription->billing_type ?? 'monthly') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('billing_type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price <span class="text-red-500">*</span></label>
        <input type="number" name="price" id="price" required min="0" step="0.01"
               value="{{ old('price', isset($subscription) ? number_format((float) $subscription->price, 2, '.', '') : '') }}"
               placeholder="0.00"
               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        @error('price')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
        <input type="text" name="currency" id="currency" maxlength="10"
               value="{{ old('currency', $subscription->currency ?? 'PHP') }}"
               placeholder="PHP"
               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm uppercase">
        @error('currency')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="starts_on" class="block text-sm font-medium text-gray-700 mb-1">Start date <span class="text-red-500">*</span></label>
        <input type="date" name="starts_on" id="starts_on" required
               value="{{ old('starts_on', isset($subscription) && $subscription->starts_on ? $subscription->starts_on->format('Y-m-d') : '') }}"
               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        @error('starts_on')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="ends_on" class="block text-sm font-medium text-gray-700 mb-1">End date <span class="text-red-500">*</span></label>
        <input type="date" name="ends_on" id="ends_on" required
               value="{{ old('ends_on', isset($subscription) && $subscription->ends_on ? $subscription->ends_on->format('Y-m-d') : '') }}"
               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        @error('ends_on')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="notify_days_before" class="block text-sm font-medium text-gray-700 mb-1">Notify days before end <span class="text-red-500">*</span></label>
        <input type="number" name="notify_days_before" id="notify_days_before" required min="0" max="90"
               value="{{ old('notify_days_before', $subscription->notify_days_before ?? 7) }}"
               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        <p class="mt-1 text-xs text-gray-500">Admins get an email when the end date is within this many days.</p>
        @error('notify_days_before')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-end">
        <label class="inline-flex items-center gap-2 cursor-pointer select-none py-2">
            <input type="checkbox" name="auto_renew" value="1"
                   @checked(old('auto_renew', $subscription->auto_renew ?? false))
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm font-medium text-gray-700">Auto renews (send renewal reminder instead of end reminder)</span>
        </label>
    </div>

    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
        <textarea name="notes" id="notes" rows="3"
                  placeholder="Account email, seats, login URL, etc."
                  class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('notes', $subscription->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
