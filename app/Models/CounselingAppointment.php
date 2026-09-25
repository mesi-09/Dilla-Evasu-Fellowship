<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounselingAppointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'counseling_request_id',
        'student_id',
        'counselor_id',
        'appointment_date',
        'appointment_time',
        'appointment_type',
        'location_or_meeting_info',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
        ];
    }

    public function counselingRequest(): BelongsTo
    {
        return $this->belongsTo(CounselingRequest::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }
}