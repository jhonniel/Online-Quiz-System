@php
    $fieldSuffix = $fieldSuffix ?? 'default';
    $notesId = 'admin_notes_decline_'.$fieldSuffix;
    $deactivateAccount = $deactivateAccount ?? false;
    $confirmMessage = $deactivateAccount
        ? 'Are you sure you want to decline this application? The applicant account will be deactivated.'
        : 'Are you sure you want to decline this application?';
@endphp
<form action="{{ url('/admin/hiring-applications/' . $application->id . '/reject') }}"
      method="POST"
      class="{{ ! empty($formClass) ? $formClass : '' }}"
      onsubmit="return confirm(@json($confirmMessage));">
    @csrf
    <div class="mb-3">
        <label for="{{ $notesId }}" class="block text-sm font-medium text-gray-700 mb-1">
            Message for decline email (Optional)
        </label>
        <textarea name="admin_notes"
                  id="{{ $notesId }}"
                  rows="3"
                  placeholder="Add a personal note to include in the decline email (optional)"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">{{ old('admin_notes', $application->admin_notes) }}</textarea>
        <p class="mt-1 text-xs text-gray-500">
            If provided, this message is included in the decline email sent to the applicant.
        </p>
    </div>
    <button type="submit" class="action-button w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed" data-loading-text="Processing...">
        <span class="button-text">Decline Application</span>
        <span class="button-spinner hidden ml-2">
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </span>
    </button>
    <p class="mt-2 text-xs text-gray-500">
        @if($deactivateAccount)
            This will mark the application as rejected, deactivate the applicant account, and send a decline email.
        @else
            This will mark the application as rejected and send a decline email.
        @endif
    </p>
</form>
