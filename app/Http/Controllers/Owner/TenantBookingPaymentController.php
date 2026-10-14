<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingPaymentRequest;
use App\Models\Booking;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\PaymentService;

/**
 * Tenant submit pembayaran booking (prerequisite konfirmasi booking).
 * booking.status TIDAK berubah. payment.verification_status = pending.
 */
class TenantBookingPaymentController extends Controller
{
    public function store(Booking $booking, StoreBookingPaymentRequest $request, PaymentService $paymentService)
    {
        $this->authorize('view', $booking);

        $data = $request->validated();

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('bukti-pembayaran', 'private');
        }

        $result = $paymentService->createForBooking(
            $booking,
            $request->user(),
            $data['payment_method'],
            $proofPath,
            $data['notes'] ?? null
        );

        if (! ($result['ok'] ?? false)) {
            if ($proofPath) {
                \Storage::disk('private')->delete($proofPath);
            }

            return back()->with('error', $result['message'] ?? 'Gagal mengirim pembayaran booking.');
        }

        try {
            AuditLogService::create(
                'Pembayaran',
                "Tenant mengirim pembayaran booking {$booking->booking_code} (nominal Rp ".number_format((float) $booking->price, 0, ',', '.').')',
                [
                    'booking_id' => $booking->id,
                    'pembayaran_id' => $result['pembayaran']->id,
                    'verification_status' => $result['pembayaran']->verification_status,
                ]
            );

            NotificationService::bookingPaymentSubmitted(
                $booking->kos->owner_id,
                $booking->user->name,
                $booking->booking_code
            );
        } catch (\Exception $e) {
            \Log::warning('Booking payment notification/audit failed: '.$e->getMessage());
        }

        return redirect()
            ->route('tenant.booking.show', $booking)
            ->with('success', 'Pembayaran dikirim. Menunggu verifikasi pemilik kos. Booking belum dikonfirmasi.');
    }
}
