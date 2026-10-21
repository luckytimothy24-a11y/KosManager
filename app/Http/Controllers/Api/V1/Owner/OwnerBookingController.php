<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\OwnerBookingResource;
use App\Models\Booking;
use App\Models\BookingCancellationRequest;
use App\Services\AuditLogService;
use App\Services\BookingStatusService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OwnerBookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:owner');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->input('per_page', 15), 50);

        $bookings = Booking::with(['user', 'kos', 'kamar'])
            ->whereHas('kos', fn ($q) => $q->where('owner_id', $request->user()->id))
            ->latest()
            ->paginate($perPage);

        return OwnerBookingResource::collection($bookings);
    }

    public function show(Booking $booking): OwnerBookingResource
    {
        $this->authorize('view', $booking);

        $booking->load(['user', 'kos', 'kamar']);

        return new OwnerBookingResource($booking);
    }

    /**
     * Approve booking pending via API. Status final ditentukan backend.
     */
    public function approve(Booking $booking, BookingStatusService $statusService): JsonResponse
    {
        $this->authorize('approve', $booking);

        $result = $statusService->approve($booking, request()->user());

        if (! ($result['ok'] ?? false)) {
            $error = $result['error'] ?? 'invalid_transition';
            $message = match ($error) {
                'payment_required' => 'Booking belum dapat dikonfirmasi. Tenant harus memiliki pembayaran yang sudah diverifikasi.',
                default => $this->mapError($error),
            };

            return response()->json(['message' => $message], 422);
        }

        try {
            NotificationService::bookingApproved(
                $booking->user_id,
                $booking->booking_code,
                $booking->kamar->room_number,
                url('/tenant/booking/'.$booking->id)
            );
            AuditLogService::approve(
                'Booking',
                "Booking {$booking->booking_code} disetujui (API)",
                ['booking_id' => $booking->id]
            );
        } catch (\Exception $e) {
            \Log::warning('API booking approve notification failed: '.$e->getMessage());
        }

        $booking->refresh()->load(['user', 'kos', 'kamar']);

        return response()->json([
            'data' => new OwnerBookingResource($booking),
            'message' => 'Booking disetujui.',
        ]);
    }

    /**
     * Reject booking pending via API.
     */
    public function reject(Request $request, Booking $booking, BookingStatusService $statusService): JsonResponse
    {
        $this->authorize('reject', $booking);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $result = $statusService->reject($booking, $request->user(), trim($data['reason']));

        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'message' => $this->mapError($result['error'] ?? 'invalid_transition'),
            ], 422);
        }

        try {
            NotificationService::bookingRejected(
                $booking->user_id,
                $booking->booking_code,
                $booking->kamar->room_number,
                trim($data['reason']),
                url('/tenant/booking/'.$booking->id)
            );
            AuditLogService::reject(
                'Booking',
                "Booking {$booking->booking_code} ditolak (API)",
                ['booking_id' => $booking->id, 'reason' => trim($data['reason'])]
            );
        } catch (\Exception $e) {
            \Log::warning('API booking reject notification failed: '.$e->getMessage());
        }

        $booking->refresh()->load(['user', 'kos', 'kamar']);

        return response()->json([
            'data' => new OwnerBookingResource($booking),
            'message' => 'Booking ditolak.',
        ]);
    }

    /**
     * Owner approve cancellation request via API.
     */
    public function approveCancellation(BookingCancellationRequest $cancellationRequest, BookingStatusService $statusService): JsonResponse
    {
        $this->authorize('approve', $cancellationRequest);

        $result = $statusService->approveCancellation($cancellationRequest, request()->user());

        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'message' => $this->mapError($result['error'] ?? 'invalid_transition'),
            ], 422);
        }

        try {
            NotificationService::bookingCancellationApproved(
                $result['booking']->user_id,
                $result['booking']->booking_code,
                $result['booking']->kamar->room_number
            );
            AuditLogService::approve(
                'Booking',
                "Pembatalan booking {$result['booking']->booking_code} disetujui (API)",
                ['booking_id' => $result['booking']->id, 'cancellation_request_id' => $cancellationRequest->id]
            );
        } catch (\Exception $e) {
            \Log::warning('API cancellation approve failed: '.$e->getMessage());
        }

        return response()->json([
            'data' => new OwnerBookingResource($result['booking']),
            'cancellation_request' => [
                'id' => $result['request']->id,
                'status' => $result['request']->status,
            ],
            'message' => 'Pembatalan disetujui. Booking telah dibatalkan.',
        ]);
    }

    /**
     * Owner reject cancellation request via API — booking tidak berubah.
     */
    public function rejectCancellation(Request $request, BookingCancellationRequest $cancellationRequest, BookingStatusService $statusService): JsonResponse
    {
        $this->authorize('reject', $cancellationRequest);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $result = $statusService->rejectCancellation($cancellationRequest, $request->user(), trim($data['reason']));

        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'message' => $this->mapError($result['error'] ?? 'invalid_transition'),
            ], 422);
        }

        try {
            NotificationService::bookingCancellationRejected(
                $result['booking']->user_id,
                $result['booking']->booking_code,
                $result['booking']->kamar->room_number,
                trim($data['reason'])
            );
            AuditLogService::reject(
                'Booking',
                "Pengajuan pembatalan booking {$result['booking']->booking_code} ditolak (API)",
                ['booking_id' => $result['booking']->id, 'cancellation_request_id' => $cancellationRequest->id]
            );
        } catch (\Exception $e) {
            \Log::warning('API cancellation reject failed: '.$e->getMessage());
        }

        return response()->json([
            'data' => new OwnerBookingResource($result['booking']),
            'cancellation_request' => [
                'id' => $result['request']->id,
                'status' => $result['request']->status,
            ],
            'message' => 'Pengajuan pembatalan ditolak. Booking tetap berstatus sebelumnya.',
        ]);
    }

    private function mapError(string $error): string
    {
        return match ($error) {
            'not_found' => 'Booking tidak ditemukan.',
            'invalid_transition' => 'Status booking tidak memungkinkan aksi ini.',
            'request_not_pending' => 'Pengajuan pembatalan sudah diproses sebelumnya.',
            'forbidden' => 'Anda tidak memiliki akses untuk memproses booking ini.',
            default => 'Gagal memproses booking.',
        };
    }
}
