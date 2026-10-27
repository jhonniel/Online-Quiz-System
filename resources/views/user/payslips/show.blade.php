@extends('layouts.user')

@section('content')
<x-user-page-shell fluid class="space-y-5 pb-6 -mx-3 px-3 sm:-mx-4 sm:px-4 lg:-mx-6 lg:px-6">
    <x-user-page-header :title="$payslip->isSigned() ? 'Signed Payslip' : 'Payslip'" :subtitle="$payslip->periodLabel()">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </x-slot:icon>
        <x-slot:subtitleSlot>
            <a href="{{ route('user.payslips.index') }}" class="text-sm font-medium text-indigo-100/90 hover:text-white">&larr; Back to payslips</a>
            @if($payslip->isSigned())
                <p class="mt-1">Signed {{ $payslip->signed_at?->format('M j, Y h:i A') }}</p>
            @else
                <p class="mt-1">Generate and sign when you are ready.</p>
            @endif
        </x-slot:subtitleSlot>
        @if($payslip->isSigned())
            <x-slot:actions>
                <button type="button"
                        onclick="openPayslipSignModal({{ $payslip->id }}, @js($payslip->periodLabel()), true)"
                        class="inline-flex items-center rounded-lg border border-white/30 bg-white/10 px-3 py-2 text-sm font-medium text-white hover:bg-white/20">
                    Re-sign
                </button>
                <x-user-header-btn-primary href="{{ route('user.payslips.signed', $payslip) }}" target="_blank">Open PDF</x-user-header-btn-primary>
                <x-user-header-btn-secondary href="{{ route('user.payslips.signed', ['payslip' => $payslip, 'download' => 1]) }}">Download</x-user-header-btn-secondary>
            </x-slot:actions>
        @endif
    </x-user-page-header>

    @if(session('success'))
        <x-user-notice tone="success">{{ session('success') }}</x-user-notice>
    @endif

    @if($payslip->isSigned())
        <x-user-dashboard-section title="Signed PDF" description="Preview your signed payslip below.">
            <div class="flex justify-center rounded-lg bg-slate-100 p-4 -mx-1 sm:mx-0">
                <iframe src="{{ route('user.payslips.signed', $payslip) }}?v={{ $payslip->signed_at?->timestamp }}"
                        title="Signed payslip PDF"
                        class="w-full max-w-3xl border-0 bg-white shadow-sm"
                        style="height: min(1120px, 75dvh); max-height: 75dvh;"></iframe>
            </div>
        </x-user-dashboard-section>
    @else
        @include('admin.employee-management.payslip.partials.print-styles')

        <x-user-dashboard-section title="Payslip preview" description="Review your payslip, then generate and sign.">
            <div class="flex flex-col items-center gap-3 sm:flex-row sm:items-start sm:justify-center">
                <div class="payslip-print-sheet payslip-print-sheet--single w-full max-w-3xl">
                    <div class="payslip-print-slot">
                        <div id="payslip-print-area" class="payslip-print-area w-full">
                            @include('admin.employee-management.payslip.partials.sheet', ['payslip' => $payslip])
                        </div>
                    </div>
                </div>
                <div class="print:hidden shrink-0 space-y-2 sm:pt-2">
                    <button type="button"
                            onclick="openPayslipSignModal({{ $payslip->id }}, @js($payslip->periodLabel()))"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                        Generate and Sign
                    </button>
                    @include('admin.employee-management.payslip.partials.print-toolbar')
                </div>
            </div>
        </x-user-dashboard-section>
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
                        @if($user->hasP12Certificate())
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
        const payslipUserHasP12 = @json($user->hasP12Certificate());
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
</x-user-page-shell>
@endsection
