<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\OwnerKamarResource;
use App\Http\Resources\OwnerKosResource;
use App\Models\Kos;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OwnerKosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:owner');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Kos::class);

        $perPage = min((int) $request->input('per_page', 15), 50);

        $kos = Kos::query()
            ->withCount('kamar', 'penghunis')
            ->where('owner_id', $request->user()->id)
            ->latest()
            ->paginate($perPage);

        return OwnerKosResource::collection($kos);
    }

    public function show(Kos $kos): OwnerKosResource
    {
        $this->authorize('view', $kos);

        $kos->loadCount('kamar', 'penghunis', 'bookings');
        $kos->load('fasilitas');

        return new OwnerKosResource($kos);
    }

    public function kamar(Kos $kos, Request $request): AnonymousResourceCollection
    {
        $this->authorize('view', $kos);

        $perPage = min((int) $request->input('per_page', 15), 50);

        $kamar = $kos->kamar()
            ->orderBy('room_number')
            ->paginate($perPage);

        return OwnerKamarResource::collection($kamar);
    }
}
