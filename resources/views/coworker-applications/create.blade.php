<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Become a Co-worker — Dilla Evasu Fellowship</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Become a Co-worker</h1>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('coworker-applications.store') }}" class="space-y-4">
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
                <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded p-2" required>
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
                <label class="block font-medium">Location</label>
                <input type="text" name="location" value="{{ old('location') }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-medium">Reason for Joining</label>
                <textarea name="reason_for_joining" rows="4" class="w-full border rounded p-2" required>{{ old('reason_for_joining') }}</textarea>
            </div>

            <div>
                <label class="block font-medium">Previous Experience</label>
                <textarea name="previous_experience" rows="3" class="w-full border rounded p-2">{{ old('previous_experience') }}</textarea>
            </div>

            <div>
                <label class="block font-medium">Area of Interest</label>
                <input type="text" name="area_of_interest" value="{{ old('area_of_interest') }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-medium">Additional Message</label>
                <textarea name="additional_message" rows="3" class="w-full border rounded p-2">{{ old('additional_message') }}</textarea>
            </div>

            <button type="submit" class="bg-black text-white px-4 py-2 rounded">Submit Application</button>
        </form>
    </div>
</body>
</html>