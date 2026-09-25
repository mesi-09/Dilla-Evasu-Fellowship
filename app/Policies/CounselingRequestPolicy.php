<?php

namespace App\Policies;

use App\Models\CounselingRequest;
use App\Models\User;

class CounselingRequestPolicy
{
    /**
     * Main Admin and Love Sharing Leader must never pass any check on
     * this model, regardless of which method is called. This runs
     * before every other method.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isMainAdmin() || $user->isLoveSharingLeader()) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isCounselingLeader() || $user->isMember();
    }

    public function view(User $user, CounselingRequest $counselingRequest): bool
    {
        if ($user->isCounselingLeader()) {
            return true;
        }

        return $user->id === $counselingRequest->student_id;
    }

    public function create(User $user): bool
    {
        return $user->isMember();
    }

    public function update(User $user, CounselingRequest $counselingRequest): bool
    {
        // Only the Counseling Leader can update status/assignment.
        return $user->isCounselingLeader();
    }

    public function delete(User $user, CounselingRequest $counselingRequest): bool
    {
        if ($user->isCounselingLeader()) {
            return true;
        }

        return $user->id === $counselingRequest->student_id
            && $counselingRequest->status === 'pending';
    }
}