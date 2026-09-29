<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoworkerApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reviewed_by',
        'full_name',
        'phone_number',
        'email',
        'academic_year',
        'department',
        'location',
        'reason_for_joining',
        'previous_experience',
        'area_of_interest',
        'additional_message',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}