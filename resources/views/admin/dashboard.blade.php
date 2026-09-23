<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold">Main Admin Dashboard</h1>
        <p>Welcome, {{ auth()->user()->name }}. Only main admins can see this page.</p>
    </div>
</x-app-layout>
