<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\KamarResource;
use App\Http\Resources\KosDetailResource;
use App\Http\Resources\KosResource;
use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KosController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Kos::query()
            ->where('status', 'active')
            ->withCount(['kamar as available_rooms' => fn ($q) => $q->where('status', 'available')])
            ->withMin(['kamar as price_min' => fn ($q) => $q->where('status', 'available')], 'monthly_price')
            ->withMax(['kamar as price_max' => fn ($q) => $q->where('status', 'available')], 'monthly_price')
            ->with(['fasilitas' => fn ($q) => $q->active()]);

        $perPage = min((int) $request->input('per_page', 15), 50);

        $kos = $query->orderByDesc('id')->paginate($perPage);

        return KosResource::collection($kos);
    }

    public function show(Kos $kos): JsonResponse|KosDetailResource
    {
        if ($kos->status !== 'active') {
            abort(404);
        }

        $kos->load('fasilitas');

        $kos->loadCount(['kamar as available_rooms' => fn ($q) => $q->where('status', 'available')]);
        $kos->loadMin(['kamar as price_min' => fn ($q) => $q->where('status', 'available')], 'monthly_price');
        $kos->loadMax(['kamar as price_max' => fn ($q) => $q->where('status', 'available')], 'monthly_price');

        return new KosDetailResource($kos);
    }

    public function kamar(Kos $kos, Request $request): AnonymousResourceCollection
    {
        if ($kos->status !== 'active') {
            abort(404);
        }

        $perPage = min((int) $request->input('per_page', 15), 50);

        $kamar = Kamar::query()
            ->where('kos_id', $kos->id)
            ->orderBy('room_number')
            ->paginate($perPage);

        return KamarResource::collection($kamar);
    }
}
