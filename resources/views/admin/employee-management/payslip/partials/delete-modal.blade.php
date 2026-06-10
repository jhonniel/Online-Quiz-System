<div id="payslip-delete-modal"
     class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 sm:p-6"
     role="dialog"
     aria-modal="true"
     aria-labelledby="payslip-delete-modal-title">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-[1px]"
         onclick="closePayslipDeleteModal()"></div>

    <div class="relative w-full max-w-md rounded-2xl bg-white shadow-xl ring-1 ring-gray-200 overflow-hidden">
        <div class="flex items-start justify-between gap-4 px-5 sm:px-6 py-4 border-b border-gray-100 bg-red-50/80">
            <div class="min-w-0">
                <h3 id="payslip-delete-modal-title" class="text-base font-semibold text-gray-900">Confirm payslip deletion</h3>
                <p id="payslip-delete-modal-message" class="mt-1 text-sm text-gray-600"></p>
            </div>
            <button type="button"
                    onclick="closePayslipDeleteModal()"
                    class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                    aria-label="Close">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="payslip-delete-form" method="POST" class="px-5 sm:px-6 py-5 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="payslip-delete-method" value="DELETE">
            <input type="hidden" name="payslip_delete_action" id="payslip-delete-action-meta" value="{{ old('payslip_delete_action') }}">
            <input type="hidden" name="payslip_delete_method" id="payslip-delete-method-meta" value="{{ old('payslip_delete_method') }}">
            <input type="hidden" name="payslip_delete_message" id="payslip-delete-message-meta" value="{{ old('payslip_delete_message') }}">
            <div id="payslip-delete-ids-container">
                @foreach(old('payslip_ids', []) as $oldPayslipId)
                    <input type="hidden" name="payslip_ids[]" value="{{ $oldPayslipId }}">
                @endforeach
            </div>

            <p class="text-sm text-gray-600">
                This action cannot be undone. Enter your account password to permanently delete the selected payslip record(s).
            </p>

            <div>
                <label for="payslip-delete-password" class="block text-sm font-medium text-gray-700 mb-2">Your password</label>
                <input type="password"
                       name="confirm_password"
                       id="payslip-delete-password"
                       required
                       autocomplete="current-password"
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500 @error('confirm_password') border-red-500 @enderror">
                @error('confirm_password')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p id="payslip-delete-password-error" class="mt-1.5 text-sm text-red-600 hidden"></p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1">
                <button type="button"
                        onclick="closePayslipDeleteModal()"
                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        id="payslip-delete-submit"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete payslip(s)
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openPayslipDeleteModal(options) {
    const modal = document.getElementById('payslip-delete-modal');
    const form = document.getElementById('payslip-delete-form');
    const methodInput = document.getElementById('payslip-delete-method');
    const messageEl = document.getElementById('payslip-delete-modal-message');
    const idsContainer = document.getElementById('payslip-delete-ids-container');
    const passwordInput = document.getElementById('payslip-delete-password');
    const passwordError = document.getElementById('payslip-delete-password-error');

    if (!modal || !form) {
        return;
    }

    form.action = options.action || '';
    methodInput.value = options.method || 'POST';
    messageEl.textContent = options.message || 'Delete the selected payslip record(s)?';

    const actionMeta = document.getElementById('payslip-delete-action-meta');
    const methodMeta = document.getElementById('payslip-delete-method-meta');
    const messageMeta = document.getElementById('payslip-delete-message-meta');
    if (actionMeta) actionMeta.value = options.action || '';
    if (methodMeta) methodMeta.value = options.method || 'POST';
    if (messageMeta) messageMeta.value = options.message || '';

    idsContainer.innerHTML = '';
    (options.payslipIds || []).forEach(function (id) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'payslip_ids[]';
        input.value = id;
        idsContainer.appendChild(input);
    });

    if (passwordInput) {
        passwordInput.value = '';
    }
    if (passwordError) {
        passwordError.classList.add('hidden');
        passwordError.textContent = '';
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    passwordInput?.focus();
}

function closePayslipDeleteModal() {
    const modal = document.getElementById('payslip-delete-modal');
    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closePayslipDeleteModal();
    }
});

@if($errors->has('confirm_password'))
document.addEventListener('DOMContentLoaded', function () {
    openPayslipDeleteModal({
        action: @json(old('payslip_delete_action', route('admin.payslip.bulk-destroy'))),
        method: @json(old('payslip_delete_method', 'POST')),
        message: @json(old('payslip_delete_message', 'Delete the selected payslip record(s)?')),
        payslipIds: @json(old('payslip_ids', [])),
    });
});
@endif
</script>
