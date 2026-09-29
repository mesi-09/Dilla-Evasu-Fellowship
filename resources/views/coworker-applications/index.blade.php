<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Co-worker Applications</h1>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">
                {{ session('status') }}
            </div>
        @endif

        @if ($applications->isEmpty())
            <p class="text-gray-500">No applications found.</p>
        @else
            <div class="bg-white rounded shadow divide-y">
                @foreach ($applications as $application)
                    <a href="{{ route('coworker-applications.show', $application) }}" class="block p-4 hover:bg-gray-50">
                        <div class="flex justify-between">
                            <div>
                                <p class="font-medium">{{ $application->full_name }}</p>
                                <p class="text-sm text-gray-500">{{ $application->email }} — {{ $application->area_of_interest ?? 'No area specified' }}</p>
                            </div>
                            <span class="px-2 py-1 rounded text-sm bg-gray-100 h-fit">
                                {{ ucfirst($application->status) }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $applications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>