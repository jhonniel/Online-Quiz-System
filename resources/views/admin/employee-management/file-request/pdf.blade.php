<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: {{ ($fullBleed ?? false) ? '0' : '14mm 12mm' }}; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #000000;
        }
        .container { padding: 0; }
        img { max-width: 100%; height: auto; }
        .coe-document { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="container">
        {!! $html !!}
    </div>
</body>
</html>
