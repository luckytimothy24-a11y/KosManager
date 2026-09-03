<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Models\Favorite;
use App\Models\Kos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:tenant');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->input('per_page', 15), 50);

        $favorites = Favorite::query()
            ->where('user_id', $request->user()->id)
            ->with(['kos' => function ($q) {
                $q->withCount(['kamar as kamar_tersedia' => fn ($qr) => $qr->where('status', 'available')])
                    ->withMin(['kamar as price_min' => fn ($qr) => $qr->where('status', 'available')], 'monthly_price')
                    ->withMax(['kamar as price_max' => fn ($qr) => $qr->where('status', 'available')], 'monthly_price');
            }])
            ->latest()
            ->paginate($perPage);

        return FavoriteResource::collection($favorites);
    }

    public function toggle(Kos $kos): JsonResponse
    {
        $user = request()->user();

        $existing = Favorite::where('user_id', $user->id)
            ->where('kos_id', $kos->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json([
                'data' => [
                    'kos_id' => $kos->id,
                    'is_favorited' => false,
                ],
                'message' => 'Kos dihapus dari favorit.',
            ]);
        }

        Favorite::create([
            'user_id' => $user->id,
            'kos_id' => $kos->id,
        ]);

        return response()->json([
            'data' => [
                'kos_id' => $kos->id,
                'is_favorited' => true,
            ],
            'message' => 'Kos ditambahkan ke favorit.',
        ]);
    }
}
