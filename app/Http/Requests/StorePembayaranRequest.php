<?php

namespace App\Http\Requests;

use App\Models\Tagihan;
use Illuminate\Foundation\Http\FormRequest;

class StorePembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isTenant();
    }

    public function rules(): array
    {
        return [
            'tagihan_id' => ['required', 'exists:tagihans,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:qris,transfer_bank'],
            'proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120', 'required_if:payment_method,transfer_bank'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->has('tagihan_id') || $validator->errors()->has('amount')) {
                return;
            }
            $tagihan = Tagihan::find($this->input('tagihan_id'));
            if (! $tagihan) {
                return;
            }
            $expected = round((float) $tagihan->total, 2);
            $given = round((float) $this->input('amount'), 2);
            if (abs($expected - $given) > 0.005) {
                $validator->errors()->add('amount', 'Nominal harus sesuai total tagihan (Rp '.number_format($expected, 0, ',', '.').').');
            }
        });
    }
}
