<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Submit pembayaran booking (prerequisite konfirmasi).
 * amount TIDAK dipercaya — server selalu memakai booking.price.
 */
class StoreBookingPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTenant() ?? false;
    }

    protected function prepareForValidation(): void
    {
        // Status tidak boleh dikontrol dari client.
        foreach (['status', 'payment_status', 'verification_status', 'tenant_status', 'amount', 'price', 'total'] as $key) {
            $this->request->remove($key);
        }
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'in:cash,transfer_bank'],
            'proof_file' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
                'required_if:payment_method,transfer_bank',
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
