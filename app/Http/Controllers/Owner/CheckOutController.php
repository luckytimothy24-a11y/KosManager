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
use Illuminate\Http\Request;

class CheckOutController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = CheckOut::with(['penghuni.user', 'kamar.kos', 'verifier']);

        if ($user->isOwner()) {
            $query->whereHas('kamar.kos', fn ($q) => $q->where('owner_id', $user->id));
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->whereHas('kamar', fn ($q) => $q->whereIn('kos_id', $kosIds));
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
            abort_unless($user->hasRole('super_admin', 'admin', 'owner') && $user->canAccessKos((int) $penghuni->kos_id), 403);
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

    public function approve(CheckOut $checkOut)
    {
        $this->authorize('approve', $checkOut);

        $outcome = \DB::transaction(function () use ($checkOut) {
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
                // Bila masih ada booking approved yang mencakup periode masa
                // depan pada kamar ini, kamar kembali ke status 'booked', bukan
                // 'available' — konsisten dengan booking:expire-old &
                // BookingService::cancel. Kamar 'available' hanya bila tidak ada
                // reservasi approved yang masih menutupi periode ke depan.
                $stillReserved = Booking::where('kamar_id', $kamar->id)
                    ->where('status', 'approved')
                    ->whereDate('end_date', '>=', today())
                    ->exists();

                $kamar->update(['status' => $stillReserved ? 'booked' : 'available']);
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
