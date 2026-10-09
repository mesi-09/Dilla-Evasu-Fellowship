<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BibleMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The routes are already behind role:main_admin. Checking here
        // as well means the rule still holds if a route is ever moved.
        return $this->user()?->isMainAdmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isScheduled = $this->input('status') === 'scheduled';

        return [
            'title' => ['required', 'string', 'max:255'],
            'verse' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'author' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'scheduled', 'published'])],
            'publish_date' => [
                'nullable',
                'date',
                // A scheduled message needs a date, and it can't be in the past:
                // the scheduler only publishes messages dated today, so a past
                // date would sit there and never go out.
                Rule::requiredIf($isScheduled),
                Rule::when($isScheduled, ['after_or_equal:today']),
            ],
        ];
    }
}