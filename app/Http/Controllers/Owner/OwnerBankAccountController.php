<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOwnerBankAccountRequest;
use App\Models\OwnerBankAccount;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

/**
 * Owner menyimpan/mengubah rekening bank penerima pembayaran booking.
 * owner_id SELALU dari auth user — tidak dari request.
 */
class OwnerBankAccountController extends Controller
{
    public function edit()
    {
        $this->authorize('updateAny', OwnerBankAccount::class);

        $account = OwnerBankAccount::where('owner_id', auth()->id())->first();

        return view('owner.bank-account.edit', compact('account'));
    }

    public function update(StoreOwnerBankAccountRequest $request)
    {
        $this->authorize('updateAny', OwnerBankAccount::class);

        $ownerId = (int) $request->user()->id;
        $data = $request->validated();

        // account_number tetap string (leading zero aman)
        $data['account_number'] = (string) $data['account_number'];

        $account = OwnerBankAccount::where('owner_id', $ownerId)->lockForUpdate()->first();

        if ($account) {
            $account->update($data);
        } else {
            $account = OwnerBankAccount::create([
                'owner_id' => $ownerId,
                'bank_name' => $data['bank_name'],
                'account_number' => $data['account_number'],
                'account_holder_name' => $data['account_holder_name'],
            ]);
        }

        try {
            AuditLogService::update(
                'Rekening Bank',
                'Rekening bank owner diperbarui',
                [
                    'owner_id' => $ownerId,
                    'bank_name' => $account->bank_name,
                    // Jangan log nomor rekening lengkap
                    'account_number_masked' => $account->maskedAccountNumber(),
                ]
            );
        } catch (\Exception $e) {
            \Log::warning('Bank account audit failed: '.$e->getMessage());
        }

        return redirect()
            ->route('owner.bank-account.edit')
            ->with('success', 'Rekening bank berhasil disimpan.');
    }
}
