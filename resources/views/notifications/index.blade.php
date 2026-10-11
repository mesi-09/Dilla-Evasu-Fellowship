<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold">Notifications</h1>

            @if (auth()->user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-sm text-blue-600 underline">Mark all as read</button>
                </form>
            @endif
        </div>

        <div class="bg-white rounded shadow divide-y">
            @forelse ($notifications as $notification)
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                    @csrf
                    <button type="submit" class="w-full text-left p-4 hover:bg-gray-50 flex justify-between items-start gap-4">
                        <span class="{{ $notification->read_at ? 'text-gray-500' : 'font-semibold text-gray-900' }}">
                            {{ $notification->data['message'] ?? 'Notification' }}
                        </span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </button>
                </form>
            @empty
                <p class="p-4 text-gray-500">No notifications yet.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>