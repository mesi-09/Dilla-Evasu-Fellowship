<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">All Members</h1>

        <form method="GET" action="{{ route('admin.members') }}" class="mb-4">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search by name or email..."
                class="border rounded p-2 w-full max-w-md"
            >
        </form>

        <div class="bg-white rounded shadow divide-y">
            @forelse ($members as $member)
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <p class="font-medium">{{ $member->name }}</p>
                        <p class="text-sm text-gray-500">{{ $member->email }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 rounded text-sm bg-gray-100">
                            {{ ucfirst(str_replace('_', ' ', $member->role)) }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1">Joined {{ $member->created_at->format('M j, Y') }}</p>
                    </div>
                </div>
            @empty
                <p class="p-4 text-gray-500">No members found.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $members->links() }}
        </div>

        <a href="{{ route('admin.dashboard') }}" class="inline-block mt-6 text-blue-600 underline">Back to dashboard</a>
    </div>
</x-app-layout>