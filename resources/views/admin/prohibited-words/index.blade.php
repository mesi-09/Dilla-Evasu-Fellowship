<x-app-layout>
    <div class="p-6 max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Prohibited Words</h1>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.prohibited-words.store') }}" class="bg-white rounded shadow p-4 mb-6 flex gap-2">
            @csrf
            <input
                type="text"
                name="word"
                placeholder="Add a word..."
                class="border rounded p-2 flex-1"
                value="{{ old('word') }}"
                required
            >
            <select name="category" class="border rounded p-2">
                @foreach (['profanity', 'insults', 'harassment', 'sexual', 'threatening', 'spam'] as $category)
                    <option value="{{ $category }}" @selected(old('category') === $category)>
                        {{ ucfirst($category) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="bg-black text-white px-4 py-2 rounded">Add</button>
        </form>
        @error('word')
            <p class="text-red-600 text-sm mb-4">{{ $message }}</p>
        @enderror

        <div class="bg-white rounded shadow divide-y">
            @forelse ($words as $word)
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <p class="font-medium">{{ $word->word }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst($word->category) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 rounded text-xs {{ $word->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $word->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <form method="POST" action="{{ route('admin.prohibited-words.toggle', $word) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-sm text-blue-600 underline">
                                {{ $word->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.prohibited-words.destroy', $word) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 underline">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="p-4 text-gray-500">No prohibited words configured yet.</p>
            @endforelse
        </div>

        <a href="{{ route('admin.dashboard') }}" class="inline-block mt-6 text-blue-600 underline">Back to dashboard</a>
    </div>
</x-app-layout>