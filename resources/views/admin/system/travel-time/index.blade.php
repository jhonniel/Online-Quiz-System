@extends('layouts.admin')

@section('page-title', 'Travel Time')

@section('breadcrumb')
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-500 text-sm">System</span>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-900 font-medium text-sm">Travel Time</span>
</li>
@endsection

@section('content')
@php
    use App\Models\TravelTimeLocation;
    $editing = $editLocation ?? null;
    $formName = old('name', $editing?->name ?? '');
    $formHours = old('hours', $editing ? $editing->hoursFormatted() : '');
    $formTripType = old('trip_type', $editing?->trip_type ?? TravelTimeLocation::TRIP_ONE_WAY);
    $formIsActive = old('is_active', $editing ? ($editing->is_active ? '1' : '') : '1');
    $shouldOpenModal = $editing || $errors->any();
@endphp
<div class="space-y-6 min-w-0 px-3 sm:px-4 lg:px-6">
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-700 rounded-2xl shadow-xl px-4 py-6 sm:px-6 sm:py-8 text-white">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-start gap-4 min-w-0">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-3">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-2xl sm:text-3xl font-bold">Travel Time</h1>
                    <p class="text-sm sm:text-base text-indigo-100 mt-1 max-w-2xl">
                        Manage travel locations with duration in <strong class="text-white">HH:MM</strong> and whether each trip is <strong class="text-white">one way</strong> or <strong class="text-white">round trip</strong>.
                    </p>
                </div>
            </div>
            <button type="button" id="openAddLocationBtn"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-white text-indigo-700 px-4 py-2.5 text-sm font-semibold shadow-sm hover:bg-indigo-50 transition-colors shrink-0">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                Add location
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-100 bg-gray-50/80">
            <h2 class="text-sm font-semibold text-gray-900">Travel locations</h2>
            <p class="mt-0.5 text-xs text-gray-500">{{ $locations->count() }} location{{ $locations->count() === 1 ? '' : 's' }} configured</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Travel time</th>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trip type</th>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($locations as $location)
                        @php
                            $isRoundTrip = $location->trip_type === TravelTimeLocation::TRIP_ROUND_TRIP;
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors {{ $location->is_active ? '' : 'opacity-75' }}">
                            <td class="px-4 sm:px-5 py-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="flex-shrink-0 h-9 w-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <p class="font-medium text-gray-900 truncate">{{ $location->name }}</p>
                                </div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-sm font-mono font-medium bg-gray-100 text-gray-800">
                                    {{ $location->hoursFormatted() }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isRoundTrip ? 'bg-violet-100 text-violet-800' : 'bg-sky-100 text-sky-800' }}">
                                    {{ $location->tripTypeLabel() }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $location->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $location->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button"
                                            class="travel-edit-trigger text-indigo-600 hover:text-indigo-900 p-1 rounded-lg hover:bg-indigo-50"
                                            title="Edit"
                                            data-id="{{ $location->id }}"
                                            data-update-url="{{ route('admin.system.travel-time.update', $location) }}"
                                            data-name="{{ e($location->name) }}"
                                            data-hours="{{ $location->hoursFormatted() }}"
                                            data-trip-type="{{ $location->trip_type }}"
                                            data-is-active="{{ $location->is_active ? '1' : '0' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.system.travel-time.destroy', $location) }}" class="inline" onsubmit="return confirm('Remove this travel location?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 p-1 rounded-lg hover:bg-red-50" title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 sm:px-5 py-14 text-center">
                                <p class="text-gray-500">No travel locations yet.</p>
                                <button type="button" id="openAddLocationEmptyBtn"
                                        class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                                    Add your first location
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add / edit location modal --}}
<div id="travelLocationModal"
     class="fixed inset-0 z-50 {{ $shouldOpenModal ? '' : 'hidden' }}"
     aria-hidden="{{ $shouldOpenModal ? 'false' : 'true' }}"
     role="dialog"
     aria-labelledby="travelLocationModalTitle"
     data-default-trip-type="{{ TravelTimeLocation::TRIP_ONE_WAY }}"
     data-store-url="{{ route('admin.system.travel-time.store') }}"
     data-should-open-on-load="{{ $shouldOpenModal ? '1' : '0' }}">
    <div id="travelLocationModalBackdrop"
         class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity duration-200 ease-out {{ $shouldOpenModal ? 'opacity-100' : 'opacity-0' }}"
         data-travel-modal-dismiss></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div id="travelLocationModalPanel"
             class="relative w-full max-w-md rounded-xl bg-white shadow-xl border border-gray-200 pointer-events-auto transition-all duration-200 ease-out {{ $shouldOpenModal ? 'opacity-100 scale-100 translate-y-0' : 'opacity-0 scale-95 translate-y-3 sm:translate-y-2 sm:scale-[0.98]' }}">
            <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between gap-3">
                <div>
                    <h2 id="travelLocationModalTitle" class="text-base font-semibold text-gray-900">{{ $editing ? 'Edit location' : 'Add location' }}</h2>
                    <p class="mt-0.5 text-sm text-gray-500">Enter the location name, travel duration, and trip type.</p>
                </div>
                <button type="button" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700" data-travel-modal-dismiss aria-label="Close">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="travelLocationForm" method="POST"
                  action="{{ $editing ? route('admin.system.travel-time.update', $editing) : route('admin.system.travel-time.store') }}"
                  class="px-5 py-4 space-y-3">
                @csrf
                <input type="hidden" name="_method" id="travelFormMethod" value="{{ $editing ? 'PUT' : 'POST' }}" {{ $editing ? '' : 'disabled' }}>
                <input type="hidden" name="location_id" id="location_id" value="{{ $editing?->id ?? old('location_id') }}" {{ $editing ? '' : 'disabled' }}>

                @if($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label for="travel_name" class="block text-xs font-semibold text-gray-700 mb-1">Location name</label>
                    <input type="text" name="name" id="travel_name" value="{{ $formName }}" required maxlength="255"
                           placeholder="e.g. Manila Office"
                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="travel_hours" class="block text-xs font-semibold text-gray-700 mb-1">Hours of travel (HH:MM)</label>
                    <input type="text" name="hours" id="travel_hours" value="{{ $formHours }}" required maxlength="6"
                           placeholder="08:00" inputmode="numeric" autocomplete="off"
                           class="time-input block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-500 @error('hours') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Example: 08:00, 02:30</p>
                    @error('hours') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <span class="block text-xs font-semibold text-gray-700 mb-2">Trip type</span>
                    <div class="flex flex-wrap gap-3">
                        @foreach(TravelTimeLocation::tripTypes() as $value => $label)
                            <label class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm cursor-pointer transition-colors {{ $formTripType === $value ? 'border-indigo-500 bg-indigo-50 text-indigo-800' : 'border-gray-200 text-gray-700 hover:border-gray-300' }}">
                                <input type="radio" name="trip_type" value="{{ $value }}" class="text-indigo-600 focus:ring-indigo-500" @checked($formTripType === $value) required>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('trip_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 cursor-pointer hover:border-gray-300">
                        <input type="checkbox" name="is_active" id="travel_is_active" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" @checked($formIsActive)>
                        Active
                    </label>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" data-travel-modal-dismiss
                            class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" id="travelFormSubmit"
                            class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700">
                        {{ $editing ? 'Update location' : 'Save location' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('travelLocationModal');
        const form = document.getElementById('travelLocationForm');
        const methodInput = document.getElementById('travelFormMethod');
        const locationIdInput = document.getElementById('location_id');
        const titleEl = document.getElementById('travelLocationModalTitle');
        const submitBtn = document.getElementById('travelFormSubmit');
        const nameInput = document.getElementById('travel_name');
        const hoursInput = document.getElementById('travel_hours');
        const isActiveInput = document.getElementById('travel_is_active');
        const defaultTripType = modal?.dataset.defaultTripType ?? '';
        const storeUrl = modal?.dataset.storeUrl ?? '';
        const shouldOpenOnLoad = (modal?.dataset.shouldOpenOnLoad ?? '0') === '1';
        const backdrop = document.getElementById('travelLocationModalBackdrop');
        const panel = document.getElementById('travelLocationModalPanel');
        const ANIM_MS = 200;

        if (!modal || !form) return;

        function tripTypeInputs() {
            return form.querySelectorAll('input[name="trip_type"]');
        }

        function setTripType(value) {
            tripTypeInputs().forEach(function (input) {
                input.checked = input.value === value;
                const label = input.closest('label');
                if (!label) return;
                if (input.checked) {
                    label.classList.add('border-indigo-500', 'bg-indigo-50', 'text-indigo-800');
                    label.classList.remove('border-gray-200', 'text-gray-700');
                } else {
                    label.classList.remove('border-indigo-500', 'bg-indigo-50', 'text-indigo-800');
                    label.classList.add('border-gray-200', 'text-gray-700');
                }
            });
        }

        tripTypeInputs().forEach(function (input) {
            input.addEventListener('change', function () {
                setTripType(input.value);
            });
        });

        document.querySelectorAll('.time-input').forEach(function (input) {
            input.addEventListener('input', function () {
                let digits = this.value.replace(/\D/g, '').slice(0, 4);
                if (digits.length <= 2) {
                    this.value = digits;
                } else {
                    const h = digits.slice(0, 2);
                    const m = digits.slice(2);
                    this.value = m ? h + ':' + m : h;
                }
            });
        });

        function playOpenAnimation() {
            backdrop?.classList.remove('opacity-0');
            backdrop?.classList.add('opacity-100');
            panel?.classList.remove('opacity-0', 'scale-95', 'translate-y-3', 'sm:translate-y-2', 'sm:scale-[0.98]');
            panel?.classList.add('opacity-100', 'scale-100', 'translate-y-0');
        }

        function playCloseAnimation(callback) {
            backdrop?.classList.remove('opacity-100');
            backdrop?.classList.add('opacity-0');
            panel?.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
            panel?.classList.add('opacity-0', 'scale-95', 'translate-y-3', 'sm:translate-y-2', 'sm:scale-[0.98]');
            window.setTimeout(callback, ANIM_MS);
        }

        function openModal() {
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
            requestAnimationFrame(function () {
                requestAnimationFrame(playOpenAnimation);
            });
            window.setTimeout(function () {
                nameInput?.focus();
            }, ANIM_MS + 20);
        }

        function closeModal() {
            playCloseAnimation(function () {
                modal.classList.add('hidden');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden');
            });
        }

        function setEditMode(updateUrl, data) {
            form.action = updateUrl;
            if (methodInput) {
                methodInput.value = 'PUT';
                methodInput.disabled = false;
            }
            if (locationIdInput) {
                locationIdInput.value = data.id || '';
                locationIdInput.disabled = false;
            }
            if (titleEl) titleEl.textContent = 'Edit location';
            if (submitBtn) submitBtn.textContent = 'Update location';
            if (nameInput) nameInput.value = data.name || '';
            if (hoursInput) hoursInput.value = data.hours || '';
            setTripType(data.tripType || defaultTripType);
            if (isActiveInput) isActiveInput.checked = data.isActive === '1';
        }

        function setAddMode() {
            form.action = storeUrl;
            if (methodInput) {
                methodInput.value = 'POST';
                methodInput.disabled = true;
            }
            if (locationIdInput) {
                locationIdInput.value = '';
                locationIdInput.disabled = true;
            }
            if (titleEl) titleEl.textContent = 'Add location';
            if (submitBtn) submitBtn.textContent = 'Save location';
            if (nameInput) nameInput.value = '';
            if (hoursInput) hoursInput.value = '';
            setTripType(defaultTripType);
            if (isActiveInput) isActiveInput.checked = true;
        }

        modal.querySelectorAll('[data-travel-modal-dismiss]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden') && modal.getAttribute('aria-hidden') !== 'true') {
                closeModal();
            }
        });

        document.getElementById('openAddLocationBtn')?.addEventListener('click', function () {
            setAddMode();
            openModal();
        });

        document.getElementById('openAddLocationEmptyBtn')?.addEventListener('click', function () {
            setAddMode();
            openModal();
        });

        document.querySelectorAll('.travel-edit-trigger').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setEditMode(btn.getAttribute('data-update-url') || '', {
                    id: btn.getAttribute('data-id') || '',
                    name: btn.getAttribute('data-name') || '',
                    hours: btn.getAttribute('data-hours') || '',
                    tripType: btn.getAttribute('data-trip-type') || defaultTripType,
                    isActive: btn.getAttribute('data-is-active') || '0',
                });
                openModal();
            });
        });

        if (shouldOpenOnLoad) {
            openModal();
        }
    });
</script>
@endsection
