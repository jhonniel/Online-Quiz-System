<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Non-Disclosure Agreement</title>
    <style>
        @page {
            size: 8.5in 14in;
            margin: 36mm 24mm 24mm 24mm;
        }
        body {
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000000;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .document-page-shell {
            page-break-inside: avoid;
        }
        .header-country {
            text-align: center;
            margin: 0 0 20px 0;
            font-size: 12pt;
        }
        .title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 20px 0;
        }
        .body-text {
            margin: 0 0 10px 0;
            text-align: justify;
        }
        .done-line {
            margin: 14px 0 0 0;
            text-align: left;
        }
        .signature-block {
            margin-top: 18px;
            text-align: center;
        }
        .signature-sign-area {
            height: 56px;
            position: relative;
        }
        .signature-image {
            position: absolute;
            left: 50%;
            top: 50%;
            margin-left: -105px;
            margin-top: -20px;
            height: 40px;
            width: 210px;
            z-index: 0;
        }
        .signature-line {
            border-top: 1px solid #000000;
            width: 78%;
            max-width: 420px;
            margin: 0 auto 0;
            height: 0;
        }
        .signature-identity {
            margin-top: 12px;
        }
        .signature-id-group {
            margin-top: 18px;
        }
        .signature-meta {
            font-weight: bold;
            margin: 0 0 6px 0;
            text-transform: uppercase;
            font-size: 11pt;
            line-height: 1.35;
        }
        .signature-identity .signature-meta:last-child {
            margin-bottom: 0;
        }
        .signature-id-group .signature-meta:last-child {
            margin-bottom: 0;
        }
        .ack-section {
            margin-top: 18px;
            text-align: center;
        }
        .ack-title {
            font-weight: bold;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            font-size: 11pt;
        }
        .ack-name {
            font-weight: bold;
            text-decoration: underline;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            font-size: 11pt;
        }
        .ack-meta {
            font-weight: bold;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            font-size: 11pt;
            line-height: 1.35;
        }
        .ack-meta:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    @include('user.employee-documents.partials.document-letterhead-pdf-fixed')
    @include('user.employee-documents.partials.document-footer-pdf-script', ['documentType' => 'nda'])

    @include('user.employee-documents.partials.nda-body')
</body>
</html>
