<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Non-Disclosure Agreement</title>
    <style>
        @page {
            size: 8.5in 14in;
            margin: 1in 1.15in 1in 1.15in;
        }
        html {
            margin: 0;
            padding: 0;
        }
        body {
            margin: 1in 1.15in;
            padding: 0;
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            font-size: 12pt;
            color: #000000;
            line-height: 1.5;
        }
        .nda-page {
            width: 100%;
            box-sizing: border-box;
        }
        .header-country {
            text-align: center;
            margin: 0 0 24px 0;
            font-size: 12pt;
        }
        .title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 0 0 28px 0;
        }
        .body-text {
            margin: 0 0 14px 0;
            text-align: justify;
        }
        .done-line {
            margin: 24px 0 0 0;
            text-align: left;
        }
        .signature-block {
            margin-top: 40px;
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
            margin-top: 28px;
        }
        .signature-meta {
            font-weight: bold;
            margin: 0 0 6px 0;
            text-transform: uppercase;
            font-size: 12pt;
            line-height: 1.35;
        }
        .signature-identity .signature-meta:last-child {
            margin-bottom: 0;
        }
        .signature-id-group .signature-meta:last-child {
            margin-bottom: 0;
        }
        .ack-section {
            margin-top: 56px;
            text-align: center;
        }
        .ack-title {
            font-weight: bold;
            margin: 0 0 20px 0;
            text-transform: uppercase;
            font-size: 12pt;
        }
        .ack-name {
            font-weight: bold;
            text-decoration: underline;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            font-size: 12pt;
        }
        .ack-meta {
            font-weight: bold;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            font-size: 12pt;
            line-height: 1.35;
        }
        .ack-meta:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="nda-page">
        <p class="header-country">Republic of the Philippines</p>

        <p class="title">NON-DISCLOSURE AGREEMENT</p>

        <p class="body-text">
            This is to certify that I, <strong>{{ $fullNameUpper }}</strong>, of
            <strong>{{ strtoupper($branding['company_inline']) }}</strong> understand that I cannot give out or share any official record
            obtained or accessed from/thru and/or involving the development of the Systems, Websites and Social Media Content
            for the clients contracted to <strong>{{ strtoupper($branding['company_inline']) }}</strong> without proper authority or unless
            in connection with my official functions or in pursuance of official transactions and processes.
        </p>

        <p class="body-text">
            I understand that any unauthorized release or negligence in the handling of the abovementioned information is
            considered a breach of confidence and prejudicial to the best interest of the Republic of the Philippines.
        </p>

        <p class="body-text">
            I further understand that any such breach may give rise to grounds for administrative or criminal liabilities as
            provided under existing laws.
        </p>

        <p class="done-line">
            Done in the City of {{ $city }}, this {{ $agreementDateFormal }}.
        </p>

        <div class="signature-block">
            <div class="signature-sign-area">
                @if(!empty($eSignatureDataUri))
                    <img src="{{ $eSignatureDataUri }}" alt="E-Signature" class="signature-image" style="object-fit: contain;">
                @endif
            </div>
            <div class="signature-line"></div>
            <div class="signature-identity">
                <p class="signature-meta">{{ $fullNameUpper }}</p>
                <p class="signature-meta">{{ strtoupper($branding['company_signature']) }}</p>
            </div>
            <div class="signature-id-group">
                <p class="signature-meta">{{ $idNumberUpper }}</p>
                <p class="signature-meta">{{ $validIdTypeUpper }}</p>
            </div>
        </div>

        <div class="ack-section">
            <p class="ack-title">PERSONALLY SIGNED BEFORE ME:</p>
            <p class="ack-name">{{ $branding['signatory_name'] }}</p>
            <p class="ack-meta">{{ $branding['signatory_title'] }}</p>
            <p class="ack-meta">{{ $branding['company_line1'] }}</p>
            @if(!empty($branding['company_line2']))
                <p class="ack-meta">{{ $branding['company_line2'] }}</p>
            @endif
            <p class="ack-meta">{{ $agreementDateUpper }}</p>
        </div>
    </div>
</body>
</html>
