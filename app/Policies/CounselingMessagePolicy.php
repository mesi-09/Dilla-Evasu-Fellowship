<?php

namespace App\Policies;

use App\Models\CounselingMessage;
use App\Models\CounselingRequest;
use App\Models\User;

class CounselingMessagePolicy
{
    /**
     * Main Admin and Love Sharing Leader must never pass any check on
     * this model, regardless of which method is called.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isMainAdmin() || $user->isLoveSharingLeader()) {
            return false;
        }

        return null;
    }

    /**
     * Can the user view/participate in the message thread for a given request?
     */
    public function viewThread(User $user, CounselingRequest $counselingRequest): bool
    {
        if ($user->isCounselingLeader()) {
            return true;
        }

        return $user->id === $counselingRequest->student_id;
    }

    public function view(User $user, CounselingMessage $counselingMessage): bool
    {
        return $this->viewThread($user, $counselingMessage->counselingRequest);
    }

    public function create(User $user, CounselingRequest $counselingRequest): bool
    {
        return $this->viewThread($user, $counselingRequest);
    }
}