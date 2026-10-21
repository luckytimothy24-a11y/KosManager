<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreManagementAgreementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->isOwner();
    }

    public function rules(): array
    {
        return [
            'kos_id' => ['required', 'exists:kos,id'],
            'commission_type' => ['required', 'in:percent,fixed'],
            'commission_value' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->commission_type === 'percent' && (float) $this->commission_value > 100) {
                $validator->errors()->add('commission_value', 'Komisi persen tidak boleh melebihi 100%.');
            }
        });
    }
}
