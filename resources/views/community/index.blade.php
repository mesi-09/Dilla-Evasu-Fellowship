<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Community Room</h1>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('community.store') }}" class="bg-white rounded shadow p-4 mb-6">
            @csrf
            <textarea
                name="body"
                rows="3"
                class="w-full border rounded p-2"
                placeholder="Share something with the fellowship..."
                required
            >{{ old('body') }}</textarea>
            @error('body')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
            <button type="submit" class="bg-black text-white px-4 py-2 rounded mt-2">Post</button>
        </form>

        <div class="space-y-4">
            @forelse ($posts as $post)
                <div class="bg-white rounded shadow p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium">{{ $post->user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $post->created_at->diffForHumans() }}</p>
                        </div>
                        @can('delete', $post)
                            <form method="POST" action="{{ route('community.destroy', $post) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 underline">Remove</button>
                            </form>
                        @endcan
                    </div>
                    <p class="mt-2">{{ $post->body }}</p>

                    <div class="flex items-center gap-3 mt-3">
                        <form method="POST" action="{{ route('community.reactions.toggle', $post) }}">
                            @csrf
                            @php
                                $hasReacted = $post->reactions->contains('user_id', auth()->id());
                            @endphp
                            <button type="submit" class="text-sm {{ $hasReacted ? 'text-blue-600 font-semibold' : 'text-gray-500' }}">
                                👍 {{ $post->reactions->count() }}
                            </button>
                        </form>
                        <span class="text-sm text-gray-400">{{ $post->comments->count() }} comments</span>
                    </div>

                    <div class="mt-3 space-y-2 border-t pt-3">
                        @foreach ($post->comments as $comment)
                            <div class="flex justify-between items-start text-sm">
                                <div>
                                    <span class="font-medium">{{ $comment->user->name }}:</span>
                                    {{ $comment->body }}
                                </div>
                                @can('delete', $comment)
                                    <form method="POST" action="{{ route('community.comments.destroy', $comment) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 underline ml-2">Remove</button>
                                    </form>
                                @endcan
                            </div>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('community.comments.store', $post) }}" class="mt-3 flex gap-2">
                        @csrf
                        <input
                            type="text"
                            name="body"
                            class="flex-1 border rounded p-2 text-sm"
                            placeholder="Write a comment..."
                            required
                        >
                        <button type="submit" class="bg-gray-200 text-sm px-3 py-2 rounded">Comment</button>
                    </form>
                </div>
            @empty
                <p class="text-gray-500">No posts yet — be the first to share something.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    </div>
</x-app-layout>