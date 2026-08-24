<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin', 'admin', 'owner');
    }

    public function rules(): array
    {
        return [
            'identity_number' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
