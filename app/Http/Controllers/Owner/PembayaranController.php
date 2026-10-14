<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\PaymentDistributionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Pembayaran::with(['tagihan', 'penghuni.user', 'booking.kos', 'booking.user', 'booking.kamar']);

        if ($user->isOwner()) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('penghuni.kos', fn ($qq) => $qq->where('owner_id', $user->id))
                    ->orWhereHas('booking.kos', fn ($qq) => $qq->where('owner_id', $user->id));
            });
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->where(function ($q) use ($kosIds) {
                $q->whereHas('penghuni', fn ($qq) => $qq->whereIn('kos_id', $kosIds))
                    ->orWhereHas('booking', fn ($qq) => $qq->whereIn('kos_id', $kosIds));
            });
        } elseif ($user->isTenant()) {
            // Semua penghuni milik tenant (termasuk yang sudah check-out) agar riwayat tidak hilang.
            // Termasuk pembayaran booking (penghuni_id null, via booking.user_id).
            $penghuniIds = $user->penghunis()->pluck('id');
            $query->where(function ($q) use ($penghuniIds, $user) {
                $q->whereIn('penghuni_id', $penghuniIds)
                    ->orWhereHas('booking', fn ($qq) => $qq->where('user_id', $user->id));
            });
        }

        if ($request->filled('search')) {
            $search = addcslashes($request->search, '%_');
            $query->where('payment_number', 'like', '%'.$search.'%');
        }

        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        $pembayarans = $query->latest()->paginate(10)->withQueryString();

        $pendingQuery = Pembayaran::where('verification_status', Pembayaran::STATUS_PENDING);
        if ($user->isOwner()) {
            $pendingQuery->where(function ($q) use ($user) {
                $q->whereHas('penghuni.kos', fn ($qq) => $qq->where('owner_id', $user->id))
                    ->orWhereHas('booking.kos', fn ($qq) => $qq->where('owner_id', $user->id));
            });
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $pendingQuery->where(function ($q) use ($kosIds) {
                $q->whereHas('penghuni', fn ($qq) => $qq->whereIn('kos_id', $kosIds))
                    ->orWhereHas('booking', fn ($qq) => $qq->whereIn('kos_id', $kosIds));
            });
        } elseif ($user->isTenant()) {
            $penghuniIds = $user->penghunis()->pluck('id');
            $pendingQuery->where(function ($q) use ($penghuniIds, $user) {
                $q->whereIn('penghuni_id', $penghuniIds)
                    ->orWhereHas('booking', fn ($qq) => $qq->where('user_id', $user->id));
            });
        }
        $pendingCount = $pendingQuery->count();

        return view($user->isTenant() ? 'tenant.pembayaran.index' : 'owner.pembayaran.index', compact('pembayarans', 'pendingCount'));
    }

    public function show(Pembayaran $pembayaran)
    {
        $this->authorize('view', $pembayaran);
        $pembayaran->load(['tagihan', 'penghuni.user', 'penghuni.kos', 'penghuni.kamar', 'verifier', 'booking.kos', 'booking.user', 'booking.kamar']);

        $user = request()->user();

        return view($user->isTenant() ? 'tenant.pembayaran.show' : 'owner.pembayaran.show', compact('pembayaran'));
    }

    public function downloadProof(Pembayaran $pembayaran)
    {
        $this->authorize('view', $pembayaran);

        abort_unless($pembayaran->proof_file && \Storage::disk('private')->exists($pembayaran->proof_file), 404);

        return \Storage::disk('private')->download($pembayaran->proof_file);
    }

    /**
     * Verifikasi massal: setiap item melewati guard yang identik dengan
     * verify() satuan (policy, pending-only, nominal, lock). Item gagal
     * dilaporkan per item tanpa menggagalkan yang valid.
     */
    public function bulkVerify(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:50'],
            'ids.*' => ['integer', 'exists:pembayarans,id'],
        ]);

        $ok = 0;
        $failed = [];

        foreach ($validated['ids'] as $id) {
            $pembayaran = Pembayaran::with(['penghuni.user', 'penghuni.kos.owner', 'tagihan', 'booking.kos', 'booking.user'])->find($id);

            if (! $pembayaran || ! $request->user()->can('verify', $pembayaran)) {
                $failed[] = "#{$id}: tidak memiliki akses.";

                continue;
            }

            if ($pembayaran->verification_status !== Pembayaran::STATUS_PENDING
                || round((float) $pembayaran->amount, 2) !== round((float) $pembayaran->expectedAmount(), 2)) {
                $failed[] = "{$pembayaran->payment_number}: sudah diproses atau nominal tidak sesuai.";

                continue;
            }

            $done = \DB::transaction(function () use ($pembayaran) {
                $locked = Pembayaran::whereKey($pembayaran->id)->lockForUpdate()->first();
                if (! $locked || $locked->verification_status !== Pembayaran::STATUS_PENDING) {
                    return false;
                }

                $locked->update([
                    'verification_status' => Pembayaran::STATUS_APPROVED,
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                ]);

                if ($locked->tagihan_id && $locked->tagihan) {
                    $locked->tagihan->update(['status' => Tagihan::STATUS_PAID]);
                }

                // Booking payment → catat distribution (pending; ready setelah booking confirm).
                if ($locked->booking_id) {
                    app(PaymentDistributionService::class)->recordFromApprovedPayment($locked);
                }

                return true;
            });

            if (! $done) {
                $failed[] = "{$pembayaran->payment_number}: sudah diproses sebelumnya.";

                continue;
            }

            $ok++;

            try {
                if ($pembayaran->isBookingPayment() && $pembayaran->booking) {
                    NotificationService::bookingPaymentVerified($pembayaran->booking->user_id, $pembayaran->booking->booking_code);
                    AuditLogService::approve(
                        'Pembayaran',
                        "Pembayaran booking {$pembayaran->booking->booking_code} disetujui (bulk; booking tetap pending)",
                        ['pembayaran_id' => $pembayaran->id, 'booking_id' => $pembayaran->booking_id]
                    );
                } else {
                    NotificationService::paymentVerified($pembayaran->penghuni->user_id, $pembayaran->tagihan->bill_number);
                    AuditLogService::approve('Pembayaran', "Pembayaran {$pembayaran->payment_number} disetujui (bulk)", ['pembayaran_id' => $pembayaran->id]);
                }
            } catch (\Exception $e) {
                \Log::warning('Bulk payment verification notification/audit failed: '.$e->getMessage());
            }
        }

        $prefix = $request->user()->isAdmin() ? 'admin' : 'owner';
        $message = "{$ok} pembayaran berhasil diverifikasi.";
        if (! empty($failed)) {
            $message .= ' Gagal: '.implode(' ', array_slice($failed, 0, 3));
            if (count($failed) > 3) {
                $message .= ' (+'.(count($failed) - 3).' lainnya)';
            }

            return redirect()->route("$prefix.pembayaran.index")->with('warning', $message);
        }

        return redirect()->route("$prefix.pembayaran.index")->with('success', $message);
    }

    public function verify(Pembayaran $pembayaran)
    {
        $this->authorize('verify', $pembayaran);
        $pembayaran->load(['penghuni.user', 'penghuni.kos.owner', 'tagihan', 'booking.kos', 'booking.user']);
        abort_unless($pembayaran->verification_status === Pembayaran::STATUS_PENDING, 400, 'Pembayaran sudah diverifikasi sebelumnya.');
        abort_if(
            round((float) $pembayaran->amount, 2) !== round((float) $pembayaran->expectedAmount(), 2),
            400,
            $pembayaran->isBookingPayment()
                ? 'Nominal pembayaran tidak sesuai harga booking.'
                : 'Nominal pembayaran tidak sesuai total tagihan.'
        );

        \DB::transaction(function () use ($pembayaran) {
            $locked = Pembayaran::whereKey($pembayaran->id)->lockForUpdate()->first();
            abort_unless($locked && $locked->verification_status === Pembayaran::STATUS_PENDING, 400, 'Pembayaran sudah diverifikasi sebelumnya.');

            $locked->update([
                'verification_status' => Pembayaran::STATUS_APPROVED,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            // Payment approved ≠ booking approved. Booking tetap pending sampai owner confirm.
            if ($locked->tagihan_id) {
                $locked->tagihan->update(['status' => Tagihan::STATUS_PAID]);
            }

            // Booking payment → catat distribution (status pending; ready setelah booking confirm).
            if ($locked->booking_id) {
                app(PaymentDistributionService::class)->recordFromApprovedPayment($locked);
            }
        });

        try {
            if ($pembayaran->isBookingPayment()) {
                NotificationService::bookingPaymentVerified($pembayaran->booking->user_id, $pembayaran->booking->booking_code);
                AuditLogService::approve(
                    'Pembayaran',
                    "Pembayaran booking {$pembayaran->booking->booking_code} disetujui (booking tetap pending)",
                    ['pembayaran_id' => $pembayaran->id, 'booking_id' => $pembayaran->booking_id]
                );
            } else {
                NotificationService::paymentVerified($pembayaran->penghuni->user_id, $pembayaran->tagihan->bill_number);
                AuditLogService::approve('Pembayaran', "Pembayaran {$pembayaran->payment_number} disetujui", ['pembayaran_id' => $pembayaran->id]);

                if ($pembayaran->penghuni?->kos?->owner && auth()->id() !== $pembayaran->penghuni->kos->owner->id) {
                    NotificationService::paymentVerified(
                        $pembayaran->penghuni->kos->owner->id,
                        $pembayaran->tagihan->bill_number
                    );
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Payment verification notification/audit failed: '.$e->getMessage());
        }

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.pembayaran.index")->with('success', 'Pembayaran berhasil diverifikasi. Booking belum dikonfirmasi otomatis.');
    }

    public function reject(Request $request, Pembayaran $pembayaran)
    {
        $this->authorize('verify', $pembayaran);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        abort_unless($pembayaran->verification_status === Pembayaran::STATUS_PENDING, 400, 'Pembayaran sudah diverifikasi sebelumnya.');

        \DB::transaction(function () use ($validated, $pembayaran) {
            $locked = Pembayaran::whereKey($pembayaran->id)->lockForUpdate()->first();
            abort_unless($locked && $locked->verification_status === Pembayaran::STATUS_PENDING, 400, 'Pembayaran sudah diverifikasi sebelumnya.');

            $locked->update([
                'verification_status' => Pembayaran::STATUS_REJECTED,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'admin_notes' => $validated['reason'],
                'active_payment_key' => null,
            ]);

            // Payment rejected ≠ booking rejected. Booking tetap pending.
            if ($locked->tagihan_id && $locked->tagihan && $locked->tagihan->status === Tagihan::STATUS_PAYMENT_PENDING) {
                $restoredStatus = $locked->tagihan->due_date && $locked->tagihan->due_date->startOfDay()->lt(now()->startOfDay())
                    ? Tagihan::STATUS_OVERDUE
                    : Tagihan::STATUS_UNPAID;
                $locked->tagihan->update(['status' => $restoredStatus]);
            }
        });

        try {
            if ($pembayaran->isBookingPayment()) {
                AuditLogService::reject(
                    'Pembayaran',
                    "Pembayaran booking {$pembayaran->booking->booking_code} ditolak (booking tetap pending)",
                    ['pembayaran_id' => $pembayaran->id, 'booking_id' => $pembayaran->booking_id, 'reason' => $validated['reason']]
                );
                NotificationService::paymentRejected($pembayaran->booking->user_id, $pembayaran->booking->booking_code);
            } else {
                AuditLogService::reject('Pembayaran', "Pembayaran {$pembayaran->payment_number} ditolak", ['pembayaran_id' => $pembayaran->id]);
                NotificationService::paymentRejected($pembayaran->penghuni->user_id, $pembayaran->tagihan->bill_number);
            }
        } catch (\Exception $e) {
            \Log::warning('Payment rejection notification/audit failed: '.$e->getMessage());
        }

        $prefix = auth()->user()->isAdmin() ? 'admin' : 'owner';

        return redirect()->route("$prefix.pembayaran.index")->with('success', 'Pembayaran ditolak. Alasan: '.$validated['reason']);
    }

    public function receipt(Pembayaran $pembayaran)
    {
        $this->authorize('view', $pembayaran);
        abort_unless($pembayaran->verification_status === 'approved', 404, 'Kuitansi hanya tersedia untuk pembayaran yang sudah diverifikasi.');

        $pembayaran->load(['tagihan.kamar.kos', 'penghuni.user', 'verifier', 'booking.kos', 'booking.user', 'booking.kamar']);

        $pdf = Pdf::loadView('exports.pdf.kuitansi', compact('pembayaran'))
            ->setPaper('a5', 'portrait');

        return $pdf->download("kuitansi-{$pembayaran->payment_number}.pdf");
    }
}
