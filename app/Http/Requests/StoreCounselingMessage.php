<?php

namespace App\Http\Requests;

use App\Models\CounselingMessage;
use Illuminate\Foundation\Http\FormRequest;

class StoreCounselingMessage extends FormRequest
{
    public function authorize(): bool
    {
        $counselingRequest = $this->route('counselingRequest');

        return $this->user()->can('create', [CounselingMessage::class, $counselingRequest]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
        ];
    }
}