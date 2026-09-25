<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Request Counseling</h1>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('counseling.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block font-medium">Full Name</label>
                <input type="text" name="full_name" value="{{ old('full_name') }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block font-medium">Phone Number</label>
                <input type="text" name="phone_number" value="{{ old('phone_number') }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block font-medium">Academic Year</label>
                <input type="text" name="academic_year" value="{{ old('academic_year') }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-medium">Department</label>
                <input type="text" name="department" value="{{ old('department') }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-medium">University</label>
                <input type="text" name="university" value="{{ old('university') }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-medium">Location</label>
                <input type="text" name="location" value="{{ old('location') }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-medium">Description</label>
                <textarea name="description" rows="4" class="w-full border rounded p-2" required>{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block font-medium">Counseling Type</label>
                <select name="counseling_type" class="w-full border rounded p-2" required>
                    <option value="">Select...</option>
                    <option value="online" @selected(old('counseling_type') === 'online')>Online</option>
                    <option value="in_person" @selected(old('counseling_type') === 'in_person')>In Person</option>
                </select>
            </div>

            <div>
                <label class="block font-medium">Preferred Contact Method</label>
                <input type="text" name="preferred_contact_method" value="{{ old('preferred_contact_method') }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-medium">Availability</label>
                <input type="text" name="availability" value="{{ old('availability') }}" class="w-full border rounded p-2" placeholder="e.g. Weekday afternoons">
            </div>

            <button type="submit" class="bg-black text-white px-4 py-2 rounded">Submit Request</button>
        </form>
    </div>
</x-app-layout>