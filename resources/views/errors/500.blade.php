<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Server Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-red-600 mb-2">500</h1>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Server Error</h2>
                <p class="text-gray-600 mb-6">Something went wrong on our end. Please try again later.</p>
                <a href="{{ url()->previous() ?? url('/admin/dashboard') }}" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Go Back
                </a>
            </div>
        </div>
    </div>
</body>
</html>
