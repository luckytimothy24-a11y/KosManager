<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isTenant();
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
            'requested_occupants' => ['nullable', 'integer', 'min:1', 'max:10'],
            'billing_mode' => ['nullable', 'in:personal,shared'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'identity_number' => ['required', 'string', 'min:8', 'max:50'],
            'phone' => ['required', 'string', 'min:9', 'max:30'],
            'address' => ['required', 'string', 'min:5'],
        ];
    }
}
