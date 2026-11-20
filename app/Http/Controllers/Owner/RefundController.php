<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Pembayaran::with(['tagihan', 'penghuni.user', 'booking.kos', 'booking.user'])
            ->where('refund_status', 'requested');

        if ($user->isOwner()) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('penghuni.kos', fn ($qq) => $qq->where('owner_id', $user->id))
                    ->orWhereHas('booking.kos', fn ($qq) => $qq->where('owner_id', $user->id));
            });
        }

        $refunds = $query->latest()->paginate(15);

        return view('owner.refund.index', compact('refunds'));
    }

    /**
     * A2. Refund Approval Guard.
     * - Authorization check via Policy (approveRefund).
     * - DB::transaction with lockForUpdate().
     * - Status re-validation inside lock.
     * - Sets refund_status to 'approved' (NOT 'completed').
     */
    public function approve(Request $request, Pembayaran $pembayaran)
    {
        $this->authorize('approveRefund', $pembayaran);

        DB::transaction(function () use ($pembayaran) {
            $locked = Pembayaran::whereKey($pembayaran->id)->lockForUpdate()->firstOrFail();

            // Revalidasi ownership setelah row dikunci (A2).
            $this->authorize('approveRefund', $locked);

            if (! $locked->isRefundApprovable()) {
                throw ValidationException::withMessages([
                    'refund_status' => 'Refund hanya dapat disetujui jika berstatus requested.',
                ]);
            }

            // Nominal refund harus valid berdasarkan data sumber (server-side).
            $refundAmount = (float) ($locked->refund_amount ?? 0);
            $paymentAmount = (float) $locked->amount;

            if ($locked->verification_status !== Pembayaran::STATUS_APPROVED) {
                throw ValidationException::withMessages([
                    'refund_status' => 'Refund hanya dapat disetujui untuk pembayaran yang sudah terverifikasi.',
                ]);
            }

            if ($refundAmount <= 0 || $refundAmount > $paymentAmount) {
                throw ValidationException::withMessages([
                    'refund_amount' => 'Nominal refund tidak valid terhadap nominal pembayaran.',
                ]);
            }

            $locked->update([
                'refund_status' => Pembayaran::REFUND_APPROVED,
                'refunded_at' => null, // Approved != refunded
            ]);

            AuditLogService::approve(
                'Pembayaran',
                "Refund pembayaran {$locked->payment_number} disetujui (menunggu bukti transfer)",
                ['pembayaran_id' => $locked->id, 'refund_amount' => (float) $locked->refund_amount]
            );

            $notifyUserId = $locked->penghuni?->user_id ?? $locked->booking?->user_id;

            if ($notifyUserId !== null) {
                try {
                    NotificationService::create(
                        (int) $notifyUserId,
                        'refund_approved',
                        'Refund Disetujui',
                        'Refund sebesar Rp '.number_format($locked->refund_amount ?? $locked->amount, 0, ',', '.').' telah disetujui pemilik dan sedang diproses.',
                        ['url' => route('tenant.pembayaran.show', $locked)],
                        "refund-approved:{$locked->id}"
                    );
                } catch (\Exception $e) {
                    \Log::warning("Refund approval notification failed: {$e->getMessage()}");
                }
            }
        });

        return back()->with('success', 'Refund berhasil disetujui.');
    }

    /**
     * A3. Refund Rejection Guard.
     * - Authorization check via Policy (rejectRefund).
     * - DB::transaction with lockForUpdate().
     * - Status re-validation inside lock.
     * - Rejection reason validation.
     */
    public function reject(Request $request, Pembayaran $pembayaran)
    {
        $this->authorize('rejectRefund', $pembayaran);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($pembayaran, $validated) {
            $locked = Pembayaran::whereKey($pembayaran->id)->lockForUpdate()->firstOrFail();

            // Revalidasi ownership setelah row dikunci (A3).
            $this->authorize('rejectRefund', $locked);

            if (! $locked->isRefundApprovable()) {
                throw ValidationException::withMessages([
                    'refund_status' => 'Refund hanya dapat ditolak jika berstatus requested.',
                ]);
            }

            $locked->update([
                'refund_status' => Pembayaran::REFUND_REJECTED,
                'refund_reason' => $validated['reason'],
            ]);

            AuditLogService::reject(
                'Pembayaran',
                "Refund pembayaran {$locked->payment_number} ditolak: {$validated['reason']}",
                ['pembayaran_id' => $locked->id, 'refund_amount' => (float) $locked->refund_amount]
            );

            $notifyUserId = $locked->penghuni?->user_id ?? $locked->booking?->user_id;

            if ($notifyUserId !== null) {
                try {
                    NotificationService::create(
                        (int) $notifyUserId,
                        'refund_rejected',
                        'Refund Ditolak',
                        "Refund untuk pembayaran {$locked->payment_number} ditolak: {$validated['reason']}",
                        ['url' => route('tenant.pembayaran.show', $locked)],
                        "refund-rejected:{$locked->id}"
                    );
                } catch (\Exception $e) {
                    \Log::warning("Refund rejection notification failed: {$e->getMessage()}");
                }
            }
        });

        return back()->with('success', 'Refund ditolak.');
    }
}
