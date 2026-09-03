<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\PembayaranResource;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OwnerTagihanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:owner');
    }

    public function pembayaran(Tagihan $tagihan, Request $request): AnonymousResourceCollection
    {
        $this->authorize('view', $tagihan);

        $perPage = min((int) $request->input('per_page', 15), 50);

        $pembayarans = $tagihan->pembayarans()
            ->latest()
            ->paginate($perPage);

        return PembayaranResource::collection($pembayarans);
    }
}
