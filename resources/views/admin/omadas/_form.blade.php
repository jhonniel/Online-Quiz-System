@php
    $omada = $omada ?? null;
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="account_linked_email" class="block text-sm font-medium text-gray-700">Account linked (email)</label>
        <input type="email" name="account_linked_email" id="account_linked_email" value="{{ old('account_linked_email', $omada->account_linked_email ?? '') }}" placeholder="Email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        @error('account_linked_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="site" class="block text-sm font-medium text-gray-700">Site</label>
        <input type="text" name="site" id="site" value="{{ old('site', $omada->site ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        @error('site')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="office" class="block text-sm font-medium text-gray-700">Office</label>
        <input type="text" name="office" id="office" value="{{ old('office', $omada->office ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        @error('office')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
        <input type="text" name="type" id="type" value="{{ old('type', $omada->type ?? '') }}" placeholder="e.g. Controller, Gateway" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="serial_number" class="block text-sm font-medium text-gray-700">Serial number</label>
        <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $omada->serial_number ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        @error('serial_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="mac_address" class="block text-sm font-medium text-gray-700">MAC address</label>
        <input type="text" name="mac_address" id="mac_address" value="{{ old('mac_address', $omada->mac_address ?? '') }}" placeholder="e.g. AA:BB:CC:DD:EE:FF" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        @error('mac_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="license" class="block text-sm font-medium text-gray-700">License</label>
        <input type="text" name="license" id="license" value="{{ old('license', $omada->license ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        @error('license')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="license_expiration" class="block text-sm font-medium text-gray-700">License expiration</label>
        <input type="date" name="license_expiration" id="license_expiration" value="{{ old('license_expiration', $omada && $omada->license_expiration ? $omada->license_expiration->format('Y-m-d') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        @error('license_expiration')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        <p class="mt-1 text-xs text-gray-500">Status (Active/Expired) is automatically set from this date.</p>
    </div>
</div>
