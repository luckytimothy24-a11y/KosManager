<?php

namespace App\Services;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SharedBillingService
{
    /**
     * Hitung jumlah unit billing berdasarkan tipe sewa dan periode.
     * Replika dari logic yang sudah ada di TagihanController.
     */
    public function calculateUnits(string $rentalType, Carbon $periodStart, Carbon $periodEnd): int
    {
        if ($rentalType === 'daily') {
            return (int) $periodStart->copy()->startOfDay()->diffInDays($periodEnd->copy()->startOfDay()) + 1;
        }

        if ($rentalType === 'weekly') {
            $days = (int) $periodStart->copy()->startOfDay()->diffInDays($periodEnd->copy()->startOfDay()) + 1;

            return (int) ceil($days / 7);
        }

        // monthly — ceiling months (sama dengan TagihanController::ceilingMonths)
        $months = ($periodEnd->year - $periodStart->year) * 12
            + ($periodEnd->month - $periodStart->month) + 1;

        return max($months, 1);
    }

    /**
     * Hitung total tagihan kamar berdasarkan harga dan periode.
     */
    public function calculateTotalRoomBill(Kamar $kamar, string $rentalType, Carbon $periodStart, Carbon $periodEnd): float
    {
        $price = match ($rentalType) {
            'daily' => (float) $kamar->daily_price,
            'weekly' => (float) $kamar->weekly_price,
            'monthly' => (float) $kamar->monthly_price,
            default => (float) $kamar->monthly_price,
        };

        $units = $this->calculateUnits($rentalType, $periodStart, $periodEnd);

        return round($price * $units, 2);
    }

    /**
     * Hitung share proporsional untuk satu kontrak.
     *
     * Default: rata (total / jumlah penghuni aktif).
     * Jika custom_share_amount di-set, gunakan nilai tersebut.
     *
     * @param  Collection<int, Kontrak>  $allKontraks  Semua kontrak aktif di kamar yang sama
     */
    public function calculateShare(Kontrak $kontrak, float $totalRoomBill, int $activeOccupants, $allKontraks = null): float
    {
        // Custom override takes precedence
        if ($kontrak->custom_share_amount !== null) {
            return (float) $kontrak->custom_share_amount;
        }

        if ($activeOccupants <= 0) {
            return 0.0;
        }

        // Equal split: total / active occupants
        // Use integer math to avoid floating-point drift
        $totalCents = (int) round($totalRoomBill * 100);
        $baseShareCents = (int) floor($totalCents / $activeOccupants);
        $remainderCents = $totalCents - ($baseShareCents * $activeOccupants);

        // Find this kontrak's position among all kontraks
        if ($allKontraks) {
            $position = $allKontraks->pluck('id')->search($kontrak->id);
            $shareCents = $baseShareCents + ($position < $remainderCents ? 1 : 0);
        } else {
            // Fallback: if we don't have the collection, just return base share
            $shareCents = $baseShareCents;
        }

        return round($shareCents / 100, 2);
    }

    /**
     * Validasi apakah custom shares menjumlah ke total room bill.
     *
     * @param  array<int, array{kontrak_id: int, share: float}>  $shares
     */
    public function validateCustomShares(float $totalRoomBill, array $shares): bool
    {
        $totalShares = 0.0;
        foreach ($shares as $item) {
            $totalShares += (float) $item['share'];
        }

        return abs($totalShares - $totalRoomBill) < 0.01;
    }

    /**
     * Hitung semua shares untuk penghuni aktif di kamar shared.
     *
     * Hanya penghuni dengan effectiveBillingMode === 'shared' yang masuk
     * perhitungan. Ini menjaga Kontrak sebagai source-of-truth (snapshot)
     * dan memungkinkan kamar shared-capable memiliki campuran personal/shared.
     * Fallback: jika tidak ada kontrak yang eksplisit shared tetapi caller
     * tetap meminta shared (pre-snapshot / legacy), gunakan semua activeKontraks
     * untuk kompatibilitas Phase A.
     *
     * @return array<int, array{kontrak_id: int, share: float, is_custom: bool}>
     */
    public function calculateAllShares(Kamar $kamar, float $totalRoomBill): array
    {
        $allActive = $kamar->activeKontraks()->with('kamar')->get();

        // Filter to shared effective mode
        $activeKontraks = $allActive->filter(function (Kontrak $k) {
            return $k->effectiveBillingMode() === 'shared';
        })->values();

        // Fallback for Phase A / legacy where kontrak billing_mode is null and
        // kamar is shared but effectiveBillingMode may not yet be considered:
        // jika tidak ada yang shared tetapi kamar sendiri mengizinkan shared,
        // tetap gunakan semua active untuk backward compatibility (tests Phase A
        // menggunakan KontrakFactory tanpa billing_mode).
        if ($activeKontraks->isEmpty() && ($kamar->billing_mode ?? 'personal') === 'shared') {
            // Jika semua kontrak memiliki billing_mode null, mereka efektif shared
            $hasAnyExplicit = $allActive->contains(fn (Kontrak $k) => $k->billing_mode !== null);
            if (! $hasAnyExplicit) {
                $activeKontraks = $allActive;
            }
        }

        $activeCount = $activeKontraks->count();

        if ($activeCount === 0) {
            return [];
        }

        // Separate custom and non-custom kontraks
        $customTotal = 0.0;
        $nonCustomKontraks = collect();

        foreach ($activeKontraks as $kontrak) {
            if ($kontrak->custom_share_amount !== null) {
                $customTotal += (float) $kontrak->custom_share_amount;
            } else {
                $nonCustomKontraks->push($kontrak);
            }
        }

        $remainingBill = $totalRoomBill - $customTotal;
        $nonCustomCount = $nonCustomKontraks->count();

        $results = [];
        foreach ($activeKontraks as $kontrak) {
            $isCustom = $kontrak->custom_share_amount !== null;

            if ($isCustom) {
                $share = (float) $kontrak->custom_share_amount;
            } else {
                // Equal split of the remaining amount among non-custom kontraks
                $share = $this->calculateShare($kontrak, $remainingBill, max($nonCustomCount, 1), $nonCustomKontraks);
            }

            $results[] = [
                'kontrak_id' => $kontrak->id,
                'share' => $share,
                'is_custom' => $isCustom,
            ];
        }

        // Balance: pastikan SUM(shares) == totalRoomBill
        $this->balanceShares($results, $totalRoomBill);

        return $results;
    }

    /**
     * Seimbangkan shares agar SUM(shares) == totalRoomBill.
     * Distribusikan remainder ke item pertama yang bukan custom.
     */
    private function balanceShares(array &$shares, float $totalRoomBill): void
    {
        $totalCents = (int) round($totalRoomBill * 100);
        $sumCents = 0;
        foreach ($shares as $item) {
            $sumCents += (int) round($item['share'] * 100);
        }

        $diff = $totalCents - $sumCents;
        if ($diff === 0) {
            return;
        }

        // Distribusikan selisih ke item pertama yang bukan custom
        foreach ($shares as &$item) {
            if (! $item['is_custom'] && $diff !== 0) {
                $adjustment = $diff > 0 ? 1 : -1;
                $item['share'] = round($item['share'] + ($adjustment / 100), 2);
                $diff -= $adjustment;
            }
            if ($diff === 0) {
                break;
            }
        }
        unset($item);
    }

    /**
     * Buat tagihan untuk seluruh penghuni aktif di kamar shared.
     *
     * @return Tagihan[] Tagihan yang berhasil dibuat
     */
    public function createSharedTagihans(
        Kamar $kamar,
        string $billType,
        string $periodStart,
        string $periodEnd,
        string $dueDate,
        float $discount = 0,
        float $penalty = 0,
    ): array {
        $pStart = Carbon::parse($periodStart);
        $pEnd = Carbon::parse($periodEnd);

        $activeKontraks = $kamar->activeKontraks()->with('penghuni.user')->get();

        if ($activeKontraks->isEmpty()) {
            return [];
        }

        $rentalType = $activeKontraks->first()->rental_type;
        $totalRoomBill = $this->calculateTotalRoomBill($kamar, $rentalType, $pStart, $pEnd);
        $activeCount = $activeKontraks->count();

        $allShares = $this->calculateAllShares($kamar, $totalRoomBill);

        $createdTagihans = [];
        foreach ($allShares as $shareData) {
            $kontrak = $activeKontraks->firstWhere('id', $shareData['kontrak_id']);

            // Duplicate guard — gunakan mekanisme yang sama dengan existing
            $duplicate = Tagihan::where('kontrak_id', $kontrak->id)
                ->where('period_start', $periodStart)
                ->where('period_end', $periodEnd)
                ->exists();

            if ($duplicate) {
                continue;
            }

            $shareAmount = $shareData['share'];
            $total = max(0, round($shareAmount - $discount + $penalty, 2));
            $billNumber = 'TB-'.strtoupper(Str::random(12));

            try {
                $tagihan = Tagihan::create([
                    'bill_number' => $billNumber,
                    'penghuni_id' => $kontrak->penghuni_id,
                    'kontrak_id' => $kontrak->id,
                    'kamar_id' => $kamar->id,
                    'bill_type' => $billType,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'subtotal' => $shareAmount,
                    'discount' => $discount,
                    'penalty' => $penalty,
                    'total' => $total,
                    'due_date' => $dueDate,
                    'status' => 'unpaid',
                    'active_billing_key' => Tagihan::billingKey($kontrak->id, $periodStart, $periodEnd),
                    'total_room_bill' => $totalRoomBill,
                ]);

                $createdTagihans[] = $tagihan;
            } catch (\Exception $e) {
                \Log::warning("Shared billing: gagal buat tagihan untuk kontrak {$kontrak->id}: ".$e->getMessage());
            }
        }

        return $createdTagihans;
    }

    /**
     * Recalculate shares untuk billing cycle baru (BUKAN retroaktif).
     * Hanya mempengaruhi tagihan yang masih 'unpaid'.
     */
    public function recalculateSharesForCycle(
        Kamar $kamar,
        string $periodStart,
        string $periodEnd,
    ): int {
        $pStart = Carbon::parse($periodStart);
        $pEnd = Carbon::parse($periodEnd);

        $activeKontraks = $kamar->activeKontraks()->get();

        if ($activeKontraks->isEmpty()) {
            return 0;
        }

        $rentalType = $activeKontraks->first()->rental_type;
        $totalRoomBill = $this->calculateTotalRoomBill($kamar, $rentalType, $pStart, $pEnd);
        $activeCount = $activeKontraks->count();

        $allShares = $this->calculateAllShares($kamar, $totalRoomBill);
        $updated = 0;

        foreach ($allShares as $shareData) {
            $tagihan = Tagihan::where('kontrak_id', $shareData['kontrak_id'])
                ->where('period_start', $periodStart)
                ->where('period_end', $periodEnd)
                ->where('status', 'unpaid')
                ->first();

            if ($tagihan) {
                $tagihan->update([
                    'subtotal' => $shareData['share'],
                    'total' => max(0, round($shareData['share'] - $tagihan->discount + $tagihan->penalty, 2)),
                    'total_room_bill' => $totalRoomBill,
                ]);
                $updated++;
            }
        }

        return $updated;
    }
}
