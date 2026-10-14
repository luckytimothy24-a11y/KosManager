<?php

namespace App\Services;

use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\PayoutItem;
use App\Models\Pembayaran;
use App\Models\Setting;
use Carbon\CarbonInterface;

class SettlementService
{
    public function resolveCommissionForPembayaran(Kos $kos, Pembayaran $pembayaran): ?array
    {
        $paymentDate = $pembayaran->payment_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $agreement = ManagementAgreement::activeForKosOn((int) $kos->id, $paymentDate);
        if (! $agreement) {
            return null;
        }
        $rent = $pembayaran->rent_amount !== null ? (float) $pembayaran->rent_amount : (float) $pembayaran->amount;
        $penalty = $pembayaran->penalty_amount !== null ? (float) $pembayaran->penalty_amount : 0.0;
        $basis = $rent;
        $commission = 0.0;
        if ($agreement->commission_type === 'percent') {
            $commission = round($basis * (float) $agreement->commission_value / 100, 2);
        } else {
            $commission = round((float) $agreement->commission_value, 2);
        }

        return [
            'agreement' => $agreement,
            'gross_rent' => round($rent, 2),
            'penalty_excluded' => round($penalty, 2),
            'commission_type' => $agreement->commission_type,
            'commission_rate' => (float) $agreement->commission_value,
            'commission_amount' => $commission,
            'net_amount' => round($rent - $commission, 2),
        ];
    }

    public function resolveSettlementItems(Kos $kos, string $periodStart, string $periodEnd): array
    {
        $kosId = (int) $kos->id;

        $pembayarans = Pembayaran::with(['tagihan', 'booking.kos'])
            ->where('verification_status', Pembayaran::STATUS_APPROVED)
            ->whereBetween('payment_date', [$periodStart, $periodEnd])
            ->where(function ($q) use ($kosId) {
                $q->whereHas('penghuni.kos', fn ($qq) => $qq->whereKey($kosId))
                    ->orWhereHas('booking.kos', fn ($qq) => $qq->whereKey($kosId));
            })
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        $resolvedGroups = [];
        $needsAgreement = [];
        $alreadySettled = [];
        $grossTotal = 0.0;
        $penaltyTotal = 0.0;

        foreach ($pembayarans as $pembayaran) {
            if (PayoutItem::where('pembayaran_id', $pembayaran->id)->exists()) {
                $alreadySettled[] = $pembayaran->id;

                continue;
            }

            $resolved = $this->resolveCommissionForPembayaran($kos, $pembayaran);

            if (! $resolved) {
                $needsAgreement[] = $pembayaran->id;

                continue;
            }

            $resolvedGroups[] = array_merge(['pembayaran' => $pembayaran], $resolved);
            $grossTotal += $resolved['gross_rent'];
            $penaltyTotal += $resolved['penalty_excluded'];
        }

        $percentTotal = 0.0;
        $fixedCandidates = [];
        foreach ($resolvedGroups as $g) {
            if ($g['commission_type'] === 'percent') {
                $percentTotal += $g['commission_amount'];
            } else {
                $fixedCandidates[] = $g;
            }
        }

        $singleFixedValue = 0.0;
        $singleFixedAgreement = null;
        if (! empty($fixedCandidates)) {
            usort($fixedCandidates, function ($a, $b) {
                $da = $a['agreement']->effective_from;
                $db = $b['agreement']->effective_from;
                $sa = $da instanceof CarbonInterface ? $da->format('Y-m-d') : (string) $da;
                $sb = $db instanceof CarbonInterface ? $db->format('Y-m-d') : (string) $db;
                if ($sa === $sb) {
                    return $b['agreement']->id <=> $a['agreement']->id;
                }

                return $sb <=> $sa;
            });
            $chosen = $fixedCandidates[0];
            $singleFixedAgreement = $chosen['agreement'];
            $singleFixedValue = round((float) $singleFixedAgreement->commission_value, 2);
        }

        $commissionTotal = round($percentTotal + $singleFixedValue, 2);
        $netTotal = round($grossTotal - $commissionTotal, 2);

        $items = [];
        $fixedApplied = false;
        foreach ($resolvedGroups as $g) {
            $isFixed = $g['commission_type'] === 'fixed';
            $commissionAmount = $g['commission_amount'];
            if ($isFixed) {
                if (! $fixedApplied && $g['agreement']->id === $singleFixedAgreement->id) {
                    $commissionAmount = $singleFixedValue;
                    $fixedApplied = true;
                } else {
                    $commissionAmount = 0.0;
                }
            }

            $netAmount = round($g['gross_rent'] - $commissionAmount, 2);

            $items[] = [
                'pembayaran' => $g['pembayaran'],
                'gross_rent' => $g['gross_rent'],
                'penalty_excluded' => $g['penalty_excluded'],
                'commission_type' => $g['commission_type'],
                'commission_rate' => $g['commission_rate'],
                'commission_amount' => round($commissionAmount, 2),
                'net_amount' => $netAmount,
            ];
        }

        return [
            'items' => $items,
            'needs_agreement_ids' => $needsAgreement,
            'already_settled_ids' => $alreadySettled,
            'gross_total' => round($grossTotal, 2),
            'penalty_excluded_total' => round($penaltyTotal, 2),
            'commission_total' => $commissionTotal,
            'net_total' => $netTotal,
            'pembayaran_count' => count($items),
        ];
    }

    public function getDefaultCommission(): ?array
    {
        $setting = Setting::where('key', 'commission.default')->first();
        if (! $setting || ! $setting->value) {
            return null;
        }
        $decoded = json_decode($setting->value, true);
        if (! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }
}
