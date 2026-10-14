<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDisbursementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user ? ($user->isOwner() || $user->isSuperAdmin()) : false;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'idempotency_key' => ['required', 'string', 'min:8', 'max:64'],
            'proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'proof_path' => ['nullable', 'string', 'max:500'],
            'transferred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->request->remove('status');
        $this->request->remove('verified_at');
        $this->request->remove('verified_by');
        $this->request->remove('payout_id');
    }
}
