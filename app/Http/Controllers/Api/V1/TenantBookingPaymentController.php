<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingPaymentRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\PembayaranResource;
use App\Models\Booking;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

/**
 * Tenant submit pembayaran booking via API.
 * Nominal SELALU dari booking.price (server). Status payment = pending.
 * Booking TIDAK berubah.
 */
class TenantBookingPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:tenant');
    }

    public function store(Booking $booking, StoreBookingPaymentRequest $request, PaymentService $paymentService): JsonResponse
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

            return response()->json([
                'message' => $result['message'] ?? 'Gagal mengirim pembayaran booking.',
            ], 422);
        }

        try {
            AuditLogService::create(
                'Pembayaran',
                "Tenant mengirim pembayaran booking {$booking->booking_code} via API",
                ['booking_id' => $booking->id, 'pembayaran_id' => $result['pembayaran']->id]
            );
            NotificationService::bookingPaymentSubmitted(
                $booking->kos->owner_id,
                $booking->user->name,
                $booking->booking_code
            );
        } catch (\Exception $e) {
            \Log::warning('API booking payment failed: '.$e->getMessage());
        }

        $booking->refresh()->load(['kos', 'kamar']);

        return response()->json([
            'data' => new BookingResource($booking),
            'payment' => new PembayaranResource($result['pembayaran']),
            'message' => 'Pembayaran dikirim. Menunggu verifikasi pemilik kos. Booking belum dikonfirmasi.',
        ], 201);
    }
}
