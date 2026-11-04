<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\Booking;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $disputes = Dispute::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('tenant.dispute.index', compact('disputes'));
    }

    public function create(Request $request)
    {
        $pembayaranId = $request->query('pembayaran_id');
        $tagihanId = $request->query('tagihan_id');
        $bookingId = $request->query('booking_id');

        $pembayaran = $pembayaranId ? Pembayaran::find($pembayaranId) : null;
        $tagihan = $tagihanId ? Tagihan::find($tagihanId) : null;
        $booking = $bookingId ? Booking::find($bookingId) : null;

        return view('tenant.dispute.create', compact('pembayaran', 'tagihan', 'booking'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:payment,billing,service,other'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'pembayaran_id' => ['nullable', 'exists:pembayarans,id'],
            'tagihan_id' => ['nullable', 'exists:tagihans,id'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
        ]);

        $dispute = Dispute::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'pembayaran_id' => $validated['pembayaran_id'] ?? null,
            'tagihan_id' => $validated['tagihan_id'] ?? null,
            'booking_id' => $validated['booking_id'] ?? null,
            'status' => Dispute::STATUS_OPEN,
        ]);

        // Notifikasi ke owner/admin
        try {
            \App\Services\NotificationService::create(
                1, // admin
                'dispute',
                'Dispute Baru',
                "Tenant {$request->user()->name} membuat dispute: {$dispute->title}",
                ['url' => route('admin.dispute.show', $dispute)],
                "dispute:{$dispute->id}"
            );
        } catch (\Exception $e) {
            \Log::warning("Dispute notification failed: {$e->getMessage()}");
        }

        return redirect()->route('tenant.dispute.index')
            ->with('success', 'Dispute berhasil dibuat. Tim kami akan segera menindaklanjuti.');
    }

    public function show(Dispute $dispute)
    {
        abort_unless($dispute->user_id === auth()->id(), 403);
        return view('tenant.dispute.show', compact('dispute'));
    }
}