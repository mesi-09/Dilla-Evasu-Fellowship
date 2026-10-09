<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Edit Bible Message</h1>

        @include('admin.bible-messages._form', [
            'action' => route('admin.bible-messages.update', $bibleMessage),
            'method' => 'PUT',
            'bibleMessage' => $bibleMessage,
        ])
    </div>
</x-app-layout>