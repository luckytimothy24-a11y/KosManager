<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BookingOccupant;
use Illuminate\Http\Request;

class BookingOccupantIdentityController extends Controller
{
    public function show(Request $request, string $token)
    {
        $occupant = $this->findByToken($token);
        abort_if(! $occupant, 404);
        abort_unless($request->user() && $request->user()->isTenant(), 403);
        abort_unless($occupant->user_id && (int) $occupant->user_id === (int) $request->user()->id, 403, 'Hanya pemilik undangan yang dapat mengisi identitas.');
        abort_unless($occupant->status === BookingOccupant::STATUS_ACCEPTED, 422, 'Undangan harus diterima terlebih dahulu.');

        if ($request->expectsJson()) {
            return response()->json(['data' => [
                'id' => $occupant->id,
                'identity_status' => $occupant->identity_status,
                'identity_number' => $occupant->maskedIdentityNumber(),
            ]]);
        }
        return view('tenant.invitations.identity', compact('occupant', 'token'));
    }

    public function submit(Request $request, string $token)
    {
        $occupant = $this->findByToken($token);
        abort_if(! $occupant, 404);
        abort_unless($request->user() && $request->user()->isTenant(), 403);
        abort_unless($occupant->user_id && (int) $occupant->user_id === (int) $request->user()->id, 403);
        abort_unless($occupant->status === BookingOccupant::STATUS_ACCEPTED, 422);

        $data = $request->validate([
            'identity_number' => ['required', 'string', 'min:8', 'max:50'],
            'phone' => ['required', 'string', 'min:9', 'max:30'],
            'address' => ['required', 'string', 'min:5'],
        ]);

        $phone = $this->normalizePhone($data['phone']);

        $occupant->update([
            'identity_number' => trim($data['identity_number']),
            'phone' => $phone,
            'address' => trim($data['address']),
            'identity_data_submitted_at' => now(),
            'identity_status' => BookingOccupant::IDENTITY_PENDING,
            'identity_rejected_at' => null,
            'identity_rejection_reason' => null,
            'identity_confirmed_at' => null,
            'identity_confirmed_by' => null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Identitas berhasil dikirim. Menunggu konfirmasi owner.']);
        }
        return redirect()->route('tenant.booking.show', $occupant->booking_id)->with('success', 'Identitas berhasil dikirim.');
    }

    private function normalizePhone(string $phone): string
    {
        $p = preg_replace('/[^0-9+]/', '', trim($phone));
        if (str_starts_with($p, '+62')) $p = '0'.substr($p, 3);
        elseif (str_starts_with($p, '62')) $p = '0'.substr($p, 2);
        return $p;
    }

    private function findByToken(string $token): ?BookingOccupant
    {
        $hash = hash('sha256', $token);
        return BookingOccupant::where('invite_token_hash', $hash)->first()
            ?? BookingOccupant::where('id', $token)->first(); // fallback for accepted (token nulled) via id in route? not used
    }

    private function findByTokenStrict(string $token): ?BookingOccupant
    {
        $hash = hash('sha256', $token);
        return BookingOccupant::where('invite_token_hash', $hash)->first();
    }
}
