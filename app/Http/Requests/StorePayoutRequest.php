<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Owner/super_admin membuat payout untuk kos.
 *
 * Request HANYA membawa: kos_id, period_start, period_end.
 * property_owner_id, owner_id, gross_amount, platform_fee, amount, status
 * ditentukan SERVER-SIDE oleh PropertyOwnerPayoutService — jangan dari request.
 */
class StorePayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user ? ($user->isOwner() || $user->isSuperAdmin()) : false;
    }

    public function rules(): array
    {
        return [
            'kos_id' => ['required', 'integer', 'exists:kos,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Strip field yang tidak boleh dikontrol request.
        $this->merge([
            'kos_id' => $this->input('kos_id'),
        ]);

        $this->request->remove('property_owner_id');
        $this->request->remove('owner_id');
        $this->request->remove('gross_amount');
        $this->request->remove('platform_fee');
        $this->request->remove('amount');
        $this->request->remove('status');
        $this->request->remove('payout_number');
        $this->request->remove('proof_path');
        $this->request->remove('verified_by');
    }
}
