<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Tenant tidak lagi memiliki field NIK di profil. Jika client lama masih
     * mengirim identity_number, nilainya diabaikan (tidak ditolak).
     */
    protected function prepareForValidation(): void
    {
        if ($this->user()?->isTenant()) {
            $this->request->remove('identity_number');
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'identity_number' => ['nullable', 'string', 'max:50'],
            'role' => ['prohibited'],
            'is_active' => ['prohibited'],
        ];
    }
}
