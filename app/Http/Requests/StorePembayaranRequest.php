<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isTenant();
    }

    public function rules(): array
    {
        $rules = [
            'tagihan_id' => ['required', 'exists:tagihans,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:transfer_bank,cash,e_wallet'],
        ];

        if ($this->payment_method !== 'cash') {
            $rules['proof_file'] = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
        }

        return $rules;
    }
}
