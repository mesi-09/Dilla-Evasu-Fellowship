<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold">Counseling Requests</h1>

            @can('create', \App\Models\CounselingRequest::class)
                <a href="{{ route('counseling.create') }}" class="bg-black text-white px-4 py-2 rounded">
                    Request Counseling
                </a>
            @endcan
        </div>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">
                {{ session('status') }}
            </div>
        @endif

        @if ($requests->isEmpty())
            <p class="text-gray-500">No requests found.</p>
        @else
            <div class="bg-white rounded shadow divide-y">
                @foreach ($requests as $request)
                    <a href="{{ route('counseling.show', $request) }}" class="block p-4 hover:bg-gray-50">
                        <div class="flex justify-between">
                            <div>
                                <p class="font-medium">{{ $request->full_name }}</p>
                                <p class="text-sm text-gray-500">{{ Str::limit($request->description, 80) }}</p>
                            </div>
                            <span class="px-2 py-1 rounded text-sm bg-gray-100 h-fit">
                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</x-app-layout>