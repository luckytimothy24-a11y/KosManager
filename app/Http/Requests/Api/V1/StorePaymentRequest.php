<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    /**
     * Sama dengan StorePembayaranRequest existing, namun tagihan didefinisikan
     * lewat route binding ({tagihan}) sehingga tidak dibutuhkan field tagihan_id.
     */
    public function authorize(): bool
    {
        return $this->user()->isTenant();
    }

    public function rules(): array
    {
        $rules = [
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:transfer_bank,cash,e_wallet'],
        ];

        if ($this->input('payment_method') !== 'cash') {
            $rules['proof_file'] = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
        }

        return $rules;
    }
}
