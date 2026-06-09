@extends('layouts.user')

@section('content')
<div class="space-y-4 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4">
        <h1 class="text-xl font-bold text-white">My Payslips</h1>
        <p class="text-indigo-100 text-sm mt-1">Generate and digitally sign your payslip records.</p>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    @unless($user->hasESignature())
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Upload your <a href="{{ url('/profile/edit') }}" class="font-medium underline">e-signature on your profile</a> before signing payslips.
        </div>
    @endunless
    @unless($user->p12_certificate_path)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Upload your <a href="{{ url('/profile/edit') }}" class="font-medium underline">P12 certificate on your profile</a> before signing payslips.
        </div>
    @endunless

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Period</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Gross Pay</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Net Pay</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($payslips as $payslip)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $payslip->periodLabel() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $payslip->formatMoney($payslip->gross_pay) }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $payslip->formatMoney($payslip->net_pay) }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($payslip->isSigned())
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 border border-green-200">Signed</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 border border-amber-200">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right space-x-3">
                                <button type="button"
                                        onclick="openPayslipSignModal({{ $payslip->id }}, @js($payslip->periodLabel()), {{ $payslip->isSigned() ? 'true' : 'false' }})"
                                        class="text-indigo-600 hover:text-indigo-900 font-medium">
                                    {{ $payslip->isSigned() ? 'Re-sign' : 'Generate and Sign' }}
                                </button>
                                @if($payslip->isSigned())
                                    <a href="{{ route('user.payslips.show', $payslip) }}" class="text-gray-600 hover:text-gray-900 font-medium">View</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No payslips available yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payslips->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $payslips->links() }}</div>
        @endif
    </div>
</div>

<div id="payslip-sign-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-gray-900/50" onclick="closePayslipSignModal()"></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md rounded-lg bg-white shadow-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Generate and Sign Payslip</h2>
                <p id="payslip-sign-period" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <form id="payslip-sign-form" class="px-6 py-5 space-y-4">
                <p class="text-sm text-gray-600">
                    Enter your P12 certificate password to generate and cryptographically sign your payslip PDF.
                </p>
                <div>
                    <label for="payslip_p12_password" class="block text-sm font-medium text-gray-700 mb-1">P12 Certificate Password</label>
                    <input type="password"
                           id="payslip_p12_password"
                           name="p12_certificate_password"
                           autocomplete="off"
                           required
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p id="payslip-sign-error" class="mt-2 text-sm text-red-600 hidden"></p>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button"
                            onclick="closePayslipSignModal()"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            id="payslip-sign-submit"
                            class="rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Generate and Sign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let activePayslipSignId = null;

function openPayslipSignModal(payslipId, periodLabel, isResign = false) {
    activePayslipSignId = payslipId;
    document.getElementById('payslip-sign-period').textContent = periodLabel;
    document.getElementById('payslip-sign-submit').textContent = isResign ? 'Re-sign' : 'Generate and Sign';
    document.getElementById('payslip_p12_password').value = '';
    document.getElementById('payslip-sign-error').classList.add('hidden');
    document.getElementById('payslip-sign-modal').classList.remove('hidden');
    document.getElementById('payslip_p12_password').focus();
}

function closePayslipSignModal() {
    activePayslipSignId = null;
    document.getElementById('payslip-sign-modal').classList.add('hidden');
}

document.getElementById('payslip-sign-form').addEventListener('submit', function (event) {
    event.preventDefault();

    if (!activePayslipSignId) {
        return;
    }

    const password = document.getElementById('payslip_p12_password').value;
    const errorEl = document.getElementById('payslip-sign-error');
    const submitBtn = document.getElementById('payslip-sign-submit');
    const originalText = submitBtn.textContent;

    errorEl.classList.add('hidden');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Signing...';

    const formData = new FormData();
    formData.append('p12_certificate_password', password);

    fetch(`/payslips/${activePayslipSignId}/sign`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(response => response.json().then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) {
            const message = data.errors?.p12_certificate_password?.[0] || data.message || 'Failed to sign payslip.';
            throw new Error(message);
        }

        window.location.href = data.redirect_url || window.location.href;
    })
    .catch(error => {
        errorEl.textContent = error.message || 'Failed to sign payslip.';
        errorEl.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});
</script>
@endsection
