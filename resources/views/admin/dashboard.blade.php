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

        <a href="{{ route('admin.members') }}" class="inline-block bg-black text-white px-4 py-2 rounded">
            View All Members
        </a>
    </div>
</x-app-layout>