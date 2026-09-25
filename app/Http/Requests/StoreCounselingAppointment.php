<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCounselingAppointment extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\CounselingAppointment::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'counseling_request_id' => ['required', 'exists:counseling_requests,id'],
            'student_id' => ['required', 'exists:users,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'appointment_type' => ['required', 'in:online,in_person'],
            'location_or_meeting_info' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}