<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $user = auth()->user();

        // Tenant, owner, admin, dan super_admin diizinkan lolos FormRequest;
        // otorisasi kepemilikan tetap ditegakkan di CheckOutController::requestCheckout
        // (tenant hanya miliknya sendiri, owner/admin hanya kos dalam scope).
        return $user->isTenant() || $user->isOwner() || $user->isAdmin() || $user->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'condition' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
