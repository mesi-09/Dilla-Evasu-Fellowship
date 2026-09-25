<?php

namespace App\Policies;

use App\Models\CounselingAppointment;
use App\Models\User;

class CounselingAppointmentPolicy
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

    public function viewAny(User $user): bool
    {
        return $user->isCounselingLeader() || $user->isMember();
    }

    public function view(User $user, CounselingAppointment $counselingAppointment): bool
    {
        if ($user->isCounselingLeader()) {
            return true;
        }

        return $user->id === $counselingAppointment->student_id;
    }

    public function create(User $user): bool
    {
        // Only the Counseling Leader schedules appointments.
        return $user->isCounselingLeader();
    }

    public function update(User $user, CounselingAppointment $counselingAppointment): bool
    {
        return $user->isCounselingLeader();
    }

    public function delete(User $user, CounselingAppointment $counselingAppointment): bool
    {
        return $user->isCounselingLeader();
    }
}