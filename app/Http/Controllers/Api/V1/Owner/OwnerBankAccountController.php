<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOwnerBankAccountRequest;
use App\Models\OwnerBankAccount;
use Illuminate\Http\JsonResponse;

/**
 * API rekening bank Owner. owner_id dari auth token — bukan request.
 */
class OwnerBankAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:owner');
    }

    public function show(): JsonResponse
    {
        $account = OwnerBankAccount::where('owner_id', auth()->id())->first();

        if (! $account) {
            return response()->json(['data' => null, 'message' => 'Belum ada rekening bank tersimpan.']);
        }

        return response()->json([
            'data' => [
                'id' => $account->id,
                'bank_name' => $account->bank_name,
                // Nomor lengkap hanya untuk owner pemilik di context ini; UI lain pakai mask.
                'account_number' => $account->account_number,
                'account_number_masked' => $account->maskedAccountNumber(),
                'account_holder_name' => $account->account_holder_name,
            ],
        ]);
    }

    public function update(StoreOwnerBankAccountRequest $request): JsonResponse
    {
        $ownerId = (int) $request->user()->id;
        $data = $request->validated();
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

        return response()->json([
            'data' => [
                'id' => $account->id,
                'bank_name' => $account->bank_name,
                'account_number' => $account->account_number,
                'account_number_masked' => $account->maskedAccountNumber(),
                'account_holder_name' => $account->account_holder_name,
            ],
            'message' => 'Rekening bank berhasil disimpan.',
        ]);
    }
}
