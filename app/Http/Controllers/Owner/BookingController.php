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
            $query->where(function ($q) use ($request) {
                $q->where('booking_code', 'like', '%'.$request->search.'%')
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$request->search.'%'));
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

        $conflict = \DB::transaction(function () use ($booking) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if ($booking->status !== 'pending') {
                return 'status';
            }

            $hasConflict = Booking::where('kamar_id', $booking->kamar_id)
                ->whereKeyNot($booking->id)
                ->where('status', 'approved')
                ->where('start_date', '<', $booking->end_date)
                ->where('end_date', '>', $booking->start_date)
                ->exists();

            if ($hasConflict) {
                return 'conflict';
            }

            $kamar = Kamar::whereKey($booking->kamar_id)->lockForUpdate()->firstOrFail();

            if (in_array($kamar->status, ['occupied', 'maintenance'])) {
                return 'kamar';
            }

            $booking->update(['status' => 'approved']);

            if ($kamar->status === 'available') {
                $kamar->update(['status' => 'booked']);
            }

            return null;
        });

        if ($conflict === 'status') {
            return redirect()->back()->with('error', 'Booking sudah diproses sebelumnya.');
        }

        if ($conflict === 'conflict') {
            return redirect()->back()->with('error', 'Booking tidak dapat disetujui. Sudah ada booking lain yang disetujui pada periode tersebut.');
        }

        if ($conflict === 'kamar') {
            return redirect()->back()->with('error', 'Kamar sedang tidak dapat dibooking.');
        }

        NotificationService::bookingApproved($booking->user_id, $booking->booking_code);
        AuditLogService::approve('Booking', "Booking {$booking->booking_code} disetujui", ['booking_id' => $booking->id]);

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

        NotificationService::bookingRejected($booking->user_id, $booking->booking_code);
        AuditLogService::reject('Booking', "Booking {$booking->booking_code} ditolak", ['booking_id' => $booking->id]);

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.booking.index")->with('success', 'Booking berhasil ditolak.');
    }
}
