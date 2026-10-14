<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Owner mengonfirmasi transfer manual + upload bukti payout.
 *
 * status, amount, kos_id, owner_id, property_owner_id TIDAK diterima dari request.
 */
class TransferPayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user ? ($user->isOwner() || $user->isSuperAdmin()) : false;
    }

    public function rules(): array
    {
        return [
            'proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'transferred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->request->remove('status');
        $this->request->remove('amount');
        $this->request->remove('gross_amount');
        $this->request->remove('platform_fee');
        $this->request->remove('kos_id');
        $this->request->remove('owner_id');
        $this->request->remove('property_owner_id');
        $this->request->remove('payout_number');
    }
}
