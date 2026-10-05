<?php

namespace App\Policies;

use App\Models\CommunityComment;
use App\Models\User;

class CommunityCommentPolicy
{
    /**
     * The comment's author or Main Admin (moderation) can delete it.
     */
    public function delete(User $user, CommunityComment $communityComment): bool
    {
        if ($user->isMainAdmin()) {
            return true;
        }

        return $user->id === $communityComment->user_id;
    }
}