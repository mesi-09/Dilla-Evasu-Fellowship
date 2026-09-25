<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isMainAdmin(): bool
    {
        return $this->role === 'main_admin';
    }

    public function isLoveSharingLeader(): bool
    {
        return $this->role === 'love_sharing_leader';
    }

    public function isCounselingLeader(): bool
    {
        return $this->role === 'counseling_leader';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function loveSharingRequests(): HasMany
    {
        return $this->hasMany(LoveSharingRequest::class, 'student_id');
    }

    public function assignedLoveSharingRequests(): HasMany
    {
        return $this->hasMany(LoveSharingRequest::class, 'assigned_leader_id');
    }

    public function counselingRequests(): HasMany
    {
        return $this->hasMany(CounselingRequest::class, 'student_id');
    }

    public function assignedCounselingRequests(): HasMany
    {
        return $this->hasMany(CounselingRequest::class, 'assigned_leader_id');
    }
}