<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Company Policy Acknowledgment</title>
    <style>
        @page {
            margin: 38mm 24mm 28mm 24mm;
        }
        body {
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000000;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .policy-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 20px 0;
        }
        .policy-field {
            margin: 0 0 8px 0;
        }
        .policy-field-label {
            font-weight: bold;
        }
        .policy-intro {
            margin: 14px 0 10px 0;
        }
        .policy-list {
            margin: 0 0 12px 0;
            padding: 0;
            list-style: none;
        }
        .policy-list li {
            margin: 0 0 4px 0;
            padding: 0;
        }
        .policy-check {
            font-size: 11pt;
            margin-right: 4px;
        }
        .policy-paragraph {
            margin: 0 0 10px 0;
            text-align: justify;
        }
        .policy-sign-block {
            margin-top: 18px;
        }
        .policy-sign-heading {
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 10px 0;
        }
        .policy-sign-row {
            margin: 0 0 8px 0;
        }
        .policy-sign-label {
            font-weight: bold;
        }
        .policy-sign-row-signature {
            min-height: 14px;
        }
        .policy-signature-image {
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
    @include('user.employee-documents.partials.document-footer-pdf-script', ['documentType' => 'policy'])

    @include('user.employee-documents.partials.policy-body')
</body>
</html>
