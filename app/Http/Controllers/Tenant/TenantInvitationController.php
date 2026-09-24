<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BookingOccupant;
use Illuminate\Http\Request;

class TenantInvitationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->isTenant(), 403);

        // Auto-expire stale invites for this user (minimal, no migration)
        BookingOccupant::where(function ($q) use ($user) {
            $q->whereRaw('LOWER(email) = ?', [strtolower($user->email)])
              ->orWhere('user_id', $user->id);
        })
            ->where('status', BookingOccupant::STATUS_INVITED)
            ->whereNotNull('invite_expires_at')
            ->where('invite_expires_at', '<', now())
            ->update([
                'status' => BookingOccupant::STATUS_EXPIRED,
                'invite_token_hash' => null,
                'invite_expires_at' => null,
            ]);

        $all = BookingOccupant::with(['booking.kos', 'booking.kamar', 'booking.user'])
            ->where(function ($q) use ($user) {
                $q->whereRaw('LOWER(email) = ?', [strtolower($user->email)])
                  ->orWhere('user_id', $user->id);
            })
            ->orderByRaw("CASE status WHEN '".BookingOccupant::STATUS_INVITED."' THEN 0 WHEN '".BookingOccupant::STATUS_ACCEPTED."' THEN 1 WHEN '".BookingOccupant::STATUS_DECLINED."' THEN 2 WHEN '".BookingOccupant::STATUS_EXPIRED."' THEN 3 WHEN '".BookingOccupant::STATUS_CANCELLED."' THEN 4 ELSE 5 END")
            ->latest('updated_at')
            ->get();

        $pending = $all->where('status', BookingOccupant::STATUS_INVITED)->values();
        $others = $all->whereNotIn('status', [BookingOccupant::STATUS_INVITED])->values();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'pending' => $pending,
                    'others' => $others,
                ],
            ]);
        }

        return view('tenant.invitations.index', [
            'pending' => $pending,
            'others' => $others,
            'all' => $all,
        ]);
    }

    public function acceptById(Request $request, BookingOccupant $occupant)
    {
        $user = $request->user();
        abort_unless($user && $user->isTenant(), 403);
        abort_unless($occupant->email && (strtolower($occupant->email) === strtolower($user->email) || (int) $occupant->user_id === (int) $user->id), 403, 'Bukan undangan milikmu.');

        if ($occupant->status !== BookingOccupant::STATUS_INVITED) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Undangan tidak dapat diterima.'], 422)
                : redirect()->route('tenant.invitations.index')->with('error', 'Undangan tidak dapat diterima.');
        }
        if ($occupant->isInviteExpired()) {
            $occupant->update(['status' => BookingOccupant::STATUS_EXPIRED, 'invite_token_hash' => null, 'invite_expires_at' => null]);
            return $request->expectsJson()
                ? response()->json(['message' => 'Undangan sudah kedaluwarsa.'], 410)
                : redirect()->route('tenant.invitations.index')->with('error', 'Undangan sudah kedaluwarsa.');
        }
        if (! $occupant->email || strtolower($occupant->email) !== strtolower($user->email)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Email akun tidak cocok dengan undangan.'], 403)
                : redirect()->route('tenant.invitations.index')->with('error', 'Email tidak cocok.');
        }

        $existing = BookingOccupant::where('booking_id', $occupant->booking_id)
            ->where('user_id', $user->id)
            ->where('status', BookingOccupant::STATUS_ACCEPTED)
            ->exists();
        if ($existing) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Kamu sudah menjadi anggota booking ini.'], 422)
                : redirect()->route('tenant.invitations.index')->with('error', 'Kamu sudah menjadi anggota booking ini.');
        }

        $occupant->update([
            'user_id' => $user->id,
            'status' => BookingOccupant::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Undangan diterima.', 'data' => ['id' => $occupant->id, 'status' => $occupant->status]]);
        }
        return redirect()->route('tenant.invitations.index')->with('success', 'Undangan diterima. Silakan lengkapi identitas.');
    }

    public function declineById(Request $request, BookingOccupant $occupant)
    {
        $user = $request->user();
        abort_unless($user && $user->isTenant(), 403);
        abort_unless($occupant->email && (strtolower($occupant->email) === strtolower($user->email) || (int) $occupant->user_id === (int) $user->id), 403, 'Bukan undangan milikmu.');

        if ($occupant->status !== BookingOccupant::STATUS_INVITED) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Undangan tidak dapat ditolak.'], 422)
                : redirect()->route('tenant.invitations.index')->with('error', 'Undangan tidak dapat ditolak.');
        }
        if ($occupant->isInviteExpired()) {
            $occupant->update(['status' => BookingOccupant::STATUS_EXPIRED, 'invite_token_hash' => null, 'invite_expires_at' => null]);
            return $request->expectsJson()
                ? response()->json(['message' => 'Undangan sudah kedaluwarsa.'], 410)
                : redirect()->route('tenant.invitations.index')->with('error', 'Undangan sudah kedaluwarsa.');
        }
        if (! $occupant->email || strtolower($occupant->email) !== strtolower($user->email)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Email akun tidak cocok.'], 403)
                : redirect()->route('tenant.invitations.index')->with('error', 'Email tidak cocok.');
        }

        $occupant->update([
            'status' => BookingOccupant::STATUS_DECLINED,
            'declined_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Undangan ditolak.']);
        }
        return redirect()->route('tenant.invitations.index')->with('success', 'Undangan ditolak.');
    }

    public function show(Request $request, string $token)
    {
        $occupant = $this->findByToken($token);
        abort_if(! $occupant, 404, 'Undangan tidak ditemukan.');

        // Expired handling: mark as expired if past
        if ($occupant->status === BookingOccupant::STATUS_INVITED && $occupant->isInviteExpired()) {
            $occupant->update([
                'status' => BookingOccupant::STATUS_EXPIRED,
                'invite_token_hash' => null,
                'invite_expires_at' => null,
            ]);
            abort(410, 'Undangan sudah kedaluwarsa.');
        }

        if ($occupant->status === BookingOccupant::STATUS_EXPIRED) {
            abort(410, 'Undangan sudah kedaluwarsa.');
        }

        // If not invited (already accepted/declined/cancelled) allow view but show status
        $booking = $occupant->booking()->with(['kos', 'kamar'])->first();

        // For show we don't require auth to preview, but if authenticated we can check email mismatch hint
        $emailMismatch = false;
        if ($request->user() && $occupant->email) {
            $emailMismatch = strtolower($request->user()->email) !== strtolower($occupant->email);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'id' => $occupant->id,
                    'status' => $occupant->status,
                    'email' => $occupant->email,
                    'booking' => [
                        'id' => $booking->id,
                        'kode' => $booking->booking_code,
                        'kos' => $booking->kos->name,
                        'kamar' => $booking->kamar->room_number,
                        'requested_occupants' => (int) ($booking->requested_occupants ?? 1),
                    ],
                    'email_mismatch' => $emailMismatch,
                ],
            ]);
        }

        return view('tenant.invitations.show', compact('occupant', 'booking', 'emailMismatch', 'token'));
    }

    public function accept(Request $request, string $token)
    {
        $user = $request->user();
        abort_unless($user && $user->isTenant(), 403);
        $occupant = $this->findByToken($token);
        abort_if(! $occupant, 404);

        if ($occupant->status !== BookingOccupant::STATUS_INVITED) {
            return response()->json(['message' => 'Undangan tidak dapat diterima.'], 422);
        }
        if ($occupant->isInviteExpired()) {
            $occupant->update(['status' => BookingOccupant::STATUS_EXPIRED, 'invite_token_hash' => null, 'invite_expires_at' => null]);
            return response()->json(['message' => 'Undangan sudah kedaluwarsa.'], 410);
        }
        if (! $occupant->email || strtolower($occupant->email) !== strtolower($user->email)) {
            return response()->json(['message' => 'Email akun tidak cocok dengan undangan.'], 403);
        }

        // Prevent duplicate membership for same user in same booking
        $existing = BookingOccupant::where('booking_id', $occupant->booking_id)
            ->where('user_id', $user->id)
            ->where('status', BookingOccupant::STATUS_ACCEPTED)
            ->exists();
        if ($existing) {
            return response()->json(['message' => 'Kamu sudah menjadi anggota booking ini.'], 422);
        }

        $occupant->update([
            'user_id' => $user->id,
            'status' => BookingOccupant::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Undangan diterima.', 'data' => ['id' => $occupant->id, 'status' => $occupant->status]]);
        }
        return redirect()->route('tenant.booking.show', $occupant->booking_id)->with('success', 'Undangan diterima. Silakan lengkapi identitas.');
    }

    public function decline(Request $request, string $token)
    {
        $user = $request->user();
        abort_unless($user && $user->isTenant(), 403);
        $occupant = $this->findByToken($token);
        abort_if(! $occupant, 404);
        if ($occupant->status !== BookingOccupant::STATUS_INVITED) {
            return response()->json(['message' => 'Undangan tidak dapat ditolak.'], 422);
        }
        if ($occupant->isInviteExpired()) {
            $occupant->update(['status' => BookingOccupant::STATUS_EXPIRED, 'invite_token_hash' => null, 'invite_expires_at' => null]);
            return response()->json(['message' => 'Undangan sudah kedaluwarsa.'], 410);
        }
        if (! $occupant->email || strtolower($occupant->email) !== strtolower($user->email)) {
            return response()->json(['message' => 'Email akun tidak cocok.'], 403);
        }

        $occupant->update([
            'status' => BookingOccupant::STATUS_DECLINED,
            'declined_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Undangan ditolak.']);
        }
        return redirect()->route('tenant.booking.index')->with('success', 'Undangan ditolak.');
    }

    private function findByToken(string $token): ?BookingOccupant
    {
        $hash = hash('sha256', $token);
        return BookingOccupant::where('invite_token_hash', $hash)->first();
    }
}
