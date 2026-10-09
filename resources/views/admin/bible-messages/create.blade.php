<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">New Bible Message</h1>

        @include('admin.bible-messages._form', [
            'action' => route('admin.bible-messages.store'),
            'method' => 'POST',
            'bibleMessage' => null,
        ])
    </div>
</x-app-layout>