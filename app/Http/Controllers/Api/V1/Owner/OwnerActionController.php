<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Controller;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Message;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\PaymentDistributionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:owner');
    }

    /**
     * Verifikasi satu pembayaran (guard identik dengan web verify).
     */
    public function verifyPayment(Pembayaran $pembayaran): JsonResponse
    {
        $this->authorize('verify', $pembayaran);
        $pembayaran->load(['penghuni.user', 'penghuni.kos.owner', 'tagihan', 'booking.kos', 'booking.user']);

        if ($pembayaran->verification_status !== Pembayaran::STATUS_PENDING) {
            return response()->json(['message' => 'Pembayaran sudah diverifikasi sebelumnya.'], 400);
        }

        if (round((float) $pembayaran->amount, 2) !== round((float) $pembayaran->expectedAmount(), 2)) {
            return response()->json(['message' => 'Nominal pembayaran tidak sesuai.'], 400);
        }

        \DB::transaction(function () use ($pembayaran) {
            $locked = Pembayaran::whereKey($pembayaran->id)->lockForUpdate()->first();
            abort_unless($locked && $locked->verification_status === Pembayaran::STATUS_PENDING, 400, 'Pembayaran sudah diverifikasi sebelumnya.');

            $locked->update([
                'verification_status' => Pembayaran::STATUS_APPROVED,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            // Payment approved ≠ booking approved.
            if ($locked->tagihan_id) {
                $locked->tagihan->update(['status' => Tagihan::STATUS_PAID]);
            }

            if ($locked->booking_id) {
                app(PaymentDistributionService::class)->recordFromApprovedPayment($locked);
            }
        });

        try {
            if ($pembayaran->isBookingPayment()) {
                NotificationService::bookingPaymentVerified($pembayaran->booking->user_id, $pembayaran->booking->booking_code);
                AuditLogService::approve(
                    'Pembayaran',
                    "Pembayaran booking {$pembayaran->booking->booking_code} disetujui (API; booking tetap pending)",
                    [
                        'pembayaran_id' => $pembayaran->id,
                        'booking_id' => $pembayaran->booking_id,
                        'amount' => (float) $pembayaran->amount,
                    ]
                );
            } else {
                NotificationService::paymentVerified($pembayaran->penghuni->user_id, $pembayaran->tagihan->bill_number);
                AuditLogService::approve('Pembayaran', "Pembayaran {$pembayaran->payment_number} disetujui (API)", ['pembayaran_id' => $pembayaran->id]);
            }
        } catch (\Exception $e) {
            \Log::warning('API payment verification notification/audit failed: '.$e->getMessage());
        }

        return response()->json([
            'message' => 'Pembayaran berhasil diverifikasi.',
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'booking_status' => $pembayaran->booking_id ? $pembayaran->booking->refresh()->status : null,
        ]);
    }

    /**
     * Tolak satu pembayaran dengan alasan wajib.
     */
    public function rejectPayment(Request $request, Pembayaran $pembayaran): JsonResponse
    {
        $this->authorize('verify', $pembayaran);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if ($pembayaran->verification_status !== Pembayaran::STATUS_PENDING) {
            return response()->json(['message' => 'Pembayaran sudah diverifikasi sebelumnya.'], 400);
        }

        \DB::transaction(function () use ($validated, $pembayaran) {
            $locked = Pembayaran::whereKey($pembayaran->id)->lockForUpdate()->first();
            abort_unless($locked && $locked->verification_status === Pembayaran::STATUS_PENDING, 400, 'Pembayaran sudah diverifikasi sebelumnya.');

            $locked->update([
                'verification_status' => Pembayaran::STATUS_REJECTED,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'admin_notes' => $validated['reason'],
                'active_payment_key' => null,
            ]);

            // Payment rejected ≠ booking rejected.
            if ($locked->tagihan_id && $locked->tagihan->status === Tagihan::STATUS_PAYMENT_PENDING) {
                $restoredStatus = $locked->tagihan->due_date && $locked->tagihan->due_date->startOfDay()->lt(now()->startOfDay())
                    ? Tagihan::STATUS_OVERDUE
                    : Tagihan::STATUS_UNPAID;
                $locked->tagihan->update(['status' => $restoredStatus]);
            }
        });

        try {
            if ($pembayaran->isBookingPayment()) {
                AuditLogService::reject(
                    'Pembayaran',
                    "Pembayaran booking {$pembayaran->booking->booking_code} ditolak (API; booking tetap pending)",
                    ['pembayaran_id' => $pembayaran->id]
                );
                NotificationService::paymentRejected($pembayaran->booking->user_id, $pembayaran->booking->booking_code);
            } else {
                AuditLogService::reject('Pembayaran', "Pembayaran {$pembayaran->payment_number} ditolak (API)", ['pembayaran_id' => $pembayaran->id]);
                NotificationService::paymentRejected($pembayaran->penghuni->user_id, $pembayaran->tagihan->bill_number);
            }
        } catch (\Exception $e) {
            \Log::warning('API payment rejection notification/audit failed: '.$e->getMessage());
        }

        return response()->json([
            'message' => 'Pembayaran ditolak.',
            'verification_status' => Pembayaran::STATUS_REJECTED,
            'booking_status' => $pembayaran->booking_id ? $pembayaran->booking->refresh()->status : null,
        ]);
    }

    /**
     * Setujui satu pengajuan checkout (guard identik dengan web approve).
     */
    public function approveCheckout(CheckOut $checkout): JsonResponse
    {
        $this->authorize('approve', $checkout);

        $outcome = \DB::transaction(function () use ($checkout) {
            $locked = CheckOut::whereKey($checkout->id)->lockForUpdate()->first();
            if (! $locked || $locked->status !== CheckOut::STATUS_PENDING) {
                return ['type' => 'processed'];
            }

            $unpaid = Tagihan::where('penghuni_id', $locked->penghuni_id)
                ->where('status', '!=', Tagihan::STATUS_PAID)
                ->where('status', '!=', Tagihan::STATUS_CANCELLED)
                ->exists();

            if ($unpaid) {
                return ['type' => 'unpaid'];
            }

            $locked->update([
                'check_out_date' => now(),
                'status' => CheckOut::STATUS_APPROVED,
                'verified_by' => auth()->id(),
            ]);

            $penghuni = $locked->penghuni;
            $penghuni->update(['status' => 'inactive']);

            $kamar = Kamar::whereKey($locked->kamar_id)->lockForUpdate()->first();
            if ($kamar) {
                $kamar->syncStatus();
            }

            $kontrak = Kontrak::where('penghuni_id', $penghuni->id)
                ->where('status', 'active')->lockForUpdate()->first();
            if ($kontrak) {
                $kontrak->update(['status' => now()->startOfDay()->lt($kontrak->end_date) ? 'terminated' : 'expired']);
            }

            return ['type' => 'approved'];
        });

        if ($outcome['type'] === 'processed') {
            return response()->json(['message' => 'Pengajuan sudah diproses sebelumnya.'], 400);
        }
        if ($outcome['type'] === 'unpaid') {
            return response()->json(['message' => 'Penghuni masih memiliki tagihan yang belum diselesaikan.'], 400);
        }

        return response()->json(['message' => 'Check-out berhasil disetujui.']);
    }

    /**
     * Setujui massal pengajuan checkout.
     */
    public function bulkApproveCheckout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:50'],
        ]);

        $ok = 0;
        $failed = [];

        foreach ($validated['ids'] as $id) {
            $checkOut = CheckOut::with('penghuni.user')->find($id);
            if (! $checkOut || ! $request->user()->can('approve', $checkOut)) {
                $failed[] = "#{$id}: tidak memiliki akses.";

                continue;
            }

            $outcome = \DB::transaction(function () use ($checkOut) {
                $locked = CheckOut::whereKey($checkOut->id)->lockForUpdate()->first();
                if (! $locked || $locked->status !== CheckOut::STATUS_PENDING) {
                    return 'skip';
                }
                $unpaid = Tagihan::where('penghuni_id', $locked->penghuni_id)
                    ->whereNotIn('status', [Tagihan::STATUS_PAID, Tagihan::STATUS_CANCELLED])->exists();
                if ($unpaid) {
                    return 'unpaid';
                }
                $locked->update(['check_out_date' => now(), 'status' => CheckOut::STATUS_APPROVED, 'verified_by' => auth()->id()]);
                $locked->penghuni->update(['status' => 'inactive']);

                $kamar = Kamar::whereKey($locked->kamar_id)->lockForUpdate()->first();
                if ($kamar) {
                    $kamar->syncStatus();
                }
                $kontrak = Kontrak::where('penghuni_id', $locked->penghuni->id)
                    ->where('status', 'active')->lockForUpdate()->first();
                if ($kontrak) {
                    $kontrak->update(['status' => now()->startOfDay()->lt($kontrak->end_date) ? 'terminated' : 'expired']);
                }

                return 'ok';
            });

            if ($outcome === 'ok') {
                $ok++;
                try {
                    NotificationService::checkoutApproved($checkOut->penghuni->user_id, $checkOut->kamar->room_number);
                    AuditLogService::approve('Check-Out', 'Check-out disetujui (bulk API)', ['check_out_id' => $checkOut->id]);
                } catch (\Exception $e) {
                    \Log::warning('API bulk checkout approval notification failed: '.$e->getMessage());
                }
            } else {
                $failed[] = "#{$id}: ".($outcome === 'unpaid' ? 'masih ada tagihan' : 'sudah diproses');
            }
        }

        return response()->json([
            'approved' => $ok,
            'failed' => count($failed),
            'failed_details' => $failed,
            'message' => "{$ok} check-out disetujui.",
        ]);
    }

    /**
     * Batalkan pengajuan checkout pending pada kos milik owner.
     */
    public function cancelCheckout(CheckOut $checkout): JsonResponse
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

        return response()->json(['message' => 'Pengajuan check-out berhasil dibatalkan.']);
    }

    /**
     * Balas pesan tenant yang terkait dengan kos milik owner.
     */
    public function replyMessage(Request $request, User $tenant): JsonResponse
    {
        $owner = $request->user();
        $kosIds = Kos::where('owner_id', $owner->id)->pluck('id');

        if ($kosIds->isEmpty()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $linked = Message::whereIn('kos_id', $kosIds)
            ->between((int) $tenant->id, (int) $owner->id)
            ->exists();

        if (! $linked) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'kos_id' => ['sometimes', 'nullable', 'integer'],
        ]);

        $kosId = $validated['kos_id'] ?? null;
        if ($kosId && ! $kosIds->contains((int) $kosId)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $kosId || ! Message::where('kos_id', (int) $kosId)
            ->between((int) $owner->id, (int) $tenant->id)
            ->exists()) {
            $kosId = Message::whereIn('kos_id', $kosIds)
                ->between((int) $owner->id, (int) $tenant->id)
                ->latest('created_at')
                ->value('kos_id');
        }

        if (! $kosId) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $message = Message::create([
            'kos_id' => (int) $kosId,
            'sender_id' => $owner->id,
            'receiver_id' => $tenant->id,
            'message' => $validated['message'],
        ]);

        try {
            NotificationService::messageReplied(
                (int) $tenant->id,
                $owner->name,
                route('tenant.messages.index', ['kos' => (int) $kosId])
            );
        } catch (\Exception $e) {
            \Log::warning('API message reply notification failed: '.$e->getMessage());
        }

        return response()->json([
            'data' => ['id' => $message->id],
            'message' => 'Balasan berhasil dikirim.',
        ], 201);
    }
}
