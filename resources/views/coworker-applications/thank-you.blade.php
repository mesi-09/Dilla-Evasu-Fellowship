<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thank You — Dilla Evasu Fellowship</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="p-6 max-w-2xl mx-auto text-center mt-12">
        <h1 class="text-2xl font-bold mb-4">Thank You!</h1>
        <p class="text-gray-600">{{ session('status', 'Your application has been received.') }}</p>
        <a href="/" class="inline-block mt-6 text-blue-600 underline">Return home</a>
    </div>
</body>
</html>