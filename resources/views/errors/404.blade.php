<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Not Found</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="text-center">
        <p class="text-6xl font-bold text-gray-200">404</p>
        <h1 class="text-xl font-semibold text-gray-900 mt-4">Page Not Found</h1>
        <p class="text-sm text-gray-500 mt-2">The page you're looking for doesn't exist or has been moved.</p>
        <div class="mt-6 flex gap-3 justify-center">
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
            <a href="{{ url('/') }}" class="btn btn-primary">Dashboard</a>
        </div>
    </div>
</body>
</html>
