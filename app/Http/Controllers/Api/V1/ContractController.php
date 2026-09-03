<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\KontrakResource;
use App\Http\Resources\TagihanResource;
use App\Models\Kontrak;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContractController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:tenant');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->input('per_page', 15), 50);

        $kontraks = Kontrak::with(['kos', 'kamar'])
            ->whereHas('penghuni', fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest()
            ->paginate($perPage);

        return KontrakResource::collection($kontraks);
    }

    public function show(Kontrak $kontrak): JsonResponse|KontrakResource
    {
        $this->authorize('view', $kontrak);

        $kontrak->load(['penghuni', 'kos', 'kamar']);

        return new KontrakResource($kontrak);
    }

    public function tagihan(Kontrak $kontrak): JsonResponse|AnonymousResourceCollection
    {
        $this->authorize('view', $kontrak);

        $kontrak->load(['penghuni', 'kos', 'kamar', 'tagihans.pembayarans']);

        return TagihanResource::collection($kontrak->tagihans);
    }
}
