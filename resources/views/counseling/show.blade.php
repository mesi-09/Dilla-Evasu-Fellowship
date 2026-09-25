<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Counseling Request #{{ $counselingRequest->id }}</h1>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded shadow p-6 space-y-3">
            <p><strong>Full Name:</strong> {{ $counselingRequest->full_name }}</p>
            <p><strong>Phone:</strong> {{ $counselingRequest->phone_number }}</p>
            <p><strong>Email:</strong> {{ $counselingRequest->email }}</p>
            <p><strong>Academic Year:</strong> {{ $counselingRequest->academic_year ?? '—' }}</p>
            <p><strong>Department:</strong> {{ $counselingRequest->department ?? '—' }}</p>
            <p><strong>University:</strong> {{ $counselingRequest->university ?? '—' }}</p>
            <p><strong>Location:</strong> {{ $counselingRequest->location ?? '—' }}</p>
            <p><strong>Description:</strong><br>{{ $counselingRequest->description }}</p>
            <p><strong>Counseling Type:</strong> {{ ucfirst(str_replace('_', ' ', $counselingRequest->counseling_type)) }}</p>
            <p><strong>Preferred Contact:</strong> {{ $counselingRequest->preferred_contact_method ?? '—' }}</p>
            <p><strong>Availability:</strong> {{ $counselingRequest->availability ?? '—' }}</p>
            <p><strong>Status:</strong>
                <span class="px-2 py-1 rounded text-sm bg-gray-100">{{ ucfirst(str_replace('_', ' ', $counselingRequest->status)) }}</span>
            </p>
        </div>

        @can('update', $counselingRequest)
            <div class="mt-6 bg-white rounded shadow p-6">
                <h2 class="font-bold mb-2">Update Status</h2>
                <form method="POST" action="{{ route('counseling.update', $counselingRequest) }}">
                    @csrf
                    @method('PUT')
                    <select name="status" class="border rounded p-2">
                        @foreach (['pending', 'reviewed', 'accepted', 'in_progress', 'completed', 'rejected', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected($counselingRequest->status === $status)>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-black text-white px-4 py-2 rounded ml-2">Save</button>
                </form>
            </div>
        @endcan

        <a href="{{ route('counseling.index') }}" class="inline-block mt-6 text-blue-600 underline">Back to list</a>
    </div>
</x-app-layout>