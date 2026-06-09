<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $documentTitle }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #111827;
            line-height: 1.5;
            margin: 0;
            padding: 36px 42px;
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
        .signature-block {
            margin-top: 36px;
            padding-top: 16px;
            border-top: 1px solid #d1d5db;
        }
        .signature-label {
            font-size: 9pt;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 10px;
        }
        .signature-image {
            height: 56px;
            margin-bottom: 8px;
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

    <div class="signature-block">
        <p class="signature-label">Employee E-Signature</p>
        @if($signedAt && !empty($eSignatureDataUri))
            <img src="{{ $eSignatureDataUri }}" alt="E-Signature" class="signature-image">
            <p class="signature-name">{{ $employeeName }}</p>
            <p class="signature-date">Signed on {{ $signedAt->format('F j, Y h:i A') }}</p>
        @else
            <div style="border-bottom: 1px solid #111827; width: 240px; height: 48px; margin: 12px 0 8px 0;"></div>
            <p class="signature-name">{{ $employeeName }}</p>
            @unless($signedAt)
                <p class="signature-date">Unsigned</p>
            @endunless
        @endif
    </div>
</body>
</html>
