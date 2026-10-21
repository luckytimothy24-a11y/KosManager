<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\AuditLogService;
use App\Services\BookingService;
use App\Services\BookingStatusService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:tenant');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->input('per_page', 15), 50);

        $bookings = Booking::with(['kos', 'kamar'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($perPage);

        return BookingResource::collection($bookings);
    }

    public function show(Booking $booking): JsonResponse|BookingResource
    {
        $this->authorize('view', $booking);

        $booking->load(['user', 'kos', 'kamar']);

        return new BookingResource($booking);
    }

    public function store(StoreBookingRequest $request, BookingService $bookingService): JsonResponse
    {
        $result = $bookingService->create([
            'kamar_id' => $request->kamar_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'rental_type' => $request->rental_type,
            'billing_mode' => $request->input('billing_mode', 'personal'),
            'requested_occupants' => $request->input('requested_occupants', 1),
            'notes' => $request->notes,
        ], $request->user()->id);

        if (! $result['ok']) {
            $message = match ($result['error']) {
                'conflict' => 'Kamar sudah dibooking pada periode tersebut.',
                'overlap' => 'Kamar sudah dibooking pada periode tersebut.',
                'capacity_exceeded' => 'Jumlah penghuni melebihi kapasitas kamar.',
                'kamar_penuh' => 'Kamar sudah penuh. Kapasitas tidak mencukupi.',
                'already_occupant' => 'Anda sudah menjadi penghuni aktif di kamar ini.',
                'no_price' => 'Harga kamar tidak tersedia.',
                'billing_mode_not_allowed' => 'Kamar ini tidak mengizinkan shared billing.',
                'personal_multi_rejected' => 'Booking personal hanya dapat untuk 1 orang.',
                default => 'Kamar tidak tersedia untuk dibooking.',
            };

            $field = match ($result['error']) {
                'billing_mode_not_allowed' => 'billing_mode',
                'capacity_exceeded', 'personal_multi_rejected' => 'requested_occupants',
                default => 'kamar_id',
            };

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [$field => [$message]],
            ], 422);
        }

        return response()->json([
            'data' => new BookingResource($result['booking']),
            'message' => 'Booking berhasil! Kamar telah dipesan untuk Anda.',
        ], 201);
    }

    /**
     * Tenant request cancellation via API.
     * booking.status TIDAK berubah — hanya membuat cancellation request pending.
     */
    public function cancel(Booking $booking, Request $request, BookingStatusService $statusService): JsonResponse
    {
        $this->authorize('cancel', $booking);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $statusService->requestCancellation(
            $booking,
            $request->user(),
            $data['reason'] ?? null
        );

        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'message' => match ($result['error'] ?? '') {
                    'already_requested' => 'Pengajuan pembatalan sudah menunggu review pemilik kos.',
                    'not_cancellable' => 'Booking ini tidak dapat dibatalkan.',
                    'forbidden' => 'Anda tidak memiliki akses untuk membatalkan booking ini.',
                    default => 'Gagal mengajukan pembatalan booking.',
                },
            ], 422);
        }

        try {
            AuditLogService::create(
                'Booking',
                "Tenant mengajukan pembatalan booking {$booking->booking_code} via API",
                [
                    'booking_id' => $booking->id,
                    'cancellation_request_id' => $result['request']->id,
                ]
            );
            NotificationService::bookingCancellationRequested(
                $booking->kos->owner_id,
                $booking->user->name,
                $booking->booking_code
            );
        } catch (\Exception $e) {
            \Log::warning('API booking cancellation request failed: '.$e->getMessage());
        }

        $booking->refresh()->load(['kos', 'kamar']);

        return response()->json([
            'data' => new BookingResource($booking),
            'cancellation_request' => [
                'id' => $result['request']->id,
                'status' => $result['request']->status,
            ],
            'message' => 'Pengajuan pembatalan terkirim. Menunggu konfirmasi pemilik kos.',
        ]);
    }
}
