<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'kos_id' => ['required', 'integer', 'exists:kos,id'],
        ];
    }
}
