<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
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
            $query->where('payment_number', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        $pembayarans = $query->latest()->paginate(10)->withQueryString();

        $pendingCount = (clone $query)->where('verification_status', 'pending')->count();

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
        abort_unless($pembayaran->verification_status === 'pending', 400, 'Pembayaran sudah diverifikasi sebelumnya.');
        abort_if(
            (float) $pembayaran->amount !== (float) $pembayaran->tagihan->total,
            400,
            'Nominal pembayaran tidak sesuai total tagihan.'
        );

        \DB::transaction(function () use ($pembayaran) {
            $pembayaran->update([
                'verification_status' => 'approved',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);
            $pembayaran->tagihan->update(['status' => 'paid']);
        });

        NotificationService::paymentVerified($pembayaran->penghuni->user_id, $pembayaran->tagihan->bill_number);
        AuditLogService::approve('Pembayaran', "Pembayaran {$pembayaran->payment_number} disetujui", ['pembayaran_id' => $pembayaran->id]);

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.pembayaran.index")->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function reject(Request $request, Pembayaran $pembayaran)
    {
        $this->authorize('verify', $pembayaran);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        abort_unless($pembayaran->verification_status === 'pending', 400, 'Pembayaran sudah diverifikasi sebelumnya.');

        \DB::transaction(function () use ($request, $pembayaran) {
            $pembayaran->update([
                'verification_status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'admin_notes' => $request->filled('reason') ? $request->input('reason') : $pembayaran->admin_notes,
            ]);

            if ($pembayaran->tagihan->status === 'pending_verification') {
                $pembayaran->tagihan->update(['status' => 'unpaid']);
            }
        });

        AuditLogService::reject('Pembayaran', "Pembayaran {$pembayaran->payment_number} ditolak", ['pembayaran_id' => $pembayaran->id]);

        NotificationService::paymentRejected($pembayaran->penghuni->user_id, $pembayaran->tagihan->bill_number);

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.pembayaran.index")->with('success', 'Pembayaran ditolak.'.($request->filled('reason') ? ' Alasan: '.$request->input('reason') : ''));
    }
}
