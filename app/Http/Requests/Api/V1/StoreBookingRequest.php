<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi pembuatan booking via API v1 (tenant self-service).
 *
 * Berbeda dengan web StoreBookingRequest: identitas (KTP/telepon/alamat)
 * TIDAK wajib saat booking — dikumpulkan pada tahap check-in identity.
 * Authorization: tenant saja (user_id diambil dari token, bukan request).
 */
class StoreBookingRequest extends FormRequest
{
    private ?int $rawOccupants = null;

    private ?string $rawBillingMode = null;

    public function authorize(): bool
    {
        return $this->user()?->isTenant() ?? false;
    }

    /**
     * Frontend hanya mengirim ACTION/data booking.
     * Status booking/tenant/payment TIDAK boleh dikontrol dari client.
     * Patungan/multi-occupant DIHAPUS — selalu 1 orang, personal.
     */
    protected function prepareForValidation(): void
    {
        foreach (['status', 'tenant_status', 'payment_status', 'check_in_status', 'check_in'] as $key) {
            $this->request->remove($key);
        }

        $this->rawOccupants = $this->input('requested_occupants') !== null
            ? (int) $this->input('requested_occupants')
            : null;
        $this->rawBillingMode = $this->input('billing_mode');

        $this->merge([
            'requested_occupants' => 1,
            'billing_mode' => 'personal',
        ]);
    }

    public function rules(): array
    {
        return [
            'kos_id' => ['required', Rule::exists('kos', 'id')->where('status', 'active')],
            'kamar_id' => [
                'required',
                Rule::exists('kamar', 'id')->where('kos_id', $this->input('kos_id')),
            ],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'rental_type' => ['required', 'in:daily,weekly,monthly'],
            'requested_occupants' => ['sometimes', 'nullable', 'integer', 'in:1'],
            'billing_mode' => ['sometimes', 'nullable', 'string', 'in:personal'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->rawOccupants !== null && $this->rawOccupants > 1) {
                $validator->errors()->add('requested_occupants', 'Satu kamar hanya untuk satu penghuni. Patungan tidak lagi didukung.');
            }
            if ($this->rawBillingMode !== null && $this->rawBillingMode !== 'personal') {
                $validator->errors()->add('billing_mode', 'Patungan tidak lagi didukung. Satu kamar untuk satu penghuni.');
            }
        });
    }
}
