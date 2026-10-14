<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectDisbursementRequest;
use App\Http\Requests\StoreDisbursementRequest;
use App\Models\Payout;
use App\Models\PayoutDisbursement;
use App\Services\PropertyOwnerPayoutService;

class PayoutDisbursementController extends Controller
{
    public function store(Payout $payout, StoreDisbursementRequest $request, PropertyOwnerPayoutService $service)
    {
        $this->authorize('update', $payout);
        $data = $request->validated();
        if ($request->hasFile('proof_file')) {
            $data['proof_file'] = $request->file('proof_file');
        }
        $result = $service->recordDisbursement($request->user(), $payout, $data);
        if (! ($result['ok'] ?? false)) {
            $error = match ($result['error'] ?? '') {
                'forbidden' => 'Anda tidak memiliki akses untuk payout ini.',
                'invalid_status' => 'Status payout tidak memungkinkan pencatatan disbursement.',
                'already_verified' => 'Payout sudah terverifikasi penuh.',
                'invalid_amount' => 'Nominal disbursement tidak valid.',
                'idempotency_key_required' => 'Idempotency key wajib diisi (min 8 karakter).',
                'idempotency_conflict' => 'Idempotency key sudah digunakan dengan nominal berbeda.',
                'overpayment' => 'Total pencairan melebihi kewajiban payout.',
                'proof_required' => 'Bukti transfer wajib diunggah.',
                default => 'Gagal mencatat disbursement.',
            };
            $status = match ($result['error'] ?? '') {
                'idempotency_conflict' => 409,
                'overpayment' => 422,
                'already_verified' => 409,
                default => 422,
            };
            if ($request->expectsJson()) {
                return response()->json(['message' => $error, 'error' => $result['error'] ?? 'failed'], $status);
            }
            return back()->with('error', $error)->withInput();
        }
        if (! empty($result['duplicate'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Disbursement sudah tercatat (idempotent).', 'disbursement' => $result['disbursement']], 200);
            }
            return redirect()->route('owner.payouts.show', $payout)->with('success', 'Disbursement sudah tercatat sebelumnya (idempotent).');
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Disbursement dicatat.', 'disbursement' => $result['disbursement']], 201);
        }
        return redirect()->route('owner.payouts.show', $payout)->with('success', 'Disbursement berhasil dicatat. Menunggu verifikasi Super Admin.');
    }

    public function verify(PayoutDisbursement $disbursement, PropertyOwnerPayoutService $service)
    {
        $this->authorize('verify', $disbursement->payout);
        $result = $service->verifyDisbursement(request()->user(), $disbursement);
        if (! ($result['ok'] ?? false)) {
            $error = match ($result['error'] ?? '') {
                'forbidden' => 'Hanya Super Admin yang dapat memverifikasi disbursement.',
                'invalid_status' => 'Disbursement harus berstatus pending.',
                default => 'Gagal memverifikasi disbursement.',
            };
            if (request()->expectsJson()) {
                return response()->json(['message' => $error], 403);
            }
            return back()->with('error', $error);
        }
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Disbursement diverifikasi.']);
        }
        return back()->with('success', 'Disbursement diverifikasi.');
    }

    public function reject(PayoutDisbursement $disbursement, RejectDisbursementRequest $request, PropertyOwnerPayoutService $service)
    {
        $this->authorize('reject', $disbursement->payout);
        $result = $service->rejectDisbursement($request->user(), $disbursement, $request->validated('reason'));
        if (! ($result['ok'] ?? false)) {
            $error = match ($result['error'] ?? '') {
                'forbidden' => 'Hanya Super Admin yang dapat menolak disbursement.',
                'invalid_status' => 'Disbursement harus berstatus pending.',
                default => 'Gagal menolak disbursement.',
            };
            if (request()->expectsJson()) {
                return response()->json(['message' => $error], 403);
            }
            return back()->with('error', $error);
        }
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Disbursement ditolak.']);
        }
        return back()->with('success', 'Disbursement ditolak.');
    }
}
