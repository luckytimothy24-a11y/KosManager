<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
    }

    public function rules(): array
    {
        return [
            'kontrak_id' => ['required', 'exists:kontraks,id'],
            'bill_type' => ['required', 'string', 'max:100'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after:period_start'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'penalty' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
        ];
    }
}
