<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

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

    public function approve(Request $request, Pembayaran $pembayaran)
    {
        abort_unless($pembayaran->refund_status === 'requested', 404);

        $pembayaran->update([
            'refund_status' => 'completed',
            'refunded_at' => now(),
        ]);

        // Notifikasi ke tenant
        try {
            \App\Services\NotificationService::create(
                $pembayaran->penghuni?->user_id ?? $pembayaran->booking?->user_id,
                'refund_completed',
                'Refund Selesai',
                "Refund sebesar Rp " . number_format($pembayaran->refund_amount, 0, ',', '.') . " telah diproses.",
                ['url' => route('tenant.pembayaran.show', $pembayaran)],
                "refund-completed:{$pembayaran->id}"
            );
        } catch (\Exception $e) {
            \Log::warning("Refund completion notification failed: {$e->getMessage()}");
        }

        return back()->with('success', 'Refund berhasil disetujui.');
    }

    public function reject(Request $request, Pembayaran $pembayaran)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $pembayaran->update([
            'refund_status' => 'rejected',
            'refund_reason' => $validated['reason'],
        ]);

        try {
            \App\Services\NotificationService::create(
                $pembayaran->penghuni?->user_id ?? $pembayaran->booking?->user_id,
                'refund_rejected',
                'Refund Ditolak',
                "Refund untuk pembayaran {$pembayaran->payment_number} ditolak: {$validated['reason']}",
                ['url' => route('tenant.pembayaran.show', $pembayaran)],
                "refund-rejected:{$pembayaran->id}"
            );
        } catch (\Exception $e) {
            \Log::warning("Refund rejection notification failed: {$e->getMessage()}");
        }

        return back()->with('success', 'Refund ditolak.');
    }
}