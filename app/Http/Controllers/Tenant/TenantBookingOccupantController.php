<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingOccupant;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantBookingOccupantController extends Controller
{
    public function index(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['occupants.user', 'kamar']);

        return response()->json([
            'data' => $booking->occupants->map(fn ($o) => [
                'id' => $o->id,
                'occupant_index' => $o->occupant_index,
                'email' => $o->email,
                'display_name' => $o->display_name,
                'status' => $o->status,
                'identity_status' => $o->identity_status,
                'user_id' => $o->user_id,
            ]),
        ]);
    }

    public function invite(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);
        abort_unless((int) $booking->user_id === (int) $request->user()->id, 403, 'Hanya pemilik booking yang dapat mengundang.');

        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $email = strtolower(trim($data['email']));
        abort_if(strtolower($request->user()->email) === $email, 422, 'Tidak dapat mengundang diri sendiri.');
        abort_if($booking->occupants()->whereRaw('LOWER(email) = ?', [$email])->exists(), 422, 'Email sudah diundang pada booking ini.');
        abort_if($booking->occupants()->where('user_id', $request->user()->id)->where('occupant_index', '!=', 1)->exists(), 422, 'Duplicate.');

        $requested = max(1, (int) ($booking->requested_occupants ?? 1));
        $currentCount = $booking->occupants()->count();
        // Ensure primary occupant exists
        if ($currentCount === 0) {
            // Auto-create primary if missing (backward compat)
            BookingOccupant::create([
                'booking_id' => $booking->id,
                'occupant_index' => 1,
                'user_id' => $booking->user_id,
                'email' => strtolower($request->user()->email),
                'display_name' => $request->user()->name,
                'status' => BookingOccupant::STATUS_ACCEPTED,
                'identity_status' => BookingOccupant::IDENTITY_NOT_SUBMITTED,
            ]);
            $currentCount = 1;
        }
        abort_if($currentCount >= $requested, 422, 'Jumlah anggota sudah mencapai batas yang dipesan ('.$requested.' orang).');

        // Determine next index
        $maxIndex = (int) $booking->occupants()->max('occupant_index');
        $nextIndex = $maxIndex + 1;
        if ($nextIndex <= 1) $nextIndex = 2;

        $rawToken = bin2hex(random_bytes(32));
        $hash = hash('sha256', $rawToken);

        $occupant = BookingOccupant::create([
            'booking_id' => $booking->id,
            'occupant_index' => $nextIndex,
            'email' => $email,
            'display_name' => null,
            'status' => BookingOccupant::STATUS_INVITED,
            'invite_token_hash' => $hash,
            'invite_expires_at' => now()->addDays(7),
            'identity_status' => BookingOccupant::IDENTITY_NOT_SUBMITTED,
        ]);

        // Try to bind user_id if email exists
        $existingUser = \App\Models\User::whereRaw('LOWER(email) = ?', [$email])->first();
        if ($existingUser) {
            $occupant->update(['user_id' => $existingUser->id]);
            try {
                NotificationService::create(
                    $existingUser->id,
                    \App\Support\NotificationType::BOOKING,
                    'Ajakan Tinggal Bersama',
                    $request->user()->name.' mengajak kamu tinggal bersama di '.$booking->kos->name.' Kamar '.$booking->kamar->room_number.'. Buka undangan untuk menerima.',
                    ['url' => url('/tenant/invitations/'.$rawToken)]
                );
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'data' => [
                'id' => $occupant->id,
                'email' => $occupant->email,
                'status' => $occupant->status,
                'invite_token' => $rawToken,
                'invite_url' => url('/tenant/invitations/'.$rawToken),
            ],
            'message' => 'Undangan berhasil dibuat.',
        ], 201);
    }

    public function cancel(Request $request, Booking $booking, BookingOccupant $occupant)
    {
        $this->authorize('view', $booking);
        abort_unless((int) $booking->user_id === (int) $request->user()->id, 403);
        abort_unless((int) $occupant->booking_id === (int) $booking->id, 404);
        abort_unless($occupant->occupant_index !== 1, 422, 'Primary tidak dapat dibatalkan.');
        abort_unless($occupant->status === BookingOccupant::STATUS_INVITED, 422, 'Hanya undangan pending yang dapat dibatalkan.');

        $occupant->update([
            'status' => BookingOccupant::STATUS_CANCELLED,
            'invite_token_hash' => null,
            'invite_expires_at' => null,
        ]);

        return response()->json(['message' => 'Undangan dibatalkan.']);
    }
}
