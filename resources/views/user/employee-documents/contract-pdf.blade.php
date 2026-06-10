<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $documentTitle }}</title>
    <style>
        @page {
            margin: 20mm 22mm 24mm 22mm;
        }
        body {
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000000;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .agreement-page {
            page-break-after: always;
            position: relative;
            min-height: 250mm;
            padding-bottom: 16mm;
        }
        .agreement-page:last-child {
            page-break-after: auto;
        }
        .agreement-main-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 4px 0;
        }
        .agreement-subtitle {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 18px 0;
        }
        .section-heading {
            font-weight: bold;
            margin: 12px 0 6px 0;
        }
        .body-text {
            margin: 0 0 10px 0;
            text-align: justify;
        }
        .body-text-center {
            margin: 0 0 10px 0;
            text-align: center;
        }
        .agreement-list {
            margin: 0 0 10px 0;
            padding: 0 0 0 18px;
            list-style-type: disc;
        }
        .agreement-list li {
            margin: 0 0 4px 0;
            text-align: justify;
        }
        .field-line {
            display: inline-block;
            border-bottom: 1px solid #000000;
            line-height: 1.2;
            padding-bottom: 1px;
        }
        .field-line--blank {
            min-width: 72%;
        }
        .agreement-page-footer {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 12mm;
        }
        .footer-logo {
            position: absolute;
            left: 0;
            bottom: 0;
            height: 9mm;
            width: auto;
        }
        .footer-page-num {
            position: absolute;
            right: 0;
            bottom: 0;
            font-size: 10pt;
            color: #000000;
        }
        .agreement-sign-block {
            margin-top: 20px;
        }
        .agreement-sign-heading {
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 10px 0;
        }
        .agreement-sign-row {
            margin: 0 0 8px 0;
        }
        .agreement-sign-label {
            font-weight: bold;
        }
        .agreement-sign-row-signature {
            min-height: 18px;
        }
        .agreement-signature-image {
            height: 14px;
            max-width: 160px;
            vertical-align: bottom;
            margin-left: 4px;
        }
    </style>
</head>
<body>
    @include('user.employee-documents.partials.contract-body')
</body>
</html>
