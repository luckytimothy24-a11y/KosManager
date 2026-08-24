<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePembayaranRequest;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Services\AuditLogService;
use App\Services\NotificationService;

class TenantPembayaranController extends Controller
{
    public function store(StorePembayaranRequest $request)
    {
        $user = $request->user();
        $penghuni = Penghuni::where('user_id', $user->id)->where('status', 'active')->first();
        abort_unless($penghuni, 403);

        $tagihan = Tagihan::findOrFail($request->tagihan_id);
        abort_unless($tagihan->penghuni_id === $penghuni->id, 403);

        if (! in_array($tagihan->status, ['unpaid', 'overdue'])) {
            return back()->withErrors([
                'amount' => 'Tagihan ini tidak dapat dibayar (sudah dibayar atau menunggu verifikasi).',
            ]);
        }

        if ((float) $request->amount !== (float) $tagihan->total) {
            return back()->withErrors([
                'amount' => 'Nominal pembayaran harus sesuai total tagihan (Rp '.number_format((float) $tagihan->total, 0, ',', '.').').',
            ])->withInput();
        }

        $proofPath = $request->hasFile('proof_file')
            ? $request->file('proof_file')->store('bukti-pembayaran')
            : null;

        $savedTagihan = \DB::transaction(function () use ($request, $tagihan, $penghuni, $proofPath) {
            $locked = Tagihan::whereKey($tagihan->id)->lockForUpdate()->first();

            if (! $locked || ! in_array($locked->status, ['unpaid', 'overdue'])) {
                return null;
            }

            $hasActivePayment = Pembayaran::where('tagihan_id', $locked->id)
                ->whereIn('verification_status', ['pending', 'approved'])
                ->exists();

            if ($hasActivePayment) {
                return null;
            }

            Pembayaran::create([
                'payment_number' => 'PY-'.strtoupper(uniqid()),
                'tagihan_id' => $locked->id,
                'penghuni_id' => $penghuni->id,
                'amount' => $request->amount,
                'payment_date' => now(),
                'payment_method' => $request->payment_method,
                'proof_file' => $proofPath,
                'verification_status' => 'pending',
            ]);

            $locked->update(['status' => 'pending_verification']);

            return $locked;
        });

        if (! $savedTagihan) {
            if ($proofPath) {
                \Storage::delete($proofPath);
            }

            return back()->withErrors([
                'amount' => 'Tagihan ini tidak dapat dibayar (sudah dibayar atau menunggu verifikasi).',
            ]);
        }

        $tagihan = $savedTagihan;

        $kosOwner = $tagihan->kamar->kos->owner_id;
        NotificationService::paymentSubmitted($kosOwner, $user->name, $tagihan->bill_number);
        AuditLogService::create('Pembayaran', "Pembayaran untuk tagihan {$tagihan->bill_number} diupload oleh {$user->name}", ['tagihan_id' => $tagihan->id]);

        return redirect()->route('tenant.tagihan.show', $tagihan)->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi.');
    }
}
