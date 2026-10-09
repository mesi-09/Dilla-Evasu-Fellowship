<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $bibleMessage->title }} — Dilla Evasu Fellowship</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="p-6 max-w-2xl mx-auto">
        <a href="{{ route('bible-messages.index') }}" class="text-sm text-blue-600 underline">&larr; All messages</a>

        <article class="bg-white rounded shadow p-6 mt-3">
            <h1 class="text-2xl font-bold">{{ $bibleMessage->title }}</h1>
            <p class="text-gray-500 italic mt-1">{{ $bibleMessage->verse }}</p>

            <div class="mt-4 text-gray-800 leading-relaxed">
                {!! nl2br(e($bibleMessage->message)) !!}
            </div>

            <p class="text-sm text-gray-400 mt-6">
                @if ($bibleMessage->author)
                    {{ $bibleMessage->author }} &middot;
                @endif
                @if ($bibleMessage->publish_date)
                    {{ $bibleMessage->publish_date->format('M j, Y') }}
                @endif
            </p>
        </article>
    </div>
</body>
</html>