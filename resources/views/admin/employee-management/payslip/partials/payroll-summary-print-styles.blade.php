<style>
    .payslip-summary-print-area {
        padding-right: 88px;
    }

    .payslip-summary-table-wrap,
    .payslip-summary-table,
    .payslip-summary-table tbody tr,
    .payslip-summary-table td {
        overflow: visible !important;
    }

    .payslip-summary-net-pay-cell {
        position: relative;
        overflow: visible !important;
    }

    .payslip-summary-signature-img {
        position: absolute;
        top: 50%;
        left: 100%;
        transform: translateY(-50%);
        margin-left: 6px;
        height: 36px;
        max-width: 80px;
        object-fit: contain;
        pointer-events: none;
        z-index: 1;
    }

    @media print {
        @page {
            size: legal landscape;
            margin: 8mm;
        }

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body * {
            visibility: hidden;
        }

        #payslip-summary-print-area,
        #payslip-summary-print-area * {
            visibility: visible;
        }

        #payslip-summary-print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none !important;
            box-shadow: none !important;
            overflow: visible !important;
            padding-right: 88px !important;
        }

        .payslip-summary-table-wrap,
        .payslip-summary-table,
        .payslip-summary-table tbody tr,
        .payslip-summary-table td {
            overflow: visible !important;
        }

        .payslip-summary-table {
            font-size: 9px !important;
            width: 100% !important;
        }

        .payslip-summary-table thead {
            display: table-header-group;
        }

        .payslip-summary-table tfoot {
            display: table-row-group;
        }

        .payslip-summary-sheet-header-row th {
            background: #fff !important;
            color: #1e3a8a !important;
            border: none !important;
            font-family: Georgia, 'Times New Roman', Times, serif !important;
            font-weight: 700 !important;
            font-style: italic !important;
            text-align: center !important;
        }

        .payslip-summary-company-name {
            font-size: 16px !important;
            padding-top: 0 !important;
        }

        .payslip-summary-sheet-title {
            font-size: 14px !important;
            padding-bottom: 8px !important;
        }

        .payslip-summary-table th,
        .payslip-summary-table td {
            padding: 4px 6px !important;
        }

        .payslip-summary-columns-header-row,
        .payslip-summary-columns-header-row th {
            background-color: #1e3a8a !important;
            color: #fff !important;
        }

        .payslip-summary-columns-header-row th {
            border-color: #1e40af !important;
        }

        .payslip-summary-table tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .payslip-summary-table tfoot tr {
            background-color: #f3f4f6 !important;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .payslip-summary-net-pay-cell {
            position: relative !important;
            overflow: visible !important;
        }

        .payslip-summary-signature-img {
            position: absolute !important;
            top: 50% !important;
            left: 100% !important;
            transform: translateY(-50%) !important;
            margin-left: 6px !important;
            display: block !important;
            height: 36px !important;
            max-width: 80px !important;
            object-fit: contain !important;
            pointer-events: none !important;
            z-index: 1 !important;
        }
    }
</style>
