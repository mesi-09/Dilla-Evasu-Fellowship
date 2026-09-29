<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoworkerApplication extends FormRequest
{
    public function authorize(): bool
    {
        // Anyone can apply — students and visitors alike, per the spec.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'academic_year' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'reason_for_joining' => ['required', 'string', 'max:5000'],
            'previous_experience' => ['nullable', 'string', 'max:5000'],
            'area_of_interest' => ['nullable', 'string', 'max:255'],
            'additional_message' => ['nullable', 'string', 'max:5000'],
        ];
    }
}