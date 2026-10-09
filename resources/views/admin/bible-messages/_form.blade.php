@if ($errors->any())
    <div class="mb-4 p-4 bg-red-50 text-red-700 rounded">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label class="block font-medium">Title</label>
        <input type="text" name="title" value="{{ old('title', $bibleMessage?->title) }}" class="w-full border rounded p-2" required>
    </div>

    <div>
        <label class="block font-medium">Bible verse</label>
        <input type="text" name="verse" value="{{ old('verse', $bibleMessage?->verse) }}" class="w-full border rounded p-2" placeholder="e.g. John 3:16" required>
    </div>

    <div>
        <label class="block font-medium">Message</label>
        <textarea name="message" rows="6" class="w-full border rounded p-2" required>{{ old('message', $bibleMessage?->message) }}</textarea>
    </div>

    <div>
        <label class="block font-medium">Author</label>
        <input type="text" name="author" value="{{ old('author', $bibleMessage?->author) }}" class="w-full border rounded p-2">
    </div>

    <div>
        <label class="block font-medium">Status</label>
        <select name="status" class="w-full border rounded p-2" required>
            @foreach (['draft' => 'Draft', 'scheduled' => 'Scheduled', 'published' => 'Published'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $bibleMessage?->status ?? 'draft') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">
            Draft: not visible. Scheduled: goes out automatically at 6:00 AM on its date. Published: visible now.
        </p>
    </div>

    <div>
        <label class="block font-medium">Publish date</label>
        <input type="date" name="publish_date" value="{{ old('publish_date', $bibleMessage?->publish_date?->format('Y-m-d')) }}" class="w-full border rounded p-2">
        <p class="text-xs text-gray-500 mt-1">Required when the status is Scheduled.</p>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="bg-black text-white px-4 py-2 rounded">Save</button>
        <a href="{{ route('admin.bible-messages.index') }}" class="px-4 py-2 rounded bg-gray-200">Cancel</a>
    </div>
</form>