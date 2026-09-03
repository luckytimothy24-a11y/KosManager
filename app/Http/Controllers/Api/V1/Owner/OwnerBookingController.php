<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\OwnerBookingResource;
use App\Models\Booking;
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
}
