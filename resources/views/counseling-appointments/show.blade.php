<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Appointment #{{ $counselingAppointment->id }}</h1>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded shadow p-6 space-y-3">
            <p><strong>Date:</strong> {{ $counselingAppointment->appointment_date->format('M j, Y') }}</p>
            <p><strong>Time:</strong> {{ $counselingAppointment->appointment_time }}</p>
            <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $counselingAppointment->appointment_type)) }}</p>
            <p><strong>Location / Meeting Info:</strong> {{ $counselingAppointment->location_or_meeting_info ?? '—' }}</p>
            <p><strong>Notes:</strong> {{ $counselingAppointment->notes ?? '—' }}</p>
            <p><strong>Status:</strong>
                <span class="px-2 py-1 rounded text-sm bg-gray-100">{{ ucfirst($counselingAppointment->status) }}</span>
            </p>
        </div>

        @can('update', $counselingAppointment)
            <div class="mt-6 bg-white rounded shadow p-6">
                <h2 class="font-bold mb-2">Update Appointment</h2>
                <form method="POST" action="{{ route('counseling-appointments.update', $counselingAppointment) }}">
                    @csrf
                    @method('PUT')
                    <select name="status" class="border rounded p-2">
                        @foreach (['requested', 'scheduled', 'confirmed', 'completed', 'cancelled', 'rescheduled'] as $status)
                            <option value="{{ $status }}" @selected($counselingAppointment->status === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-black text-white px-4 py-2 rounded ml-2">Save</button>
                </form>
            </div>
        @endcan

        <a href="{{ route('counseling-appointments.index') }}" class="inline-block mt-6 text-blue-600 underline">Back to list</a>
    </div>
</x-app-layout>