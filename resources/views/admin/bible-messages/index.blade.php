<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold">Bible Messages</h1>
            <a href="{{ route('admin.bible-messages.create') }}" class="bg-black text-white px-4 py-2 rounded">New message</a>
        </div>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">
                {{ session('status') }}
            </div>
        @endif

        <p class="text-sm text-gray-500 mb-4">
            Scheduled messages are published automatically at 6:00 AM (Addis Ababa time) on their date.
        </p>

        <div class="bg-white rounded shadow divide-y">
            @forelse ($messages as $message)
                @php
                    $badge = [
                        'draft' => 'bg-gray-100 text-gray-600',
                        'scheduled' => 'bg-yellow-100 text-yellow-700',
                        'published' => 'bg-green-100 text-green-700',
                    ][$message->status];
                @endphp
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <p class="font-medium">{{ $message->title }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $message->verse }}
                            @if ($message->publish_date)
                                &middot; {{ $message->publish_date->format('M j, Y') }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-1 rounded text-xs {{ $badge }}">{{ ucfirst($message->status) }}</span>
                        <a href="{{ route('admin.bible-messages.edit', $message) }}" class="text-sm text-blue-600 underline">Edit</a>
                        <form method="POST" action="{{ route('admin.bible-messages.destroy', $message) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 underline">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="p-4 text-gray-500">No Bible messages yet. Create one to get started.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $messages->links() }}
        </div>

        <a href="{{ route('admin.dashboard') }}" class="inline-block mt-6 text-blue-600 underline">Back to dashboard</a>
    </div>
</x-app-layout>