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
use App\Support\NotificationType;
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

    /**
     * Owner reviews pending identity and confirms it.
     * Only owner/admin of this kos can confirm. Tenant cannot call this.
     */
    public function confirmIdentity(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);
        abort_unless(in_array($request->user()->role, ['owner', 'admin', 'super_admin'], true), 403, 'Hanya owner/admin yang dapat mengonfirmasi.');
        abort_unless($booking->status === 'approved', 400, 'Booking tidak dalam tahap check-in.');
        abort_unless($booking->identityPending(), 400, 'Pengajuan tidak dalam status menunggu konfirmasi.');
        abort_unless($booking->hasIdentityData(), 400, 'Data identitas tenant belum lengkap.');

        // strict scope: owner must own kos, admin must be assigned
        $user = $request->user();
        if ($user->isOwner() && (int) $booking->kos->owner_id !== (int) $user->id) {
            abort(403, 'Bukan kos Anda.');
        }
        if ($user->isAdmin() && ! $user->canAccessKos((int) $booking->kos_id)) {
            abort(403, 'Bukan kos yang ditugaskan kepada Anda.');
        }

        $booking->update([
            'identity_confirmed_at' => now(),
            'identity_confirmed_by' => $user->id,
            'identity_rejected_at' => null,
            'identity_rejection_reason' => null,
        ]);

        try {
            NotificationService::create(
                $booking->user_id,
                NotificationType::IDENTITY,
                'Check-In Dikonfirmasi',
                "Pengajuan check-in kamar {$booking->kamar->room_number} telah dikonfirmasi oleh owner."
            );
            AuditLogService::approve('Check-In', 'Check-in dikonfirmasi', ['booking_id' => $booking->id, 'user_id' => $booking->user_id]);
        } catch (\Exception $e) {
            \Log::warning('Identity confirm notification/audit failed: '.$e->getMessage());
        }

        return back()->with('success', 'Check-in berhasil dikonfirmasi.');
    }

    /**
     * Owner rejects pending identity with reason.
     */
    public function rejectIdentity(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);
        abort_unless(in_array($request->user()->role, ['owner', 'admin', 'super_admin'], true), 403, 'Hanya owner/admin yang dapat menolak.');
        abort_unless($booking->status === 'approved', 400, 'Booking tidak dalam tahap check-in.');
        abort_unless($booking->identityPending(), 400, 'Hanya pengajuan pending yang dapat ditolak.');

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $user = $request->user();
        if ($user->isOwner() && (int) $booking->kos->owner_id !== (int) $user->id) {
            abort(403, 'Bukan kos Anda.');
        }
        if ($user->isAdmin() && ! $user->canAccessKos((int) $booking->kos_id)) {
            abort(403, 'Bukan kos yang ditugaskan kepada Anda.');
        }

        $booking->update([
            'identity_rejected_at' => now(),
            'identity_rejection_reason' => trim($data['reason']),
            'identity_confirmed_at' => null,
            'identity_confirmed_by' => null,
        ]);

        try {
            NotificationService::create(
                $booking->user_id,
                NotificationType::IDENTITY,
                'Pengajuan Check-In Ditolak',
                "Pengajuan check-in kamar {$booking->kamar->room_number} ditolak. Alasan: ".trim($data['reason']).' Silakan perbaiki data Anda.'
            );
            AuditLogService::reject('Check-In', 'Pengajuan check-in ditolak', ['booking_id' => $booking->id, 'user_id' => $booking->user_id]);
        } catch (\Exception $e) {
            \Log::warning('Identity reject notification/audit failed: '.$e->getMessage());
        }

        return back()->with('success', 'Pengajuan check-in ditolak. Tenant dapat memperbaiki data.');
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
            abort_unless($locked->identity_confirmed_at !== null, 400, 'Identitas tenant belum dikonfirmasi owner. Menunggu konfirmasi owner terlebih dahulu.');
            abort_unless($locked->hasIdentityData(), 400, 'Data identitas tenant belum lengkap. Tenant harus melengkapi data identitas terlebih dahulu.');

            $kamar = Kamar::whereKey($locked->kamar_id)->lockForUpdate()->first();
            abort_unless($kamar && $kamar->status === 'booked', 400, 'Kamar tidak tersedia.');
            abort_unless($kamar->occupancy() < (int) $kamar->capacity, 400, 'Kamar sudah penuh. Kapasitas tidak mencukupi.');

            $penghuni = Penghuni::create([
                'user_id' => $locked->user_id,
                'kos_id' => $locked->kos_id,
                'kamar_id' => $locked->kamar_id,
                'identity_number' => $locked->identity_number,
                'phone' => $locked->phone,
                'address' => $locked->address,
                'check_in_date' => now(),
                'status' => 'active',
            ]);

            $billingMode = $locked->billing_mode ?? 'personal';
            // Snapshot billing_mode from booking; fallback to personal for legacy bookings
            if (! in_array($billingMode, ['personal', 'shared'], true)) {
                $billingMode = 'personal';
            }

            $kontrak = Kontrak::create([
                'contract_number' => 'KT-'.strtoupper(Str::random(12)),
                'penghuni_id' => $penghuni->id,
                'kos_id' => $locked->kos_id,
                'kamar_id' => $locked->kamar_id,
                'rental_type' => $locked->rental_type,
                'rental_price' => $locked->price,
                'billing_mode' => $billingMode,
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
