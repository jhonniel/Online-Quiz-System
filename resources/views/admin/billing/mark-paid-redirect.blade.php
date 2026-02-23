<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Redirecting...</title>
</head>
<body>
    <p>Redirecting back to billing...</p>
    <script>
        window.open(@json($statementUrl), '_blank');
        window.location = @json($billingUrl) + '?success=' + encodeURIComponent(@json($successMessage));
    </script>
</body>
</html>
