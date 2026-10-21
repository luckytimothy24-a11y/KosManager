<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Controller;
use App\Models\PaymentDistribution;
use App\Services\PaymentDistributionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerPaymentDistributionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:owner');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 15), 50);

        $query = PaymentDistribution::with(['booking.kos', 'booking.user', 'pembayaran', 'bankAccount'])
            ->where('owner_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $distributions = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $distributions->getCollection()->map(fn ($d) => [
                'id' => $d->id,
                'booking_id' => $d->booking_id,
                'booking_code' => $d->booking?->booking_code,
                'kos_name' => $d->booking?->kos?->name,
                'tenant_name' => $d->booking?->user?->name,
                'pembayaran_id' => $d->pembayaran_id,
                'gross_amount' => (float) $d->gross_amount,
                'platform_fee' => (float) $d->platform_fee,
                'owner_amount' => (float) $d->owner_amount,
                'status' => $d->status,
                'processed_at' => $d->processed_at?->toDateTimeString(),
                'created_at' => $d->created_at?->toDateTimeString(),
            ]),
            'meta' => [
                'current_page' => $distributions->currentPage(),
                'per_page' => $distributions->perPage(),
                'total' => $distributions->total(),
            ],
        ]);
    }

    public function markProcessed(PaymentDistribution $distribution, PaymentDistributionService $service): JsonResponse
    {
        $this->authorize('markProcessed', $distribution);

        $result = $service->markProcessed($distribution, request()->user());

        if (! ($result['ok'] ?? false)) {
            return response()->json([
                'message' => match ($result['error'] ?? '') {
                    'not_ready' => 'Distribution belum siap diproses.',
                    'forbidden' => 'Anda tidak memiliki akses.',
                    default => 'Gagal memproses distribution.',
                },
            ], 422);
        }

        return response()->json([
            'data' => [
                'id' => $result['distribution']->id,
                'status' => $result['distribution']->status,
                'processed_at' => $result['distribution']->processed_at?->toDateTimeString(),
            ],
            'message' => 'Distribution ditandai sudah dicairkan.',
        ]);
    }
}
