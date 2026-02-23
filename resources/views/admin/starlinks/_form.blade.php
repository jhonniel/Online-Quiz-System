@php
    $starlink = $starlink ?? null;
    $currentLinkedAccountId = $currentLinkedAccountId ?? $starlink?->linked_account_id ?? null;
    $inputClass = 'mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2';
    $selectClass = 'mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2';
    $gridClass = 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6';
@endphp

{{-- Account --}}
<div class="mb-8">
    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">Account</h3>
    <div class="{{ $gridClass }}">
        <div>
            <label for="linked_account_id" class="block text-sm font-medium text-gray-700">Account linked (from list)</label>
            <select name="linked_account_id" id="linked_account_id" class="{{ $selectClass }}">
                <option value="">— None —</option>
                @foreach($linkedAccounts as $acc)
                    <option value="{{ $acc->id }}" {{ old('linked_account_id', $currentLinkedAccountId) == $acc->id ? 'selected' : '' }}>{{ $acc->email }}{{ $acc->name ? ' (' . $acc->name . ')' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="account_linked_email" class="block text-sm font-medium text-gray-700">Account linked (email)</label>
            <input type="text" name="account_linked_email" id="account_linked_email" value="{{ old('account_linked_email', $starlink?->account_linked_email ?? '') }}" placeholder="Email" class="{{ $inputClass }}" />
            @error('account_linked_email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- Device --}}
<div class="mb-8">
    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">Device</h3>
    <div class="{{ $gridClass }}">
        <div>
            <label for="starlink_id" class="block text-sm font-medium text-gray-700">Starlink ID</label>
            <input type="text" name="starlink_id" id="starlink_id" value="{{ old('starlink_id', $starlink?->starlink_id ?? '') }}" class="{{ $inputClass }}" placeholder="e.g. 1L-XXXXX" />
            @error('starlink_id')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="serial_number" class="block text-sm font-medium text-gray-700">Serial number</label>
            <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $starlink?->serial_number ?? '') }}" class="{{ $inputClass }}" />
            @error('serial_number')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="kit_number" class="block text-sm font-medium text-gray-700">Kit number</label>
            <input type="text" name="kit_number" id="kit_number" value="{{ old('kit_number', $starlink?->kit_number ?? '') }}" class="{{ $inputClass }}" />
            @error('kit_number')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="router_id" class="block text-sm font-medium text-gray-700">Router ID</label>
            <input type="text" name="router_id" id="router_id" value="{{ old('router_id', $starlink?->router_id ?? '') }}" class="{{ $inputClass }}" />
            @error('router_id')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- Network --}}
<div class="mb-8">
    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">Network</h3>
    <div class="{{ $gridClass }}">
        <div>
            <label for="ssid" class="block text-sm font-medium text-gray-700">SSID</label>
            <input type="text" name="ssid" id="ssid" value="{{ old('ssid', $starlink?->ssid ?? '') }}" class="{{ $inputClass }}" />
            @error('ssid')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="wifi_password" class="block text-sm font-medium text-gray-700">WiFi password</label>
            <input type="text" name="wifi_password" id="wifi_password" value="{{ old('wifi_password', $starlink->wifi_password ?? '') }}" class="{{ $inputClass }}" autocomplete="off" />
            @error('wifi_password')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- Location & dates --}}
<div class="mb-8">
    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">Location & dates</h3>
    <div class="{{ $gridClass }}">
        <div>
            <label for="office_location" class="block text-sm font-medium text-gray-700">Office / location</label>
            <input type="text" name="office_location" id="office_location" value="{{ old('office_location', $starlink?->office_location ?? '') }}" class="{{ $inputClass }}" placeholder="e.g. Main Office" />
            @error('office_location')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700">Start date</label>
            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $starlink?->start_date?->format('Y-m-d') ?? '') }}" class="{{ $inputClass }}" />
            @error('start_date')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="po_no" class="block text-sm font-medium text-gray-700">PO No.</label>
            <input type="text" name="po_no" id="po_no" value="{{ old('po_no', $starlink?->po_no ?? '') }}" class="{{ $inputClass }}" />
            @error('po_no')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- Plan, status & contact --}}
<div class="mb-0">
    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">Plan, status & contact</h3>
    <div class="{{ $gridClass }}">
        <div>
            <label for="plan" class="block text-sm font-medium text-gray-700">Plan</label>
            <input type="text" name="plan" id="plan" value="{{ old('plan', $starlink?->plan ?? '') }}" placeholder="e.g. Standard, Priority" class="{{ $inputClass }}" />
            @error('plan')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <input type="text" name="status" id="status" value="{{ old('status', $starlink?->status ?? '') }}" placeholder="e.g. Active, Inactive" class="{{ $inputClass }}" />
            @error('status')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="contact_email" class="block text-sm font-medium text-gray-700">Contact email</label>
            <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $starlink?->contact_email ?? '') }}" class="{{ $inputClass }}" />
            @error('contact_email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2 xl:col-span-3">
            <label for="end_user_email" class="block text-sm font-medium text-gray-700">End user email</label>
            <input type="email" name="end_user_email" id="end_user_email" value="{{ old('end_user_email', $starlink?->end_user_email ?? '') }}" class="{{ $inputClass }}" placeholder="Email of the end user" />
            @error('end_user_email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
