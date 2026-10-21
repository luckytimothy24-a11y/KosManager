<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Services\AuditLogService;
use App\Services\BookingService;
use App\Services\BookingStatusService;
use App\Services\NotificationService;
use App\Support\StatusLabels;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
            $allForKos = Kamar::where('kos_id', $request->kos_id)
                ->where('status', '!=', 'maintenance')
                ->get();
            // Tampilkan kamar available (status) + shared occupied yang masih ada slot.
            // Untuk kompatibilitas data lama/tests, kamar dengan status available tetap tampil
            // meski occupancy penuh secara teknis (data setup artificial).
            $kamar = $allForKos->filter(function (Kamar $k) {
                if ($k->status === 'available') {
                    return true;
                }
                if (($k->billing_mode ?? 'personal') === 'shared' && $k->hasAvailableSlot()) {
                    return true;
                }

                return false;
            })->values();
        }

        // Default tanggal mulai hanya nilai awal form — tenant dapat mengubah.
        // monthly → bulan depan; weekly → +7 hari; daily → hari ini.
        $rentalType = $request->input('rental_type', old('rental_type', 'monthly'));
        if (! in_array($rentalType, ['daily', 'weekly', 'monthly'], true)) {
            $rentalType = 'monthly';
        }

        $defaultStartDate = old(
            'start_date',
            $request->input('start_date', StatusLabels::defaultBookingStartDate($rentalType))
        );
        // Validasi sederhana format tanggal dari request (opsional)
        if (! Carbon::hasFormat($defaultStartDate, 'Y-m-d')) {
            $defaultStartDate = StatusLabels::defaultBookingStartDate($rentalType);
        }
        $defaultEndDate = old(
            'end_date',
            StatusLabels::defaultBookingEndDate($defaultStartDate, $rentalType)
        );
        if (! Carbon::hasFormat($defaultEndDate, 'Y-m-d')) {
            $defaultEndDate = StatusLabels::defaultBookingEndDate($defaultStartDate, $rentalType);
        }

        return view('tenant.booking.create', compact(
            'kos',
            'kamar',
            'selectedKos',
            'rentalType',
            'defaultStartDate',
            'defaultEndDate'
        ));
    }

    public function store(StoreBookingRequest $request, BookingService $bookingService)
    {
        $result = $bookingService->create([
            'kamar_id' => $request->kamar_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'rental_type' => $request->rental_type,
            'billing_mode' => 'personal',
            'requested_occupants' => 1,
            'notes' => $request->notes,
            'identity_number' => $request->identity_number,
            'phone' => $request->phone,
            'address' => $request->address,
        ], $request->user()->id);

        if (! $result['ok']) {
            $message = match ($result['error']) {
                'conflict' => 'Kamar sudah dibooking pada periode tersebut.',
                'overlap' => 'Kamar sudah dibooking pada periode tersebut.',
                'capacity_exceeded' => 'Jumlah penghuni melebihi kapasitas kamar.',
                'kamar_penuh' => 'Kamar sudah penuh. Kapasitas tidak mencukupi.',
                'already_occupant' => 'Anda sudah menjadi penghuni aktif di kamar ini.',
                'no_price' => 'Harga kamar tidak tersedia.',
                'billing_mode_not_allowed' => 'Kamar ini tidak mengizinkan shared billing.',
                'personal_multi_rejected' => 'Booking personal hanya dapat untuk 1 orang.',
                default => 'Kamar tidak tersedia untuk dibooking.',
            };

            $field = match ($result['error']) {
                'billing_mode_not_allowed' => 'billing_mode',
                'capacity_exceeded', 'personal_multi_rejected' => 'requested_occupants',
                default => 'kamar_id',
            };

            return redirect()->route('tenant.booking.create', ['kos_id' => $request->kos_id])
                ->withErrors([$field => $message])->withInput();
        }

        return redirect(route('tenant.booking.show', $result['booking']).'#pembayaran')
            ->with('success', 'Booking dibuat. Silakan lakukan pembayaran untuk melanjutkan.');
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

        // Source of truth payment: pembayarans.verification_status
        $bookingPayment = Pembayaran::where('booking_id', $booking->id)
            ->whereIn('verification_status', ['pending', 'approved', 'rejected'])
            ->orderByDesc('id')
            ->first();

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

        return view('tenant.booking.show', compact('booking', 'hasCheckOut', 'kontrak', 'tagihan', 'bookingPayment'));
    }

    /**
     * Tenant request cancellation.
     * booking.status TIDAK berubah — hanya membuat cancellation request pending.
     */
    public function cancel(Booking $booking, Request $request, BookingStatusService $statusService)
    {
        $this->authorize('cancel', $booking);

        // Hanya field yang diizinkan dari tenant.
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $statusService->requestCancellation(
            $booking,
            $request->user(),
            $data['reason'] ?? null
        );

        if (! ($result['ok'] ?? false)) {
            $error = match ($result['error'] ?? '') {
                'already_requested' => 'Pengajuan pembatalan sudah menunggu review pemilik kos.',
                'not_cancellable' => 'Booking ini tidak dapat dibatalkan.',
                'forbidden' => 'Anda tidak memiliki akses untuk membatalkan booking ini.',
                default => 'Gagal mengajukan pembatalan booking.',
            };

            return back()->with('error', $error);
        }

        try {
            AuditLogService::create(
                'Booking',
                "Tenant mengajukan pembatalan booking {$booking->booking_code} (menunggu review owner)",
                [
                    'booking_id' => $booking->id,
                    'cancellation_request_id' => $result['request']->id,
                    'booking_status' => $booking->fresh()->status,
                ]
            );

            NotificationService::bookingCancellationRequested(
                $booking->kos->owner_id,
                $booking->user->name,
                $booking->booking_code
            );
        } catch (\Exception $e) {
            \Log::warning('Booking cancellation request notification/audit failed: '.$e->getMessage());
        }

        return redirect()->route('tenant.booking.index')
            ->with('success', 'Pengajuan pembatalan terkirim. Menunggu konfirmasi pemilik kos.');
    }
}
