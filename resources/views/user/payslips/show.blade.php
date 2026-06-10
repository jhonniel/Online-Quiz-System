@extends('layouts.user')

@section('content')
<div class="space-y-4 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
    <a href="{{ route('user.payslips.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to payslips</a>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    @if($payslip->isSigned())
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">Signed Payslip</h1>
                    <p class="text-sm text-gray-500">{{ $payslip->periodLabel() }} &middot; Signed {{ $payslip->signed_at?->format('M j, Y h:i A') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button"
                            onclick="openPayslipSignModal({{ $payslip->id }}, @js($payslip->periodLabel()), true)"
                            class="inline-flex items-center rounded-lg border border-indigo-300 bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                        Re-sign
                    </button>
                    <a href="{{ route('user.payslips.signed', $payslip) }}"
                       target="_blank"
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                        Open PDF
                    </a>
                    <a href="{{ route('user.payslips.signed', ['payslip' => $payslip, 'download' => 1]) }}"
                       class="inline-flex items-center rounded-lg border border-indigo-300 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 shadow-sm hover:bg-indigo-100">
                        Download PDF
                    </a>
                </div>
            </div>
            <div class="bg-gray-100 p-4 flex justify-center">
                <iframe src="{{ route('user.payslips.signed', $payslip) }}?v={{ $payslip->signed_at?->timestamp }}"
                        title="Signed payslip PDF"
                        class="w-full max-w-3xl border-0 bg-white shadow-sm"
                        style="height: 1120px; max-height: 90vh;"></iframe>
            </div>
        </div>
    @else
        @include('admin.employee-management.payslip.partials.print-styles')

        <div class="flex flex-col items-center gap-3 sm:flex-row sm:items-start sm:justify-center">
            <div class="payslip-print-sheet payslip-print-sheet--single w-full max-w-3xl">
                <div class="payslip-print-slot">
                    <div id="payslip-print-area" class="payslip-print-area w-full">
                        @include('admin.employee-management.payslip.partials.sheet', ['payslip' => $payslip])
                    </div>
                </div>
            </div>
            <div class="print:hidden shrink-0 sm:pt-2 space-y-2">
                <button type="button"
                        onclick="openPayslipSignModal({{ $payslip->id }}, @js($payslip->periodLabel()))"
                        class="inline-flex items-center gap-2 rounded-lg border border-indigo-300 bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    Generate and Sign
                </button>
                @include('admin.employee-management.payslip.partials.print-toolbar')
            </div>
        </div>
    @endif

    <div id="payslip-sign-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-900/50" onclick="closePayslipSignModal()"></div>
            <div class="relative flex min-h-full items-center justify-center p-4">
                <div class="w-full max-w-md rounded-lg bg-white shadow-xl border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Generate and Sign Payslip</h2>
                        <p id="payslip-sign-period" class="mt-1 text-sm text-gray-500"></p>
                    </div>
                    <form id="payslip-sign-form" class="px-6 py-5 space-y-4">
                        @if($user->p12_certificate_path)
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
                            </div>
                        @else
                            <p class="text-sm text-gray-600">
                                Your uploaded e-signature will be placed on the <span class="font-medium">Received by</span> line when this payslip PDF is generated.
                            </p>
                        @endif
                        <p id="payslip-sign-error" class="text-sm text-red-600 hidden"></p>
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
        const payslipUserHasP12 = @json((bool) $user->p12_certificate_path);
        const payslipUserHasESignature = @json($user->hasESignature());

        function openPayslipSignModal(payslipId, periodLabel, isResign = false) {
            if (!payslipUserHasESignature) {
                window.location.href = '{{ url('/profile/edit') }}';
                return;
            }

            activePayslipSignId = payslipId;
            document.getElementById('payslip-sign-period').textContent = periodLabel;
            document.getElementById('payslip-sign-submit').textContent = isResign ? 'Re-sign' : 'Generate and Sign';
            const passwordInput = document.getElementById('payslip_p12_password');
            if (passwordInput) {
                passwordInput.value = '';
            }
            document.getElementById('payslip-sign-error').classList.add('hidden');
            document.getElementById('payslip-sign-modal').classList.remove('hidden');
            if (passwordInput) {
                passwordInput.focus();
            } else {
                document.getElementById('payslip-sign-submit').focus();
            }
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

            const passwordInput = document.getElementById('payslip_p12_password');
            const errorEl = document.getElementById('payslip-sign-error');
            const submitBtn = document.getElementById('payslip-sign-submit');
            const originalText = submitBtn.textContent;

            errorEl.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Signing...';

            const formData = new FormData();
            if (payslipUserHasP12 && passwordInput) {
                formData.append('p12_certificate_password', passwordInput.value);
            }

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
</div>
@endsection
