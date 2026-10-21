<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModerationActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }
}
