<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $documentTitle }}</title>
    <style>
        @page {
            margin: 38mm 22mm 28mm 22mm;
        }
        body {
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000000;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .agreement-page,
        .document-page-shell {
            page-break-after: always;
            position: relative;
            min-height: 250mm;
            padding-bottom: 16mm;
            box-sizing: border-box;
        }
        .agreement-page:last-child,
        .document-page-shell:last-child {
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
        .agreement-position-content {
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
        .document-page-footer {
            text-align: left;
            font-size: 7.5pt;
            color: #111827;
            line-height: 1.3;
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
            min-height: 14px;
        }
        .agreement-signature-image {
            height: 16px;
            max-width: 85px;
            width: auto;
            vertical-align: bottom;
            margin-left: 4px;
        }
    </style>
</head>
<body>
    @include('user.employee-documents.partials.document-letterhead-pdf-fixed')
    @include('user.employee-documents.partials.document-footer-pdf-script', [
        'documentType' => 'contract',
        'companyName' => $companyName ?? null,
    ])

    @include('user.employee-documents.partials.contract-body')
</body>
</html>
