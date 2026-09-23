<?php

namespace App\Policies;

use App\Models\LoveSharingRequest;
use App\Models\User;

class LoveSharingRequestPolicy
{
    /**
     * Main Admin must never pass any check on this model, regardless
     * of which method is called. This runs before every other method.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isMainAdmin()) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isLoveSharingLeader() || $user->isMember();
    }

    public function view(User $user, LoveSharingRequest $loveSharingRequest): bool
    {
        if ($user->isLoveSharingLeader()) {
            return true;
        }

        return $user->id === $loveSharingRequest->student_id;
    }

    public function create(User $user): bool
    {
        return $user->isMember();
    }

    public function update(User $user, LoveSharingRequest $loveSharingRequest): bool
    {
        // Only the Love Sharing Leader can update status/assignment.
        return $user->isLoveSharingLeader();
    }

    public function delete(User $user, LoveSharingRequest $loveSharingRequest): bool
    {
        // A student may cancel their own pending request; leader can also remove.
        if ($user->isLoveSharingLeader()) {
            return true;
        }

        return $user->id === $loveSharingRequest->student_id
            && $loveSharingRequest->status === 'pending';
    }
}
