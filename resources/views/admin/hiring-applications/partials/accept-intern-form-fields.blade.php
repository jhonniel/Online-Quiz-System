@php
    $fieldSuffix = $fieldSuffix ?? 'intern';
    $requiredHoursValue = old('required_training_hours', $application->user?->required_training_hours);
@endphp

<div class="mb-3">
    <label for="required_training_hours_{{ $fieldSuffix }}" class="block text-sm font-medium text-gray-700 mb-1">
        Required Training Hours <span class="text-red-500">*</span>
    </label>
    <input type="number"
           name="required_training_hours"
           id="required_training_hours_{{ $fieldSuffix }}"
           step="0.01"
           min="0.01"
           required
           value="{{ $requiredHoursValue }}"
           placeholder="e.g. 486"
           class="accept-intern-required-hours w-full px-3 py-2 border border-gray-300 rounded-md text-sm @error('required_training_hours') border-red-500 @enderror">
    <p class="mt-1 text-xs text-gray-500">
        Total OJT hours the intern must complete via DTR before finishing their program.
    </p>
    @error('required_training_hours')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
