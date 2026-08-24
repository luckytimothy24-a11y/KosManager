<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$this->route('user')->id],
            'role' => ['required', 'in:super_admin,admin,owner,tenant'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ];
    }
}
