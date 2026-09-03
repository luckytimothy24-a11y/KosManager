<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Pembayaran::with(['tagihan', 'penghuni.user']);

        if ($user->isOwner()) {
            $query->whereHas('penghuni.kos', fn ($q) => $q->where('owner_id', $user->id));
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->whereHas('penghuni', fn ($q) => $q->whereIn('kos_id', $kosIds));
        } elseif ($user->isTenant()) {
            // Semua penghuni milik tenant (termasuk yang sudah check-out) agar riwayat tidak hilang.
            $query->whereIn('penghuni_id', $user->penghunis()->pluck('id'));
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where('payment_number', 'like', '%'.$search.'%');
        }

        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        $pembayarans = $query->latest()->paginate(10)->withQueryString();

        $pendingQuery = Pembayaran::where('verification_status', Pembayaran::STATUS_PENDING);
        if ($user->isOwner()) {
            $pendingQuery->whereHas('penghuni.kos', fn ($q) => $q->where('owner_id', $user->id));
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $pendingQuery->whereHas('penghuni', fn ($q) => $q->whereIn('kos_id', $kosIds));
        } elseif ($user->isTenant()) {
            $pendingQuery->whereIn('penghuni_id', $user->penghunis()->pluck('id'));
        }
        $pendingCount = $pendingQuery->count();

        return view($user->isTenant() ? 'tenant.pembayaran.index' : 'owner.pembayaran.index', compact('pembayarans', 'pendingCount'));
    }

    public function show(Pembayaran $pembayaran)
    {
        $this->authorize('view', $pembayaran);
        $pembayaran->load(['tagihan', 'penghuni.user', 'penghuni.kos', 'penghuni.kamar', 'verifier']);

        $user = request()->user();

        return view($user->isTenant() ? 'tenant.pembayaran.show' : 'owner.pembayaran.show', compact('pembayaran'));
    }

    public function downloadProof(Pembayaran $pembayaran)
    {
        $this->authorize('view', $pembayaran);

        abort_unless($pembayaran->proof_file && \Storage::exists($pembayaran->proof_file), 404);

        return \Storage::download($pembayaran->proof_file);
    }

    public function verify(Pembayaran $pembayaran)
    {
        $this->authorize('verify', $pembayaran);
        $pembayaran->load(['penghuni', 'tagihan']);
        abort_unless($pembayaran->verification_status === Pembayaran::STATUS_PENDING, 400, 'Pembayaran sudah diverifikasi sebelumnya.');
        abort_if(
            round((float) $pembayaran->amount, 2) !== round((float) $pembayaran->tagihan->total, 2),
            400,
            'Nominal pembayaran tidak sesuai total tagihan.'
        );

        \DB::transaction(function () use ($pembayaran) {
            $locked = Pembayaran::whereKey($pembayaran->id)->lockForUpdate()->first();
            abort_unless($locked && $locked->verification_status === Pembayaran::STATUS_PENDING, 400, 'Pembayaran sudah diverifikasi sebelumnya.');

            $locked->update([
                'verification_status' => Pembayaran::STATUS_APPROVED,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);
            $locked->tagihan->update(['status' => Tagihan::STATUS_PAID]);
        });

        try {
            NotificationService::paymentVerified($pembayaran->penghuni->user_id, $pembayaran->tagihan->bill_number);
            AuditLogService::approve('Pembayaran', "Pembayaran {$pembayaran->payment_number} disetujui", ['pembayaran_id' => $pembayaran->id]);
        } catch (\Exception $e) {
            \Log::warning('Payment verification notification/audit failed: '.$e->getMessage());
        }

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.pembayaran.index")->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function reject(Request $request, Pembayaran $pembayaran)
    {
        $this->authorize('verify', $pembayaran);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        abort_unless($pembayaran->verification_status === Pembayaran::STATUS_PENDING, 400, 'Pembayaran sudah diverifikasi sebelumnya.');

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

            if ($locked->tagihan->status === Tagihan::STATUS_PAYMENT_PENDING) {
                $restoredStatus = $locked->tagihan->due_date && $locked->tagihan->due_date->startOfDay()->lt(now()->startOfDay())
                    ? Tagihan::STATUS_OVERDUE
                    : Tagihan::STATUS_UNPAID;
                $locked->tagihan->update(['status' => $restoredStatus]);
            }
        });

        try {
            AuditLogService::reject('Pembayaran', "Pembayaran {$pembayaran->payment_number} ditolak", ['pembayaran_id' => $pembayaran->id]);
            NotificationService::paymentRejected($pembayaran->penghuni->user_id, $pembayaran->tagihan->bill_number);
        } catch (\Exception $e) {
            \Log::warning('Payment rejection notification/audit failed: '.$e->getMessage());
        }

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.pembayaran.index")->with('success', 'Pembayaran ditolak. Alasan: '.$validated['reason']);
    }
}
