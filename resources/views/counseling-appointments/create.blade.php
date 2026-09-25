<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Schedule Appointment</h1>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('counseling-appointments.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block font-medium">Counseling Request ID</label>
                <input type="number" name="counseling_request_id" value="{{ old('counseling_request_id', $counselingRequestId) }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block font-medium">Student ID</label>
                <input type="number" name="student_id" value="{{ old('student_id') }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block font-medium">Date</label>
                <input type="date" name="appointment_date" value="{{ old('appointment_date') }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block font-medium">Time</label>
                <input type="time" name="appointment_time" value="{{ old('appointment_time') }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block font-medium">Type</label>
                <select name="appointment_type" class="w-full border rounded p-2" required>
                    <option value="online" @selected(old('appointment_type') === 'online')>Online</option>
                    <option value="in_person" @selected(old('appointment_type') === 'in_person')>In Person</option>
                </select>
            </div>

            <div>
                <label class="block font-medium">Location / Meeting Info</label>
                <input type="text" name="location_or_meeting_info" value="{{ old('location_or_meeting_info') }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-medium">Notes</label>
                <textarea name="notes" rows="3" class="w-full border rounded p-2">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="bg-black text-white px-4 py-2 rounded">Schedule Appointment</button>
        </form>
    </div>
</x-app-layout>