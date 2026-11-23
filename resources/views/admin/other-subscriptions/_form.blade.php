@php
    /** @var \App\Models\OtherSubscription|null $subscription */
    /** @var \Illuminate\Support\Collection|\App\Models\OtherSubscriptionProvider[] $providers */
    $billingTypes = \App\Models\OtherSubscription::BILLING_TYPES;
    $providers = $providers ?? collect();
    $selectedProviderId = (string) old(
        'other_subscription_provider_id',
        $subscription->other_subscription_provider_id ?? ''
    );
    $newProviderName = old('new_provider_name', '');
    $startInNewMode = $selectedProviderId === 'new' || filled($newProviderName);
    $initialProviderId = $startInNewMode ? 'new' : $selectedProviderId;
    $initialBillingType = old('billing_type', $subscription->billing_type ?? 'monthly');
    $initialStartsOn = old('starts_on', isset($subscription) && $subscription->starts_on ? $subscription->starts_on->format('Y-m-d') : '');
    $initialEndsOn = old('ends_on', isset($subscription) && $subscription->ends_on ? $subscription->ends_on->format('Y-m-d') : '');
    $fieldClass = 'block w-full max-w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20';
@endphp

<div
    class="w-full min-w-0 space-y-5"
    x-data="{
        providerId: {{ json_encode($initialProviderId) }},
        billingType: {{ json_encode($initialBillingType) }},
        startsOn: {{ json_encode($initialStartsOn) }},
        endsOn: {{ json_encode($initialEndsOn) }},
        formatDate(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return y + '-' + m + '-' + d;
        },
        syncEndDate() {
            if (!this.startsOn) {
                return;
            }
            const parts = String(this.startsOn).split('-').map(Number);
            if (parts.length !== 3 || parts.some((n) => Number.isNaN(n))) {
                return;
            }
            const end = new Date(parts[0], parts[1] - 1, parts[2]);
            if (this.billingType === 'annual') {
                end.setFullYear(end.getFullYear() + 1);
            } else if (this.billingType === 'monthly') {
                end.setMonth(end.getMonth() + 1);
            } else {
                return;
            }
            this.endsOn = this.formatDate(end);
        }
    }"
>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5 min-w-0">
        <div class="min-w-0">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                Subscription name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" id="name" required
                   value="{{ old('name', $subscription->name ?? '') }}"
                   placeholder="e.g. ChatGPT Plus, Gemini Advanced, Notion"
                   class="{{ $fieldClass }}">
            @error('name')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="min-w-0">
            <label for="provider_select" class="block text-sm font-medium text-gray-700 mb-1.5">
                Provider <span class="text-red-500">*</span>
            </label>

            <select id="provider_select"
                    x-model="providerId"
                    class="{{ $fieldClass }}">
                <option value="">Select provider</option>
                @foreach($providers as $provider)
                    <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                @endforeach
                <option value="new">+ Add new provider…</option>
            </select>

            {{-- Submitted value: existing id, or "new" when creating a provider --}}
            <input type="hidden" name="other_subscription_provider_id" :value="providerId">

            <div class="mt-2" x-show="providerId === 'new'" x-cloak>
                <label for="new_provider_name" class="sr-only">New provider name</label>
                <input type="text"
                       name="new_provider_name"
                       id="new_provider_name"
                       value="{{ $newProviderName }}"
                       placeholder="Provider name (e.g. OpenAI, Google, Zoom)"
                       :disabled="providerId !== 'new'"
                       :required="providerId === 'new'"
                       class="{{ $fieldClass }}">
            </div>

            <p class="mt-1.5 text-xs text-gray-500">
                Pick a saved provider, or choose “+ Add new provider…” and type the name.
            </p>
            @error('other_subscription_provider_id')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('new_provider_name')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5 min-w-0">
        <div class="min-w-0">
            <label for="client_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                Client name
            </label>
            <input type="text" name="client_name" id="client_name"
                   value="{{ old('client_name', $subscription->client_name ?? '') }}"
                   placeholder="e.g. Juan Dela Cruz"
                   class="{{ $fieldClass }}">
            @error('client_name')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="min-w-0">
            <label for="client_email" class="block text-sm font-medium text-gray-700 mb-1.5">
                Client email
            </label>
            <input type="email" name="client_email" id="client_email"
                   value="{{ old('client_email', $subscription->client_email ?? '') }}"
                   placeholder="client@example.com"
                   class="{{ $fieldClass }}">
            @error('client_email')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 min-w-0">
        <div class="min-w-0">
            <label for="billing_type" class="block text-sm font-medium text-gray-700 mb-1.5">
                Billing type <span class="text-red-500">*</span>
            </label>
            <select name="billing_type" id="billing_type" required
                    x-model="billingType"
                    @change="syncEndDate()"
                    class="{{ $fieldClass }}">
                @foreach($billingTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('billing_type')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="min-w-0">
            <label for="price" class="block text-sm font-medium text-gray-700 mb-1.5">
                Price <span class="text-red-500">*</span>
            </label>
            <input type="number" name="price" id="price" required min="0" step="0.01" inputmode="decimal"
                   value="{{ old('price', isset($subscription) ? number_format((float) $subscription->price, 2, '.', '') : '') }}"
                   placeholder="0.00"
                   class="{{ $fieldClass }}">
            @error('price')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="min-w-0">
            <label for="starts_on" class="block text-sm font-medium text-gray-700 mb-1.5">
                Start date <span class="text-red-500">*</span>
            </label>
            <input type="date" name="starts_on" id="starts_on" required
                   x-model="startsOn"
                   @change="syncEndDate()"
                   class="{{ $fieldClass }}">
            @error('starts_on')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="min-w-0">
            <label for="ends_on" class="block text-sm font-medium text-gray-700 mb-1.5">
                End date <span class="text-red-500">*</span>
            </label>
            <input type="date" name="ends_on" id="ends_on" required
                   x-model="endsOn"
                   class="{{ $fieldClass }}">
            <p class="mt-1.5 text-xs text-gray-500" x-show="billingType === 'annual'" x-cloak>
                For annual billing, end date is set to one year after the start date. You can still adjust it.
            </p>
            <p class="mt-1.5 text-xs text-gray-500" x-show="billingType === 'monthly'" x-cloak>
                For monthly billing, end date is set to one month after the start date. You can still adjust it.
            </p>
            @error('ends_on')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="min-w-0">
            <label for="notify_days_before" class="block text-sm font-medium text-gray-700 mb-1.5">
                Notify days before end <span class="text-red-500">*</span>
            </label>
            <input type="number" name="notify_days_before" id="notify_days_before" required min="0" max="90" inputmode="numeric"
                   value="{{ old('notify_days_before', $subscription->notify_days_before ?? 7) }}"
                   class="{{ $fieldClass }}">
            <p class="mt-1.5 text-xs text-gray-500">Used for on-screen “ending soon” hints. Reminder emails are sent only when due today or overdue.</p>
            @error('notify_days_before')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="min-w-0 flex items-end">
            <label class="flex w-full items-start gap-3 rounded-lg border border-gray-200 bg-gray-50/80 px-3.5 py-3 cursor-pointer select-none hover:bg-gray-50">
                <input type="checkbox" name="auto_renew" value="1"
                       @checked(old('auto_renew', $subscription->auto_renew ?? false))
                       class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 flex-shrink-0">
                <span class="min-w-0">
                    <span class="block text-sm font-medium text-gray-800">Auto renews</span>
                    <span class="block text-xs text-gray-500 mt-0.5 leading-snug">Send a renewal reminder instead of an end reminder.</span>
                </span>
            </label>
        </div>
    </div>

    <div class="min-w-0">
        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
        <textarea name="notes" id="notes" rows="3"
                  placeholder="Seats, login URL, billing notes, etc."
                  class="{{ $fieldClass }}">{{ old('notes', $subscription->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
