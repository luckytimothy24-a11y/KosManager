<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        if ($request->filled('kos_id')) {
            $kamar = Kamar::where('kos_id', $request->kos_id)
                ->where('status', 'available')
                ->get();
        }

        return view('tenant.booking.create', compact('kos', 'kamar'));
    }

    public function store(StoreBookingRequest $request)
    {
        $booking = \DB::transaction(function () use ($request) {
            $kamar = Kamar::whereKey($request->kamar_id)->lockForUpdate()->firstOrFail();

            if ($kamar->status !== 'available' || $kamar->kos->status !== 'active') {
                return null;
            }

            $hasConflict = Booking::where('kamar_id', $kamar->id)
                ->whereIn('status', ['pending', 'approved'])
                ->where('start_date', '<', $request->end_date)
                ->where('end_date', '>', $request->start_date)
                ->exists();

            if ($hasConflict) {
                return 'conflict';
            }

            $price = $request->rental_type === 'daily' ? $kamar->daily_price : $kamar->monthly_price;

            if (! $price) {
                return 'no_price';
            }

            return Booking::create([
                'booking_code' => 'BK-'.strtoupper(Str::random(8)),
                'user_id' => $request->user()->id,
                'kos_id' => $kamar->kos_id,
                'kamar_id' => $kamar->id,
                'booking_date' => now(),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'rental_type' => $request->rental_type,
                'price' => $price,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);
        });

        if ($booking === null) {
            return redirect()->route('tenant.booking.create', ['kos_id' => $request->kos_id])
                ->withErrors(['kamar_id' => 'Kamar tidak tersedia untuk dibooking.'])->withInput();
        }

        if ($booking === 'conflict') {
            return redirect()->route('tenant.booking.create', ['kos_id' => $request->kos_id])
                ->withErrors(['kamar_id' => 'Kamar sudah dibooking pada periode tersebut.'])->withInput();
        }

        if ($booking === 'no_price') {
            return redirect()->route('tenant.booking.create', ['kos_id' => $request->kos_id])
                ->withErrors(['kamar_id' => 'Harga kamar tidak tersedia.'])->withInput();
        }

        try {
            NotificationService::bookingNew($booking->kos->owner_id, $request->user()->name, $booking->kamar->room_number);
            AuditLogService::create('Booking', "Booking baru {$request->user()->name} untuk kamar {$booking->kamar->room_number}", ['kos_id' => $booking->kos_id, 'kamar_id' => $booking->kamar_id]);
        } catch (\Exception $e) {
            \Log::warning('Booking notification/audit failed: '.$e->getMessage());
        }

        return redirect()->route('tenant.booking.index')->with('success', 'Booking berhasil dibuat. Menunggu persetujuan.');
    }

    public function cancel(Booking $booking)
    {
        $this->authorize('cancel', $booking);

        $cancelled = \DB::transaction(function () use ($booking) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->first();

            if (! $locked || ! in_array($locked->status, ['pending', 'approved'])) {
                return false;
            }

            $wasApproved = $locked->status === 'approved';
            $locked->update(['status' => 'cancelled']);

            if ($wasApproved) {
                $kamar = Kamar::whereKey($locked->kamar_id)->lockForUpdate()->first();

                if ($kamar && $kamar->status === 'booked') {
                    $stillReserved = Booking::where('kamar_id', $kamar->id)
                        ->whereKeyNot($locked->id)
                        ->where('status', 'approved')
                        ->whereDate('end_date', '>=', today())
                        ->exists();

                    if (! $stillReserved) {
                        $kamar->update(['status' => 'available']);
                    }
                }
            }

            return true;
        });

        if (! $cancelled) {
            return back()->with('error', 'Booking sudah diproses sebelumnya.');
        }

        AuditLogService::reject('Booking', "Booking {$booking->booking_code} dibatalkan oleh pengguna", ['booking_id' => $booking->id]);

        return redirect()->route('tenant.booking.index')->with('success', 'Booking berhasil dibatalkan.');
    }
}
