<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
            padding: 20px;
        }
        img {
            max-width: 100%;
            height: auto;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        {!! $html !!}
    </div>
</body>
</html>
