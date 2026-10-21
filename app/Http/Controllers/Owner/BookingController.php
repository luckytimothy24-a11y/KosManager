<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCancellationRequest;
use App\Services\AuditLogService;
use App\Services\BookingStatusService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Booking::class);

        $user = $request->user();

        $query = Booking::with(['user', 'kos', 'kamar']);

        if ($user->isOwner()) {
            $query->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id));
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->whereIn('kos_id', $kosIds);
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->paginate(10)->withQueryString();

        return view('owner.booking.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['user', 'kos', 'kamar']);

        $cancellationRequest = $booking->latestCancellationRequest;

        return view('owner.booking.show', compact('booking', 'cancellationRequest'));
    }

    /**
     * Konfirmasi (approve) booking pending.
     * Frontend hanya mengirim ACTION; backend menentukan status final.
     * Tidak mengubah status tenant.
     */
    public function approve(Request $request, Booking $booking, BookingStatusService $statusService)
    {
        $this->authorize('approve', $booking);

        $result = $statusService->approve($booking, $request->user());

        if (! ($result['ok'] ?? false)) {
            $error = $result['error'] ?? 'invalid_transition';
            $message = match ($error) {
                'payment_required' => 'Booking belum dapat dikonfirmasi. Tenant harus memiliki pembayaran yang sudah diverifikasi.',
                default => $this->mapError($error),
            };

            return back()->with('error', $message);
        }

        try {
            NotificationService::bookingApproved(
                $booking->user_id,
                $booking->booking_code,
                $booking->kamar->room_number,
                url('/tenant/booking/'.$booking->id)
            );
            AuditLogService::approve(
                'Booking',
                "Booking {$booking->booking_code} disetujui oleh {$request->user()->name}",
                ['booking_id' => $booking->id, 'user_id' => $booking->user_id]
            );
        } catch (\Exception $e) {
            \Log::warning('Booking approve notification/audit failed: '.$e->getMessage());
        }

        return back()->with('success', 'Booking disetujui dan dikonfirmasi.');
    }

    /**
     * Tolak booking pending.
     */
    public function reject(Request $request, Booking $booking, BookingStatusService $statusService)
    {
        $this->authorize('reject', $booking);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $result = $statusService->reject($booking, $request->user(), trim($data['reason']));

        if (! ($result['ok'] ?? false)) {
            return back()->with('error', $this->mapError($result['error'] ?? 'invalid_transition'));
        }

        try {
            NotificationService::bookingRejected(
                $booking->user_id,
                $booking->booking_code,
                $booking->kamar->room_number,
                trim($data['reason']),
                url('/tenant/booking/'.$booking->id)
            );
            AuditLogService::reject(
                'Booking',
                "Booking {$booking->booking_code} ditolak oleh {$request->user()->name}",
                ['booking_id' => $booking->id, 'user_id' => $booking->user_id, 'reason' => trim($data['reason'])]
            );
        } catch (\Exception $e) {
            \Log::warning('Booking reject notification/audit failed: '.$e->getMessage());
        }

        return back()->with('success', 'Booking ditolak.');
    }

    /**
     * Owner approve cancellation request → booking menjadi cancelled.
     * Atomic transaction di BookingStatusService::approveCancellation.
     */
    public function approveCancellation(Request $request, BookingCancellationRequest $cancellationRequest, BookingStatusService $statusService)
    {
        $this->authorize('approve', $cancellationRequest);

        $result = $statusService->approveCancellation($cancellationRequest, $request->user());

        if (! ($result['ok'] ?? false)) {
            return back()->with('error', $this->mapError($result['error'] ?? 'invalid_transition'));
        }

        $booking = $result['booking'];

        try {
            NotificationService::bookingCancellationApproved(
                $booking->user_id,
                $booking->booking_code,
                $booking->kamar->room_number
            );
            AuditLogService::approve(
                'Booking',
                "Pembatalan booking {$booking->booking_code} disetujui oleh {$request->user()->name}",
                [
                    'booking_id' => $booking->id,
                    'cancellation_request_id' => $cancellationRequest->id,
                ]
            );
        } catch (\Exception $e) {
            \Log::warning('Cancellation approve notification/audit failed: '.$e->getMessage());
        }

        return back()->with('success', 'Pembatalan disetujui. Booking telah dibatalkan.');
    }

    /**
     * Owner reject cancellation request — booking TIDAK berubah.
     */
    public function rejectCancellation(Request $request, BookingCancellationRequest $cancellationRequest, BookingStatusService $statusService)
    {
        $this->authorize('reject', $cancellationRequest);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $result = $statusService->rejectCancellation($cancellationRequest, $request->user(), trim($data['reason']));

        if (! ($result['ok'] ?? false)) {
            return back()->with('error', $this->mapError($result['error'] ?? 'invalid_transition'));
        }

        $booking = $result['booking'];

        try {
            NotificationService::bookingCancellationRejected(
                $booking->user_id,
                $booking->booking_code,
                $booking->kamar->room_number,
                trim($data['reason'])
            );
            AuditLogService::reject(
                'Booking',
                "Pengajuan pembatalan booking {$booking->booking_code} ditolak oleh {$request->user()->name}",
                [
                    'booking_id' => $booking->id,
                    'cancellation_request_id' => $cancellationRequest->id,
                    'reason' => trim($data['reason']),
                ]
            );
        } catch (\Exception $e) {
            \Log::warning('Cancellation reject notification/audit failed: '.$e->getMessage());
        }

        return back()->with('success', 'Pengajuan pembatalan ditolak. Booking tetap berstatus sebelumnya.');
    }

    private function mapError(string $error): string
    {
        return match ($error) {
            'not_found' => 'Booking tidak ditemukan.',
            'invalid_transition' => 'Status booking tidak memungkinkan aksi ini.',
            'request_not_pending' => 'Pengajuan pembatalan sudah diproses sebelumnya.',
            'forbidden' => 'Anda tidak memiliki akses untuk memproses booking ini.',
            default => 'Gagal memproses booking.',
        };
    }
}
