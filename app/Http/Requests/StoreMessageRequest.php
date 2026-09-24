<?php

namespace App\Http\Requests;

use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isTenant() || $this->user()->isOwner();
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:2000'],
            'category' => ['sometimes', 'nullable', 'string', 'in:'.implode(',', array_keys(Message::CATEGORIES))],
        ];
    }
}
