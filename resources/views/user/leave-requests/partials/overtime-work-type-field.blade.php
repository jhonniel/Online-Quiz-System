@if(auth()->user()->role !== 'student')
    @php
        $travelLocations = $travelTimeLocations ?? collect();
        $selectedTravelLocationId = old('travel_time_location_id', $selectedTravelLocationId ?? '');
        $showTravelLocation = ($visible ?? false)
            && (old('overtime_work_type', $selected ?? '') === 'travel_time');
    @endphp
    <div id="overtime-work-type-field" class="{{ ($visible ?? false) ? '' : 'hidden' }}">
        <fieldset>
            <legend class="block text-sm font-medium text-gray-700 mb-2">
                Overtime Type <span class="text-red-500">*</span>
            </legend>
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6">
                @foreach(\App\Models\LeaveRequest::overtimeWorkTypes() as $value => $label)
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio"
                               name="overtime_work_type"
                               value="{{ $value }}"
                               {{ old('overtime_work_type', $selected ?? '') === $value ? 'checked' : '' }}
                               class="overtime-work-type-radio border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
        <p class="mt-1 text-xs text-gray-500">Choose one: Office Work or Travel Time.</p>
        @error('overtime_work_type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div id="overtime-travel-location-field" class="mt-4 {{ $showTravelLocation ? '' : 'hidden' }}">
            <label for="travel_time_location_id" class="block text-sm font-medium text-gray-700 mb-2">
                Travel Location <span class="text-red-500">*</span>
            </label>
            @if($travelLocations->isEmpty())
                <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    No travel locations are available yet. Please contact HR or an admin.
                </p>
            @else
                <select name="travel_time_location_id"
                        id="travel_time_location_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white text-sm">
                    <option value="">Select a travel location</option>
                    @foreach($travelLocations as $location)
                        <option value="{{ $location->id }}"
                                data-hours="{{ $location->hoursFormatted() }}"
                                data-name="{{ $location->name }}"
                                {{ (string) $selectedTravelLocationId === (string) $location->id ? 'selected' : '' }}>
                            {{ $location->name }} — {{ $location->hoursFormatted() }} hrs
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Pick the destination configured under System → Travel Time.</p>
            @endif
            @error('travel_time_location_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
@endif
