<?php

namespace App\Policies;

use App\Models\CommunityPost;
use App\Models\User;

class CommunityPostPolicy
{
    /**
     * The post's author or Main Admin (moderation) can delete it.
     */
    public function delete(User $user, CommunityPost $communityPost): bool
    {
        if ($user->isMainAdmin()) {
            return true;
        }

        return $user->id === $communityPost->user_id;
    }
}