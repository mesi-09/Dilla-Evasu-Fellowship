<?php

namespace App\Policies;

use App\Models\CoworkerApplication;
use App\Models\User;

class CoworkerApplicationPolicy
{
    /**
     * Only Main Admin can view/manage applications. This is an
     * administrative matter, not a private pastoral request, so
     * unlike the Love Sharing/Counseling policies, Main Admin is
     * explicitly allowed here rather than blocked.
     */
    public function viewAny(User $user): bool
    {
        return $user->isMainAdmin();
    }

    public function view(User $user, CoworkerApplication $coworkerApplication): bool
    {
        return $user->isMainAdmin();
    }

    public function update(User $user, CoworkerApplication $coworkerApplication): bool
    {
        return $user->isMainAdmin();
    }
}