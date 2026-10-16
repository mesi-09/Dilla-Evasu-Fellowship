<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public const ROLES = ['main_admin', 'love_sharing_leader', 'counseling_leader', 'member'];

    public function __construct(
        protected AuditLogService $audit
    ) {
    }

    public function updateRole(Request $request, User $user)
    {
        // An admin cannot change their own role. Otherwise an admin could hand
        // themselves a leader role and read that team's private requests, or
        // remove their own admin access by mistake.
        abort_if($request->user()->is($user), 403, 'You cannot change your own role.');

        $validated = $request->validate([
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        $oldRole = $user->role;

        if ($oldRole === $validated['role']) {
            return back()->with('status', 'No change: that is already their role.');
        }

        // Assigned directly: 'role' is deliberately not mass-assignable.
        $user->role = $validated['role'];
        $user->save();

        $this->audit->userRoleChanged($request->user(), $user, $oldRole);

        return back()->with('status', "Role updated for {$user->name}.");
    }

    public function toggleActive(Request $request, User $user)
    {
        // Because an admin can never deactivate themselves, the person doing
        // this is always an active admin, so the fellowship can never be left
        // with no active administrator.
        abort_if($request->user()->is($user), 403, 'You cannot deactivate your own account.');

        $user->is_active = ! $user->is_active;
        $user->save();

        $this->audit->userActiveChanged($request->user(), $user);

        return back()->with(
            'status',
            $user->is_active ? "{$user->name} was activated." : "{$user->name} was deactivated."
        );
    }
}