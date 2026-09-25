<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounselingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'assigned_leader_id',
        'full_name',
        'phone_number',
        'email',
        'academic_year',
        'department',
        'university',
        'location',
        'description',
        'counseling_type',
        'preferred_contact_method',
        'availability',
        'status',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assignedLeader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_leader_id');
    }
}