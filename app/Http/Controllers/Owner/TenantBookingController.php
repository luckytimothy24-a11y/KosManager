<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Services\AuditLogService;
use App\Services\BookingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class TenantBookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with(['kos', 'kamar'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('tenant.booking.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $kos = Kos::where('status', 'active')->get();
        $kamar = collect();
        $selectedKos = null;

        if ($request->filled('kos_id')) {
            $selectedKos = $kos->firstWhere('id', (int) $request->kos_id);
            $kamar = Kamar::where('kos_id', $request->kos_id)
                ->where('status', 'available')
                ->get();
        }

        return view('tenant.booking.create', compact('kos', 'kamar', 'selectedKos'));
    }

    public function store(StoreBookingRequest $request, BookingService $bookingService)
    {
        $result = $bookingService->create([
            'kamar_id' => $request->kamar_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'rental_type' => $request->rental_type,
            'notes' => $request->notes,
        ], $request->user()->id);

        if (! $result['ok']) {
            $message = match ($result['error']) {
                'conflict' => 'Kamar sudah dibooking pada periode tersebut.',
                'no_price' => 'Harga kamar tidak tersedia.',
                default => 'Kamar tidak tersedia untuk dibooking.',
            };

            return redirect()->route('tenant.booking.create', ['kos_id' => $request->kos_id])
                ->withErrors(['kamar_id' => $message])->withInput();
        }

        return redirect()->route('tenant.booking.success', $result['booking'])
            ->with('success', 'Booking berhasil! Kamar telah dipesan untuk Anda.');
    }

    public function success(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['user', 'kos', 'kamar']);

        return view('tenant.booking.success', compact('booking'));
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['user', 'kos', 'kamar']);

        $hasCheckOut = false;
        $kontrak = null;
        $tagihan = null;

        if ($booking->status === 'completed') {
            $penghuni = Penghuni::where('user_id', $booking->user_id)
                ->where('kamar_id', $booking->kamar_id)
                ->latest()
                ->first();
            $hasCheckOut = $penghuni
                ? CheckOut::where('penghuni_id', $penghuni->id)->where('status', 'approved')->exists()
                : false;

            // Jembatan kontrak & tagihan untuk booking yang sudah selesai (G3).
            $bridgePenghuni = Penghuni::where('user_id', $booking->user_id)
                ->where('kamar_id', $booking->kamar_id)
                ->first();

            if ($bridgePenghuni) {
                $kontrak = $bridgePenghuni->kontraks()->latest()->first();
                $tagihan = $kontrak ? $kontrak->tagihans()->latest()->first() : null;
            }
        }

        return view('tenant.booking.show', compact('booking', 'hasCheckOut', 'kontrak', 'tagihan'));
    }

    public function cancel(Booking $booking, BookingService $bookingService)
    {
        $this->authorize('cancel', $booking);

        $cancelled = $bookingService->cancel($booking->id);

        if (! $cancelled) {
            return back()->with('error', 'Booking sudah diproses sebelumnya.');
        }

        try {
            AuditLogService::reject('Booking', "Booking {$booking->booking_code} dibatalkan oleh pengguna", ['booking_id' => $booking->id]);

            NotificationService::bookingCancelledByTenant($booking->kos->owner_id, $booking->user->name, $booking->booking_code);
        } catch (\Exception $e) {
            \Log::warning('Booking cancel notification/audit failed: '.$e->getMessage());
        }

        return redirect()->route('tenant.booking.index')->with('success', 'Booking berhasil dibatalkan.');
    }
}
