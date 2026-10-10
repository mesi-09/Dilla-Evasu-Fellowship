@php
    // Love Sharing and Counseling requests are made by members. Leaders and the
    // Main Admin have their own dashboards, and the request pages would refuse them.
    $canRequest = ! auth()->check() || auth()->user()->isMember();
@endphp

<x-public-layout title="Dilla Evasu Fellowship">
    <section class="bg-white border-b border-stone-200">
        <div class="max-w-3xl mx-auto px-4 py-16 sm:py-24 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-stone-900">Dilla Evasu Fellowship</h1>
            <p class="mt-4 text-lg sm:text-xl text-stone-600">Love, Faith, Fellowship and Support</p>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                @if ($canRequest)
                    <a href="{{ route('love-sharing.create') }}"
                       class="rounded-full bg-stone-800 text-white px-6 py-3 hover:bg-stone-700">Request Love Sharing</a>
                    <a href="{{ route('counseling.create') }}"
                       class="rounded-full bg-stone-800 text-white px-6 py-3 hover:bg-stone-700">Request Counseling</a>
                @endif
                <a href="{{ route('community.index') }}"
                   class="rounded-full border border-stone-300 bg-white px-6 py-3 hover:bg-stone-100">Join the Community</a>
                <a href="{{ route('coworker-applications.create') }}"
                   class="rounded-full border border-stone-300 bg-white px-6 py-3 hover:bg-stone-100">Become a Co-worker</a>
            </div>
        </div>
    </section>

    @if ($latestMessage)
        <section class="max-w-3xl mx-auto px-4 pt-12">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Latest Bible Message</h2>

            <a href="{{ route('bible-messages.show', $latestMessage) }}"
               class="mt-3 block bg-white rounded-2xl border border-stone-200 p-6 hover:shadow-sm">
                <p class="text-xl font-semibold text-stone-900">{{ $latestMessage->title }}</p>
                <p class="mt-1 italic text-stone-500">{{ $latestMessage->verse }}</p>
                <p class="mt-3 text-stone-700">{{ \Illuminate\Support\Str::limit($latestMessage->message, 220) }}</p>
            </a>

            <a href="{{ route('bible-messages.index') }}" class="inline-block mt-3 text-sm text-stone-600 underline">
                See all messages
            </a>
        </section>
    @endif

    <section class="max-w-5xl mx-auto px-4 pt-12">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h3 class="font-semibold text-stone-900">Love Sharing</h3>
                <p class="mt-2 text-sm text-stone-600">
                    Someone to walk with you. Share what is on your heart with our Love Sharing team.
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h3 class="font-semibold text-stone-900">Counseling</h3>
                <p class="mt-2 text-sm text-stone-600">
                    Talk with a counselor, online or in person, at a time that suits you.
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h3 class="font-semibold text-stone-900">Community</h3>
                <p class="mt-2 text-sm text-stone-600">
                    Ask questions, encourage one another, and discuss the Bible together.
                </p>
            </div>
        </div>

        <p class="mt-4 text-center text-sm text-stone-500">
            Your Love Sharing and Counseling requests are private. They are seen only by the team that serves you.
        </p>
    </section>
</x-public-layout>