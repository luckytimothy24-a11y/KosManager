<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Dispute::with(['user', 'pembayaran', 'tagihan', 'booking']);

        if ($user->isOwner()) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('pembayaran.penghuni.kos', fn ($qq) => $qq->where('owner_id', $user->id))
                    ->orWhereHas('pembayaran.booking.kos', fn ($qq) => $qq->where('owner_id', $user->id))
                    ->orWhereHas('tagihan.kamar.kos', fn ($qq) => $qq->where('owner_id', $user->id))
                    ->orWhereHas('booking.kos', fn ($qq) => $qq->where('owner_id', $user->id));
            });
        }

        $disputes = $query->latest()->paginate(15);

        return view('owner.dispute.index', compact('disputes'));
    }

    public function show(Dispute $dispute)
    {
        $dispute->load(['user', 'pembayaran', 'tagihan', 'booking']);
        return view('owner.dispute.show', compact('dispute'));
    }

    public function resolve(Request $request, Dispute $dispute)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:in_progress,resolved,closed'],
            'resolution' => ['nullable', 'string', 'max:2000'],
        ]);

        $dispute->update([
            'status' => $validated['status'],
            'resolution' => $validated['resolution'] ?? $dispute->resolution,
            'resolved_by' => $validated['status'] === 'resolved' ? $request->user()->id : $dispute->resolved_by,
            'resolved_at' => $validated['status'] === 'resolved' ? now() : $dispute->resolved_at,
        ]);

        // Notifikasi ke tenant
        try {
            \App\Services\NotificationService::create(
                $dispute->user_id,
                'dispute_update',
                'Dispute Diperbarui',
                "Dispute '{$dispute->title}' status: {$validated['status']}.",
                ['url' => route('tenant.dispute.show', $dispute)],
                "dispute-update:{$dispute->id}"
            );
        } catch (\Exception $e) {
            \Log::warning("Dispute update notification failed: {$e->getMessage()}");
        }

        return back()->with('success', 'Dispute berhasil diperbarui.');
    }
}