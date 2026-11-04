<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Support\NotificationType;
use App\Support\WhatsAppNormalizer;
use Illuminate\Http\Request;

/**
 * Tenant Check-In: Tenant owns contact data.
 * - show: tenant views current identity status + form if allowed
 * - submit: tenant fills WhatsApp + address -> status pending (owner review), NOT self-confirmed
 * - update: tenant can fix rejected/pending data before owner confirm
 *
 * KTP tidak lagi dikumpulkan. Data KTP lama tetap tersimpan di database.
 */
class TenantCheckInController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::where('user_id', $request->user()->id)
            ->where('status', 'approved')
            ->with(['kos', 'kamar'])
            ->latest()
            ->get();

        return view('tenant.checkin.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['user', 'kos', 'kamar']);

        abort_unless($booking->status === 'approved', 404, 'Booking tidak dalam tahap check-in.');

        return view('tenant.checkin.identity', compact('booking'));
    }

    /**
     * Tenant submits contact data for OWNER review.
     * WhatsApp normalized to 08..., address trimmed. KTP is no longer collected.
     * Sets submitted_at, clears rejected state, does NOT set confirmed_at.
     */
    public function submit(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);

        abort_unless($booking->status === 'approved', 400, 'Booking tidak dalam tahap check-in.');
        abort_if($booking->identityConfirmed(), 400, 'Identitas sudah dikonfirmasi owner dan tidak dapat diubah.');

        $validated = $request->validate([
            'phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+\-\s\(\)]+$/'],
            'address' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'phone.regex' => 'Format nomor WhatsApp tidak valid.',
        ]);

        $phone = trim((string) $validated['phone']);
        $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);
        $phone = WhatsAppNormalizer::normalize($phone);
        $address = trim((string) $validated['address']);

        if (mb_strlen($phone) < 9) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp minimal 9 digit.'])->withInput();
        }
        if (mb_strlen($address) < 5) {
            return back()->withErrors(['address' => 'Alamat minimal 5 karakter.'])->withInput();
        }

        $booking->update([
            'phone' => $phone,
            'address' => $address,
            'identity_data_submitted_at' => now(),
            // reset rejection so owner can review again; do NOT set confirmed_at/ by
            'identity_rejected_at' => null,
            'identity_rejection_reason' => null,
        ]);

        try {
            $ownerId = (int) $booking->kos->owner_id;
            // notify owner that tenant submitted identity (re-use existing identity notification but with pending tone)
            NotificationService::create(
                $ownerId,
                NotificationType::IDENTITY,
                'Pengajuan Check-In Baru',
                "{$booking->user->name} mengajukan check-in kamar {$booking->kamar->room_number}. Silakan periksa data identitas."
            );
            AuditLogService::create(
                'Check-In',
                'Pengajuan check-in dikirim',
                ['booking_id' => $booking->id, 'user_id' => $booking->user_id]
            );
        } catch (\Exception $e) {
            \Log::warning('Identity submit notification/audit failed: '.$e->getMessage());
        }

        return redirect()->route('tenant.checkin.identity', $booking)
            ->with('success', 'Pengajuan check-in berhasil dikirim. Menunggu konfirmasi owner.');
    }
}
