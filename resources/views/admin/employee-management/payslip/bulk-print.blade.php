<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Payslips ({{ $payslips->count() }})</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('admin.employee-management.payslip.partials.print-styles')
    <style>
        @media screen {
            body {
                background: #f3f4f6;
            }

            .payslip-print-sheet {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1.5rem;
                max-width: 80rem;
                margin: 0 auto 2.5rem;
            }

            .payslip-print-slot {
                min-width: 0;
            }
        }
    </style>
</head>
<body class="payslip-bulk-print text-gray-900 antialiased">
    <div class="print:hidden sticky top-0 z-10 border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6">
            <div>
                <p class="text-sm font-semibold text-gray-900">{{ $payslips->count() }} payslip(s) ready to print</p>
                <p class="text-xs text-gray-500">Two payslips per landscape page.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button"
                        onclick="window.close()"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Close
                </button>
                @include('admin.employee-management.payslip.partials.print-toolbar')
            </div>
        </div>
    </div>

    <div class="px-4 py-6 sm:px-6">
        @foreach($payslips->chunk(2) as $sheetPayslips)
            <div class="payslip-print-sheet">
                @foreach($sheetPayslips as $payslip)
                    <div class="payslip-print-slot">
                        <div class="payslip-print-area">
                            @include('admin.employee-management.payslip.partials.sheet', ['payslip' => $payslip])
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <script>
        window.addEventListener('load', function () {
            if (document.querySelectorAll('.payslip-print-area').length > 0) {
                requestAnimationFrame(function () {
                    printPayslip();
                });
            }
        });
    </script>
</body>
</html>
