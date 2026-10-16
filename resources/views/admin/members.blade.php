<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-1">All Members</h1>
        <p class="text-sm text-gray-500 mb-4">
            Change a member's role or deactivate their account here. Every change is recorded in the audit log.
            You cannot change your own role or deactivate yourself.
        </p>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 text-green-700 rounded">
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" action="{{ route('admin.members') }}" class="mb-4 flex gap-2">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search by name or email..."
                class="border rounded p-2 flex-1"
            >
            <select name="team" class="border rounded p-2">
                <option value="">All teams</option>
                @foreach ($teams as $team)
                    <option value="{{ $team }}" @selected(request('team') === $team)>
                        {{ ucfirst(str_replace('_', ' ', $team)) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="bg-black text-white px-4 py-2 rounded">Filter</button>
        </form>

        <div class="bg-white rounded shadow divide-y">
            @forelse ($members as $member)
                <div class="p-4 flex flex-wrap justify-between items-center gap-3">
                    <div>
                        <p class="font-medium">
                            {{ $member->name }}
                            @unless ($member->is_active)
                                <span class="ml-1 px-2 py-0.5 rounded text-xs bg-red-100 text-red-700">Inactive</span>
                            @endunless
                        </p>
                        <p class="text-sm text-gray-500">{{ $member->email }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ ucfirst(str_replace('_', ' ', $member->team)) }} team
                            &middot; Joined {{ $member->created_at->format('M j, Y') }}
                        </p>
                    </div>

                    <div>
                        @if ($member->is(auth()->user()))
                            <span class="px-2 py-1 rounded text-sm bg-gray-100">
                                {{ ucfirst(str_replace('_', ' ', $member->role)) }}
                            </span>
                            <span class="text-xs text-gray-400">(you)</span>
                        @else
                            <form method="POST" action="{{ route('admin.users.role', $member) }}" class="flex gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="role" class="border rounded p-1 text-sm">
                                    @foreach (\App\Http\Controllers\Admin\UserManagementController::ROLES as $role)
                                        <option value="{{ $role }}" @selected($member->role === $role)>
                                            {{ ucfirst(str_replace('_', ' ', $role)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="bg-black text-white px-3 py-1 rounded text-sm">Save</button>
                            </form>

                            <form method="POST" action="{{ route('admin.users.toggle-active', $member) }}" class="mt-2 text-right">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs underline {{ $member->is_active ? 'text-red-600' : 'text-blue-600' }}">
                                    {{ $member->is_active ? 'Deactivate account' : 'Activate account' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="p-4 text-gray-500">No members found.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $members->links() }}
        </div>

        <a href="{{ route('admin.dashboard') }}" class="inline-block mt-6 text-blue-600 underline">Back to dashboard</a>
    </div>
</x-app-layout>