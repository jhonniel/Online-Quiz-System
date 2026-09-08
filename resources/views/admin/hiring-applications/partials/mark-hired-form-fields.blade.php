@php
    $fieldSuffix = $fieldSuffix ?? 'default';
    $startDateId = 'start_date_hired_'.$fieldSuffix;
    $notesId = 'admin_notes_hired_'.$fieldSuffix;
@endphp
<div class="mb-3">
    <label for="{{ $startDateId }}" class="block text-sm font-medium text-gray-700 mb-1">
        Start Date (Optional)
    </label>
    <input type="date"
           name="start_date"
           id="{{ $startDateId }}"
           value="{{ old('start_date', $application->start_date?->format('Y-m-d')) }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
    <p class="mt-1 text-xs text-gray-500">
        If set, the start date is included in the hired email sent to the applicant.
    </p>
</div>
<div class="mb-3">
    <label for="{{ $notesId }}" class="block text-sm font-medium text-gray-700 mb-1">
        Message for hired email (Optional)
    </label>
    <textarea name="admin_notes"
              id="{{ $notesId }}"
              rows="3"
              placeholder="Add a personal note to include in the hired email (optional)"
              class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">{{ old('admin_notes', $application->admin_notes) }}</textarea>
    <p class="mt-1 text-xs text-gray-500">
        If provided, this message is included in the hired email sent to the applicant.
    </p>
</div>
