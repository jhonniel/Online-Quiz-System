<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $payslip->periodLabel() }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #111827;
            margin: 0;
            padding: 0;
            line-height: 1.35;
        }
        .sheet {
            border: 1px solid #d1d5db;
            page-break-inside: avoid;
            page-break-after: avoid;
        }
        .letterhead {
            text-align: center;
            padding: 10px 16px 8px;
        }
        .letterhead-name {
            margin: 0;
            font-family: "DejaVu Serif", "Times New Roman", serif;
            font-size: 13pt;
            color: #6b8e23;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .letterhead-address {
            margin: 4px 0 0;
            font-size: 8.5pt;
            line-height: 1.3;
        }
        .banner {
            border-top: 1px solid #111827;
            border-bottom: 1px solid #111827;
            background: #e5e7eb;
            text-align: center;
            padding: 5px 0;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10pt;
            letter-spacing: 0.04em;
        }
        .meta {
            padding: 10px 16px;
            border-bottom: 1px solid #d1d5db;
        }
        .meta-title {
            margin: 0 0 8px;
            font-weight: bold;
        }
        .meta-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-grid td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        .meta-grid p { margin: 0 0 4px; }
        .columns {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .columns > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            border-bottom: 1px solid #d1d5db;
        }
        .columns > tbody > tr > td:first-child {
            border-right: 1px solid #d1d5db;
        }
        .col-heading {
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            background: #f9fafb;
            padding: 8px 16px;
            border-bottom: 1px solid #d1d5db;
            font-size: 9pt;
        }
        .col-body { padding: 10px 16px 12px; }
        .line {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 5px;
        }
        .line td { padding: 0; }
        .line td:last-child { text-align: right; white-space: nowrap; }
        .loans-label {
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            padding: 4px;
            margin: 8px 0 6px;
        }
        .total-line {
            border-top: 1px solid #e5e7eb;
            margin-top: 6px;
            padding-top: 6px;
            font-weight: bold;
        }
        .net-pay {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 12px 16px;
            border-top: 1px solid #d1d5db;
            font-size: 11pt;
        }
        .footer {
            border-top: 1px solid #d1d5db;
            padding: 14px 16px 16px;
        }
        .declaration {
            text-align: center;
            margin: 0 0 18px;
            line-height: 1.45;
        }
        .signatures {
            width: 100%;
            border-collapse: collapse;
        }
        .signatures td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 6px;
        }
        .sign-label {
            font-weight: 500;
            margin: 0 0 8px;
        }
        .sign-block {
            position: relative;
            display: inline-block;
            max-width: 100%;
            margin: 0 auto;
            padding-top: 14px;
        }
        .signature-image {
            position: absolute;
            top: -2px;
            left: 50%;
            margin-left: -88px;
            height: 36px;
            width: 176px;
            display: block;
            z-index: 0;
        }
        .sign-line {
            position: relative;
            z-index: 10;
            border-bottom: 1px solid #111827;
            padding: 0 6px 2px;
            font-weight: bold;
            margin: 0;
            min-height: 14px;
            white-space: nowrap;
            font-size: 8.5pt;
            text-align: center;
        }
        .sign-role {
            margin: 8px 0 0;
            font-size: 8.5pt;
        }
    </style>
</head>
<body>
@php
    $companyName = trim((string) \App\Models\Setting::get('system_name', config('app.name', 'Laravel')));
    $companyAddress = trim((string) \App\Models\Setting::get('contact_address', ''));
@endphp
    <div class="sheet">
        <div class="letterhead">
            <p class="letterhead-name">{{ $companyName }}</p>
            @if($companyAddress !== '')
                <p class="letterhead-address">{{ $companyAddress }}</p>
            @endif
        </div>

        <div class="banner">Payslip</div>

        <div class="meta">
            <p class="meta-title">Period Covered: {{ $payslip->periodLabel() }}</p>
            <table class="meta-grid">
                <tr>
                    <td>
                        <p>Employee Name: <strong>{{ strtoupper($payslip->employee_name) }}</strong></p>
                        <p>Position: <strong>{{ strtoupper($payslip->position ?: '—') }}</strong></p>
                    </td>
                    <td>
                        <p>Date Hired: <strong>{{ $payslip->date_hired?->format('F j, Y') ?: '—' }}</strong></p>
                        <p>Rate per day: <strong>{{ $payslip->formatMoney($payslip->rate_per_day) }}</strong></p>
                    </td>
                </tr>
            </table>
        </div>

        <table class="columns">
            <tr>
                <td>
                    <div class="col-heading">Deductions</div>
                    <div class="col-body">
                        <table class="line"><tr><td>SSS</td><td>{{ $payslip->formatMoney($payslip->sss) }}</td></tr></table>
                        <table class="line"><tr><td>PHIC</td><td>{{ $payslip->formatMoney($payslip->phic) }}</td></tr></table>
                        <table class="line"><tr><td>HDMF</td><td>{{ $payslip->formatMoney($payslip->hdmf) }}</td></tr></table>
                        <table class="line"><tr><td>Late/hrs</td><td>{{ $payslip->formatCount($payslip->late_hours) }}</td></tr></table>
                        <table class="line"><tr><td>Absences/day</td><td>{{ $payslip->formatCount($payslip->absences_days) }}</td></tr></table>
                        <table class="line"><tr><td>Withholding Tax</td><td>{{ $payslip->formatMoney($payslip->withholding_tax) }}</td></tr></table>
                        <div class="loans-label">Company Loans</div>
                        <table class="line"><tr><td>CA</td><td>{{ $payslip->formatMoney($payslip->ca) }}</td></tr></table>
                        <table class="line"><tr><td>Gov't Loans</td><td>{{ $payslip->formatMoney($payslip->govt_loans) }}</td></tr></table>
                        <table class="line"><tr><td>Loans</td><td>{{ $payslip->formatMoney($payslip->loans) }}</td></tr></table>
                        <table class="line total-line"><tr><td>Total Deduction</td><td>{{ $payslip->formatMoney($payslip->calculatedTotalDeductions()) }}</td></tr></table>
                    </div>
                </td>
                <td>
                    <div class="col-heading">Earnings</div>
                    <div class="col-body">
                        <table class="line"><tr><td>Total Working Days</td><td>{{ $payslip->total_working_days ?: '-' }}</td></tr></table>
                        <table class="line"><tr><td>Overtime pay</td><td>{{ $payslip->formatMoney($payslip->overtime_pay) }}</td></tr></table>
                        <table class="line"><tr><td>Holidays pay</td><td>{{ $payslip->formatMoney($payslip->holiday_pay) }}</td></tr></table>
                        <table class="line"><tr><td>Allowances</td><td>{{ $payslip->formatMoney($payslip->allowances) }}</td></tr></table>
                        <table class="line"><tr><td>13th Month</td><td>{{ $payslip->formatMoney($payslip->thirteenth_month_pay) }}</td></tr></table>
                        <table class="line total-line"><tr><td>Gross Pay</td><td>{{ $payslip->formatMoney($payslip->gross_pay) }}</td></tr></table>
                    </div>
                </td>
            </tr>
        </table>

        <div class="net-pay">Net Pay: {{ $payslip->formatMoney($payslip->net_pay) }}</div>

        <div class="footer">
            <p class="declaration">
                I hereby declared that I received this payroll and I don't have any questions or further clarifications.
            </p>

            <table class="signatures">
                <tr>
                    <td>
                        <p class="sign-label">Prepared by:</p>
                        <div class="sign-block">
                            <p class="sign-line">{{ $payslip->prepared_by ?: ' ' }}</p>
                        </div>
                        <p class="sign-role">Admin Officer</p>
                    </td>
                    <td>
                        <p class="sign-label">Approved by:</p>
                        <div class="sign-block">
                            <p class="sign-line">{{ $payslip->approved_by ?: ' ' }}</p>
                        </div>
                        <p class="sign-role">Proprietor</p>
                    </td>
                    <td>
                        <p class="sign-label">Received by:</p>
                        <div class="sign-block">
                            @if(!empty($eSignatureDataUri))
                                <img src="{{ $eSignatureDataUri }}" alt="E-Signature" class="signature-image" style="object-fit: contain;">
                            @endif
                            <p class="sign-line">{{ $payslip->employee_name }}</p>
                        </div>
                        <p class="sign-role">{{ $payslip->position ?: '—' }}</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
