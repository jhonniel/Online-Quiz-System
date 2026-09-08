@php
    $fieldSuffix = $fieldSuffix ?? 'default';
    $startDateId = 'start_date_hired_'.$fieldSuffix;
    $notesId = 'admin_notes_hired_'.$fieldSuffix;
    $previewId = 'hired_email_preview_content_'.$fieldSuffix;
    $applicantName = trim($application->full_name ?? trim(($application->first_name ?? '').' '.($application->last_name ?? '')));
    if ($applicantName === '') {
        $applicantName = 'Applicant';
    }
@endphp
<div class="hired-email-preview-root mb-3 space-y-3"
     data-field-suffix="{{ $fieldSuffix }}"
     data-applicant-name="{{ $applicantName }}">
    <div>
        <label for="{{ $notesId }}" class="block text-sm font-medium text-gray-700 mb-1">
            Message for hired email (Optional)
        </label>
        <textarea name="admin_notes"
                  id="{{ $notesId }}"
                  rows="3"
                  placeholder="Add a personal note to include in the hired email (optional)"
                  class="hired-email-preview-input w-full px-3 py-2 border border-gray-300 rounded-md text-sm">{{ old('admin_notes', $application->admin_notes) }}</textarea>
        <p class="mt-1 text-xs text-gray-500">
            If provided, this message is included in the hired email sent to the applicant.
        </p>
    </div>
    <div>
        <label for="{{ $startDateId }}" class="block text-sm font-medium text-gray-700 mb-1">
            Start Date (Optional)
        </label>
        <input type="date"
               name="start_date"
               id="{{ $startDateId }}"
               value="{{ old('start_date', $application->start_date?->format('Y-m-d')) }}"
               class="hired-email-preview-input w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
        <p class="mt-1 text-xs text-gray-500">
            If set, the start date is included in the hired email sent to the applicant.
        </p>
    </div>
    <div class="rounded-md border border-gray-200 bg-gray-50 p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Hired Email Preview</p>
        <div id="{{ $previewId }}" class="text-sm text-gray-800 leading-relaxed space-y-3"></div>
    </div>
</div>

@once
@push('scripts')
<script>
    (function () {
        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function formatHiredStartDate(isoDate) {
            if (!isoDate) {
                return null;
            }

            const parts = isoDate.split('-').map(Number);
            if (parts.length !== 3) {
                return null;
            }

            const date = new Date(parts[0], parts[1] - 1, parts[2]);
            if (Number.isNaN(date.getTime())) {
                return null;
            }

            return date.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric',
            });
        }

        function updateHiredEmailPreview(root) {
            const suffix = root.dataset.fieldSuffix;
            const applicantName = root.dataset.applicantName || 'Applicant';
            const startDateInput = document.getElementById('start_date_hired_' + suffix);
            const notesInput = document.getElementById('admin_notes_hired_' + suffix);
            const previewEl = document.getElementById('hired_email_preview_content_' + suffix);

            if (!previewEl) {
                return;
            }

            const startDate = startDateInput ? startDateInput.value : '';
            const note = notesInput ? notesInput.value.trim() : '';
            const formattedStartDate = formatHiredStartDate(startDate);

            let html = '';
            html += '<p>Dear ' + escapeHtml(applicantName) + ',</p>';
            html += '<p>We are pleased to inform you that you have been selected to join our team.</p>';

            if (formattedStartDate) {
                html += '<p>Your start date will be <strong>' + escapeHtml(formattedStartDate) + '</strong>. Please make sure to be available and ready to begin on this date. Further details regarding your schedule, responsibilities, and other onboarding information will be provided separately.</p>';
            } else {
                html += '<p>Further details regarding your start date, schedule, responsibilities, and other onboarding information will be provided separately.</p>';
            }

            html += '<p>We are excited to have you join us and look forward to working with you.</p>';
            html += '<p>Welcome to the team!</p>';

            if (note !== '') {
                html += '<p class="whitespace-pre-line">' + escapeHtml(note) + '</p>';
            }

            html += '<p class="text-xs text-gray-500">Policy documents and Employee Handbook will be attached to the email.</p>';
            html += '<p>Best regards,<br>Infosoft</p>';

            previewEl.innerHTML = html;
        }

        function initHiredEmailPreviews() {
            document.querySelectorAll('.hired-email-preview-root').forEach(function (root) {
                if (root.dataset.previewBound === '1') {
                    updateHiredEmailPreview(root);
                    return;
                }

                root.dataset.previewBound = '1';

                root.querySelectorAll('.hired-email-preview-input').forEach(function (input) {
                    input.addEventListener('input', function () {
                        updateHiredEmailPreview(root);
                    });
                    input.addEventListener('change', function () {
                        updateHiredEmailPreview(root);
                    });
                });

                updateHiredEmailPreview(root);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initHiredEmailPreviews);
        } else {
            initHiredEmailPreviews();
        }
    })();
</script>
@endpush
@endonce
