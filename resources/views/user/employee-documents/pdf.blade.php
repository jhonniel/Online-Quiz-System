<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $documentTitle }}</title>
    <style>
        @page {
            margin: 15mm 15mm 55mm 15mm;
        }
        body {
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #111827;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .title {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }
        .meta {
            margin: 20px 0;
            padding: 12px 14px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .meta p {
            margin: 0 0 4px 0;
            font-size: 10pt;
        }
        .body-text {
            margin: 0 0 12px 0;
            text-align: justify;
        }
        .signature-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 15mm;
            padding: 0 15mm;
        }
        .signature-block {
            border-top: 1px solid #d1d5db;
            padding-top: 12px;
        }
        .signature-label {
            font-size: 9pt;
            text-transform: uppercase;
            color: #6b7280;
            margin: 0 0 6px 0;
        }
        .digital-signature-slot {
            min-height: 22mm;
            margin-bottom: 4mm;
            padding: 6px 8px;
            border: 1px dashed #9ca3af;
            background: #f9fafb;
        }
        .digital-signature-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #374151;
            margin: 0 0 4px 0;
        }
        .digital-signature-note {
            font-size: 8pt;
            color: #6b7280;
            margin: 0;
        }
        .signature-image {
            height: 14mm;
            margin-bottom: 6px;
        }
        .signature-name {
            font-weight: bold;
            margin: 0;
        }
        .signature-date {
            font-size: 10pt;
            color: #4b5563;
            margin: 4px 0 0 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <x-document-letterhead-pdf />
        <h1 class="title">{{ $documentTitle }}</h1>
    </div>

    <div class="meta">
        <p><strong>Employee:</strong> {{ $employeeName }}</p>
        <p><strong>Department:</strong> {{ $department }}</p>
        <p><strong>Position:</strong> {{ $position }}</p>
        <p><strong>Date Hired:</strong> {{ $dateHired }}</p>
    </div>

    @foreach($paragraphs as $paragraph)
        <p class="body-text">{{ $paragraph }}</p>
    @endforeach

    <div class="signature-footer">
        <div class="signature-block">
            @if(!empty($digitalSignatureEnabled))
                <div class="digital-signature-slot">
                    <p class="digital-signature-title">Digital Signature (P12)</p>
                    <p class="digital-signature-note">Cryptographically signed by {{ $companyName }}</p>
                </div>
            @endif

            <p class="signature-label">Employee E-Signature</p>
            @if(!empty($eSignatureDataUri))
                <img src="{{ $eSignatureDataUri }}" alt="E-Signature" class="signature-image">
                <p class="signature-name">{{ $employeeName }}</p>
                @if($signedAt)
                    <p class="signature-date">Signed on {{ $signedAt->format('F j, Y h:i A') }}</p>
                @endif
            @else
                <div style="border-bottom: 1px solid #111827; width: 240px; height: 14mm; margin: 0 0 6px 0;"></div>
                <p class="signature-name">{{ $employeeName }}</p>
                @unless($signedAt)
                    <p class="signature-date">Unsigned</p>
                @endunless
            @endif
        </div>
    </div>
</body>
</html>
