<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCheckInRequest;
use App\Models\Booking;
use App\Models\CheckIn;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Penghuni;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = CheckIn::with(['penghuni.user', 'kamar.kos', 'officer']);

        $bookingQuery = Booking::with(['user', 'kos', 'kamar'])
            ->where('status', 'approved')
            ->latest('booking_date');

        if ($user->isOwner()) {
            $query->whereHas('kamar.kos', fn ($q) => $q->where('owner_id', $user->id));
            $bookingQuery->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id));
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->whereHas('kamar', fn ($q) => $q->whereIn('kos_id', $kosIds));
            $bookingQuery->whereIn('kos_id', $kosIds);
        }

        $checkIns = $query->latest()->paginate(10)->withQueryString();
        $readyBookings = $bookingQuery->get();

        return view('owner.checkin.index', compact('checkIns', 'readyBookings'));
    }

    public function process(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);

        abort_unless($booking->status === 'approved', 400, 'Booking belum disetujui.');

        $data = $request->validate(app(StoreCheckInRequest::class)->rules());

        $kontrak = \DB::transaction(function () use ($booking, $request, $data) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->first();
            abort_unless($booking && $booking->status === 'approved', 400, 'Booking sudah diproses sebelumnya.');

            $kamar = Kamar::whereKey($booking->kamar_id)->lockForUpdate()->first();
            abort_unless($kamar && $kamar->status === 'booked', 400, 'Kamar tidak tersedia.');

            $penghuni = Penghuni::create([
                'user_id' => $booking->user_id,
                'kos_id' => $booking->kos_id,
                'kamar_id' => $booking->kamar_id,
                'identity_number' => $data['identity_number'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'check_in_date' => now(),
                'status' => 'active',
            ]);

            $kontrak = Kontrak::create([
                'contract_number' => 'KT-'.strtoupper(uniqid()),
                'penghuni_id' => $penghuni->id,
                'kos_id' => $booking->kos_id,
                'kamar_id' => $booking->kamar_id,
                'rental_type' => $booking->rental_type,
                'rental_price' => $booking->price,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'status' => 'active',
            ]);

            CheckIn::create([
                'penghuni_id' => $penghuni->id,
                'kamar_id' => $booking->kamar_id,
                'check_in_date' => now(),
                'check_in_time' => now()->format('H:i'),
                'officer_id' => $request->user()->id,
                'notes' => $data['notes'] ?? null,
            ]);

            $booking->update(['status' => 'completed']);
            $kamar->update(['status' => 'occupied']);

            return $kontrak;
        });

        NotificationService::checkin($booking->user_id, $booking->kamar->room_number);
        NotificationService::kontrakActive($booking->user_id, $kontrak->contract_number);
        AuditLogService::create('Check-In', "Check-in berhasil untuk kamar {$booking->kamar->room_number}", ['booking_id' => $booking->id, 'kamar_id' => $booking->kamar_id]);

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.booking.index")->with('success', 'Check-in berhasil dilakukan.');
    }
}
