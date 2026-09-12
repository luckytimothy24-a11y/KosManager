<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Support\NotificationType;
use Illuminate\Http\Request;

/**
 * Tenant Check-In: Tenant owns identity data.
 * - show: tenant views current identity status + form if allowed
 * - submit: tenant fills KTP + WA + address -> status pending (owner review), NOT self-confirmed
 * - update: tenant can fix rejected/pending data before owner confirm
 */
class TenantCheckInController extends Controller
{
    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['user', 'kos', 'kamar']);

        abort_unless($booking->status === 'approved', 404, 'Booking tidak dalam tahap check-in.');

        return view('tenant.checkin.identity', compact('booking'));
    }

    /**
     * Tenant submits identity for OWNER review.
     * Validates KTP (NIK) lenient but not empty, WA normalized to 08..., trims all.
     * No KTP in log. Sets submitted_at, clears rejected state, does NOT set confirmed_at.
     */
    public function submit(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);

        abort_unless($booking->status === 'approved', 400, 'Booking tidak dalam tahap check-in.');
        abort_if($booking->identityConfirmed(), 400, 'Identitas sudah dikonfirmasi owner dan tidak dapat diubah.');

        $validated = $request->validate([
            'identity_number' => ['required', 'string', 'min:8', 'max:50', 'regex:/^[0-9 ]+$/'],
            'phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+\-\s\(\)]+$/'],
            'address' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'identity_number.regex' => 'Nomor KTP hanya boleh berisi angka dan spasi.',
            'phone.regex' => 'Format nomor WhatsApp tidak valid.',
        ]);

        $identityNumber = trim((string) $validated['identity_number']);
        $identityNumber = preg_replace('/\s+/', '', $identityNumber);
        $phone = trim((string) $validated['phone']);
        $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);
        // normalize 62 -> 08 if needed (lenient: keep as stored but ensure starts 0 if id number)
        if (str_starts_with($phone, '+62')) {
            $phone = '0'.substr($phone, 3);
        } elseif (str_starts_with($phone, '62') && strlen($phone) >= 10) {
            $phone = '0'.substr($phone, 2);
        }
        $address = trim((string) $validated['address']);

        // extra guard: after trimming, must still meet min
        if (mb_strlen($identityNumber) < 8) {
            return back()->withErrors(['identity_number' => 'Nomor KTP minimal 8 karakter.'])->withInput();
        }
        if (mb_strlen($phone) < 9) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp minimal 9 digit.'])->withInput();
        }
        if (mb_strlen($address) < 5) {
            return back()->withErrors(['address' => 'Alamat minimal 5 karakter.'])->withInput();
        }

        $booking->update([
            'identity_number' => $identityNumber,
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
