@props(['title' => 'Dilla Evasu Fellowship'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 text-stone-800 antialiased">
    <header class="bg-white border-b border-stone-200">
        <nav class="max-w-5xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="font-semibold text-lg text-stone-900">Dilla Evasu Fellowship</a>

            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-stone-600">
                <a href="{{ route('home') }}" class="hover:text-stone-900">Home</a>
                <a href="{{ route('bible-messages.index') }}" class="hover:text-stone-900">Bible Messages</a>
                <a href="{{ route('community.index') }}" class="hover:text-stone-900">Community</a>
                <a href="{{ route('coworker-applications.create') }}" class="hover:text-stone-900">Become a Co-worker</a>

                @auth
                    <a href="{{ auth()->user()->isMainAdmin() ? route('admin.dashboard') : route('dashboard') }}"
                       class="rounded-full bg-stone-800 text-white px-4 py-1.5 hover:bg-stone-700">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-stone-900">Login</a>
                    <a href="{{ route('register') }}"
                       class="rounded-full bg-stone-800 text-white px-4 py-1.5 hover:bg-stone-700">Register</a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="border-t border-stone-200 mt-12">
        <div class="max-w-5xl mx-auto px-4 py-6 text-sm text-stone-500 text-center">
            &copy; {{ date('Y') }} Dilla Evasu Fellowship
        </div>
    </footer>
</body>
</html>