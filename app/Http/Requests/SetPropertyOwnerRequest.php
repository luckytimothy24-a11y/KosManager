<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Owner/super_admin menentukan Pemilik Kos Asli + rekening tujuan
 * untuk kos tertentu (dalam satu transaksi di service).
 *
 * owner_id / property_owner_id tidak diterima dari request —
 * property owner dibuat baru dan di-link ke kos yang sedang dikelola.
 */
class SetPropertyOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user ? ($user->isOwner() || $user->isSuperAdmin()) : false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
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

    protected function prepareForValidation(): void
    {
        $this->request->remove('owner_id');
        $this->request->remove('property_owner_id');
    }
}
