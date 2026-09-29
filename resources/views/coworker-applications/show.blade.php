<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Application from {{ $coworkerApplication->full_name }}</h1>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded shadow p-6 space-y-3">
            <p><strong>Full Name:</strong> {{ $coworkerApplication->full_name }}</p>
            <p><strong>Phone:</strong> {{ $coworkerApplication->phone_number }}</p>
            <p><strong>Email:</strong> {{ $coworkerApplication->email }}</p>
            <p><strong>Academic Year:</strong> {{ $coworkerApplication->academic_year ?? '—' }}</p>
            <p><strong>Department:</strong> {{ $coworkerApplication->department ?? '—' }}</p>
            <p><strong>Location:</strong> {{ $coworkerApplication->location ?? '—' }}</p>
            <p><strong>Reason for Joining:</strong><br>{{ $coworkerApplication->reason_for_joining }}</p>
            <p><strong>Previous Experience:</strong><br>{{ $coworkerApplication->previous_experience ?? '—' }}</p>
            <p><strong>Area of Interest:</strong> {{ $coworkerApplication->area_of_interest ?? '—' }}</p>
            <p><strong>Additional Message:</strong><br>{{ $coworkerApplication->additional_message ?? '—' }}</p>
            <p><strong>Applied by account:</strong> {{ $coworkerApplication->user?->name ?? 'Guest (not logged in)' }}</p>
            <p><strong>Status:</strong>
                <span class="px-2 py-1 rounded text-sm bg-gray-100">{{ ucfirst($coworkerApplication->status) }}</span>
            </p>
        </div>

        <div class="mt-6 bg-white rounded shadow p-6">
            <h2 class="font-bold mb-2">Update Status</h2>
            <form method="POST" action="{{ route('coworker-applications.update', $coworkerApplication) }}">
                @csrf
                @method('PUT')
                <select name="status" class="border rounded p-2">
                    @foreach (['pending', 'reviewed', 'accepted', 'rejected'] as $status)
                        <option value="{{ $status }}" @selected($coworkerApplication->status === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="bg-black text-white px-4 py-2 rounded ml-2">Save</button>
            </form>
        </div>

        <a href="{{ route('coworker-applications.index') }}" class="inline-block mt-6 text-blue-600 underline">Back to list</a>
    </div>
</x-app-layout>