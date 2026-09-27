<?php

namespace App\Policies;

use App\Models\LoveSharingMessage;
use App\Models\LoveSharingRequest;
use App\Models\User;

class LoveSharingMessagePolicy
{
    /**
     * Main Admin must never pass any check on this model.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isMainAdmin()) {
            return false;
        }

        return null;
    }

    /**
     * Can the user view/participate in the message thread for a given request?
     * Used to gate the thread view and to authorize posting a new message.
     */
    public function viewThread(User $user, LoveSharingRequest $loveSharingRequest): bool
    {
        if ($user->isLoveSharingLeader()) {
            return true;
        }

        return $user->id === $loveSharingRequest->student_id;
    }

    public function view(User $user, LoveSharingMessage $loveSharingMessage): bool
    {
        return $this->viewThread($user, $loveSharingMessage->loveSharingRequest);
    }

    public function create(User $user, LoveSharingRequest $loveSharingRequest): bool
    {
        return $this->viewThread($user, $loveSharingRequest);
    }
}