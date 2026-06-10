@once
<style>
    .payslip-name-line,
    .payslip-print-area .payslip-name-line {
        white-space: nowrap;
    }

    @media screen {
        .payslip-print-sheet--single {
            width: 100%;
        }

        .payslip-print-sheet--single .payslip-print-slot {
            width: 100%;
        }
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        html,
        body {
            width: 297mm;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body * {
            visibility: hidden;
        }

        .payslip-print-sheet,
        .payslip-print-slot,
        #payslip-print-area,
        .payslip-print-area,
        .payslip-print-sheet *,
        .payslip-print-slot *,
        #payslip-print-area *,
        .payslip-print-area * {
            visibility: visible;
        }

        .payslip-print-sheet {
            display: flex;
            flex-direction: row;
            align-items: stretch;
            width: 277mm;
            height: 190mm;
            page-break-after: always;
            break-after: page;
            box-sizing: border-box;
        }

        body.payslip-bulk-print .payslip-print-sheet {
            overflow: visible;
        }

        body:not(.payslip-bulk-print) .payslip-print-sheet--single {
            position: absolute;
            left: 0;
            top: 0;
            width: 277mm;
            height: 190mm;
            overflow: visible;
        }

        .payslip-print-sheet--single .payslip-print-slot {
            flex: 1 1 100%;
        }

        .payslip-print-slot {
            flex: 1 1 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            min-width: 0;
        }

        .payslip-print-sheet:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        .payslip-print-sheet--single {
            justify-content: center;
        }

        body.payslip-bulk-print .payslip-print-slot,
        body:not(.payslip-bulk-print) .payslip-print-slot {
            overflow: visible;
        }

        body.payslip-bulk-print .payslip-print-slot:first-child {
            border-right: 0.4mm solid #d1d5db;
            padding-right: 2mm;
        }

        body.payslip-bulk-print .payslip-print-slot:nth-child(2) {
            padding-left: 2mm;
        }

        #payslip-print-area,
        .payslip-print-area {
            position: relative;
            flex-shrink: 0;
            transform-origin: center center;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        #payslip-print-area > div,
        .payslip-print-area > div {
            box-shadow: none !important;
            margin: 0 !important;
            border-color: #d1d5db !important;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        body:not(.payslip-bulk-print) .payslip-print-area > div {
            width: 100% !important;
            max-width: 277mm !important;
            box-sizing: border-box !important;
            overflow: visible !important;
        }

        body.payslip-bulk-print .payslip-print-area > div {
            width: 100% !important;
            max-width: 100% !important;
        }

        #payslip-print-area .px-6,
        .payslip-print-area .px-6 {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }

        #payslip-print-area .py-6,
        .payslip-print-area .py-6 {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }

        #payslip-print-area .py-4,
        .payslip-print-area .py-4 {
            padding-top: 0.35rem !important;
            padding-bottom: 0.35rem !important;
        }

        #payslip-print-area .py-3,
        .payslip-print-area .py-3 {
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
        }

        #payslip-print-area .pt-5,
        .payslip-print-area .pt-5 {
            padding-top: 0.5rem !important;
        }

        #payslip-print-area .pb-3,
        .payslip-print-area .pb-3 {
            padding-bottom: 0.35rem !important;
        }

        #payslip-print-area .mt-8,
        .payslip-print-area .mt-8 {
            margin-top: 0.5rem !important;
        }

        #payslip-print-area .gap-8,
        .payslip-print-area .gap-8 {
            gap: 0.5rem !important;
        }

        #payslip-print-area .space-y-2 > :not([hidden]) ~ :not([hidden]),
        .payslip-print-area .space-y-2 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 0.15rem !important;
        }

        #payslip-print-area .sm\:grid-cols-2,
        .payslip-print-area .sm\:grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        #payslip-print-area .sm\:grid-cols-3,
        .payslip-print-area .sm\:grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }

        .payslip-print-area [class*="min-w-"]:not(.payslip-sign-name-block) {
            min-width: 0 !important;
            max-width: 7rem !important;
            width: 100% !important;
        }

        .payslip-print-area .payslip-signatures-grid > div {
            min-width: 0 !important;
            max-width: 100% !important;
        }

        .payslip-print-area .payslip-sign-name-block {
            min-width: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }

        .payslip-print-area .payslip-sign-name,
        .payslip-print-area .payslip-employee-name,
        .payslip-print-area .payslip-position-name,
        .payslip-print-area .payslip-name-line {
            white-space: nowrap !important;
            word-break: normal !important;
            overflow-wrap: normal !important;
            line-height: 1.2 !important;
            max-width: 100% !important;
        }

        .payslip-print-area .payslip-esign-float {
            width: 11rem !important;
            max-width: 11rem !important;
            height: 3.5rem !important;
            object-fit: contain !important;
        }

        body.payslip-bulk-print .payslip-print-area [class*="min-w-"]:not(.payslip-sign-name-block) {
            max-width: 4.5rem !important;
        }

        .payslip-print-area .h-14:not(.payslip-esign-float) {
            height: 2.25rem !important;
            max-width: 7rem !important;
        }

        body.payslip-bulk-print .payslip-print-area .h-14:not(.payslip-esign-float) {
            height: 2rem !important;
            max-width: 4.5rem !important;
        }
    }
</style>

<script>
    function isBulkPayslipPrint() {
        return document.body.classList.contains('payslip-bulk-print');
    }

    function mmToPx(mm) {
        return mm * (96 / 25.4);
    }

    function getBulkSlotDimensionsMm() {
        return {
            widthMm: (297 - 20) / 2 - 4,
            heightMm: 210 - 20 - 4,
        };
    }

    function getSinglePrintDimensionsMm() {
        return {
            widthMm: 297 - 20 - 4,
            heightMm: 210 - 20 - 4,
        };
    }

    function getPayslipPrintAreas() {
        if (isBulkPayslipPrint()) {
            return Array.from(document.querySelectorAll('.payslip-print-sheet .payslip-print-area'));
        }

        const singleArea = document.getElementById('payslip-print-area');
        return singleArea ? [singleArea] : [];
    }

    function clearPayslipMeasureStyles(sheet) {
        if (!sheet) {
            return;
        }

        sheet.style.width = '';
        sheet.style.maxWidth = '';
        sheet.style.boxSizing = '';
    }

    function scalePayslipPrintAreas(areas) {
        const bulk = isBulkPayslipPrint();
        const dimensions = bulk ? getBulkSlotDimensionsMm() : getSinglePrintDimensionsMm();
        const maxWidthPx = mmToPx(dimensions.widthMm);
        const maxHeightPx = mmToPx(dimensions.heightMm);
        const safety = 0.96;

        areas.forEach(function (area) {
            const sheet = area.firstElementChild;
            if (!sheet) {
                return;
            }

            sheet.style.width = maxWidthPx + 'px';
            sheet.style.maxWidth = maxWidthPx + 'px';
            sheet.style.boxSizing = 'border-box';

            area.style.transform = 'scale(1)';

            const naturalWidth = Math.max(sheet.scrollWidth, sheet.offsetWidth);
            const naturalHeight = Math.max(sheet.scrollHeight, sheet.offsetHeight);

            let scale = (maxWidthPx * safety) / naturalWidth;

            if (naturalHeight * scale > maxHeightPx * safety) {
                scale = (maxHeightPx * safety) / naturalHeight;
            }

            scale = Math.min(Math.max(scale, 0.1), 1);

            area.style.transform = 'scale(' + scale + ')';
        });
    }

    function resetPayslipPrintAreas(areas) {
        areas.forEach(function (area) {
            area.style.transform = 'scale(1)';
            clearPayslipMeasureStyles(area.firstElementChild);
        });
    }

    function printPayslip() {
        const areas = getPayslipPrintAreas();

        if (areas.length === 0) {
            window.print();
            return;
        }

        scalePayslipPrintAreas(areas);

        window.addEventListener('afterprint', function () {
            resetPayslipPrintAreas(areas);
        }, { once: true });

        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                window.print();
            });
        });
    }
</script>
@endonce
