<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">
            Messages — Love Sharing Request #{{ $loveSharingRequest->id }}
        </h1>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded shadow p-6 space-y-4 mb-6">
            @forelse ($messages as $message)
                <div class="{{ $message->sender_id === auth()->id() ? 'text-right' : 'text-left' }}">
                    <div class="inline-block max-w-md px-4 py-2 rounded-lg {{ $message->sender_id === auth()->id() ? 'bg-black text-white' : 'bg-gray-100' }}">
                        <p class="text-xs opacity-70 mb-1">{{ $message->sender->name }}</p>
                        <p>{{ $message->body }}</p>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $message->created_at->format('M j, g:i A') }}</p>
                </div>
            @empty
                <p class="text-gray-500">No messages yet.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('love-sharing.messages.store', $loveSharingRequest) }}" class="space-y-3">
            @csrf
            <textarea name="body" rows="3" class="w-full border rounded p-2" placeholder="Write a message..." required>{{ old('body') }}</textarea>
            @error('body')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
            <button type="submit" class="bg-black text-white px-4 py-2 rounded">Send</button>
        </form>

        <a href="{{ route('love-sharing.show', $loveSharingRequest) }}" class="inline-block mt-6 text-blue-600 underline">Back to request</a>
    </div>
</x-app-layout>