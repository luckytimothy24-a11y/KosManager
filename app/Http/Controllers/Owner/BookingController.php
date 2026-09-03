<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kamar;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Booking::with(['user', 'kos', 'kamar']);

        if ($user->isOwner()) {
            $query->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id));
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->whereIn('kos_id', $kosIds);
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(10)->withQueryString();

        return view('owner.booking.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['user', 'kos', 'kamar']);

        return view('owner.booking.show', compact('booking'));
    }

    public function approve(Booking $booking)
    {
        $this->authorize('approve', $booking);

        $result = \DB::transaction(function () use ($booking) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                return ['type' => 'status'];
            }

            // Kunci kamar TERLEBIH DAHULU sebelum pemeriksaan overlap agar dua
            // proses persetujuan bersamaan untuk booking yang overlap pada kamar
            // yang sama tidak bisa lolos bersama-sama. Setelah lock diperoleh,
            // baca ulang status kamar terkini sesuai aturan bisnis existing.
            $kamar = Kamar::whereKey($locked->kamar_id)->lockForUpdate()->firstOrFail();

            // Status 'occupied'/'maintenance' menandakan kamar tidak dapat
            // di-booking. Status 'booked' tidak diblokir di sini karena overlap
            // terhadap reservasi approved yang ada sudah diperiksa di bawah
            // sambil lock kamar ditahan.
            if (in_array($kamar->status, ['occupied', 'maintenance'])) {
                return ['type' => 'kamar'];
            }

            $hasConflict = Booking::where('kamar_id', $locked->kamar_id)
                ->whereKeyNot($locked->id)
                ->where('status', 'approved')
                ->where('start_date', '<', $locked->end_date)
                ->where('end_date', '>', $locked->start_date)
                ->exists();

            if ($hasConflict) {
                return ['type' => 'conflict'];
            }

            $locked->update(['status' => 'approved']);

            $kamar->update(['status' => 'booked']);

            return ['type' => 'ok', 'booking' => $locked];
        });

        if ($result['type'] !== 'ok') {
            $errors = [
                'status' => 'Booking sudah diproses sebelumnya.',
                'conflict' => 'Booking tidak dapat disetujui. Sudah ada booking lain yang disetujui pada periode tersebut.',
                'kamar' => 'Kamar sedang tidak dapat dibooking.',
            ];

            return redirect()->back()->with('error', $errors[$result['type']]);
        }

        $fresh = $result['booking'];

        try {
            NotificationService::bookingApproved($fresh->user_id, $fresh->booking_code);
            AuditLogService::approve('Booking', "Booking {$fresh->booking_code} disetujui", ['booking_id' => $fresh->id]);
        } catch (\Exception $e) {
            \Log::warning('Booking approval notification/audit failed: '.$e->getMessage());
        }

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.booking.index")->with('success', 'Booking berhasil disetujui.');
    }

    public function reject(Booking $booking)
    {
        $this->authorize('reject', $booking);

        $rejected = \DB::transaction(function () use ($booking) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->first();

            if (! $locked || $locked->status !== 'pending') {
                return false;
            }

            $locked->update(['status' => 'rejected']);

            return true;
        });

        if (! $rejected) {
            return back()->with('error', 'Booking sudah diproses sebelumnya.');
        }

        try {
            NotificationService::bookingRejected($booking->user_id, $booking->booking_code);
            AuditLogService::reject('Booking', "Booking {$booking->booking_code} ditolak", ['booking_id' => $booking->id]);
        } catch (\Exception $e) {
            \Log::warning('Booking rejection notification/audit failed: '.$e->getMessage());
        }

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.booking.index")->with('success', 'Booking berhasil ditolak.');
    }
}
