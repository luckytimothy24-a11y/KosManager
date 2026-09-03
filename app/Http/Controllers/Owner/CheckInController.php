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
use Illuminate\Support\Str;

class CheckInController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = CheckIn::with(['penghuni.user', 'kamar.kos', 'officer']);

        $bookingQuery = Booking::with(['user', 'kos', 'kamar'])
            ->where('status', 'approved')
            ->whereDate('end_date', '>=', now()->toDateString())
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

        $result = \DB::transaction(function () use ($booking, $request, $data) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->first();
            abort_unless($locked && $locked->status === 'approved', 400, 'Booking sudah diproses sebelumnya.');
            abort_unless(now()->startOfDay()->lessThanOrEqualTo($locked->end_date), 400, 'Masa booking sudah berakhir.');

            $kamar = Kamar::whereKey($locked->kamar_id)->lockForUpdate()->first();
            abort_unless($kamar && $kamar->status === 'booked', 400, 'Kamar tidak tersedia.');

            $penghuni = Penghuni::create([
                'user_id' => $locked->user_id,
                'kos_id' => $locked->kos_id,
                'kamar_id' => $locked->kamar_id,
                'identity_number' => $data['identity_number'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'check_in_date' => now(),
                'status' => 'active',
            ]);

            $kontrak = Kontrak::create([
                'contract_number' => 'KT-'.strtoupper(Str::random(12)),
                'penghuni_id' => $penghuni->id,
                'kos_id' => $locked->kos_id,
                'kamar_id' => $locked->kamar_id,
                'rental_type' => $locked->rental_type,
                'rental_price' => $locked->price,
                'start_date' => $locked->start_date,
                'end_date' => $locked->end_date,
                'status' => 'active',
            ]);

            CheckIn::create([
                'penghuni_id' => $penghuni->id,
                'kamar_id' => $locked->kamar_id,
                'check_in_date' => now(),
                'check_in_time' => now()->format('H:i'),
                'officer_id' => $request->user()->id,
                'notes' => $data['notes'] ?? null,
            ]);

            $locked->update(['status' => 'completed']);
            $kamar->update(['status' => 'occupied']);

            return ['booking' => $locked, 'kontrak' => $kontrak, 'kamar' => $kamar];
        });

        $freshBooking = $result['booking'];
        $freshKamar = $result['kamar'];

        try {
            NotificationService::checkin($freshBooking->user_id, $freshKamar->room_number);
            NotificationService::kontrakActive($freshBooking->user_id, $result['kontrak']->contract_number);
            AuditLogService::create('Check-In', "Check-in berhasil untuk kamar {$freshKamar->room_number}", ['booking_id' => $freshBooking->id, 'kamar_id' => $freshBooking->kamar_id]);
        } catch (\Exception $e) {
            \Log::warning('Check-in notification/audit failed: '.$e->getMessage());
        }

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.booking.index")->with('success', 'Check-in berhasil dilakukan.');
    }
}
