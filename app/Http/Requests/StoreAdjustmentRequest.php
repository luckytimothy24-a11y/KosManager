<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user ? ($user->isOwner() || $user->isSuperAdmin()) : false;
    }

    public function rules(): array
    {
        return [
            'pembayaran_id' => ['required', 'integer', 'exists:pembayarans,id'],
            'payout_id' => ['nullable', 'integer', 'exists:payouts,id'],
            'type' => ['required', 'in:refund,correction'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
            'commission_delta' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->request->remove('created_by');
        if (! $this->user()?->isSuperAdmin()) {
            $this->request->remove('commission_delta');
        }
    }
}
