<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCheckOutRequest;
use App\Models\Booking;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Support\NotificationType;
use Illuminate\Http\Request;

class CheckOutController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = CheckOut::with(['penghuni.user', 'kamar.kos', 'verifier']);

        if ($user->isOwner()) {
            $query->whereHas('penghuni.kos', fn ($q) => $q->where('owner_id', $user->id));
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->whereHas('penghuni', fn ($q) => $q->whereIn('kos_id', $kosIds));
        }

        $checkOuts = $query->latest()->paginate(10)->withQueryString();

        return view('owner.checkout.index', compact('checkOuts'));
    }

    public function requestCheckout(StoreCheckOutRequest $request, Penghuni $penghuni)
    {
        $penghuni->load(['kos', 'user', 'kamar']);
        $user = $request->user();

        if ($user->isTenant()) {
            abort_unless($penghuni->user_id === $user->id, 403);
        } else {
            abort_unless(
                $user->isSuperAdmin()
                    || ($user->isAdmin() && $user->canAccessKos((int) $penghuni->kos_id))
                    || ($user->isOwner() && $user->canAccessKos((int) $penghuni->kos_id)),
                403
            );
        }

        abort_unless($penghuni->status === 'active', 400, 'Penghuni tidak aktif.');

        $created = \DB::transaction(function () use ($request, $penghuni) {
            $existing = CheckOut::where('penghuni_id', $penghuni->id)->where('status', 'pending')->lockForUpdate()->exists();
            if ($existing) {
                return false;
            }

            CheckOut::create([
                'penghuni_id' => $penghuni->id,
                'kamar_id' => $penghuni->kamar_id,
                'request_date' => now(),
                'room_condition' => $request->validated('condition'),
                'notes' => $request->validated('notes'),
                'status' => 'pending',
            ]);

            return true;
        });

        if (! $created) {
            return back()->with('error', 'Pengajuan check-out untuk penghuni ini sudah ada dan menunggu persetujuan.');
        }

        $kosOwner = $penghuni->kos->owner_id;
        NotificationService::checkoutRequested($kosOwner, $penghuni->user->name, $penghuni->kamar->room_number);
        AuditLogService::create('Check-Out', "Pengajuan check-out oleh {$penghuni->user->name} dari kamar {$penghuni->kamar->room_number}", ['penghuni_id' => $penghuni->id]);

        return back()->with('success', 'Pengajuan check-out berhasil.');
    }

    /**
     * Inti approve satu pengajuan ( dipakai approve() satuan & bulkApprove() ).
     * Guard identik: pending-only + tagihan outstanding harus nol.
     *
     * @return array{type: string, count?: int, checkOut?: CheckOut, penghuni?: Penghuni}
     */
    private function approveLocked(CheckOut $checkOut): array
    {
        return \DB::transaction(function () use ($checkOut) {
            $locked = CheckOut::whereKey($checkOut->id)->lockForUpdate()->first();

            if (! $locked || $locked->status !== 'pending') {
                return ['type' => 'processed'];
            }

            $unpaidCount = Tagihan::where('penghuni_id', $locked->penghuni_id)
                ->outstanding()
                ->count();

            if ($unpaidCount > 0) {
                return ['type' => 'unpaid', 'count' => $unpaidCount];
            }

            $kamar = Kamar::whereKey($locked->kamar_id)->lockForUpdate()->first();

            $locked->update([
                'check_out_date' => now(),
                'status' => 'approved',
                'verified_by' => auth()->id(),
            ]);

            $penghuni = $locked->penghuni;
            $penghuni->update(['status' => 'inactive']);

            if ($kamar) {
                // Occupancy-aware: jika masih ada penghuni aktif, tetap occupied
                $remainingOccupancy = Penghuni::where('kamar_id', $kamar->id)->where('status', 'active')->count();
                if ($remainingOccupancy > 0) {
                    $kamar->update(['status' => 'occupied']);
                } else {
                    $stillReserved = Booking::where('kamar_id', $kamar->id)
                        ->where('status', 'approved')
                        ->whereDate('end_date', '>=', today())
                        ->exists();
                    $kamar->update(['status' => $stillReserved ? 'booked' : 'available']);
                }
            }

            $kontrak = Kontrak::where('penghuni_id', $penghuni->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();
            if ($kontrak) {
                // Keluar sebelum kontrak berakhir = terminated, sesuai/di akhir masa sewa = expired (PRD §6).
                $kontrakStatus = now()->startOfDay()->lt($kontrak->end_date) ? 'terminated' : 'expired';
                $kontrak->update(['status' => $kontrakStatus]);
            }

            return ['type' => 'approved', 'checkOut' => $locked, 'penghuni' => $penghuni];
        });
    }

    /**
     * Setujui massal: setiap item melewati policy approve + guard identik
     * approve() satuan. Item gagal (scope/status/tagihan) dilaporkan per
     * item tanpa menggagalkan yang valid.
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:50'],
        ]);

        $ok = 0;
        $failed = [];

        foreach ($validated['ids'] as $id) {
            $checkOut = CheckOut::with('penghuni.user', 'kamar')->find($id);

            if (! $checkOut || ! $request->user()->can('approve', $checkOut)) {
                $failed[] = "#{$id}: tidak memiliki akses.";

                continue;
            }

            $outcome = $this->approveLocked($checkOut);

            if ($outcome['type'] === 'approved') {
                $ok++;

                try {
                    NotificationService::checkoutApproved($outcome['penghuni']->user_id, $outcome['checkOut']->kamar->room_number);
                    AuditLogService::approve('Check-Out', "Check-out disetujui untuk {$outcome['penghuni']->user->name} (bulk)", ['check_out_id' => $outcome['checkOut']->id]);
                } catch (\Exception $e) {
                    \Log::warning('Bulk check-out approval notification/audit failed: '.$e->getMessage());
                }
            } elseif ($outcome['type'] === 'unpaid') {
                $failed[] = "Pengajuan #{$id}: masih memiliki {$outcome['count']} tagihan belum selesai.";
            } else {
                $failed[] = "Pengajuan #{$id}: sudah diproses sebelumnya.";
            }
        }

        $prefix = $request->user()->isAdmin() ? 'admin' : 'owner';
        $message = "{$ok} check-out berhasil disetujui.";
        if (! empty($failed)) {
            $message .= ' Gagal: '.implode(' ', array_slice($failed, 0, 3));
            if (count($failed) > 3) {
                $message .= ' (+'.(count($failed) - 3).' lainnya)';
            }

            return redirect()->route("$prefix.checkout.index")->with('warning', $message);
        }

        return redirect()->route("$prefix.checkout.index")->with('success', $message);
    }

    public function approve(CheckOut $checkOut)
    {
        $this->authorize('approve', $checkOut);

        $outcome = $this->approveLocked($checkOut);

        if ($outcome['type'] === 'processed') {
            abort(400, 'Pengajuan check-out sudah diproses sebelumnya.');
        }

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';

        if ($outcome['type'] === 'unpaid') {
            return redirect()->route("$prefix.checkout.index")
                ->with('error', "Check-out tidak dapat disetujui. Penghuni masih memiliki {$outcome['count']} tagihan yang belum diselesaikan (belum dibayar atau menunggu verifikasi).");
        }

        try {
            NotificationService::checkoutApproved($outcome['penghuni']->user_id, $outcome['checkOut']->kamar->room_number);
            AuditLogService::approve('Check-Out', "Check-out disetujui untuk {$outcome['penghuni']->user->name}", ['check_out_id' => $outcome['checkOut']->id]);
        } catch (\Exception $e) {
            \Log::warning('Check-out approval notification/audit failed: '.$e->getMessage());
        }

        return redirect()->route("$prefix.checkout.index")->with('success', 'Check-out berhasil disetujui.');
    }

    public function cancel(CheckOut $checkOut)
    {
        $this->authorize('cancel', $checkOut);

        \DB::transaction(function () use ($checkOut) {
            $locked = CheckOut::whereKey($checkOut->id)->lockForUpdate()->first();
            // Hanya pending yang dapat dibatalkan. Status final
            // (approved/rejected/completed/cancelled) ditolak dengan 400.
            abort_unless($locked && $locked->status === CheckOut::STATUS_PENDING, 400, 'Check-out hanya dapat dibatalkan selama masih menunggu persetujuan.');

            // Cancel tidak menghapus record dan tidak menyentuh
            // penghuni/kontrak/kamar/billing — hanya transisi status.
            $locked->update([
                'status' => CheckOut::STATUS_CANCELLED,
                'verified_by' => auth()->id(),
            ]);
        });

        try {
            AuditLogService::create('Check-Out', 'Pengajuan check-out dibatalkan oleh '.auth()->user()->name, ['check_out_id' => $checkOut->id]);
            NotificationService::create(
                $checkOut->penghuni->user_id,
                NotificationType::CHECKOUT,
                'Check-Out Dibatalkan',
                "Pengajuan check-out dari kamar {$checkOut->kamar->room_number} telah dibatalkan."
            );
        } catch (\Exception $e) {
            \Log::warning('Check-out cancel notification/audit failed: '.$e->getMessage());
        }

        if (auth()->user()->isTenant()) {
            return redirect()->route('tenant.kontrak.index')->with('success', 'Pengajuan check-out berhasil dibatalkan.');
        }

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.checkout.index")->with('success', 'Pengajuan check-out berhasil dibatalkan.');
    }

    public function reject(CheckOut $checkOut)
    {
        $this->authorize('reject', $checkOut);

        \DB::transaction(function () use ($checkOut) {
            $locked = CheckOut::whereKey($checkOut->id)->lockForUpdate()->first();
            abort_unless($locked && $locked->status === 'pending', 400, 'Pengajuan check-out sudah diproses sebelumnya.');

            $locked->update(['status' => 'rejected']);
        });

        try {
            AuditLogService::reject('Check-Out', 'Check-out ditolak', ['check_out_id' => $checkOut->id]);
            NotificationService::checkoutRejected($checkOut->penghuni->user_id, $checkOut->kamar->room_number);
        } catch (\Exception $e) {
            \Log::warning('Check-out rejection notification/audit failed: '.$e->getMessage());
        }

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.checkout.index")->with('success', 'Check-out ditolak.');
    }
}
