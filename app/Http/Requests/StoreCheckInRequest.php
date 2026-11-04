<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->isSuperAdmin() || $user->isOwner() || $user->isAdmin();
    }

    public function rules(): array
    {
        // Identitas check-in tenant baru memakai WhatsApp + alamat.
        // KTP tidak lagi dikumpulkan; data KTP lama tetap tersimpan di database.
        return [
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
