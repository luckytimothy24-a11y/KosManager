<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\OwnerBankAccount;
use App\Models\PaymentDistribution;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\PaymentDistributionService;
use Illuminate\Http\Request;

/**
 * Owner melihat distribusi pembayaran booking miliknya.
 * Status distribution ≠ payment verification ≠ booking status.
 */
class OwnerPaymentDistributionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', PaymentDistribution::class);

        $user = $request->user();

        $query = PaymentDistribution::with(['booking.kos', 'booking.user', 'pembayaran', 'owner', 'bankAccount']);

        if ($user->isOwner()) {
            $query->where('owner_id', $user->id);
        } elseif ($user->isAdmin()) {
            $kosIds = $user->assignedKos()->pluck('kos.id');
            $query->whereHas('booking', fn ($q) => $q->whereIn('kos_id', $kosIds));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $distributions = $query->latest()->paginate(10)->withQueryString();

        // Ringkasan hanya untuk owner (scope sendiri)
        $summary = null;
        if ($user->isOwner()) {
            $base = PaymentDistribution::where('owner_id', $user->id);
            $summary = [
                'total_gross' => (clone $base)->sum('gross_amount'),
                'total_owner' => (clone $base)->where('status', 'processed')->sum('owner_amount'),
                'waiting' => (clone $base)->whereIn('status', ['pending', 'ready'])->sum('owner_amount'),
                'processed' => (clone $base)->where('status', 'processed')->sum('owner_amount'),
                'count_waiting' => (clone $base)->whereIn('status', ['pending', 'ready'])->count(),
                'count_processed' => (clone $base)->where('status', 'processed')->count(),
            ];
        }

        $bankAccount = $user->isOwner()
            ? OwnerBankAccount::where('owner_id', $user->id)->first()
            : null;

        return view('owner.distribution.index', compact('distributions', 'summary', 'bankAccount'));
    }

    /**
     * Owner menandai distribution sudah dicairkan (manual transfer).
     * BUKAN otomatis dari payment approved.
     */
    public function markProcessed(Request $request, PaymentDistribution $distribution, PaymentDistributionService $service)
    {
        $this->authorize('markProcessed', $distribution);

        $result = $service->markProcessed($distribution, $request->user());

        if (! ($result['ok'] ?? false)) {
            $error = match ($result['error'] ?? '') {
                'not_ready' => 'Distribution belum siap diproses (booking belum dikonfirmasi atau status tidak valid).',
                'forbidden' => 'Anda tidak memiliki akses untuk distribution ini.',
                default => 'Gagal memproses distribution.',
            };

            return back()->with('error', $error);
        }

        try {
            AuditLogService::update(
                'Pembayaran',
                'Distribution pembayaran ditandai sudah dicairkan (manual)',
                [
                    'payment_distribution_id' => $distribution->id,
                    'pembayaran_id' => $distribution->pembayaran_id,
                    'booking_id' => $distribution->booking_id,
                    'owner_amount' => $distribution->owner_amount,
                ]
            );

            NotificationService::distributionProcessed(
                $distribution->owner_id,
                $distribution->booking?->booking_code ?? '-',
                (float) $distribution->owner_amount
            );
        } catch (\Exception $e) {
            \Log::warning('Distribution processed notification/audit failed: '.$e->getMessage());
        }

        return back()->with('success', 'Distribution ditandai sudah dicairkan.');
    }
}
