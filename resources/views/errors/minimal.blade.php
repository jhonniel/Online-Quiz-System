<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Error</h1>
                <p class="text-gray-600">{{ $message ?? 'An error occurred' }}</p>
            </div>
        </div>
    </div>
</body>
</html>
