<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-1">Audit Log</h1>
        <p class="text-sm text-gray-500 mb-4">
            A record of administrative actions. Entries cannot be edited or deleted, and they never include
            the content of private requests. Times are shown in Addis Ababa time.
        </p>

        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="mb-4 flex gap-2">
            <select name="action" class="border rounded p-2">
                <option value="">All actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-black text-white px-4 py-2 rounded">Filter</button>
        </form>

        <div class="bg-white rounded shadow divide-y">
            @forelse ($logs as $log)
                <div class="p-4">
                    <p class="text-gray-900">{{ $log->description }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $log->actor_name }}
                        &middot;
                        {{ $log->created_at->timezone('Africa/Addis_Ababa')->format('M j, Y g:i A') }}
                    </p>
                </div>
            @empty
                <p class="p-4 text-gray-500">Nothing has been logged yet.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>

        <a href="{{ route('admin.dashboard') }}" class="inline-block mt-6 text-blue-600 underline">Back to dashboard</a>
    </div>
</x-app-layout>