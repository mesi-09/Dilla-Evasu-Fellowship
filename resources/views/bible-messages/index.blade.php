<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bible Messages — Dilla Evasu Fellowship</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="p-6 max-w-2xl mx-auto">
        <a href="/" class="text-sm text-blue-600 underline">&larr; Home</a>
        <h1 class="text-2xl font-bold mt-2 mb-4">Bible Messages</h1>

        <div class="space-y-4">
            @forelse ($messages as $message)
                <a href="{{ route('bible-messages.show', $message) }}" class="block bg-white rounded shadow p-4 hover:bg-gray-50">
                    <p class="font-semibold">{{ $message->title }}</p>
                    <p class="text-sm text-gray-500 italic">{{ $message->verse }}</p>
                    <p class="text-gray-700 mt-2">{{ \Illuminate\Support\Str::limit($message->message, 140) }}</p>
                    @if ($message->publish_date)
                        <p class="text-xs text-gray-400 mt-2">{{ $message->publish_date->format('M j, Y') }}</p>
                    @endif
                </a>
            @empty
                <p class="text-gray-500">No messages have been published yet.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $messages->links() }}
        </div>
    </div>
</body>
</html>