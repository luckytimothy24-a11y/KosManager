<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

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

    /**
     * Patungan/group booking DIHAPUS — 1 kamar untuk 1 penghuni.
     */
    public function invite(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);
        abort(403, 'Patungan tidak lagi didukung. Satu kamar hanya untuk satu penghuni.');
    }

    public function cancel(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);
        abort(403, 'Patungan tidak lagi didukung. Satu kamar hanya untuk satu penghuni.');
    }
}
