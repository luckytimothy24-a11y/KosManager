<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePaymentRequest;
use App\Http\Resources\PembayaranResource;
use App\Http\Resources\TagihanResource;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BillingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:tenant');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->input('per_page', 15), 50);

        $penghunis = $request->user()->penghunis()->pluck('id');

        $tagihans = Tagihan::with(['kamar', 'pembayarans'])
            ->whereIn('penghuni_id', $penghunis)
            ->latest()
            ->paginate($perPage);

        return TagihanResource::collection($tagihans);
    }

    public function show(Tagihan $tagihan): JsonResponse|TagihanResource
    {
        $this->authorize('view', $tagihan);

        $tagihan->load(['penghuni.user', 'kontrak', 'kamar.kos', 'pembayarans']);

        return new TagihanResource($tagihan);
    }

    public function paymentStore(Tagihan $tagihan, StorePaymentRequest $request, PaymentService $paymentService): JsonResponse
    {
        $this->authorize('view', $tagihan);

        if (round((float) $request->amount, 2) !== round((float) $tagihan->total, 2)) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['amount' => ['Nominal pembayaran harus sesuai total tagihan.']],
            ], 422);
        }

        $penghuni = Penghuni::where('user_id', $request->user()->id)->where('status', 'active')->first();

        if (! $penghuni || (int) $tagihan->penghuni_id !== (int) $penghuni->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $result = $paymentService->createManual([
            'penghuni' => $penghuni,
            'tagihan' => $tagihan,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'proof_file' => $request->hasFile('proof_file')
                ? $request->file('proof_file')->store('bukti-pembayaran')
                : null,
        ]);

        if (! $result['ok']) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['amount' => [$result['message']]],
            ], 422);
        }

        $savedTagihan = $result['tagihan'];

        $kosOwner = $savedTagihan->kamar->kos->owner_id;
        NotificationService::paymentSubmitted($kosOwner, $request->user()->name, $savedTagihan->bill_number);
        AuditLogService::create('Pembayaran', "Pembayaran untuk tagihan {$savedTagihan->bill_number} diupload oleh {$request->user()->name}", ['tagihan_id' => $savedTagihan->id]);

        $savedTagihan->load(['kamar', 'pembayarans']);

        return response()->json([
            'data' => new TagihanResource($savedTagihan),
            'message' => 'Bukti pembayaran berhasil diupload. Menunggu verifikasi.',
        ], 201);
    }

    public function paymentShow(Tagihan $tagihan): JsonResponse|PembayaranResource
    {
        $this->authorize('view', $tagihan);

        $pembayaran = Pembayaran::where('tagihan_id', $tagihan->id)
            ->latest()
            ->first();

        if (! $pembayaran) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        return new PembayaranResource($pembayaran);
    }
}
