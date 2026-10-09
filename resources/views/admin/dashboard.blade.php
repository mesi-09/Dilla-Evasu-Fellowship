<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-1">Main Admin Dashboard</h1>
        <p class="text-gray-600 mb-6">Welcome, {{ auth()->user()->name }}.</p>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Total Members</p>
                <p class="text-3xl font-bold">{{ $stats['total_members'] }}</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Active Members</p>
                <p class="text-3xl font-bold">{{ $stats['active_members'] }}</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Love Sharing Leaders</p>
                <p class="text-3xl font-bold">{{ $stats['by_role']['love_sharing_leader'] }}</p>
            </div>
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Counseling Leaders</p>
                <p class="text-3xl font-bold">{{ $stats['by_role']['counseling_leader'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded shadow p-4 mb-6">
            <h2 class="font-bold mb-3">Members by Team</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach ($stats['by_team'] as $team => $count)
                    <div class="flex justify-between border-b pb-1">
                        <span class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $team)) }}</span>
                        <span class="font-semibold">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.members') }}" class="inline-block bg-black text-white px-4 py-2 rounded">
                View All Members
            </a>
            <a href="{{ route('admin.bible-messages.index') }}" class="inline-block bg-gray-200 px-4 py-2 rounded">
                Bible Messages
            </a>
            <a href="{{ route('admin.prohibited-words.index') }}" class="inline-block bg-gray-200 px-4 py-2 rounded">
                Manage Prohibited Words
            </a>
        </div>
    </div>
</x-app-layout>