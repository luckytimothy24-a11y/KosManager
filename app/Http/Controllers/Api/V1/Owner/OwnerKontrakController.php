<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\OwnerKontrakResource;
use App\Http\Resources\OwnerTagihanResource;
use App\Models\Kontrak;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OwnerKontrakController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:owner');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->input('per_page', 15), 50);

        $kontraks = Kontrak::with(['penghuni.user', 'kos', 'kamar'])
            ->whereHas('kos', fn ($q) => $q->where('owner_id', $request->user()->id))
            ->latest()
            ->paginate($perPage);

        return OwnerKontrakResource::collection($kontraks);
    }

    public function show(Kontrak $kontrak): OwnerKontrakResource
    {
        $this->authorize('view', $kontrak);

        $kontrak->load(['penghuni.user', 'kos', 'kamar']);

        return new OwnerKontrakResource($kontrak);
    }

    public function tagihan(Kontrak $kontrak, Request $request): AnonymousResourceCollection
    {
        $this->authorize('view', $kontrak);

        $perPage = min((int) $request->input('per_page', 15), 50);

        $tagihans = $kontrak->tagihans()
            ->with(['penghuni.user'])
            ->latest()
            ->paginate($perPage);

        return OwnerTagihanResource::collection($tagihans);
    }
}
