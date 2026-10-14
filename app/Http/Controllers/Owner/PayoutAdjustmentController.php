<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdjustmentRequest;
use App\Services\PropertyOwnerPayoutService;

class PayoutAdjustmentController extends Controller
{
    public function store(StoreAdjustmentRequest $request, PropertyOwnerPayoutService $service)
    {
        $result = $service->createAdjustment($request->user(), $request->validated());
        if (! ($result['ok'] ?? false)) {
            $error = match ($result['error'] ?? '') {
                'forbidden' => 'Anda tidak memiliki akses untuk adjustment ini.',
                'pembayaran_not_found' => 'Pembayaran tidak ditemukan.',
                'invalid_amount' => 'Nominal adjustment tidak valid.',
                'reason_required' => 'Alasan wajib diisi (min 5 karakter).',
                'kos_mismatch' => 'Payout dan pembayaran harus berada pada kos yang sama.',
                'refund_exceeds_gross' => 'Total refund melebihi nilai sewa yang dapat dikembalikan.',
                'commission_delta_exceeds_amount' => 'Koreksi komisi tidak boleh melebihi nominal adjustment.',
                default => 'Gagal membuat adjustment.',
            };
            $status = match ($result['error'] ?? '') {
                'forbidden' => 403,
                'refund_exceeds_gross' => 422,
                'kos_mismatch' => 422,
                'commission_delta_exceeds_amount' => 422,
                default => 422,
            };
            if ($request->expectsJson()) {
                return response()->json(['message' => $error, 'error' => $result['error'] ?? 'failed'], $status);
            }

            return back()->with('error', $error)->withInput();
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Adjustment dicatat.', 'adjustment' => $result['adjustment']], 201);
        }

        return back()->with('success', 'Adjustment berhasil dicatat.');
    }
}
