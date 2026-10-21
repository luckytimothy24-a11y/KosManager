<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CheckOut;
use App\Models\Penghuni;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:tenant');
    }

    /**
     * Tenant mengajukan check-out atas hunian miliknya sendiri.
     */
    public function request(Request $request, Penghuni $penghuni): JsonResponse
    {
        $user = $request->user();
        abort_unless($penghuni->user_id === $user->id, 403, 'Unauthorized.');

        $validated = $request->validate([
            'condition' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless($penghuni->status === 'active', 400, 'Penghuni tidak aktif.');
        $penghuni->load(['kos', 'user', 'kamar']);

        $created = \DB::transaction(function () use ($validated, $penghuni) {
            $existing = CheckOut::where('penghuni_id', $penghuni->id)->where('status', 'pending')->lockForUpdate()->exists();
            if ($existing) {
                return false;
            }

            CheckOut::create([
                'penghuni_id' => $penghuni->id,
                'kamar_id' => $penghuni->kamar_id,
                'request_date' => now(),
                'room_condition' => $validated['condition'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
            ]);

            return true;
        });

        if (! $created) {
            return response()->json([
                'message' => 'Pengajuan check-out untuk penghuni ini sudah ada dan menunggu persetujuan.',
            ], 422);
        }

        try {
            NotificationService::checkoutRequested($penghuni->kos->owner_id, $penghuni->user->name, $penghuni->kamar->room_number);
            AuditLogService::create('Check-Out', "Pengajuan check-out oleh {$penghuni->user->name} dari kamar {$penghuni->kamar->room_number}", ['penghuni_id' => $penghuni->id]);
        } catch (\Exception $e) {
            \Log::warning('Check-out request notification/audit failed: '.$e->getMessage());
        }

        return response()->json([
            'message' => 'Pengajuan check-out berhasil.',
        ], 201);
    }

    /**
     * Tenant membatalkan pengajuan miliknya yang masih pending.
     */
    public function cancel(CheckOut $checkout): JsonResponse
    {
        $this->authorize('cancel', $checkout);

        $cancelled = \DB::transaction(function () use ($checkout) {
            $locked = CheckOut::whereKey($checkout->id)->lockForUpdate()->first();
            if (! $locked || $locked->status !== CheckOut::STATUS_PENDING) {
                return false;
            }

            $locked->update([
                'status' => CheckOut::STATUS_CANCELLED,
                'verified_by' => auth()->id(),
            ]);

            return true;
        });

        if (! $cancelled) {
            return response()->json([
                'message' => 'Check-out hanya dapat dibatalkan selama masih menunggu persetujuan.',
            ], 400);
        }

        return response()->json([
            'message' => 'Pengajuan check-out berhasil dibatalkan.',
        ]);
    }
}
