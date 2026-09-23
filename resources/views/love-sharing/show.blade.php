<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Love Sharing Request #{{ $loveSharingRequest->id }}</h1>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded shadow p-6 space-y-3">
            <p><strong>Full Name:</strong> {{ $loveSharingRequest->full_name }}</p>
            <p><strong>Phone:</strong> {{ $loveSharingRequest->phone_number }}</p>
            <p><strong>Email:</strong> {{ $loveSharingRequest->email }}</p>
            <p><strong>Academic Year:</strong> {{ $loveSharingRequest->academic_year ?? '—' }}</p>
            <p><strong>Department:</strong> {{ $loveSharingRequest->department ?? '—' }}</p>
            <p><strong>University:</strong> {{ $loveSharingRequest->university ?? '—' }}</p>
            <p><strong>Location:</strong> {{ $loveSharingRequest->location ?? '—' }}</p>
            <p><strong>Description:</strong><br>{{ $loveSharingRequest->description }}</p>
            <p><strong>Preferred Contact:</strong> {{ $loveSharingRequest->preferred_contact_method ?? '—' }}</p>
            <p><strong>Additional Info:</strong> {{ $loveSharingRequest->additional_information ?? '—' }}</p>
            <p><strong>Status:</strong>
                <span class="px-2 py-1 rounded text-sm bg-gray-100">{{ ucfirst(str_replace('_', ' ', $loveSharingRequest->status)) }}</span>
            </p>
        </div>

        @can('update', $loveSharingRequest)
            <div class="mt-6 bg-white rounded shadow p-6">
                <h2 class="font-bold mb-2">Update Status</h2>
                <form method="POST" action="{{ route('love-sharing.update', $loveSharingRequest) }}">
                    @csrf
                    @method('PUT')
                    <select name="status" class="border rounded p-2">
                        @foreach (['pending', 'reviewed', 'accepted', 'in_progress', 'completed', 'rejected', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected($loveSharingRequest->status === $status)>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-black text-white px-4 py-2 rounded ml-2">Save</button>
                </form>
            </div>
        @endcan

        <a href="{{ route('love-sharing.index') }}" class="inline-block mt-6 text-blue-600 underline">Back to list</a>
    </div>
</x-app-layout>