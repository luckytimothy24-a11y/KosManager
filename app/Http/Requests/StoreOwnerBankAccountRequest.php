<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Simpan rekening bank Owner.
 * Hanya owner yang login — owner_id SELALU dari auth user, bukan request.
 */
class StoreOwnerBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() ?? false;
    }

    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'min:2', 'max:50'],
            'account_number' => ['required', 'string', 'min:5', 'max:30', 'regex:/^[0-9]+$/'],
            'account_holder_name' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }

    protected function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $num = (string) $this->input('account_number');
            if ($num !== '' && ! preg_match('/^[0-9]+$/', $num)) {
                $validator->errors()->add('account_number', 'Nomor rekening hanya boleh berisi angka.');
            }
        });
    }
}
