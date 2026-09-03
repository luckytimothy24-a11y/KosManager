<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\PaymentGateway\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Menerima callback payment gateway untuk auto-verifikasi.
 *
 * Endpoint ini publik (tanpa auth/session) sehingga otentikasi dilakukan
 * lewat signature HMAC pada header X-Gateway-Signature.
 */
class PaymentGatewayWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->json()->all();
        $signature = $request->header('X-Gateway-Signature', '');

        try {
            $gateway = PaymentGatewayManager::driver();
        } catch (\InvalidArgumentException $e) {
            // Secret belum terkonfigurasi/hanya placeholder -> fail closed.
            return response()->json(['message' => 'Payment gateway tidak terkonfigurasi.'], 403);
        }

        if (! $gateway->verifySignature($payload, $signature)) {
            return response()->json(['message' => 'Signature tidak valid.'], 403);
        }

        $reference = $payload['gateway_reference'] ?? null;

        if (! $reference) {
            return response()->json(['message' => 'gateway_reference wajib diisi.'], 422);
        }

        $payment = Pembayaran::where('gateway_reference', $reference)
            ->where('gateway_provider', $gateway->id())
            ->first();

        if (! $payment) {
            return response()->json(['message' => 'Pembayaran tidak ditemukan.'], 404);
        }

        if (strtolower((string) ($payload['status'] ?? '')) !== 'success') {
            return response()->json(['message' => 'Status bukan success, diabaikan.'], 200);
        }

        if ($payment->verification_status !== Pembayaran::STATUS_PENDING) {
            return response()->json(['message' => 'Pembayaran sudah diproses sebelumnya.'], 200);
        }

        if (round((float) $payment->amount, 2) !== round((float) ($payload['amount'] ?? 0), 2)) {
            return response()->json(['message' => 'Nominal callback tidak sesuai.'], 422);
        }

        \DB::transaction(function () use ($payment) {
            $locked = Pembayaran::whereKey($payment->id)->lockForUpdate()->first();

            if (! $locked || $locked->verification_status !== Pembayaran::STATUS_PENDING) {
                return;
            }

            $locked->update([
                'verification_status' => Pembayaran::STATUS_APPROVED,
                'verified_at' => now(),
            ]);

            $tagihan = $locked->tagihan;
            if ($tagihan && $tagihan->status !== Tagihan::STATUS_PAID) {
                $tagihan->update(['status' => Tagihan::STATUS_PAID]);
            }
        });

        try {
            NotificationService::paymentVerified($payment->penghuni->user_id, $payment->tagihan->bill_number);
            AuditLogService::approve('Pembayaran', "Pembayaran {$payment->payment_number} terverifikasi otomatis (gateway {$payment->gateway_provider})", ['pembayaran_id' => $payment->id]);
        } catch (\Exception $e) {
            \Log::warning('Payment webhook verification notification/audit failed: '.$e->getMessage());
        }

        return response()->json(['message' => 'Pembayaran diverifikasi otomatis.'], 200);
    }
}
