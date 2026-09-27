<?php

namespace App\Http\Requests;

use App\Models\LoveSharingMessage;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoveSharingMessage extends FormRequest
{
    public function authorize(): bool
    {
        $loveSharingRequest = $this->route('loveSharingRequest');

        return $this->user()->can('create', [LoveSharingMessage::class, $loveSharingRequest]);
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