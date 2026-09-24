<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\BookingOccupant;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Support\NotificationType;
use Illuminate\Http\Request;
use App\Models\CheckIn;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Penghuni;
use Illuminate\Support\Str;

class OwnerBookingOccupantController extends Controller
{
    public function index(Request $request, \App\Models\Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['occupants.user', 'kos', 'kamar']);
        return view('owner.booking.occupants', compact('booking'));
    }

    public function confirmIdentity(Request $request, BookingOccupant $occupant)
    {
        $this->authorize('confirmIdentity', $occupant);
        abort_unless($occupant->booking->status === \App\Models\Booking::STATUS_APPROVED, 400, 'Booking tidak dalam tahap check-in.');
        abort_unless($occupant->identityPending(), 400, 'Pengajuan tidak dalam status menunggu konfirmasi.');
        abort_unless($occupant->hasIdentityData(), 400, 'Data identitas belum lengkap.');

        $user = $request->user();
        $booking = $occupant->booking;
        if ($user->isOwner() && (int) $booking->kos->owner_id !== (int) $user->id) {
            abort(403, 'Bukan kos Anda.');
        }
        if ($user->isAdmin() && ! $user->canAccessKos((int) $booking->kos_id)) {
            abort(403, 'Bukan kos yang ditugaskan kepada Anda.');
        }

        $occupant->update([
            'identity_status' => BookingOccupant::IDENTITY_CONFIRMED,
            'identity_confirmed_at' => now(),
            'identity_confirmed_by' => $user->id,
            'identity_rejected_at' => null,
            'identity_rejection_reason' => null,
        ]);

        // Keep booking primary identity in sync if occupant_index 1
        if ($occupant->occupant_index === 1) {
            $booking->update([
                'identity_confirmed_at' => now(),
                'identity_confirmed_by' => $user->id,
                'identity_rejected_at' => null,
                'identity_rejection_reason' => null,
            ]);
        }

        try {
            if ($occupant->user_id) {
                NotificationService::create(
                    $occupant->user_id,
                    NotificationType::IDENTITY,
                    'Check-In Dikonfirmasi',
                    "Pengajuan check-in kamar {$booking->kamar->room_number} telah dikonfirmasi oleh owner.",
                    ['url' => route('tenant.booking.show', $booking)]
                );
            }
            AuditLogService::approve('Check-In', 'Check-in occupant dikonfirmasi', ['booking_occupant_id' => $occupant->id, 'booking_id' => $booking->id]);
        } catch (\Throwable $e) {
            \Log::warning('Occupant identity confirm failed: '.$e->getMessage());
        }

        return back()->with('success', 'Check-in berhasil dikonfirmasi.');
    }

    public function rejectIdentity(Request $request, BookingOccupant $occupant)
    {
        $this->authorize('confirmIdentity', $occupant);
        abort_unless($occupant->booking->status === \App\Models\Booking::STATUS_APPROVED, 400, 'Booking tidak dalam tahap check-in.');
        abort_unless($occupant->identityPending(), 400, 'Hanya pengajuan pending yang dapat ditolak.');

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $user = $request->user();
        $booking = $occupant->booking;
        if ($user->isOwner() && (int) $booking->kos->owner_id !== (int) $user->id) {
            abort(403, 'Bukan kos Anda.');
        }
        if ($user->isAdmin() && ! $user->canAccessKos((int) $booking->kos_id)) {
            abort(403, 'Bukan kos yang ditugaskan kepada Anda.');
        }

        $occupant->update([
            'identity_status' => BookingOccupant::IDENTITY_REJECTED,
            'identity_rejected_at' => now(),
            'identity_rejection_reason' => trim($data['reason']),
            'identity_confirmed_at' => null,
            'identity_confirmed_by' => null,
        ]);

        if ($occupant->occupant_index === 1) {
            $booking->update([
                'identity_rejected_at' => now(),
                'identity_rejection_reason' => trim($data['reason']),
                'identity_confirmed_at' => null,
                'identity_confirmed_by' => null,
            ]);
        }

        try {
            if ($occupant->user_id) {
                NotificationService::create(
                    $occupant->user_id,
                    NotificationType::IDENTITY,
                    'Pengajuan Check-In Ditolak',
                    "Pengajuan check-in kamar {$booking->kamar->room_number} ditolak. Alasan: ".trim($data['reason']).' Silakan perbaiki data Anda.',
                    ['url' => route('tenant.booking.show', $booking)]
                );
            }
            AuditLogService::reject('Check-In', 'Pengajuan check-in occupant ditolak', ['booking_occupant_id' => $occupant->id]);
        } catch (\Throwable $e) {
            \Log::warning('Occupant identity reject failed: '.$e->getMessage());
        }

        return back()->with('success', 'Pengajuan check-in ditolak.');
    }

    public function checkIn(Request $request, BookingOccupant $occupant)
    {
        $this->authorize('view', $occupant->booking);
        $booking = $occupant->booking;
        abort_unless($booking->status === \App\Models\Booking::STATUS_APPROVED, 400, 'Booking belum disetujui.');
        abort_unless($occupant->status === BookingOccupant::STATUS_ACCEPTED, 400, 'Occupant belum accepted.');
        abort_unless($occupant->identityConfirmed(), 400, 'Identitas belum dikonfirmasi.');
        abort_unless($occupant->penghuni_id === null, 400, 'Occupant sudah check-in.');

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $result = \DB::transaction(function () use ($booking, $occupant, $request, $data) {
            $lockedOccupant = BookingOccupant::whereKey($occupant->id)->lockForUpdate()->first();
            abort_unless($lockedOccupant && $lockedOccupant->status === BookingOccupant::STATUS_ACCEPTED, 400, 'Occupant tidak valid.');
            abort_unless($lockedOccupant->identity_status === BookingOccupant::IDENTITY_CONFIRMED, 400, 'Identitas belum dikonfirmasi.');
            abort_unless($lockedOccupant->penghuni_id === null, 400, 'Sudah check-in.');

            $kamar = Kamar::whereKey($booking->kamar_id)->lockForUpdate()->first();
            abort_unless($kamar && $kamar->status !== 'maintenance', 400, 'Kamar tidak tersedia.');
            abort_unless($kamar->occupancy() < (int) $kamar->capacity, 400, 'Kamar sudah penuh.');

            $userId = $lockedOccupant->user_id;
            abort_unless($userId, 400, 'User belum terikat.');

            $penghuni = Penghuni::create([
                'user_id' => $userId,
                'kos_id' => $booking->kos_id,
                'kamar_id' => $booking->kamar_id,
                'identity_number' => $lockedOccupant->identity_number,
                'phone' => $lockedOccupant->phone,
                'address' => $lockedOccupant->address,
                'check_in_date' => now(),
                'status' => 'active',
            ]);

            $billingMode = $booking->billing_mode ?? 'personal';
            if (! in_array($billingMode, ['personal', 'shared'], true)) $billingMode = 'personal';

            $kontrak = Kontrak::create([
                'contract_number' => 'KT-'.strtoupper(Str::random(12)),
                'penghuni_id' => $penghuni->id,
                'kos_id' => $booking->kos_id,
                'kamar_id' => $booking->kamar_id,
                'rental_type' => $booking->rental_type,
                'rental_price' => $booking->price,
                'billing_mode' => $billingMode,
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

            $lockedOccupant->update(['penghuni_id' => $penghuni->id]);
            $kamar->update(['status' => 'occupied']);

            // If all occupants checked in, mark booking completed? Keep approved until all checked in? For now keep approved.
            $remaining = BookingOccupant::where('booking_id', $booking->id)->whereNull('penghuni_id')->where('status', BookingOccupant::STATUS_ACCEPTED)->count();
            if ($remaining === 0) {
                // Optionally mark booking completed when all occupants checked in
                // Keep approved for backward compat; check if Booking has occupant check
            }

            return ['penghuni' => $penghuni, 'kontrak' => $kontrak, 'kamar' => $kamar];
        });

        try {
            NotificationService::checkin($occupant->user_id, $result['kamar']->room_number, route('tenant.kontrak.show', $result['kontrak']));
            NotificationService::kontrakActive($occupant->user_id, $result['kontrak']->contract_number, route('tenant.kontrak.show', $result['kontrak']));
            AuditLogService::create('Check-In', "Check-in occupant berhasil untuk kamar {$result['kamar']->room_number}", ['booking_occupant_id' => $occupant->id]);
        } catch (\Throwable $e) {
            \Log::warning('Occupant check-in notification failed: '.$e->getMessage());
        }

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';
        return redirect()->route("$prefix.booking.index")->with('success', 'Check-in occupant berhasil.');
    }
}
