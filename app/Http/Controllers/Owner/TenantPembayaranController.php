<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePembayaranRequest;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class TenantPembayaranController extends Controller
{
    /**
     * Buat pembayaran online via payment gateway (verifikasi otomatis).
     */
    public function gatewayStore(Request $request, PaymentService $paymentService)
    {
        $user = $request->user();
        $penghuni = Penghuni::where('user_id', $user->id)->where('status', 'active')->first();
        abort_unless($penghuni, 403);

        $tagihan = Tagihan::findOrFail($request->tagihan_id);
        abort_unless((int) $tagihan->penghuni_id === (int) $penghuni->id, 403);

        $result = $paymentService->createGateway([
            'penghuni' => $penghuni,
            'tagihan' => $tagihan,
        ]);

        if (! $result['ok']) {
            return back()->withErrors([
                'amount' => $result['message'],
            ]);
        }

        $tagihan = $result['tagihan'];
        $pembayaran = $result['pembayaran'];

        $kosOwner = $tagihan->kamar->kos->owner_id;
        NotificationService::paymentSubmitted($kosOwner, $user->name, $tagihan->bill_number);
        AuditLogService::create('Pembayaran', "Pembayaran online untuk tagihan {$tagihan->bill_number} dibuat oleh {$user->name}", ['tagihan_id' => $tagihan->id, 'gateway_reference' => $pembayaran->gateway_reference]);

        return redirect()->route('tenant.pembayaran.show', $pembayaran)->with('success', 'Pembayaran online berhasil dibuat. Selesaikan pembayaran Anda lalu sistem akan memverifikasi otomatis.');
    }

    public function store(StorePembayaranRequest $request, PaymentService $paymentService)
    {
        $user = $request->user();
        $penghuni = Penghuni::where('user_id', $user->id)->where('status', 'active')->first();
        abort_unless($penghuni, 403);

        $tagihan = $paymentService->resolveOwnTagihan((int) $request->tagihan_id, $user->id);
        abort_unless($tagihan, 403);

        if (round((float) $request->amount, 2) !== round((float) $tagihan->total, 2)) {
            return back()->withErrors([
                'amount' => 'Nominal pembayaran harus sesuai total tagihan (Rp '.number_format((float) $tagihan->total, 0, ',', '.').').',
            ])->withInput();
        }

        $result = $paymentService->createManual([
            'penghuni' => $penghuni,
            'tagihan' => $tagihan,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'proof_file' => $request->hasFile('proof_file')
                ? $request->file('proof_file')->store('bukti-pembayaran')
                : null,
        ]);

        if (! $result['ok']) {
            return back()->withErrors([
                'amount' => $result['message'],
            ]);
        }

        $tagihan = $result['tagihan'];

        $kosOwner = $tagihan->kamar->kos->owner_id;
        NotificationService::paymentSubmitted($kosOwner, $user->name, $tagihan->bill_number);
        AuditLogService::create('Pembayaran', "Pembayaran untuk tagihan {$tagihan->bill_number} diupload oleh {$user->name}", ['tagihan_id' => $tagihan->id]);

        return redirect()->route('tenant.tagihan.show', $tagihan)->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi.');
    }
}
