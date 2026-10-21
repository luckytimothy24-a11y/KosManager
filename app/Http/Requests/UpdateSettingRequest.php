<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ];
    }
}
