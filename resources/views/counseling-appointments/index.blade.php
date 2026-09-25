<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Counseling Appointments</h1>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">
                {{ session('status') }}
            </div>
        @endif

        @if ($appointments->isEmpty())
            <p class="text-gray-500">No appointments found.</p>
        @else
            <div class="bg-white rounded shadow divide-y">
                @foreach ($appointments as $appointment)
                    <a href="{{ route('counseling-appointments.show', $appointment) }}" class="block p-4 hover:bg-gray-50">
                        <div class="flex justify-between">
                            <div>
                                <p class="font-medium">{{ $appointment->appointment_date->format('M j, Y') }} at {{ $appointment->appointment_time }}</p>
                                <p class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</p>
                            </div>
                            <span class="px-2 py-1 rounded text-sm bg-gray-100 h-fit">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>
</x-app-layout>