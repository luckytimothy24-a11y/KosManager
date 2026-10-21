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
        // Identitas sudah tersimpan di booking setelah tenant submit + owner confirm.
        // Check-in process hanya membutuhkan notes tambahan (opsional).
        return [
            'identity_number' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
