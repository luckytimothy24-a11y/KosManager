<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
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
            'notes' => $request->notes,
        ], $request->user()->id);

        if (! $result['ok']) {
            $message = match ($result['error']) {
                'conflict' => 'Kamar sudah dibooking pada periode tersebut.',
                'no_price' => 'Harga kamar tidak tersedia.',
                default => 'Kamar tidak tersedia untuk dibooking.',
            };

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['kamar_id' => [$message]],
            ], 422);
        }

        return response()->json([
            'data' => new BookingResource($result['booking']),
            'message' => 'Booking berhasil! Kamar telah dipesan untuk Anda.',
        ], 201);
    }

    public function cancel(Booking $booking, BookingService $bookingService): JsonResponse
    {
        $this->authorize('cancel', $booking);

        $cancelled = $bookingService->cancel($booking->id);

        if (! $cancelled) {
            return response()->json([
                'message' => 'Booking sudah diproses sebelumnya.',
            ], 422);
        }

        $booking->refresh()->load(['kos', 'kamar']);

        return response()->json([
            'data' => new BookingResource($booking),
            'message' => 'Booking berhasil dibatalkan.',
        ]);
    }
}
