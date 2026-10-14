<?php

namespace App\Services;

use App\Models\Kos;
use App\Models\Payout;
use App\Models\PayoutAdjustment;
use App\Models\PayoutDisbursement;
use App\Models\PayoutItem;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SettlementReportService
{
    public function forOwner(User $owner): array
    {
        $kosIds = Kos::where('owner_id', $owner->id)->pluck('id');
        if ($kosIds->isEmpty()) {
            return $this->emptyReport();
        }

        $received = (float) Pembayaran::where('verification_status', Pembayaran::STATUS_APPROVED)
            ->where(function ($q) use ($kosIds) {
                $q->whereHas('penghuni.kos', fn ($qq) => $qq->whereIn('id', $kosIds))
                    ->orWhereHas('booking.kos', fn ($qq) => $qq->whereIn('id', $kosIds));
            })->sum(DB::raw('COALESCE(rent_amount, amount)'));

        $penaltyExcluded = (float) Pembayaran::where('verification_status', Pembayaran::STATUS_APPROVED)
            ->where(function ($q) use ($kosIds) {
                $q->whereHas('penghuni.kos', fn ($qq) => $qq->whereIn('id', $kosIds))
                    ->orWhereHas('booking.kos', fn ($qq) => $qq->whereIn('id', $kosIds));
            })->sum(DB::raw('COALESCE(penalty_amount, 0)'));

        $payoutIds = Payout::whereIn('kos_id', $kosIds)->pluck('id');

        $commission = (float) PayoutItem::whereIn('payout_id', $payoutIds)->sum('commission_amount');
        $netObligation = (float) PayoutItem::whereIn('payout_id', $payoutIds)->sum('net_amount');

        $adjustmentsNet = 0.0;
        if ($payoutIds->isNotEmpty()) {
            $adjustmentsNet = (float) PayoutAdjustment::whereIn('payout_id', $payoutIds)
                ->selectRaw('COALESCE(SUM(commission_delta - amount),0) as net')
                ->value('net');
        }
        $orphanAdjustments = (float) PayoutAdjustment::whereNull('payout_id')
            ->whereHas('pembayaran', function ($q) use ($kosIds) {
                $q->where(function ($qq) use ($kosIds) {
                    $qq->whereHas('penghuni.kos', fn ($x) => $x->whereIn('id', $kosIds))
                        ->orWhereHas('booking.kos', fn ($x) => $x->whereIn('id', $kosIds));
                });
            })->selectRaw('COALESCE(SUM(commission_delta - amount),0) as net')->value('net');
        $adjustmentsNet += $orphanAdjustments;

        $orphanByKos = [];
        $orphanRows = PayoutAdjustment::whereNull('payout_id')
            ->whereHas('pembayaran', function ($q) use ($kosIds) {
                $q->where(function ($qq) use ($kosIds) {
                    $qq->whereHas('penghuni.kos', fn ($x) => $x->whereIn('id', $kosIds))
                        ->orWhereHas('booking.kos', fn ($x) => $x->whereIn('id', $kosIds));
                });
            })
            ->with(['pembayaran.penghuni.kos', 'pembayaran.booking.kos'])
            ->get();
        foreach ($orphanRows as $adj) {
            $pembayaran = $adj->pembayaran;
            if (! $pembayaran) {
                continue;
            }
            $kosId = null;
            if ($pembayaran->penghuni && $pembayaran->penghuni->kos) {
                $kosId = (int) $pembayaran->penghuni->kos->id;
            } elseif ($pembayaran->booking && $pembayaran->booking->kos) {
                $kosId = (int) $pembayaran->booking->kos->id;
            }
            if ($kosId === null || ! $kosIds->contains($kosId)) {
                continue;
            }
            $orphanByKos[$kosId] = ($orphanByKos[$kosId] ?? 0.0) + ((float) $adj->commission_delta - (float) $adj->amount);
        }

        $payoutIdsWithDisbursements = $payoutIds->isNotEmpty()
            ? PayoutDisbursement::whereIn('payout_id', $payoutIds)->distinct()->pluck('payout_id')
            : collect();
        $payoutIdsWithoutDisbursements = $payoutIds->diff($payoutIdsWithDisbursements);
        $verified = (float) PayoutDisbursement::whereIn('payout_id', $payoutIdsWithDisbursements)->where('status', 'verified')->sum('amount')
            + (float) Payout::whereIn('id', $payoutIdsWithoutDisbursements)->where('status', Payout::STATUS_VERIFIED)->sum('amount');
        $pendingTransfer = (float) PayoutDisbursement::whereIn('payout_id', $payoutIdsWithDisbursements)->where('status', 'pending')->sum('amount')
            + (float) Payout::whereIn('id', $payoutIdsWithoutDisbursements)->where('status', Payout::STATUS_TRANSFERRED)->sum('amount');
        $rejected = (float) PayoutDisbursement::whereIn('payout_id', $payoutIdsWithDisbursements)->whereIn('status', ['rejected', 'failed'])->sum('amount')
            + (float) Payout::whereIn('id', $payoutIdsWithoutDisbursements)->where('status', Payout::STATUS_REJECTED)->sum('amount');

        $remainingRaw = $netObligation + $adjustmentsNet - $verified;
        $remainingDisplay = max(0, $remainingRaw);
        $creditBalance = max(0, -$remainingRaw);

        $perKos = [];
        foreach (Kos::whereIn('id', $kosIds)->get() as $kos) {
            $kosPayoutIds = Payout::where('kos_id', $kos->id)->pluck('id');
            $kosNet = (float) PayoutItem::whereIn('payout_id', $kosPayoutIds)->sum('net_amount');
            $kosCommission = (float) PayoutItem::whereIn('payout_id', $kosPayoutIds)->sum('commission_amount');
            $kosReceived = (float) Pembayaran::where('verification_status', Pembayaran::STATUS_APPROVED)
                ->where(function ($q) use ($kos) {
                    $q->whereHas('penghuni.kos', fn ($qq) => $qq->whereKey($kos->id))
                        ->orWhereHas('booking.kos', fn ($qq) => $qq->whereKey($kos->id));
                })->sum(DB::raw('COALESCE(rent_amount, amount)'));
            $settlementForKos = app(SettlementService::class)->resolveSettlementItems($kos, '2000-01-01', now()->format('Y-m-d'));
            $kosAdjustmentsNet = 0.0;
            if ($kosPayoutIds->isNotEmpty()) {
                $kosAdjustmentsNet = (float) PayoutAdjustment::whereIn('payout_id', $kosPayoutIds)->selectRaw('COALESCE(SUM(commission_delta - amount),0) as net')->value('net');
            }
            $kosAdjustmentsNet += (float) ($orphanByKos[$kos->id] ?? 0.0);
            $kosWith = $kosPayoutIds->intersect($payoutIdsWithDisbursements);
            $kosWithout = $kosPayoutIds->diff($kosWith);
            $kosVerified = (float) PayoutDisbursement::whereIn('payout_id', $kosWith)->where('status', 'verified')->sum('amount')
                + (float) Payout::whereIn('id', $kosWithout)->where('status', Payout::STATUS_VERIFIED)->sum('amount');
            $kosPending = (float) PayoutDisbursement::whereIn('payout_id', $kosWith)->where('status', 'pending')->sum('amount')
                + (float) Payout::whereIn('id', $kosWithout)->where('status', Payout::STATUS_TRANSFERRED)->sum('amount');
            $kosRemainingRaw = $kosNet + $kosAdjustmentsNet - $kosVerified;
            $perKos[] = [
                'kos' => $kos,
                'received' => $kosReceived,
                'commission' => $kosCommission,
                'net' => $kosNet,
                'adjustments_net' => $kosAdjustmentsNet,
                'verified' => $kosVerified,
                'pending' => $kosPending,
                'remaining_raw' => $kosRemainingRaw,
                'remaining_display' => max(0, $kosRemainingRaw),
                'credit_balance' => max(0, -$kosRemainingRaw),
                'needs_agreement' => count($settlementForKos['needs_agreement_ids']),
            ];
        }

        return [
            'received' => $received,
            'penalty_excluded' => $penaltyExcluded,
            'commission' => $commission,
            'net_obligation' => $netObligation,
            'adjustments_net' => $adjustmentsNet,
            'payout_verified' => $verified,
            'payout_pending' => $pendingTransfer,
            'payout_rejected' => $rejected,
            'remaining_raw' => $remainingRaw,
            'remaining_display' => $remainingDisplay,
            'credit_balance' => $creditBalance,
            'per_kos' => $perKos,
        ];
    }

    private function emptyReport(): array
    {
        return ['received' => 0, 'penalty_excluded' => 0, 'commission' => 0, 'net_obligation' => 0, 'adjustments_net' => 0, 'payout_verified' => 0, 'payout_pending' => 0, 'payout_rejected' => 0, 'remaining_raw' => 0, 'remaining_display' => 0, 'credit_balance' => 0, 'per_kos' => []];
    }
}
